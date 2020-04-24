<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:5',
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        if ($user->save()) {
            $user->signin = [
                'hred' => 'api/v1/user/signin',
                'method' => 'POST',
                'params' => 'email', 'password',
            ];
            $response = [
                'msg' => 'User created',
                'user' => $user,
            ];
            return response()->json($response, 200);
        }
        //api without db
        // $user = [
        //     'name' => $name,
        //     'email' => $email,
        //     'password' => $password,
        //     'signin' => [
        //         'hred' => 'api/v1/user/signin',
        //         'method' => 'POST',
        //         'params' => 'email', 'password',
        //     ]
        // ];

        $response = [
            'msg' => 'An error accurred',
            'user' => $user,
        ];

        return response()->json($response, 404);
    }


    public function signin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        // $password = $request->input('password');
        // $email = $request->input('email');

        try {
            if (! $token = JWTAuth::attempt($credentials)){
                return response()->json(['msg' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['msg' => 'Could not find token'], 500);
        }

        return response()->json(['token' => $token]);

        // delete when we are using JWT
        // $user = [
        //     'name' => 'Name',
        //     'email' => $email,
        //     'password' => $password,
        // ];

        // $response = [
        //     'msg' => 'User signed in',
        //     'user' => $user,
        // ];

    }
}
