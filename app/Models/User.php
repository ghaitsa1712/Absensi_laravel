<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',       // ✅ Allows mass assignment of the name
        'email',
        'password',
        'role',
    ];

    public function user()
{
    return $this->belongsTo(User::class);
}

}
