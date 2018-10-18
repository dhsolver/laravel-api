<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\Http\Requests\SignupRequest;
use App\Http\Resources\UserSessionResource;
use Laravel\Socialite\Facades\Socialite;
use App\Events\UserWasRegistered;

class AuthController extends Controller
{
    /**
     * Validate user credentials and return JWT token
     *
     * @param LoginRequest $req
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $req)
    {
        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($req->validated())) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json([
            'user' => new UserSessionResource(auth()->user()),
            'token' => $token,
        ]);
    }

    /**
     * Register user and return JWT token
     *
     * @param SignupRequest $req
     * @return \Illuminate\Http\Response
     */
    public function signup(SignupRequest $req)
    {
        $data = $req->validated();

        $attributes = [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => bcrypt($data['password']),
        ];

        switch ($req->role) {
            case 'client':
                $user = \App\Client::create($attributes);
                break;
            case 'user':
            default:
                $user = \App\MobileUser::create($attributes);
                break;
        }

        event(new UserWasRegistered($user));

        return $this->createTokenForUser($user);
    }

    /**
     * Creates a JWT for the given User model and returns the user/token response.
     *
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function createTokenForUser($user)
    {
        try {
            if (! $token = JWTAuth::fromUser($user)) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json([
            'user' => new UserSessionResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Get the current user session
     *
     * @return \Illuminate\Http\Response
     */
    public function userSession()
    {
        return response()->json(new UserSessionResource(auth()->user()));
    }

    /**
     * Handles user authentication using Facebook access token.
     *
     * @param FacebookLoginRequestequest $request
     * @return \Illuminate\Http\Response
     */
    public function facebook()
    {
        try {
            $facebook = Socialite::driver('facebook')->userFromToken(request()->token);
        } catch (\Exception $ex) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        // make sure fb object has the required data
        if (empty($facebook->email) || empty($facebook->id) || empty($facebook->token)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        // first check if user is already linked to Facebook
        $user = User::findByFacebookId($facebook->id);

        // then check if the user exists with the same email
        if (empty($user)) {
            $user = User::where('email', $facebook->email)->first();
        }

        // if still no user, create one
        if (empty($user)) {
            $attributes = [
                'name' => $facebook->name,
                'email' => $facebook->email,
                'fb_id' => $facebook->id,
                'fb_token' => $facebook->token,
                'password' => bcrypt($facebook->token),
            ];

            switch (request()->role) {
                case 'client':
                    $user = \App\Client::create($attributes);
                    break;
                case 'user':
                default:
                    $user = \App\MobileUser::create($attributes);
                    break;
            }
        } else {
            $user->update([
                'fb_id' => $facebook->id,
                'fb_token' => $facebook->token,
            ]);
        }

        return $this->createTokenForUser($user);
    }
}
