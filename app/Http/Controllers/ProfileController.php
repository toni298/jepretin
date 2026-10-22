<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return Inertia::render('Profile/Edit', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $validated = $request->validated();
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (isset($validated['new_password']) && $validated['new_password'] !== '') {
            $user->password = $validated['new_password'];
        }

        $user->save();

        return redirect()->route('profile.edit');
    }
}
