<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $fillable = [
        'commande_number',
        'client_id',
        'magasin_id',
        'date_commande',
        'statut',
        'adresse_livraison'
    ];
}
