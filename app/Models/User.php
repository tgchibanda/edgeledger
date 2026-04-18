<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','email','password','role','is_active'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];

    public function isSuperuser(): bool    { return $this->role === 'superuser'; }
    public function categories()           { return $this->hasMany(Category::class); }
    public function tradeDatabases()       { return $this->hasMany(TradeDatabase::class); }
    public function journals()             { return $this->hasMany(Journal::class); }
    public function pairs()                { return $this->hasMany(Pair::class); }
}
