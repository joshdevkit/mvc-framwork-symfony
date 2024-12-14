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
        $user = User::find($id);

        dd($user);
    }
}
