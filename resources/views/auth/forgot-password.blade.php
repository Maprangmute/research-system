<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/sihp-logo.png') }}" alt="SiHP Logo" class="w-auto drop-shadow-lg"
                    style="height: 150px;" />
            </div>
        </x-slot>

        <h2 class="text-lg font-semibold mt-2 mb-2 flex justify-center">ลืมรหัสผ่าน</h2>
        <div class="mb-4 text-sm text-gray-600">
            หากลืมรหัสผ่าน กรุณากรอกอีเมลที่ใช้เข้าสู่ระบบ ระบบจะส่งรหัส OTP เพื่อใช้ตั้งรหัสผ่านใหม่
        </div>

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="อีเมล" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                    required autofocus autocomplete="username" />
            </div>



            <div class="flex items-center justify-between mt-4">
                <a href="{{ route('login') }}" class="text-sm text-blue-500 hover:text-blue-700 underline">
                    ← ย้อนกลับไปหน้าเข้าสู่ระบบ
                </a>

                <x-button>
                    ส่งรหัส OTP ไปทางอีเมล
                </x-button>
            </div>

        </form>
    </x-authentication-card>
</x-guest-layout>
