<?php

namespace App\Console\Commands;

use App\Models\Candle;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ImportCandles extends Command
{
    protected $signature   = 'candles:import {file : Path to Dukascopy CSV file} {pair : e.g. EURUSD} {timeframe : e.g. H1}';
    protected $description = 'Import OHLCV candle data from a Dukascopy CSV file';

    // Dukascopy CSV formats vary — we handle the most common ones:
    // Format A: DateTime,Open,High,Low,Close,Volume  (with header row)
    // Format B: Gmt time,Open,High,Low,Close,Volume  (Dukascopy JForex export)
    // Format C: timestamp,open,high,low,close,volume (unix timestamp, no header)

    public function handle(): int
    {
        $file      = $this->argument('file');
        $pair      = strtoupper($this->argument('pair'));
        $timeframe = strtoupper($this->argument('timeframe'));

        // Validate arguments
        if (!in_array($pair, ['EURUSD', 'GBPUSD', 'AUDUSD'])) {
            $this->error("Invalid pair: {$pair}. Must be EURUSD, GBPUSD, or AUDUSD.");
            return 1;
        }
        if (!in_array($timeframe, ['M1', 'M5', 'M15', 'M30', 'H1', 'H4', 'D1'])) {
            $this->error("Invalid timeframe: {$timeframe}. Must be M1, M5, M15, M30, H1, H4, or D1.");
            return 1;
        }
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Importing {$pair} {$timeframe} from {$file}");

        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error("Cannot open file.");
            return 1;
        }

        $inserted  = 0;
        $skipped   = 0;
        $errors    = 0;
        $batch     = [];
        $batchSize = 500;
        $lineNum   = 0;

        while (($line = fgets($handle)) !== false) {
            $lineNum++;
            $line = trim($line);
            if (empty($line)) continue;

            // Skip header rows
            if ($lineNum === 1 && !is_numeric(substr($line, 0, 1))) {
                $this->line("  Skipping header: {$line}");
                continue;
            }

            // Try to parse the line
            $parsed = $this->parseLine($line);
            if (!$parsed) {
                $errors++;
                if ($errors <= 3) $this->warn("  Could not parse line {$lineNum}: {$line}");
                continue;
            }

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

            if (count($batch) >= $batchSize) {
                $result = $this->insertBatch($batch);
                $inserted += $result['inserted'];
                $skipped  += $result['skipped'];
                $batch = [];
                $this->output->write("  Processed {$lineNum} lines...\r");
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            $result = $this->insertBatch($batch);
            $inserted += $result['inserted'];
            $skipped  += $result['skipped'];
        }

        fclose($handle);

        $this->newLine();
        $this->info("Done!");
        $this->table(['Metric', 'Count'], [
            ['Lines processed', $lineNum],
            ['Candles inserted', $inserted],
            ['Duplicates skipped', $skipped],
            ['Parse errors', $errors],
        ]);

        return 0;
    }

    private function insertBatch(array $batch): array
    {
        $inserted = 0;
        $skipped  = 0;

        foreach ($batch as $row) {
            try {
                $exists = Candle::where('pair',      $row['pair'])
                    ->where('timeframe', $row['timeframe'])
                    ->where('timestamp', $row['timestamp'])
                    ->exists();
                if ($exists) {
                    $skipped++;
                } else {
                    Candle::create($row);
                    $inserted++;
                }
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        return compact('inserted', 'skipped');
    }

    private function parseLine(string $line): ?array
    {
        // Try comma-separated first
        $parts = str_getcsv($line, ',');

        // Try semicolon if comma gave only 1 part
        if (count($parts) < 5) {
            $parts = str_getcsv($line, ';');
        }

        if (count($parts) < 6) return null;

        $parts = array_map('trim', $parts);

        /**
         * FORMAT HANDLING
         * ----------------------------------
         * Format D (your file):
         * Date, Time, Open, High, Low, Close, Volume
         * Example:
         * 2025.01.01,17:00,1.035030,1.035140,1.035030,1.035140,0
         */

        // Detect split date + time format
        if (preg_match('/^\d{4}\.\d{2}\.\d{2}$/', $parts[0]) && preg_match('/^\d{2}:\d{2}/', $parts[1])) {
            $dateTimeStr = $parts[0] . ' ' . $parts[1];

            try {
                $ts = Carbon::createFromFormat('Y.m.d H:i', $dateTimeStr, 'UTC');
            } catch (\Exception $e) {
                return null;
            }

            $open   = (float)$parts[2];
            $high   = (float)$parts[3];
            $low    = (float)$parts[4];
            $close  = (float)$parts[5];
            $volume = isset($parts[6]) ? (int)$parts[6] : 0;
        }
        // Existing Unix timestamp format
        elseif (is_numeric($parts[0]) && strlen($parts[0]) >= 10) {
            $ts     = Carbon::createFromTimestamp((int)$parts[0])->utc();
            $open   = (float)$parts[1];
            $high   = (float)$parts[2];
            $low    = (float)$parts[3];
            $close  = (float)$parts[4];
            $volume = isset($parts[5]) ? (int)$parts[5] : 0;
        }
        // Single datetime column formats
        else {
            $dateStr = $parts[0];
            $ts = null;

            $formats = [
                'd.m.Y H:i:s.v',
                'd.m.Y H:i:s',
                'Y.m.d H:i:s',
                'Y-m-d H:i:s',
                'Y-m-d H:i',
                'd/m/Y H:i:s',
                'Y/m/d H:i:s',
            ];

            foreach ($formats as $fmt) {
                try {
                    $ts = Carbon::createFromFormat($fmt, $dateStr, 'UTC');
                    if ($ts) break;
                } catch (\Exception $e) {
                }
            }

            if (!$ts) {
                try {
                    $ts = Carbon::parse($dateStr)->utc();
                } catch (\Exception $e) {
                    return null;
                }
            }

            $open   = (float)$parts[1];
            $high   = (float)$parts[2];
            $low    = (float)$parts[3];
            $close  = (float)$parts[4];
            $volume = isset($parts[5]) ? (int)$parts[5] : 0;
        }

        // Validation
        if ($open <= 0 || $high <= 0 || $low <= 0 || $close <= 0) return null;
        if ($high < $low) return null;

        return [
            'timestamp' => $ts->format('Y-m-d H:i:s'),
            'open'      => $open,
            'high'      => $high,
            'low'       => $low,
            'close'     => $close,
            'volume'    => $volume,
        ];
    }
}
