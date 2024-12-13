<?php


namespace App\Http\Controllers;

use App\Core\Hash;
use App\Core\Request;
use App\Models\User;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $message = "Welcome to my custom php mvc framework";
        return view('home', compact('message'));
    }


    public function users($id)
    {
        $user = User::find($id);
        return response()->json(['user' => $user]);
    }


    public function login()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.signup');
    }


    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);


        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->back()->with('message', 'Account Created Successfully');
    }
}
