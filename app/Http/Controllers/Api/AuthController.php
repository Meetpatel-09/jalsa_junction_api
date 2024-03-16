<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

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
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string',
            // 'date_of_birth'=> 'required|string'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            // 'date_of_birth'=> $fields['date_of_birth']
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
            'email' => 'required|string',
            'password' => 'required|string',
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

    public function getProfile(Request $request)
    {

        $id = $request->user()->id;

        $user = User::where("id", $id)->first();

        return response()->json($user);
    }

    public function updateProfile(Request $request, User $user)
    {

        $id = $request->user()->id;

        $user = User::where("id", $id)->first();

        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'bio' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|string',
            'profile_pic_url' => 'mimes:png,jpg,jpeg,gif'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if ($request->hasFile('profile_pic_url')) {
            $img = $request->file('profile_pic_url');
            $ext = $img->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;
            $img->move(public_path() . '/profile', $imageName);

            $user->update($request->only('name', 'bio', 'date_of_birth'));
            $imageName = 'http://127.0.0.1:8000/api/images/' . $imageName;
            $user->update([
                'profile_pic_url' => $imageName,
            ]);

            return response()->json(['message' => 'Profile updated successfully.']);
        }

        // Update the user's profile
        $user->update($request->only('name', 'bio', 'date_of_birth'));

        return response()->json(['message' => 'Profile updated successfully.']);
    }

    public function images($filename) {
        $path = storage_path('..\\public\\profile\\' . $filename);
        return response()->file($path);
    }
}
