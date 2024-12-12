<?php


namespace App\Http\Controllers;

use App\Models\User;

class HomeController
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
}
