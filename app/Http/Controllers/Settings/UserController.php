<?php

namespace App\Http\Controllers\Settings;

use App\Domain\Identity\Services\UserAccountGuard;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UserStoreRequest;
use App\Http\Requests\Settings\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly UserAccountGuard $accountGuard,
    ) {}

    /**
     * Show the user management page.
     */
    public function index(): Response
    {
        $users = User::query()
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ]);

        return Inertia::render('settings/Users/Index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Create a new user.
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$data['role']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuario creado.')]);

        return to_route('users.index');
    }

    /**
     * Update an existing user's name, email and role.
     */
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()->is($user) && $data['role'] !== $request->user()->role) {
            throw ValidationException::withMessages([
                'role' => __('No puedes cambiar tu propio rol.'),
            ]);
        }

        if (! $this->accountGuard->canChangeRole($user, $data['role'])) {
            throw ValidationException::withMessages([
                'role' => __('No puedes quitar el rol de propietario al último propietario activo.'),
            ]);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $user->syncRoles([$data['role']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuario actualizado.')]);

        return to_route('users.index');
    }

    /**
     * Toggle a user's active status.
     */
    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages([
                'is_active' => __('No puedes desactivar tu propia cuenta.'),
            ]);
        }

        if ($user->is_active && ! $this->accountGuard->canDeactivate($user)) {
            throw ValidationException::withMessages([
                'is_active' => __('No puedes desactivar al último propietario activo.'),
            ]);
        }

        $user->update(['is_active' => ! $user->is_active]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Estado del usuario actualizado.')]);

        return to_route('users.index');
    }
}
