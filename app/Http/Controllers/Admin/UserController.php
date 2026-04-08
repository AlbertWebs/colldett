<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $allUsers = collect($this->users());
        $search = trim((string) $request->query('q', ''));
        $role = trim((string) $request->query('role', ''));
        $status = trim((string) $request->query('status', ''));

        $users = $allUsers
            ->when($search !== '', function ($collection) use ($search) {
                $needle = mb_strtolower($search);

                return $collection->filter(function (array $user) use ($needle) {
                    return str_contains(mb_strtolower($user['name']), $needle)
                        || str_contains(mb_strtolower($user['email']), $needle);
                });
            })
            ->when($role !== '', fn ($collection) => $collection->where('role', $role))
            ->when($status !== '', fn ($collection) => $collection->where('status', $status))
            ->values()
            ->all();

        return view('admin.users', [
            'users' => $users,
            'filters' => [
                'q' => $search,
                'role' => $role,
                'status' => $status,
            ],
            'totalUsers' => $allUsers->count(),
        ]);
    }

    public function show(int $id): View
    {
        $user = collect($this->users())->firstWhere('id', $id);
        abort_unless($user, 404);

        return view('admin.user-show', compact('user'));
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $user = collect($this->users())->firstWhere('id', $id);
        abort_unless($user, 404);

        $nextStatus = $user['status'] === 'Active' ? 'Suspended' : 'Active';

        return redirect()
            ->route('admin.users.show', $id)
            ->with('status', "User '{$user['name']}' marked as {$nextStatus}.");
    }

    public function resetPassword(int $id): RedirectResponse
    {
        $user = collect($this->users())->firstWhere('id', $id);
        abort_unless($user, 404);

        return redirect()
            ->route('admin.users.show', $id)
            ->with('status', "Password reset link sent to {$user['email']}.");
    }

    public function edit(int $id): View
    {
        $user = collect($this->users())->firstWhere('id', $id);
        abort_unless($user, 404);

        $editableUser = array_merge([
            'phone' => '+254 700 000000',
            'department' => 'Operations',
            'job_title' => $user['role'],
            'employee_id' => 'EMP-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'timezone' => 'Africa/Nairobi',
            'language' => 'English',
            'two_factor_enabled' => false,
            'email_verified' => true,
            'can_manage_users' => $user['role'] === 'Admin',
            'can_manage_billing' => $user['role'] !== 'Viewer',
            'can_manage_cases' => true,
            'can_publish_content' => $user['role'] !== 'Viewer',
            'notes' => '',
        ], $user);

        return view('admin.user-edit', ['user' => $editableUser]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = collect($this->users())->firstWhere('id', $id);
        abort_unless($user, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'in:Admin,Manager,Viewer'],
            'status' => ['required', 'in:Active,Suspended'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:255'],
            'two_factor_enabled' => ['nullable', 'in:0,1'],
            'email_verified' => ['nullable', 'in:0,1'],
            'can_manage_users' => ['nullable', 'in:0,1'],
            'can_manage_billing' => ['nullable', 'in:0,1'],
            'can_manage_cases' => ['nullable', 'in:0,1'],
            'can_publish_content' => ['nullable', 'in:0,1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return redirect()
            ->route('admin.users.edit', $id)
            ->with('status', "User '{$data['name']}' updated successfully.");
    }

    public function deleteConfirm(int $id): View
    {
        $user = collect($this->users())->firstWhere('id', $id);
        abort_unless($user, 404);

        return view('admin.user-delete', compact('user'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = collect($this->users())->firstWhere('id', $id);
        abort_unless($user, 404);

        return redirect()
            ->route('admin.users')
            ->with('status', "User '{$user['name']}' deleted successfully.");
    }

    private function users(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Finance Admin',
                'email' => 'finance@company.com',
                'role' => 'Admin',
                'status' => 'Active',
                'last_login' => '2 mins ago',
            ],
            [
                'id' => 2,
                'name' => 'Ops Admin',
                'email' => 'ops@company.com',
                'role' => 'Admin',
                'status' => 'Active',
                'last_login' => '1 hour ago',
            ],
            [
                'id' => 3,
                'name' => 'Collections Manager',
                'email' => 'collections@company.com',
                'role' => 'Manager',
                'status' => 'Active',
                'last_login' => 'Yesterday',
            ],
            [
                'id' => 4,
                'name' => 'Audit Viewer',
                'email' => 'audit@company.com',
                'role' => 'Viewer',
                'status' => 'Suspended',
                'last_login' => '2 weeks ago',
            ],
        ];
    }
}
