<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => [
                Rule::requiredIf($request->filled('password')),
                'nullable',
                'current_password',
            ],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $oldValues = ['name' => $user->name, 'email' => $user->email];
        DB::transaction(function () use ($user, $validated, $activityLogger, $oldValues) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            if (filled($validated['password'] ?? null)) {
                $user->password = $validated['password'];
            }
            $user->save();

            $activityLogger->record(
                'updated',
                'profile',
                $user,
                $oldValues,
                ['name' => $user->name, 'email' => $user->email]
            );
        });

        return back()->with('status', 'Your profile was updated.');
    }
}
