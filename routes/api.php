<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ProduitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register/client', [AuthController::class, 'register_client']);
Route::post('/login/client', [AuthController::class, 'login_client']);

Route::post('/register/magasin', [AuthController::class, 'register_magasin']);
Route::post('/login/magasin', [AuthController::class, 'login_magasin']);

Route::post('/ajout/categorie', [CategorieController::class, 'ajout_categorie']);
Route::get('/categories', [CategorieController::class, 'categories']);
Route::get('/categorie/{id}', [CategorieController::class, 'categorie']);


Route::middleware('auth:sanctum')->group(function(){
    Route::post('/ajout/produit', [ProduitController::class, 'ajout_produit']);
    Route::get('/produits/magasin', [ProduitController::class, 'produits_magasin']);
    Route::post('/update/produit/{id}', [ProduitController::class, 'update_produit']);
    Route::post('/delete/produit/{id}', [ProduitController::class, 'delete_produit']);

    Route::post('/passer/commande', [CommandeController::class, 'passer_commande']);
    Route::get('/commande/client', [CommandeController::class, 'commandes_client']);
    Route::get('/commande/magasin', [CommandeController::class, 'commandes_magasin']);

    Route::post('/changer/livree/{id}', [CommandeController::class, 'changer_statut_livree']);
    Route::post('/changer/annulee/{id}', [CommandeController::class, 'changer_statut_annulee']);

    Route::get('/nombre/client', [AuthController::class, 'nombre_clients']);
});

Route::get('/produits', [ProduitController::class, 'produits']);
Route::get('/produit/{id}', [ProduitController::class, 'produit']);
