<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class FirstAdminSetupController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (User::query()->exists()) {
            return redirect()->route('login');
        }

        return view('auth.setup');
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                'max:256',
                Password::min(12)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        try {
            $user = Cache::lock('edamuhanhub:first-admin-setup', 10)->block(5, function () use ($validated) {
                if (User::query()->exists()) {
                    abort(404);
                }

                return DB::transaction(function () use ($validated) {
                    $role = Role::query()->firstOrCreate(
                        ['name' => 'Administrator'],
                        ['description' => 'Manages weed species, users, imports, audit logs, and backups.']
                    );

                    return User::query()->create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => $validated['password'],
                        'role_id' => $role->id,
                        'is_active' => true,
                    ]);
                });
            });
        } catch (LockTimeoutException) {
            return back()->withErrors([
                'email' => 'Initial account setup is busy. Please try again.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $activityLogger->record('first_admin_created', 'users', $user);

        return redirect()->route('dashboard')->with('status', 'Administrator account created.');
    }
}
