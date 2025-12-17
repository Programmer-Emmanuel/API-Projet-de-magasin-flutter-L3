<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategorieController extends Controller
{
    public function ajout_categorie(Request $request){
        $validator = Validator::make($request->all(),[
            'nom' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $categorie = new Categorie();
            $categorie->nom = $request->nom;
            $categorie->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $categorie->id,
                    'nom' => $categorie->nom
                ],
                'message' => 'Categorie ajoutée'
            ]);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’ajout de la categorie',
                'erreur' => $e->getMessage() 
            ],500);
        }
    }

    public function categories(){
        try{
            $categorie = Categorie::all();
            if($categorie->isEmpty()){
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Aucune catégorie trouvée'
                ],200);
            }

            return response()->json([
                'success' => true,
                'data' => $categorie,
                'message' => 'Categorie affichée avec succès'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’affichage des categories',
                'erreur' => $e->getMessage()
            ],500);
        }
    }

    public function categorie($id){
        try{
            $categorie = Categorie::find($id);
            if(!$categorie){
                return response()->json([
                    'success' => false,
                    'message' => 'Categorie introuvable'
                ],404);
            }
            return response()->json([
                'success' => true,
                'data' => $categorie,
                'message' => 'Categorie affichée avec succès'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’affichage d’une categorie',
                'erreur' => $e->getMessage()
            ],500);
        }
    }
}
