<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Candle extends Model
{
    protected $fillable = ['pair','timeframe','timestamp','open','high','low','close','volume'];

    protected $casts = [
        'timestamp' => 'datetime',
        'open'      => 'float',
        'high'      => 'float',
        'low'       => 'float',
        'close'     => 'float',
        'volume'    => 'integer',
    ];
}
