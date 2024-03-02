<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(User::latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name'=> 'required|string',
            'email'=> 'required|string|unique:users,email',
            'password'=> 'required|string',
            'date_of_birth'=> 'required|string'
        ]);

        $user = User::create([
            'name'=> $fields['name'],
            'email'=> $fields['email'],
            'password'=> bcrypt($fields['password']),
            'date_of_birth'=> $fields['date_of_birth']
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $data = [
            "user" => $user,
            "token" => $token,
        ];
        return response()->json($data, 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email'=> 'required|string',
            'password'=> 'required|string',
        ]);


        $user = User::where('email', $fields['email'])->first();
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            $data = [
                "message" => "Invalid Email or Password",
            ];
            return response()->json($data, 401);
        }


        $token = $user->createToken('myapptoken')->plainTextToken;

        $data = [
            "user" => $user,
            "token" => $token,
        ];
        return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
