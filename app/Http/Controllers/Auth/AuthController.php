<?php


namespace App\Http\Controllers\Auth;

use App\Core\Auth;
use App\Core\Redirector;
use App\Core\Request;
use App\Http\Controllers\Controller;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    public function register()
    {
        return view('auth.signup');
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

    public function logout(): Response
    {
        Auth::logout();
        return redirect()->to('/');
    }
}
