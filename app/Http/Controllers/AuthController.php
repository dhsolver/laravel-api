<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\Http\Requests\SignupRequest;

class AuthController extends Controller
{
    /**
     * Validate user credentials and return JWT token
     *
     * @param LoginRequest $req
     * @return void
     */
    public function login(LoginRequest $req)
    {
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($req->validated())) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json([
            'user' => auth()->user(),
            'token' => $token,
        ]);
    }

    /**
     * Register user and return JWT token
     *
     * @param SignupRequest $req
     * @return void
     */
    public function signup(SignupRequest $req)
    {
        $data = $req->validated();

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ];

        switch ($data['role']) {
            case 'client':
                $user = \App\Client::create($attributes);
                break;
            case 'user':
            default:
                $user = \App\MobileUser::create($attributes);
                break;
        }

        // $user->assignRole($data['role']);

        try {
            if (!$token = JWTAuth::fromUser($user)) {
                return response()->json(['error' => 'could_not_create_token'], v);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Get the current user session
     *
     * @return void
     */
    public function userSession()
    {
        return response()->json(auth()->user());
    }
}
