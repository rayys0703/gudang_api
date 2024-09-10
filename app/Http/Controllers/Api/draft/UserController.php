<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request){
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $data = $request->all();
        $data['api_token'] = Str::random(100);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        if($user){
            return $user;
        }
        
        return $request->all();
    }
}
