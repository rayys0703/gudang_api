<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get credentials from request
        $credentials = $request->only('email', 'password');

        //if auth failed
        if(!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        //if auth success
        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api')->user(),    
            'token'   => $token   
        ], 200);
    }

    public function register(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required'
        ], [
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'name.min' => 'Nama minimal harus :min karakter.',
            'email.min' => 'Email minimal harus :min karakter.',
            'password.min' => 'Password minimal harus :min karakter.'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }
        //create user
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password)
        ]);

        //return response JSON user is created
        if($user) {
            return response()->json([
                'success' => true,
                'user'    => $user,  
            ], 201);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 400);
    }

    public function logout(Request $request)
    {        
        //remove token
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        if($removeToken) {
            //return response JSON
            return response()->json([
                'success' => true,
                'message' => 'Logout Berhasil!',  
            ]);
        }
    }

    public function me()
    {
        try {
            // Mendapatkan user dari token yang valid
            $user = auth()->guard('api')->user();

            // Jika user ditemukan, kembalikan informasi user
            return response()->json([
                'success' => true,
                'user' => $user,
            ], 200);

        } catch (TokenExpiredException $e) {
            // Jika token kadaluarsa
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
            ], 401);

        } catch (TokenInvalidException $e) {
            // Jika token tidak valid
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
            ], 401);

        } catch (JWTException $e) {
            // Jika token tidak ditemukan
            return response()->json([
                'success' => false,
                'message' => 'Token is required',
            ], 401);
        }
    }

    // /**
    //  * Register a new user.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8|confirmed',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     $token = $user->createToken('Personal Access Token')->plainTextToken;

    //     return response()->json([
    //         'token' => $token,
    //         'user_id' => $user->id, // Mengembalikan ID pengguna
    //     ], 201);
    // }

    // /**
    //  * Login a user.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     if (!Auth::attempt($credentials)) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $user = Auth::user();
    //     $token = $user->createToken('Personal Access Token')->plainTextToken;

    //     return response()->json(['token' => $token]);
    // }

    // /**
    //  * Logout a user.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function logout(Request $request)
    // {
    //     $user = $request->user();
    //     $user->tokens()->delete(); // Menghapus semua token

    //     return response()->json(['message' => 'Logged out successfully'], 200);
    // }

    // public function user(Request $request)
    // {
    //     return response()->json($request->user());
    // }
}
