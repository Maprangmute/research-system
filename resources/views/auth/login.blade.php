<x-guest-layout>
    <style>
        .login-submit-button {
            justify-content: center;
        }

        .login-submit-content {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-submit-spinner {
            display: none;
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
            animation: login-submit-spin 0.8s linear infinite;
        }

        .login-submit-spinner circle {
            opacity: 0.25;
        }

        .login-submit-spinner path {
            opacity: 0.9;
        }

        .login-submit-spinner.is-visible {
            display: inline-block;
        }

        .login-loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(6px);
        }

        .login-loading-overlay.is-visible {
            display: flex;
        }

        .login-loading-card {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            border-radius: 0.875rem;
            background: #ffffff;
            padding: 1rem 1.25rem;
            color: #334155;
            font-size: 0.95rem;
            font-weight: 600;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
        }

        .login-loading-card svg {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
            animation: login-submit-spin 0.8s linear infinite;
            color: #2563eb;
        }

        .login-loading-card circle {
            opacity: 0.25;
        }

        .login-loading-card path {
            opacity: 0.9;
        }

        .login-help-card {
            border: 1px solid #dbeafe;
            border-radius: 1rem;
            background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
            padding: 0.9rem 1rem;
        }

        .login-help-card__title {
            color: #1d4ed8;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .login-help-card__body {
            margin-top: 0.35rem;
            color: #475569;
            font-size: 0.82rem;
            line-height: 1.55;
        }

        @keyframes login-submit-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <x-authentication-card>
        <x-slot name="logo">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/sihp-logo.png') }}" alt="SiHP Logo" class="w-auto drop-shadow-lg"
                    style="height: 150px;" />
            </div>
        </x-slot>

        <h2 class="text-lg font-semibold mt-2 mb-2 flex justify-center">เข้าสู่ระบบ</h2>
        <div class="mb-4 text-sm text-gray-600 text-center">
            ใช้อีเมลและรหัสผ่านของหน่วยงานเพื่อเข้าสู่ระบบฐานข้อมูลงานวิจัย
        </div>

        <div class="login-help-card mb-4">
            <div class="login-help-card__title">หากเข้าสู่ระบบไม่ได้</div>
            <div class="login-help-card__body">
                ตรวจสอบอีเมลหน่วยงานและรหัสผ่านให้ถูกต้อง
                หากเป็นผู้ที่ออกจากหน่วยงานแล้ว ระบบจะไม่อนุญาตให้เข้าสู่ระบบ
            </div>
        </div>

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('login') }}" data-login-form>
            @csrf

            <div class="block">
                <x-label for="email" value="อีเมล" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                    required autofocus autocomplete="username" />
            </div>

            <div class="block mt-4">
                <div class="flex items-center justify-between gap-3">
                    <x-label for="password" value="รหัสผ่าน" />

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-500 hover:text-blue-700 underline">
                            ลืมรหัสผ่าน?
                        </a>
                    @endif
                </div>

                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="current-password" />
            </div>

            <div class="flex items-center justify-between mt-4 gap-3">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">จดจำการเข้าสู่ระบบ</span>
                </label>
            </div>

            <div class="mt-6">
                <x-button class="w-full login-submit-button" data-submit-button>
                    <span class="login-submit-content">
                    <svg class="login-submit-spinner" viewBox="0 0 24 24" fill="none" data-submit-spinner
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="9" class="opacity-25" stroke="currentColor" stroke-width="4"></circle>
                        <path d="M21 12a9 9 0 0 0-9-9" class="opacity-90" stroke="currentColor" stroke-width="4"
                            stroke-linecap="round"></path>
                    </svg>
                    <span data-submit-label>เข้าสู่ระบบ</span>
                    </span>
                </x-button>
            </div>
        </form>
    </x-authentication-card>

    <div class="login-loading-overlay" data-login-overlay>
        <div class="login-loading-card">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="9" class="opacity-25" stroke="currentColor" stroke-width="4"></circle>
                <path d="M21 12a9 9 0 0 0-9-9" class="opacity-90" stroke="currentColor" stroke-width="4"
                    stroke-linecap="round"></path>
            </svg>
            <span>กำลังเข้าสู่ระบบ...</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('[data-login-form]');

            if (!form) {
                return;
            }

            const submitButton = form.querySelector('[data-submit-button]');
            const submitLabel = form.querySelector('[data-submit-label]');
            const submitSpinner = form.querySelector('[data-submit-spinner]');
            const overlay = document.querySelector('[data-login-overlay]');

            form.addEventListener('submit', function (event) {
                if (form.dataset.submitting === '1') {
                    event.preventDefault();
                    return;
                }

                form.dataset.submitting = '1';

                if (submitButton) {
                    submitButton.disabled = true;
                }

                if (submitLabel) {
                    submitLabel.textContent = 'กำลังเข้าสู่ระบบ...';
                }

                if (submitSpinner) {
                    submitSpinner.classList.add('is-visible');
                }

                if (overlay) {
                    overlay.classList.add('is-visible');
                }
            });
        });
    </script>
</x-guest-layout>
