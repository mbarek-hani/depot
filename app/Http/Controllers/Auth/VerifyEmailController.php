<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = $request->user();

        if (! hash_equals((string) $user->getKey(), $id)
            || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return redirect()
                ->route('verification.notice')
                ->withErrors([
                    'email' => 'رابط التأكيد لا يطابق الحساب المسجل حاليًا. سجّل الدخول بالحساب الصحيح ثم أعد إرسال رابط التأكيد.',
                ]);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard').'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(route('dashboard').'?verified=1');
    }
}
