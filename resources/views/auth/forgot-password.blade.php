<x-guest-layout>
    <style>
        .reset-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 3vw, 2.5rem);
            background:
                radial-gradient(circle at 18% 12%, rgba(249, 115, 22, 0.15), transparent 24rem),
                radial-gradient(circle at 82% 18%, rgba(14, 165, 233, 0.16), transparent 24rem),
                linear-gradient(135deg, #f8fafc 0%, #eef6f7 52%, #fff7ed 100%);
        }

        .reset-shell {
            width: min(100%, 58rem);
            display: grid;
            grid-template-columns: minmax(0, 0.85fr) minmax(340px, 1fr);
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.55rem;
            background: rgba(255, 255, 255, 0.58);
            box-shadow: 0 24px 72px rgba(15, 23, 42, 0.13);
            backdrop-filter: blur(16px);
        }

        .reset-aside {
            position: relative;
            padding: clamp(1.5rem, 3vw, 2.4rem);
            color: #0f172a;
        }

        .reset-aside::before {
            content: "";
            position: absolute;
            inset: clamp(1rem, 2vw, 1.35rem);
            border: 1px solid rgba(249, 115, 22, 0.16);
            border-radius: 1.25rem;
            pointer-events: none;
        }

        .reset-aside-content {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: 100%;
            flex-direction: column;
            justify-content: space-between;
            gap: 3rem;
            padding: clamp(2rem, 6vh, 4rem) clamp(0.7rem, 2vw, 1.4rem);
        }

        .reset-kicker {
            color: #c2410c;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .reset-title {
            margin-top: 0.85rem;
            max-width: 19rem;
            font-family: "Mitr", "IBM Plex Sans Thai", sans-serif;
            font-size: clamp(1.55rem, 3vw, 2.35rem);
            font-weight: 500;
            line-height: 1.14;
            color: #0f172a;
        }

        .reset-copy {
            margin-top: 1rem;
            max-width: 20rem;
            color: #475569;
            font-size: 0.9rem;
            line-height: 1.75;
        }

        .reset-aside-footer {
            border-top: 1px solid rgba(15, 23, 42, 0.08);
            padding-top: 0.85rem;
            color: #64748b;
            font-size: 0.72rem;
            line-height: 1.4;
        }

        .reset-form-zone {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1.25rem, 3vw, 2.4rem);
            background: rgba(255, 255, 255, 0.76);
            box-shadow: -18px 0 45px rgba(15, 23, 42, 0.05);
        }

        .reset-card {
            width: min(100%, 26rem);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.15rem;
            background: rgba(255, 255, 255, 0.94);
            padding: clamp(1.25rem, 2.5vw, 2rem);
            box-shadow: 0 18px 55px rgba(15, 23, 42, 0.1);
        }

        .reset-logo {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .reset-logo img {
            width: 3.8rem;
            height: 3.8rem;
            object-fit: contain;
            filter: drop-shadow(0 10px 18px rgba(15, 23, 42, 0.1));
        }

        .reset-logo strong {
            color: #0f172a;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .reset-card h2 {
            margin-top: 1.4rem;
            color: #0f172a;
            font-family: "Mitr", "IBM Plex Sans Thai", sans-serif;
            font-size: 1.55rem;
            font-weight: 500;
        }

        .reset-card p {
            margin-top: 0.55rem;
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.7;
        }

        .reset-status {
            margin-top: 1rem;
            border: 1px solid #bbf7d0;
            border-radius: 0.9rem;
            background: #f0fdf4;
            padding: 0.85rem 1rem;
            color: #166534;
            font-size: 0.86rem;
            font-weight: 700;
        }

        .reset-field {
            margin-top: 1.15rem;
        }

        .reset-field label {
            color: #334155;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .reset-field input {
            margin-top: 0.45rem;
            min-height: 3rem;
            border-radius: 0.9rem;
            border-color: #dbe4ef;
            background: #f8fafc;
            color: #0f172a;
            box-shadow: none;
        }

        .reset-field input:focus {
            border-color: #f97316;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
        }

        .reset-actions {
            margin-top: 1.4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .reset-back-link {
            color: #64748b;
            font-size: 0.86rem;
            font-weight: 700;
            text-decoration-line: underline;
            text-underline-offset: 4px;
        }

        .reset-back-link:hover {
            color: #c2410c;
        }

        .reset-submit {
            min-height: 3rem;
            justify-content: center;
            border-radius: 0.9rem;
            background: linear-gradient(135deg, #f97316 0%, #0f766e 100%);
            box-shadow: 0 16px 35px rgba(249, 115, 22, 0.18);
        }

        @media (max-width: 767px) {
            .reset-page {
                align-items: stretch;
                padding: 0;
            }

            .reset-shell {
                width: 100%;
                min-height: 100dvh;
                display: block;
                border: 0;
                border-radius: 0;
                background: transparent;
                box-shadow: none;
            }

            .reset-aside {
                display: none;
            }

            .reset-form-zone {
                min-height: 100dvh;
                padding: 1rem;
                background:
                    radial-gradient(circle at 50% 0%, rgba(249, 115, 22, 0.14), transparent 18rem),
                    rgba(255, 255, 255, 0.6);
                box-shadow: none;
            }

            .reset-card {
                padding: 1.2rem;
            }

            .reset-actions {
                align-items: stretch;
                flex-direction: column-reverse;
            }

            .reset-submit,
            .reset-actions a {
                width: 100%;
                text-align: center;
            }
        }
    </style>

    <main class="reset-page">
        <div class="reset-shell">
            <section class="reset-aside" aria-label="Password reset">
                <div class="reset-aside-content">
                    <div>
                        <div class="reset-kicker">Password Assistance</div>
                        <h1 class="reset-title">ส่งรหัส OTP เพื่อรีเซ็ตรหัสผ่าน</h1>
                        <p class="reset-copy">
                            กรอกอีเมลที่ใช้เข้าสู่ระบบ ระบบจะส่งรหัส OTP สำหรับยืนยันตัวตนและตั้งรหัสผ่านใหม่
                        </p>
                    </div>

                    <div class="reset-aside-footer">
                        SiHP Research Database Management system
                    </div>
                </div>
            </section>

            <section class="reset-form-zone" aria-label="ลืมรหัสผ่าน">
                <div class="reset-card">
                    <div class="reset-logo">
                        <img src="{{ asset('images/sihp-logo.png') }}" alt="SiHP Logo">
                        <strong>SiHP Research Database Management system</strong>
                    </div>

                    <h2>ลืมรหัสผ่าน</h2>
                    <p>กรอกอีเมลของคุณเพื่อรับรหัส OTP สำหรับตั้งรหัสผ่านใหม่</p>

                    @session('status')
                        <div class="reset-status">
                            {{ $value }}
                        </div>
                    @endsession

                    <x-validation-errors class="mt-4" />

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="reset-field">
                            <x-label for="email" value="อีเมล" />
                            <x-input id="email" class="block w-full" type="email" name="email" :value="old('email')"
                                required autofocus autocomplete="username" />
                        </div>

                        <div class="reset-actions">
                            <a href="{{ route('login') }}" class="reset-back-link">
                                ย้อนกลับไปหน้าเข้าสู่ระบบ
                            </a>

                            <x-button class="reset-submit">
                                ส่งรหัส OTP
                            </x-button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
