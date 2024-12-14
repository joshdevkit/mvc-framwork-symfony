<?php


namespace App\Http\Controllers\Auth;

use App\Core\Auth;
use App\Core\Hash;
use App\Core\Redirector;
use App\Core\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): Response
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try {
            if (Auth::attempt($validated['email'], $validated['password'])) {
                return redirect()->to('/');
            } else {
                session(['errors' => ['email' => ['Invalid credentials provided']]]);
                Redirector::back()->send();
            }
        } catch (Exception $e) {
            return new Response($e->getMessage(), 404);
        }
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
        session(['message' => 'Account Created Successfully']);
        return redirect()->to('/');
    }


    public function profile()
    {
        return view('auth.profile.edit');
    }

    public function logout(): Response
    {
        Auth::destroy();
        return redirect()->to('/');
    }
}
