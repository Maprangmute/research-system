<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/sihp-logo.png') }}"
                     alt="SiHP Logo"
                     class="w-auto drop-shadow-lg" style="height: 150px;" />
            </div>
        </x-slot>

        <div class="mb-4">
            <a href="{{ route('password.request') }}" class="text-sm text-blue-500 hover:text-blue-700 underline">
                ← ย้อนกลับ
            </a>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="อีเมล" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email"
                    :value="old('email', $email ?? $request->email)" readonly required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="otp" value="รหัส OTP (6 หลัก)" />
                <x-input id="otp" class="block mt-1 w-full" type="text" name="otp" inputmode="numeric" maxlength="6"
                    required autocomplete="one-time-code" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="รหัสผ่านใหม่" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="ยืนยันรหัสผ่านใหม่" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    ตั้งรหัสผ่านใหม่
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
