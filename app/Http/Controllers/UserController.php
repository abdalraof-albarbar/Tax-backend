<?php

namespace App\Http\Controllers;
    use Illuminate\Http\Request;
use App\Models\User;  
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
  
    public function index()
    {
    $users = User::all();
    return response()->json($users);
    }
   

    public function store(Request $request)
    {   $request->validate([
        'name' => 'required|string|max:255',
        'email'=> 'required|string|email|max:255|unique:users',
        'password' =>Hash::make('required|string|min:8'),
        'role' => 'required|string|in:supervisor,admin,user',
        'is_active' => 'boolean',
        "must_change_password" => 'boolean',
    ]);
        $user = User::create($request->all());
        return response()->json($user, 201);
    }

  
    public function show(int $id)
    {
        $user = User::find($id);
        return response()->json($user, 200); 
    }


    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'=> 'string|max:255',
            'email'=> 'string|email|max:255|unique:users',
            'password'=> Hash::make('string|min:8'),
            'role'=> 'string|in:supervisor,admin,user',
            'is_active'=> 'boolean',
            'must_change_password'=> 'boolean',
            

        ]);
        $user = User::find($id);
        $user->update($request->all());
        return response()->json($user, 200);
    }

    
    public function destroy(int $id)
    {
        $user = User::find($id);
        $user->delete();
        return response()->json(['message' =>
         'User deleted successfully',
         'User' => $user], 204);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            //the same as User::where('email', $request->email)->firstOrFail();

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                "message" => 'Login successful',
                
                ],200);
            
            
        }
        return response()->json(['message'=> 'Invalid login credentials'], 401);
    }
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:supervisor,admin,user',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        return response()->json($user, 201);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
