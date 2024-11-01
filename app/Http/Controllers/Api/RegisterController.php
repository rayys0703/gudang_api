<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User successfully registered',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'message' => 'User successfully logged in',
            'token' => $token,
            'user' => Auth::user(),
        ]);
    }

    public function getUserData()
    {
        $user = Auth::user()->load('roles.permissions');
        return response()->json([
            //'user' => $user,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->roles->flatMap(function ($role) {
                return $role->permissions->pluck('name');
            })->unique(), // Nama permissions
        ]);
    }

}

// namespace App\Http\Controllers\Api;
   
// use Illuminate\Http\Request;
// use App\Http\Controllers\Api\BaseController as BaseController;
// use App\Models\User;
// use Illuminate\Support\Facades\Auth;
// use Validator;
// use Illuminate\Http\JsonResponse;
   
// class RegisterController extends BaseController
// {
//     /**
//      * Register api
//      *
//      * @return \Illuminate\Http\Response
//      */
//     public function register(Request $request): JsonResponse
//     {
//         $validator = Validator::make($request->all(), [
//             'name' => 'required',
//             'email' => 'required|email|unique:users,email',
//             'password' => 'required',
//             'c_password' => 'required|same:password',
//         ]);
   
//         if($validator->fails()){
//             return $this->sendError('Validation Error.', $validator->errors());       
//         }
   
//         $input = $request->all();
//         $input['password'] = bcrypt($input['password']);
//         $user = User::create($input);
//         $success['token'] =  $user->createToken('MyApp')->plainTextToken;
//         $success['name'] =  $user->name;
   
//         return $this->sendResponse($success, 'User register successfully.');
//     }   
    
//     /**
//      * Login api
//      *
//      * @return \Illuminate\Http\Response
//      */
//     public function login(Request $request): JsonResponse
//     {
//         if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) { 
//             $user = Auth::user(); 
//             $success['token'] = $user->createToken('MyApp')->plainTextToken; 
//             $success['name'] = $user->name;
            
//             // Mengambil daftar permissions berdasarkan role user
//             $permissions = $user->getAllPermissions()->pluck('name')->toArray();
//             $success['permissions'] = $permissions;

//             return $this->sendResponse($success, 'User login successfully.');
//         } else { 
//             return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
//         } 
//     }

//     public function getUserData(Request $request)
//     {
//         $user = $request->user()->load(['roles', 'roles.permissions']);

//         // Menyusun daftar permissions unik yang dimiliki user berdasarkan role
//         $permissions = $user->roles->flatMap(function ($role) {
//             return $role->permissions->pluck('name');
//         })->unique();

//         return response()->json([
//             'id' => $user->id,
//             'name' => $user->name,
//             'email' => $user->email,
//             'roles' => $user->roles->pluck('name'), // Mengambil nama role
//             'permissions' => $permissions->values(), // Mengambil nama permission secara unik
//         ]);
//     }

// }