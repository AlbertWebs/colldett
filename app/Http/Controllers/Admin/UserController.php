<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $role = trim((string) $request->query('role', ''));
        $status = trim((string) $request->query('status', '')); // Active | Suspended

        return view('admin.users', [
            'users' => AdminUser::query()
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
                })
                ->when($role !== '', fn ($q) => $q->where('role', $role))
                ->when($status !== '', fn ($q) => $q->where('is_active', $status === 'Active'))
                ->orderBy('is_active', 'desc')
                ->orderBy('name')
                ->get(),
            'filters' => [
                'q' => $search,
                'role' => $role,
                'status' => $status,
            ],
            'totalUsers' => AdminUser::query()->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.user-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admin_users,email'],
            'role' => ['required', Rule::in(['Admin', 'Manager', 'Viewer'])],
            'is_active' => ['nullable', 'boolean'],
            'access_code' => ['required', 'string', 'min:4', 'max:200'],
        ]);

        $user = AdminUser::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
            'access_code_hash' => Hash::make($data['access_code']),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', "User '{$user->name}' created. Share their PIN/password securely.");
    }

    public function show(AdminUser $user): View
    {
        return view('admin.user-show', compact('user'));
    }

    public function toggleStatus(AdminUser $user): RedirectResponse
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', "User '{$user->name}' marked as ".($user->is_active ? 'Active' : 'Suspended').'.');
    }

    public function resetPassword(AdminUser $user): RedirectResponse
    {
        $newPin = (string) random_int(100000, 999999);
        $user->access_code_hash = Hash::make($newPin);
        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', "New access PIN for {$user->email}: {$newPin} (share securely).");
    }

    public function edit(AdminUser $user): View
    {
        return view('admin.user-edit', compact('user'));
    }

    public function update(Request $request, AdminUser $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admin_users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['Admin', 'Manager', 'Viewer'])],
            'is_active' => ['nullable', 'boolean'],
            'access_code' => ['nullable', 'string', 'min:4', 'max:200'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active');
        if (! empty($data['access_code'])) {
            $user->access_code_hash = Hash::make($data['access_code']);
        }
        $user->save();

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', "User '{$data['name']}' updated successfully.");
    }

    public function deleteConfirm(AdminUser $user): View
    {
        return view('admin.user-delete', compact('user'));
    }

    public function destroy(AdminUser $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('admin.users')
            ->with('status', "User '{$user->name}' deleted successfully.");
    }
}
