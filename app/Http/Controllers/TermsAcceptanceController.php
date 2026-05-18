<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TermsAcceptanceController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->terms_accepted_version === (string) config('terms.current_version')) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.accept-terms', [
            'termsVersion' => (string) config('terms.current_version'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'terms_accepted' => ['accepted'],
        ], [
            'terms_accepted.accepted' => 'โปรดอ่าน รับทราบ และยอมรับข้อตกลงและเงื่อนไขการใช้งานระบบก่อนดำเนินการต่อ',
        ]);

        $request->user()->forceFill([
            'terms_accepted_at' => now('Asia/Bangkok'),
            'terms_accepted_version' => (string) config('terms.current_version'),
            'terms_accepted_ip' => $request->ip(),
            'terms_accepted_user_agent' => (string) $request->userAgent(),
        ])->save();

        return redirect()->intended(route('dashboard'));
    }
}
