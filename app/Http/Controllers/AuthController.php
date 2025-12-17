<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Magasin;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register_client(Request $request){
        $validator = Validator::make($request->all(),[
            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'required|unique:users',
            'adresse' => 'required', 
            'telephone' => 'required|digits:10|unique:users',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $client = new User();
            $client->nom = $request->nom;
            $client->prenom = $request->prenom;
            $client->email = $request->email;
            $client->adresse = $request->adresse;
            $client->telephone = $request->telephone;
            $client->password = Hash::make($request->password);
            $client->save();

            $token =  $client->createToken('token-client')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $client->id,
                    'nom' => $client->nom,
                    'prenom' => $client->prenom,
                    'email' => $client->email,
                    'adresse' => $client->adresse,
                    'telephone' => $client->telephone,
                    'created_at' => $client->created_at,
                    'token' => $token
                ],
                'message' => 'Client inscrit avec succès'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’inscription du client',
                'erreur' => $e->getMessage()
            ],500);
        }
    }

    public function login_client(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $client = User::where('email', $request->email)->first();
            if(!$client){
                return response()->json([
                    'success' => false,
                    'message' =>'Client non trouvé' 
                ],404);
            }

            if($client && Hash::check($request->password, $client->password)){
                $token =  $client->createToken('token-client')->plainTextToken;
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $client->id,
                        'nom' => $client->nom,
                        'prenom' => $client->prenom,
                        'email' => $client->email,
                        'adresse' => $client->adresse,
                        'telephone' => $client->telephone,
                        'created_at' => $client->created_at,
                        'token' => $token
                    ],
                    'message' => 'Client connecté avec succès'
                ],200);
            }
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion du client'
            ],500);
            
        }
    }

    public function register_magasin(Request $request){
        $validator = Validator::make($request->all(),[
            'nom' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $magasin = new Magasin();
            $magasin->nom = $request->nom;
            $magasin->email = $request->email;
            $magasin->password = Hash::make($request->password);
            $magasin->save();

            $token =  $magasin->createToken('token-magasin')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $magasin->id,
                    'nom' => $magasin->nom,
                    'email' => $magasin->email,
                    'created_at' => $magasin->created_at,
                    'token' => $token
                ],
                'message' => 'Magasin inscrit avec succès'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’inscription du magasin',
                'erreur' => $e->getMessage()
            ],500);
        }
    }

    
    public function login_magasin(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $magasin = Magasin::where('email', $request->email)->first();
            if(!$magasin){
                return response()->json([
                    'success' => false,
                    'message' =>'Magasin non trouvé' 
                ],404);
            }

            if($magasin && Hash::check($request->password, $magasin->password)){
                $token =  $magasin->createToken('token-magasin')->plainTextToken;
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $magasin->id,
                        'nom' => $magasin->nom,
                        'email' => $magasin->email,
                        'created_at' => $magasin->created_at,
                        'token' => $token
                    ],
                    'message' => 'Magasin connecté avec succès'
                ],200);
            }
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion du magasin'
            ],500);
        }
    }

    public function nombre_clients(Request $request){
        try {
            $magasin = $request->user();

            if (!$magasin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non authentifié'
                ], 401);
            }

            $nombreClients = Commande::where('magasin_id', $magasin->id)
                ->distinct('client_id')
                ->count('client_id');

            return response()->json([
                'success' => true,
                'data' => $nombreClients,
                'message' => 'Nombre de clients récupéré avec succès',
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des statistiques'
            ], 500);
        }
    }
}
