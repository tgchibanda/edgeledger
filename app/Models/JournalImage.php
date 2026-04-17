<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JournalImage extends Model
{
    protected $fillable = ['journal_id','timeframe','path'];
    public function journal() { return $this->belongsTo(Journal::class); }
}
