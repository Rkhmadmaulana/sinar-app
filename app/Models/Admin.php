<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin'; // pakai nama tabel lama kamu

    protected $fillable = [
        'usere', 'passworde',
    ];

    protected $hidden = [
        'passworde',
        'remember_token',
    ];

    // Override default password field agar Auth bisa pakai
    public function getAuthPassword()
    {
        return $this->passworde;
    }

    // Override field username kalau pakai 'usere' bukan 'email'
    public function getAuthIdentifierName()
    {
        return 'usere';
    }
}
