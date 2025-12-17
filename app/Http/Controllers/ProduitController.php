<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Magasin;
use App\Models\Produit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProduitController extends Controller
{
    public function ajout_produit(Request $request){
        $validator = Validator::make($request->all(),[
            'nom' => 'required',
            'description' => 'min:5|max:300',
            'prix' => 'required|integer|min:1',
            'stock' => 'required|integer|min:1',
            'categorie_id' => 'required|integer'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $user = $request->user();
            $magasin = Magasin::find($user->id);
            if(!$magasin){
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin introuvable'
                ],404);
            }
            $categorie = Categorie::find($request->categorie_id);
            if(!$categorie){
                return response()->json([
                    'success' => false,
                    'message' => 'Categorie introuvable'
                ],404);
            }
            $produit = new Produit();
            $produit->nom = $request->nom;
            $produit->description = $request->description;
            $produit->prix = $request->prix;
            $produit->stock = $request->stock;
            $produit->categorie_id = $categorie->id;
            $produit->magasin_id = $user->id;
            $produit->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $produit->id,
                    'nom' => $produit->nom,
                    'description' => $produit->description,
                    'prix' => $produit->prix,
                    'stock' => $produit->stock,
                    'categorie' => [
                        'id' => $categorie->id,
                        'nom' => $categorie->nom
                    ],
                    'magasin' => [
                        'id' => $magasin->id,
                        'nom' => $magasin->nom
                    ]
                ],
                'message' => 'Produit ajouté avec succès'
            ]);

        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’ajout de produit',
                'erreur' => $e->getMessage()
            ],500);
        }
    }

    public function produits_magasin(Request $request){
        try{
            $user = $request->user();
            $magasin = Magasin::find($user->id);

            if(!$magasin){
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin introuvable'
                ],404);
            }

            $produits = Produit::where('magasin_id', $magasin->id)->get();

            $produits = $produits->map(function($produit){
                $categorie = Categorie::find($produit->categorie_id);
                $magasin = Magasin::find($produit->magasin_id);

                return [
                    'id' => $produit->id,
                    'nom' => $produit->nom,
                    'description' => $produit->description,
                    'prix' => $produit->prix,
                    'stock' => $produit->stock,
                    'categorie' => [
                        'id' => $categorie->id,
                        'nom' => $categorie->nom
                    ],
                    'magasin' => [
                        'id' => $magasin->id,
                        'nom' => $magasin->nom
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $produits,
                'message' => 'Liste des produits du magasin affiche avec succès'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’affichage des produits',
                'erreur' => $e->getMessage()
            ],500);
        }
    }   


    public function produits(){
        try {
            $produits = Produit::all();

            $produits = $produits->map(function ($produit) {
                $categorie = Categorie::find($produit->categorie_id);
                $magasin = Magasin::find($produit->magasin_id);

                return [
                    'id' => $produit->id,
                    'nom' => $produit->nom,
                    'description' => $produit->description,
                    'prix' => $produit->prix,
                    'stock' => $produit->stock,
                    'categorie' => [
                        'id' => $categorie->id,
                        'nom' => $categorie->nom
                    ],
                    'magasin' => [
                        'id' => $magasin->id,
                        'nom' => $magasin->nom
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $produits,
                'message' => 'Liste de tous les produits affichée avec succès'
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’affichage des produits',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }

    public function produit($id){
        try {
            $produit = Produit::find($id);

            if (!$produit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit introuvable'
                ], 404);
            }

            $categorie = Categorie::find($produit->categorie_id);
            $magasin = Magasin::find($produit->magasin_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $produit->id,
                    'nom' => $produit->nom,
                    'description' => $produit->description,
                    'prix' => $produit->prix,
                    'stock' => $produit->stock,
                    'categorie' => [
                        'id' => $categorie->id,
                        'nom' => $categorie->nom
                    ],
                    'magasin' => [
                        'id' => $magasin->id,
                        'nom' => $magasin->nom
                    ]
                ],
                'message' => 'Produit récupéré avec succès'
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du produit',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }

    public function update_produit(Request $request, $id){
        $validator = Validator::make($request->all(),[
            'nom' => 'required',
            'description' => 'min:5|max:300',
            'prix' => 'required|integer|min:1',
            'stock' => 'required|integer|min:1',
            'categorie_id' => 'required|integer'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $user = $request->user();
            $magasin = Magasin::find($user->id);

            if(!$magasin){
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin introuvable'
                ],404);
            }

            $produit = Produit::where('id', $id)
                ->where('magasin_id', $magasin->id)
                ->first();

            if(!$produit){
                return response()->json([
                    'success' => false,
                    'message' => 'Produit introuvable ou non autorisé'
                ],404);
            }

            $categorie = Categorie::find($request->categorie_id);
            if(!$categorie){
                return response()->json([
                    'success' => false,
                    'message' => 'Categorie introuvable'
                ],404);
            }

            $produit->nom = $request->nom;
            $produit->description = $request->description;
            $produit->prix = $request->prix;
            $produit->stock = $request->stock;
            $produit->categorie_id = $categorie->id;
            $produit->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $produit->id,
                    'nom' => $produit->nom,
                    'description' => $produit->description,
                    'prix' => $produit->prix,
                    'stock' => $produit->stock,
                    'categorie' => [
                        'id' => $categorie->id,
                        'nom' => $categorie->nom
                    ],
                    'magasin' => [
                        'id' => $magasin->id,
                        'nom' => $magasin->nom
                    ]
                ],
                'message' => 'Produit mis à jour avec succès'
            ]);

        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du produit',
                'erreur' => $e->getMessage()
            ],500);
        }
    }


    public function delete_produit(Request $request, $id){
        try {
            $user = $request->user();
            $magasin = Magasin::find($user->id);

            if (!$magasin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin introuvable'
                ], 404);
            }

            $produit = Produit::where('id', $id)
                ->where('magasin_id', $magasin->id)
                ->first();

            if (!$produit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit introuvable ou non autorisé'
                ], 404);
            }

            $produit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé avec succès'
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du produit',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }



}
