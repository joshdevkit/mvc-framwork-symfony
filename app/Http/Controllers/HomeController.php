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
        $message = "Welcome to Symfony Mvc Framework";
        $dev = "JoshDev - JP";
        return view('home', compact('message', 'dev'));
    }


    public function users($id)
    {
        $user = User::findOrFail($id);

        return response()->json(['user' => $user]);
    }

    public function test(Request $request)
    {
        if ($request->hasHeader('X-CSRF-TOKEN')) {
            return response()->json([
                'message' => 'CSRF validation passed!',
                'data' => $request->server->all()
            ]);
        }

        return response()->json(['error' => 'CSRF token not provided'], 400);
    }
}
