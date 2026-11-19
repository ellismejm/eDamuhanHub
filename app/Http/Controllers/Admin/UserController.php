<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('role')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'managedUser' => new User,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'password' => [
                'required',
                'confirmed',
                'max:256',
                Password::min(12)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = DB::transaction(function () use ($validated, $activityLogger) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $validated['role_id'],
                'password' => $validated['password'],
                'is_active' => true,
            ]);

            $activityLogger->record(
                'created',
                'users',
                $user,
                null,
                $this->auditedUserValues($user)
            );

            return $user;
        });

        return redirect()->route('admin.users.index')->with('status', 'User account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'managedUser' => $user,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(
        Request $request,
        User $user,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'is_active' => ['required', 'boolean'],
            'password' => [
                'nullable',
                'confirmed',
                'max:256',
                Password::min(12)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $administratorRole = Role::query()->where('name', 'Administrator')->firstOrFail();
        $removesActiveAdministrator = (int) $validated['is_active'] !== 1
            || (int) $validated['role_id'] !== $administratorRole->id;

        if (
            $user->role_id === $administratorRole->id
            && $user->is_active
            && $removesActiveAdministrator
            && ! User::query()
                ->where('role_id', $administratorRole->id)
                ->where('is_active', true)
                ->where('id', '<>', $user->id)
                ->exists()
        ) {
            return back()->withErrors([
                'role_id' => 'The system must keep at least one active Administrator.',
            ])->withInput();
        }

        $oldValues = $this->auditedUserValues($user);
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];
        $user->is_active = (bool) $validated['is_active'];

        if (filled($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        DB::transaction(function () use ($user, $activityLogger, $oldValues) {
            $user->save();
            $activityLogger->record('updated', 'users', $user, $oldValues, $this->auditedUserValues($user));
        });

        return redirect()->route('admin.users.index')->with('status', 'User account updated.');
    }

    public function destroy(User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors(['user' => 'You cannot delete the account you are currently using.']);
        }

        $adminRole = Role::query()->where('name', 'Administrator')->firstOrFail();
        if (
            $user->role_id === $adminRole->id
            && $user->is_active
            && ! User::query()
                ->where('role_id', $adminRole->id)
                ->where('is_active', true)
                ->where('id', '<>', $user->id)
                ->exists()
        ) {
            return back()->withErrors(['user' => 'The system must keep at least one active Administrator.']);
        }

        $values = $this->auditedUserValues($user);
        DB::transaction(function () use ($user, $activityLogger, $values) {
            $user->delete();
            $activityLogger->record('deleted', 'users', $user, $values);
        });

        return redirect()->route('admin.users.index')->with('status', 'User account deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function auditedUserValues(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name,
            'is_active' => $user->is_active,
        ];
    }
}
