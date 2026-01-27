<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpPasswordResetController extends Controller
{
    private int $ttlMinutes = 10;

    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'ไม่พบอีเมลนี้ในระบบ'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $data['email']],
            ['token' => Hash::make($otp), 'created_at' => now()]
        );

        Mail::send('emails.password-otp', [
            'user' => $user,
            'otp' => $otp,
            'ttlMinutes' => $this->ttlMinutes,
        ], function ($message) use ($data) {
            $message->from('researchsystem@sihealthpolicy.com', 'Research System');
            $message->to($data['email']);
            $message->subject('รหัส OTP สำหรับรีเซ็ตรหัสผ่าน');
        });

        return redirect()
            ->route('password.reset', ['email' => $data['email']])
            ->with('status', 'ส่งรหัส OTP ไปยังอีเมลเรียบร้อยแล้ว');
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'otp.digits' => 'รหัส OTP ต้องมี 6 หลัก',
        ]);

        $tokenRow = DB::table('password_reset_tokens')->where('email', $data['email'])->first();
        if (!$tokenRow) {
            return back()->withErrors(['otp' => 'ไม่พบคำขอรีเซ็ตรหัสผ่าน'])->withInput();
        }

        $expired = now()->diffInMinutes($tokenRow->created_at) > $this->ttlMinutes;
        if ($expired) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            return back()->withErrors(['otp' => 'รหัส OTP หมดอายุแล้ว กรุณาขอใหม่'])->withInput();
        }

        if (!Hash::check($data['otp'], $tokenRow->token)) {
            return back()->withErrors(['otp' => 'รหัส OTP ไม่ถูกต้อง'])->withInput();
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'ไม่พบผู้ใช้งาน'])->withInput();
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')->with('status', 'รีเซ็ตรหัสผ่านสำเร็จแล้ว');
    }
}
