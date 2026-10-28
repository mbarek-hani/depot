<?php

namespace App\Http\Controllers;

use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = in_array($request->query('tab'), ['general', 'appearance', 'account'], true)
            ? $request->query('tab')
            : 'general';

        return Inertia::render('Settings/Index', [
            'tab' => $tab,
            'settings' => Settings::all(),
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'association_name' => ['required', 'string', 'max:120'],
            'association_phone' => ['nullable', 'string', 'max:30'],
            'association_email' => ['nullable', 'email', 'max:120'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'expiry_warning_days' => ['required', 'integer', 'min:1', 'max:365'],
            'currency' => ['required', 'string', 'max:12'],
            'report_footer' => ['nullable', 'string', 'max:180'],
        ]);

        Settings::setMany($data);

        return redirect()
            ->route('settings.index', ['tab' => 'general'])
            ->with('status', 'تم حفظ إعدادات النظام بنجاح.');
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('users', 'name')->ignore($user)],
        ]);

        $user->update(['name' => $data['name']]);

        return redirect()
            ->route('settings.index', ['tab' => 'account'])
            ->with('status', 'تم تحديث بيانات الحساب بنجاح.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [], [
            'current_password' => 'كلمة المرور الحالية',
            'password' => 'كلمة المرور الجديدة',
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('settings.index', ['tab' => 'account'])
            ->with('status', 'تم تغيير كلمة المرور بنجاح.');
    }

    public function updateTheme(Request $request): JsonResponse
    {
        $data = $request->validate([
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
        ]);

        $request->user()->update(['theme' => $data['theme']]);

        return response()->json(['theme' => $data['theme']]);
    }
}
