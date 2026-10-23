<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->trim()->toString(),
            'role' => $request->string('role')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $users = User::query()
            ->withCount('movements')
            ->when($filters['search'], function ($query, $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->when(in_array($filters['role'], ['admin', 'user'], true), fn ($query) => $query->where('role', $filters['role']))
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'total' => User::count(),
            'admins' => User::admins()->count(),
            'active' => User::active()->count(),
            'restricted' => User::where('role', UserRole::User)->where('is_active', true)->count(),
        ];

        return view('users.index', compact('users', 'filters', 'summary'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::User,
            'is_active' => true,
            'email_verified_at' => now(),
            'receive_expiry_alerts' => $request->boolean('receive_expiry_alerts'),
            'receive_low_stock_alerts' => $request->boolean('receive_low_stock_alerts'),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'تم إنشاء حساب المستخدم «'.$user->name.'» بنجاح.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $emailChanged = $user->email !== $data['email'];

        if ($request->user()->is($user) && ! $request->boolean('is_active')) {
            throw ValidationException::withMessages([
                'is_active' => 'لا يمكنك إيقاف حسابك الحالي.',
            ]);
        }

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $request->boolean('is_active'),
            'receive_expiry_alerts' => $request->boolean('receive_expiry_alerts'),
            'receive_low_stock_alerts' => $request->boolean('receive_low_stock_alerts'),
        ];

        if ($emailChanged) {
            $attributes['email_verified_at'] = $user->isAdmin() ? null : now();
        }

        if (filled($data['password'] ?? null)) {
            $attributes['password'] = Hash::make($data['password']);
        }

        $user->update($attributes);

        if ($emailChanged && $user->isAdmin()) {
            $user->sendEmailVerificationNotification();

            if ($request->user()->is($user)) {
                return redirect()->route('verification.notice')->with('status', 'تم تحديث البريد الإلكتروني. يرجى تأكيد البريد الجديد.');
            }
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'تم تحديث بيانات المستخدم بنجاح.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'لا يمكنك حذف حسابك الحالي.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'تم حذف حساب «'.$name.'».');
    }
}
