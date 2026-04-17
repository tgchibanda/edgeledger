<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TradingSession extends Model
{
    protected $fillable = ['name','open_time','close_time'];
}
