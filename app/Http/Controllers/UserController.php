<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Support\Present;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString(),
            'role' => $request->string('role')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $users = User::query()
            ->with('createdBy')
            ->withCount('movements')
            ->when($filters['search'], function ($query, string $term) {
                $query->where('name', 'like', "%{$term}%");
            })
            ->when(in_array($filters['role'], ['admin', 'user'], true), fn ($query) => $query->where('role', $filters['role']))
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'blocked', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (User $user) => Present::user($user));

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $filters,
            'summary' => [
                'total' => User::query()->count(),
                'admins' => User::query()->where('role', UserRole::Admin->value)->count(),
                'active' => User::query()->where('is_active', true)->count(),
                'blocked' => User::query()->where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Users/Create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'password' => $data['password'],
            'role' => UserRole::User,
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'تم إنشاء حساب المستخدم «'.$user->name.'» بنجاح.');
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        $user->load('createdBy')->loadCount('movements');

        return Inertia::render('Users/Edit', [
            'user' => Present::user($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $data = $request->validated();
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : $user->is_active;
        $passwordChanged = filled($data['password'] ?? null);

        $attributes = [
            'name' => $data['name'],
            'is_active' => $isActive,
        ];

        if ($passwordChanged) {
            $attributes['password'] = $data['password'];
        }

        if (! $isActive || $passwordChanged) {
            $attributes['remember_token'] = null;
        }

        $user->update($attributes);

        if (! $isActive || $passwordChanged) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'تم تحديث بيانات المستخدم بنجاح.');
    }
}
