<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

// class Admin extends Model
class Admin extends Authenticatable

{
    use \App\Models\Concerns\Recoverable;

    use \Illuminate\Notifications\Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
}
