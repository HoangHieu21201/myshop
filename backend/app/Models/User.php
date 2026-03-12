<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'fullName',
        'email',
        'phone',
        'password',
        'avatar_url',
        'status',
        'birthday',
        'gender',
        'google_id',
        'facebook_id',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 1 User có nhiều địa chỉ
    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id', 'id');
    }

    // Lấy địa chỉ mặc định của User
    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class, 'user_id', 'id')->where('is_default', 1);
    }
}