<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Penduduk extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'id_user', 
        'nama_lengkap', 
        'no_hp', 
        'alamat', 
        'jenis_kelamin', 
        'tempat_lahir',
        'tanggal_lahir', 
        'kebangsaan',
        'pekerjaan',
        'golongan_darah',
        'status_nikah',
        'foto_profil',
        'nik',
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

    protected $table = 'penduduk';
}