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

        dd($user);
    }
}
