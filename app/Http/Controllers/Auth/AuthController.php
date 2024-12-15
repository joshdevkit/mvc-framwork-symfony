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


    public function update(Request $request)
    {
        /**
         * @var App\Models\User;
         */
        $user = auth()->user();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            // 'avatar' => 'nullable|image|mimes:jpg,jpeg,jfif,png|max:2048',
        ]);

        //can support a array attributes
        /*
         * array attributes example
         * $user->update($validatedData);
         * 
         */

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        if ($user->update()) {
            session(['message' => 'Profile Updated Successfully']);
            return redirect()->back();
        }
    }


    public function update_avatar(Request $request)
    {
        // $userId = Auth::user()->id;

        // // Validate the input
        // $validated = $request->validate([
        //     'avatar' => 'nullable|image|mime:png,jpg,jfif,webp|max:2048'
        // ]);

        // if ($request->hasFile('avatar')) {
        //     $avatarFile = $validated['avatar'];
        //     $originalFilename = $avatarFile->getClientOriginalName();
        //     $name = 'profile_' . $userId . '_' . $originalFilename;

        //     // Define the path to store the avatar in the public folder
        //     $path = public_path('profile/avatars/');

        //     $avatarFile->move($path, $name);
        //     $avatarPath = 'profile/avatars/' . $name;

        //     // Load user record and update the avatar path

        //     /**
        //      * @var App\Models\User;
        //      */
        //     $user = User::findOrFail($userId);
        //     dd($user);
        //     $user->avatar = $avatarPath;

        //     if ($user->save()) {
        //         return redirect()->back()->with('success', 'Avatar updated successfully.');
        //     } else {
        //         return redirect()->back()->with('error', 'Failed to save avatar.');
        //     }
        // }

        // return redirect()->back()->with('info', 'No new avatar provided.');
        dd($request);
    }



    public function logout(): Response
    {
        Auth::destroy();
        return redirect()->to('/');
    }
}
