<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CandleImportController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file'      => 'required|file|mimes:csv,txt|max:102400', // 100MB max
            'pair'      => 'required|in:EURUSD,GBPUSD,AUDUSD',
            'timeframe' => 'required|in:M1,M5,M15,M30,H1,H4,D1',
        ]);

        // Store file in uploads/candles/
        $file = $request->file('file');
        $path = $file->store('candles', 'local');

        // Process immediately (for files up to ~50k rows this is fine in a request)
        $result = $this->processFile(
            Storage::disk('local')->path($path),
            $request->pair,
            $request->timeframe
        );

        // Clean up uploaded file after import
        Storage::disk('local')->delete($path);

        return response()->json([
            'success'   => true,
            'pair'      => $request->pair,
            'timeframe' => $request->timeframe,
            'inserted'  => $result['inserted'],
            'skipped'   => $result['skipped'],
            'errors'    => $result['errors'],
            'total'     => $result['inserted'] + $result['skipped'],
        ]);
    }

    private function processFile(string $filePath, string $pair, string $timeframe): array
    {
        $handle   = fopen($filePath, 'r');
        $inserted = 0;
        $skipped  = 0;
        $errors   = 0;
        $batch    = [];
        $lineNum  = 0;

        while (($line = fgets($handle)) !== false) {
            $lineNum++;
            $line = trim($line);
            if (empty($line)) continue;

            // Skip header row
            if ($lineNum === 1 && !is_numeric(substr(ltrim($line), 0, 1))) continue;

            $parsed = $this->parseLine($line);
            if (!$parsed) { $errors++; continue; }

            $batch[] = [
                'pair'       => $pair,
                'timeframe'  => $timeframe,
                'timestamp'  => $parsed['timestamp'],
                'open'       => $parsed['open'],
                'high'       => $parsed['high'],
                'low'        => $parsed['low'],
                'close'      => $parsed['close'],
                'volume'     => $parsed['volume'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                $r = $this->insertBatch($batch);
                $inserted += $r['inserted'];
                $skipped  += $r['skipped'];
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $r = $this->insertBatch($batch);
            $inserted += $r['inserted'];
            $skipped  += $r['skipped'];
        }

        fclose($handle);
        return compact('inserted', 'skipped', 'errors');
    }

    private function insertBatch(array $batch): array
    {
        $inserted = 0;
        $skipped  = 0;

        // Use upsert to handle duplicates efficiently
        try {
            $affected = Candle::upsert(
                $batch,
                ['pair', 'timeframe', 'timestamp'],
                ['open', 'high', 'low', 'close', 'volume', 'updated_at']
            );
            $inserted = count($batch);
        } catch (\Exception $e) {
            // Fallback: insert one by one
            foreach ($batch as $row) {
                try {
                    Candle::firstOrCreate(
                        ['pair' => $row['pair'], 'timeframe' => $row['timeframe'], 'timestamp' => $row['timestamp']],
                        $row
                    );
                    $inserted++;
                } catch (\Exception $e2) {
                    $skipped++;
                }
            }
        }

        return compact('inserted', 'skipped');
    }

    private function parseLine(string $line): ?array
    {
        $parts = str_getcsv($line, ',');
        if (count($parts) < 5) $parts = str_getcsv($line, ';');
        if (count($parts) < 5) return null;

        $parts = array_map('trim', $parts);

        if (is_numeric($parts[0]) && strlen($parts[0]) >= 10) {
            $ts = Carbon::createFromTimestamp((int)$parts[0])->utc();
        } else {
            $dateStr = $parts[0];
            $ts      = null;
            $formats = [
                'd.m.Y H:i:s.v', 'd.m.Y H:i:s', 'Y.m.d H:i:s',
                'Y-m-d H:i:s',   'Y-m-d H:i',   'd/m/Y H:i:s',
            ];
            foreach ($formats as $fmt) {
                try { $ts = Carbon::createFromFormat($fmt, $dateStr, 'UTC'); if ($ts) break; } catch (\Exception $e) {}
            }
            if (!$ts) {
                try { $ts = Carbon::parse($dateStr)->utc(); } catch (\Exception $e) { return null; }
            }
        }

        $open   = (float)$parts[1];
        $high   = (float)$parts[2];
        $low    = (float)$parts[3];
        $close  = (float)$parts[4];
        $volume = isset($parts[5]) ? (int)$parts[5] : 0;

        if ($open <= 0 || $high < $low) return null;

        return [
            'timestamp' => $ts->format('Y-m-d H:i:s'),
            'open'  => $open, 'high' => $high,
            'low'   => $low,  'close' => $close,
            'volume'=> $volume,
        ];
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'pair'      => 'required|in:EURUSD,GBPUSD,AUDUSD',
            'timeframe' => 'required|in:M1,M5,M15,M30,H1,H4,D1',
        ]);
        $deleted = Candle::where('pair', $request->pair)
                         ->where('timeframe', $request->timeframe)
                         ->delete();
        return response()->json(['deleted' => $deleted]);
    }
    public function stats()
    {
        $pairs      = ['EURUSD','GBPUSD','AUDUSD'];
        $timeframes = ['M1','M5','M15','M30','H1','H4','D1'];
        $stats      = [];

        foreach ($pairs as $pair) {
            foreach ($timeframes as $tf) {
                $count = Candle::where('pair', $pair)->where('timeframe', $tf)->count();
                if ($count > 0) {
                    $first = Candle::where('pair',$pair)->where('timeframe',$tf)->min('timestamp');
                    $last  = Candle::where('pair',$pair)->where('timeframe',$tf)->max('timestamp');
                    $stats[] = [
                        'pair'       => $pair,
                        'timeframe'  => $tf,
                        'count'      => $count,
                        'first'      => $first,
                        'last'       => $last,
                    ];
                }
            }
        }

        return response()->json($stats);
    }
}