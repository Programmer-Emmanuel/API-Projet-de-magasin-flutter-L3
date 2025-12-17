<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Magasin extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'nom',
        'email',
        'password',
    ];
}
