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
        // session(['message' => 'Account Created Successfully']);
        Auth::attempt($validated['email'], $validated['password']);
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
        $userId = auth()->user()->id;

        $validated = $request->validate([
            'avatar' => 'nullable|image|mime:png,jpg,jfif,webp|max:2048'
        ]);

        if ($request->hasFile('avatar')) {
            $avatarFile = $validated['avatar'];
            // $originalFilename = $avatarFile->getClientOriginalName();
            $extension = $avatarFile->getClientOriginalExtension();
            $name = 'profile_' . $userId . '_' . time() . '.' . $extension;


            $path = public_path('profile/avatars/');

            $avatarFile->move($path, $name);
            $avatarPath = 'profile/avatars/' . $name;
            /**
             * @var App\Models\User;
             */
            $user = User::findOrFail($userId);

            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            if ($user->save(['avatar' => $avatarPath])) {
                session(['message' => 'Avatar updated successfully.']);
                return redirect()->back();
            } else {
                session(['errors' => ['avatar' => ['Failed to updated']]]);
                return redirect()->back();
            }
        }
        session(['message' => 'No update was made.']);
        return redirect()->back();
    }



    public function logout(): Response
    {
        Auth::destroy();
        return redirect()->to('/');
    }
}
