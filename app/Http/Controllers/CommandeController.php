<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Magasin;
use App\Models\Produit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class CommandeController extends Controller
{
    public function passer_commande(Request $request){
        $validator = Validator::make($request->all(),[
            'produit_id' => 'required',
            'adresse_livraison' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $client = $request->user();
            if(!$client){
                return response()->json([
                    'success' => false,
                    'message' => 'Client non trouvé'
                ],404);
            }
            $produit = Produit::find($request->produit_id);
            if(!$produit){
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ],404);
            }
            if($produit->stock == 0){
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez plus commander.'
                ]);
            }
            $magasin = Magasin::find($produit->magasin_id);
            if(!$magasin){
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non trouvé'
                ],404);
            }

            $random = rand(0000,9999);
            $commande = new Commande();
            $commande->commande_number = 'CMD-'.$random.'-MAG';
            $commande->client_id = $client->id;
            $commande->produit_id = $produit->id;
            $commande->magasin_id = $magasin->id;
            $commande->adresse_livraison = $request->adresse_livraison;
            $commande->date_commande = Carbon::now();
            $commande->save();

            $produit->stock -= 1;
            $produit->save();
        
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $commande->id,
                    'commande_number' => $commande->commande_number,
                    'client' => [
                        'id' => $client->id,
                        'nom' => $client->nom,
                        'prenom' => $client->prenom,
                        'telephone' => $client->telephone,
                        'email' => $client->email
                    ],
                    'produit' => [
                        'id' => $produit->id,
                        'nom' => $produit->nom,
                        'prix' => $produit->prix
                    ],
                    'magasin' => [
                        'id' => $magasin->id,
                        'nom' => $magasin->nom,
                        'email' => $magasin->email
                    ],
                    'date_commande' => $commande->created_at
                ]
            ],200);
            
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la commande de produit',
                'erreur' => $e->getMessage()
            ],500);
        }
    }


    public function commandes_client(Request $request){
        try {
            $client = $request->user();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client non authentifié'
                ], 401);
            }

            $commandes = Commande::where('client_id', $client->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $data = [];

            foreach ($commandes as $commande) {

                $produit = Produit::find($commande->produit_id);
                $magasin = Magasin::find($commande->magasin_id);

                $data[] = [
                    'id' => $commande->id,
                    'commande_number' => $commande->commande_number,
                    'client' => [
                        'id' => $client->id,
                        'nom' => $client->nom,
                        'prenom' => $client->prenom,
                        'telephone' => $client->telephone,
                        'email' => $client->email
                    ],
                    'produit' => $produit ? [
                        'id' => $produit->id,
                        'nom' => $produit->nom,
                        'prix' => $produit->prix
                    ] : null,
                    'magasin' => $magasin ? [
                        'id' => $magasin->id,
                        'nom' => $magasin->nom,
                        'email' => $magasin->email
                    ] : null,
                    'date_commande' => $commande->created_at
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Commande affichée avec succès'
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des commandes',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }


    public function commandes_magasin(Request $request){
        try {
            $magasin = $request->user();

            if (!$magasin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non authentifié'
                ], 401);
            }

            $commandes = Commande::where('magasin_id', $magasin->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $data = [];

            foreach ($commandes as $commande) {

                $produit = Produit::find($commande->produit_id);
                $client  = \App\Models\User::find($commande->client_id);

                $data[] = [
                    'id' => $commande->id,
                    'commande_number' => $commande->commande_number,
                    'client' => $client ? [
                        'id' => $client->id,
                        'nom' => $client->nom,
                        'prenom' => $client->prenom,
                        'telephone' => $client->telephone,
                        'email' => $client->email
                    ] : null,
                    'produit' => $produit ? [
                        'id' => $produit->id,
                        'nom' => $produit->nom,
                        'prix' => $produit->prix
                    ] : null,
                    'magasin' => [
                        'id' => $magasin->id,
                        'nom' => $magasin->nom,
                        'email' => $magasin->email
                    ],
                    'date_commande' => $commande->created_at
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Commande affichée avec succès'
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des commandes',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }

    public function changer_statut_livree(Request $request, $id){
        try {
            $magasin = $request->user();

            if (!$magasin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non authentifié'
                ], 401);
            }

            $commande = Commande::where('id', $id)
                ->where('magasin_id', $magasin->id)
                ->first();

            if (!$commande) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande introuvable ou non autorisée'
                ], 403);
            }

            $commande->statut = 'Livrée';
            $commande->save();

            return response()->json([
                'success' => true,
                'message' => 'Commande livrée avec succès'
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la livraison de la commande'
            ], 500);
        }
    }


    public function changer_statut_annulee(Request $request, $id)
    {
        try {
            $magasin = $request->user();

            if (!$magasin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non authentifié'
                ], 401);
            }

            $commande = Commande::where('id', $id)
                ->where('magasin_id', $magasin->id)
                ->first();

            if (!$commande) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande introuvable ou non autorisée'
                ], 403);
            }

            $commande->statut = 'Annulée';
            $commande->save();

            return response()->json([
                'success' => true,
                'message' => 'Commande annulée avec succès'
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’annulation de la commande'
            ], 500);
        }
    }

}
