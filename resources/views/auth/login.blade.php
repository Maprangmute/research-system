<x-guest-layout>
    <style>
        .login-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1.25rem, 3vw, 2.5rem);
            background:
                radial-gradient(circle at 15% 15%, rgba(14, 165, 233, 0.18), transparent 28rem),
                radial-gradient(circle at 88% 12%, rgba(16, 185, 129, 0.16), transparent 24rem),
                linear-gradient(135deg, #f8fafc 0%, #eef6f7 48%, #f5f7fb 100%);
        }

        .login-shell {
            width: min(100%, 1080px);
            min-height: min(680px, calc(100vh - clamp(2.5rem, 6vw, 5rem)));
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 0.82fr);
            align-items: stretch;
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.75rem;
            background: rgba(255, 255, 255, 0.46);
            box-shadow: 0 24px 72px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(16px);
        }

        .login-hero {
            --hero-gutter: clamp(1.25rem, 2.7vw, 2.25rem);
            position: relative;
            display: flex;
            min-height: 100%;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: clamp(1.7rem, 3vw, 2.9rem);
            color: #0f172a;
        }

        .login-hero::before {
            content: "";
            position: absolute;
            inset: clamp(1rem, 2vw, 1.45rem);
            border: 1px solid rgba(14, 116, 144, 0.13);
            border-radius: 1.35rem;
            pointer-events: none;
        }

        .login-hero-content,
        .login-hero-footer {
            position: relative;
            z-index: 1;
        }

        .login-hero-content {
            margin: 0;
            width: min(100%, 25.5rem);
            padding-top: clamp(4.25rem, 12vh, 6rem);
            padding-left: var(--hero-gutter);
        }

        .login-title {
            max-width: 25rem;
            color: #0f172a;
            font-family: "Mitr", "IBM Plex Sans Thai", sans-serif;
            font-size: clamp(1.42rem, 2vw, 2.08rem);
            font-weight: 600;
            line-height: 1.12;
            letter-spacing: 0;
        }

        .login-title span {
            display: block;
        }

        .login-title__accent {
            color: #0f766e;
        }

        .login-title__soft {
            color: #334155;
            font-size: 0.9em;
            font-weight: 500;
        }

        .login-title::after {
            content: "";
            display: block;
            width: 4.8rem;
            height: 0.22rem;
            margin-top: 1rem;
            border-radius: 999px;
            background: linear-gradient(90deg, #0f766e 0%, #38bdf8 100%);
        }

        .login-lead {
            margin-top: 0.95rem;
            max-width: 23.5rem;
            color: #475569;
            font-size: clamp(0.82rem, 0.95vw, 0.9rem);
            line-height: 1.7;
        }

        .login-hero-footer {
            display: flex;
            width: calc(100% - (var(--hero-gutter) * 2));
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin: 0 var(--hero-gutter);
            border-top: 1px solid rgba(15, 23, 42, 0.07);
            padding-top: 0.85rem;
            color: #64748b;
            font-size: 0.68rem;
            line-height: 1.35;
        }

        .login-hero-bottom {
            position: relative;
            z-index: 1;
        }

        .login-terms-link {
            display: inline;
            border: 0;
            border-radius: 0.35rem;
            background: transparent;
            padding: 0.1rem 0.2rem;
            color: #c2410c;
            font-size: 0.86rem;
            font-weight: 800;
            line-height: 1.55;
            text-decoration-line: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 4px;
            transition: background-color 0.18s ease, color 0.18s ease;
        }

        .login-terms-link:hover {
            background: #ffedd5;
            color: #9a3412;
        }

        .login-hero-terms {
            margin-top: 0.95rem;
            color: #64748b;
            font-size: 0.82rem;
            line-height: 1.7;
        }

        .login-card-terms {
            display: none;
            margin-top: 1.1rem;
            text-align: center;
        }

        .login-system-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.12rem;
            text-align: right;
        }

        .login-system-meta small {
            color: #94a3b8;
            font-size: 0.64rem;
            font-weight: 600;
        }

        .login-form-zone {
            display: flex;
            min-height: 100%;
            align-items: center;
            justify-content: center;
            padding: clamp(1.35rem, 2.6vw, 2.2rem);
            background: rgba(255, 255, 255, 0.72);
            box-shadow: -18px 0 45px rgba(15, 23, 42, 0.05);
            backdrop-filter: blur(18px);
        }

        .login-card {
            width: min(100%, 25.5rem);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.2rem;
            background: rgba(255, 255, 255, 0.92);
            padding: clamp(1.25rem, 2.5vw, 2rem);
            box-shadow: 0 18px 55px rgba(15, 23, 42, 0.11);
        }

        .login-logo-lockup {
            display: flex;
            align-items: center;
            gap: 0.95rem;
        }

        .login-logo-lockup img {
            width: 4.1rem;
            height: 4.1rem;
            object-fit: contain;
            filter: drop-shadow(0 10px 18px rgba(15, 23, 42, 0.12));
        }

        .login-logo-lockup span {
            display: block;
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .login-logo-lockup strong {
            display: block;
            margin-top: 0.15rem;
            color: #0f172a;
            font-size: 1.12rem;
            font-weight: 800;
        }

        .login-card h2 {
            margin-top: 1.45rem;
            color: #0f172a;
            font-family: "Mitr", "IBM Plex Sans Thai", sans-serif;
            font-size: 1.65rem;
            font-weight: 500;
            letter-spacing: 0;
        }

        .login-card p {
            margin-top: 0.55rem;
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.7;
        }

        .login-field {
            margin-top: 1.05rem;
        }

        .login-field label,
        .login-remember label {
            color: #334155;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .login-field input {
            margin-top: 0.45rem;
            min-height: 3rem;
            border-radius: 0.9rem;
            border-color: #dbe4ef;
            background: #f8fafc;
            color: #0f172a;
            box-shadow: none;
            transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
        }

        .login-field input:focus {
            border-color: #0f766e;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.12);
        }

        .login-field input.is-invalid {
            border-color: #f97316;
            background: #fff7ed;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
        }

        .login-error-card {
            margin-top: 1.1rem;
            border: 1px solid #fed7aa;
            border-radius: 1rem;
            background: linear-gradient(180deg, #fff7ed 0%, #fffbeb 100%);
            padding: 0.9rem 1rem;
            box-shadow: 0 12px 28px rgba(249, 115, 22, 0.12);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .login-error-card:hover {
            border-color: #fb923c;
            box-shadow: 0 16px 34px rgba(249, 115, 22, 0.2);
            transform: translateY(-1px);
        }

        .login-error-card__title {
            color: #c2410c;
            font-size: 0.9rem;
            font-weight: 800;
        }

        .login-error-card__body {
            margin-top: 0.35rem;
            color: #7c2d12;
            font-size: 0.82rem;
            line-height: 1.65;
        }

        .login-error-card__list {
            margin-top: 0.45rem;
            list-style-position: inside;
            list-style-type: disc;
        }

        .login-actions {
            margin-top: 1.5rem;
        }

        .login-submit-button {
            min-height: 3.2rem;
            width: 100%;
            justify-content: center;
            border-radius: 0.95rem;
            background: linear-gradient(135deg, #0f766e 0%, #0369a1 100%);
            font-size: 0.98rem;
            box-shadow: 0 16px 35px rgba(3, 105, 161, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
        }

        .login-submit-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 42px rgba(3, 105, 161, 0.27);
        }

        .login-submit-button:disabled {
            opacity: 0.72;
            transform: none;
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
            border-radius: 0.95rem;
            background: #ffffff;
            padding: 1rem 1.25rem;
            color: #334155;
            font-size: 0.95rem;
            font-weight: 700;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
        }

        .login-loading-card svg {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
            animation: login-submit-spin 0.8s linear infinite;
            color: #0f766e;
        }

        .login-terms-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(7px);
        }

        .login-terms-modal.is-visible {
            display: flex;
        }

        .login-terms-dialog {
            width: min(100%, 46rem);
            max-height: min(82vh, 46rem);
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 1.2rem;
            background: #ffffff;
            box-shadow: 0 28px 85px rgba(15, 23, 42, 0.26);
        }

        .login-terms-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.2rem 1.35rem;
        }

        .login-terms-title {
            color: #0f172a;
            font-size: 1.05rem;
            font-weight: 800;
            line-height: 1.45;
        }

        .login-terms-close {
            display: inline-flex;
            width: 2.1rem;
            height: 2.1rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #ffffff;
            color: #475569;
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1;
            transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }

        .login-terms-close:hover {
            border-color: #0f766e;
            background: #f0fdfa;
            color: #0f766e;
        }

        .login-terms-body {
            max-height: calc(min(82vh, 46rem) - 5.2rem);
            overflow-y: auto;
            padding: 1.25rem 1.35rem 1.45rem;
            color: #334155;
            font-size: 0.92rem;
            line-height: 1.8;
        }

        .login-terms-intro {
            border-left: 4px solid #0f766e;
            border-radius: 0.85rem;
            background: #f8fafc;
            padding: 0.9rem 1rem;
            color: #334155;
        }

        .login-terms-list {
            margin-top: 1rem;
            display: grid;
            gap: 0.8rem;
        }

        .login-terms-list li {
            list-style: none;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            background: #ffffff;
            padding: 0.9rem 1rem;
        }

        .login-terms-list strong {
            display: block;
            margin-bottom: 0.25rem;
            color: #0f766e;
            font-weight: 800;
        }

        @keyframes login-submit-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 1180px) and (min-width: 1024px) and (orientation: landscape) {
            .login-page {
                padding: 1rem;
            }

            .login-shell {
                width: min(100%, 1040px);
                min-height: min(620px, calc(100dvh - 2rem));
                grid-template-columns: minmax(0, 0.95fr) minmax(340px, 0.82fr);
                border-radius: 1.45rem;
            }

            .login-hero {
                padding: clamp(1.35rem, 2.2vw, 2rem);
            }

            .login-title {
                max-width: 26rem;
                font-size: clamp(1.55rem, 2.7vw, 2.35rem);
            }

            .login-lead {
                max-width: 25rem;
                font-size: 0.88rem;
            }

            .login-form-zone {
                padding: 1.25rem;
            }

            .login-card {
                width: min(100%, 24rem);
                padding: 1.35rem;
            }
        }

        @media (min-width: 768px) and (max-width: 1023px) {
            .login-page {
                align-items: center;
                padding: 2rem;
            }

            .login-shell {
                width: min(100%, 34rem);
                min-height: auto;
                display: block;
                border-radius: 1.55rem;
            }

            .login-hero {
                display: none;
            }

            .login-form-zone {
                min-height: auto;
                padding: 1.5rem;
                background: rgba(255, 255, 255, 0.76);
                box-shadow: none;
            }

            .login-card {
                width: 100%;
            }

            .login-card-terms {
                display: block;
            }

            .login-card-terms .login-terms-link {
                text-align: left;
            }
        }

        @media (max-width: 767px) {
            .login-page {
                align-items: stretch;
                padding: 0;
            }

            .login-shell {
                width: 100%;
                min-height: 100vh;
                min-height: 100dvh;
                display: block;
                border: 0;
                border-radius: 0;
                background: transparent;
                box-shadow: none;
                backdrop-filter: none;
            }

            .login-hero {
                display: none;
            }

            .login-form-zone {
                min-height: 100vh;
                min-height: 100dvh;
                padding: 1rem;
                background:
                    radial-gradient(circle at 50% 0%, rgba(14, 165, 233, 0.14), transparent 18rem),
                    rgba(255, 255, 255, 0.55);
                box-shadow: none;
            }

            .login-card {
                width: min(100%, 26rem);
                border-radius: 1.1rem;
                padding: 1.2rem;
            }

            .login-card h2 {
                margin-top: 1.2rem;
                font-size: 1.45rem;
            }

            .login-card p {
                font-size: 0.86rem;
            }

            .login-field label,
            .login-remember label {
                font-size: 0.84rem;
            }

            .login-field input {
                min-height: 2.9rem;
            }

            .login-submit-button {
                min-height: 3rem;
            }

            .login-logo-lockup img {
                width: 3.5rem;
                height: 3.5rem;
            }

            .login-logo-lockup strong {
                font-size: 1rem;
            }

            .login-card-terms {
                display: block;
            }

            .login-card-terms .login-terms-link {
                text-align: left;
            }

            .login-terms-dialog {
                max-height: 88vh;
                border-radius: 1rem;
            }

            .login-terms-header {
                padding: 1rem;
            }

            .login-terms-title {
                font-size: 0.95rem;
            }

            .login-terms-body {
                max-height: calc(88vh - 4.8rem);
                padding: 1rem;
                font-size: 0.86rem;
            }
        }

        @media (max-width: 380px) {
            .login-form-zone {
                padding: 0.75rem;
            }

            .login-card {
                padding: 1rem;
            }

            .login-remember {
                align-items: flex-start;
            }
        }
    </style>

    <main class="login-page">
        <div class="login-shell">
            <section class="login-hero" aria-label="SiHP Research Database Management system">
                <div class="login-hero-content">
                    <h1 class="login-title">
                        <span>Data collection builds</span>
                        <span>the foundation for</span>
                        <span class="login-title__accent">intelligence</span>
                        <span class="login-title__soft">and better decisions.</span>
                    </h1>
                    <p class="login-lead">
                        ระบบศูนย์กลางสำหรับติดตามโครงการวิจัย ผลผลิตวิชาการ
                        และข้อมูลสนับสนุนการทำงานของหน่วยงานอย่างเป็นระบบ
                    </p>

                    <div class="login-hero-terms">
                        โปรดอ่านและยอมรับ
                        <button type="button" class="login-terms-link" data-terms-open>
                            ข้อตกลงและเงื่อนไขการใช้งานระบบ
                        </button>
                    </div>
                </div>

                <div class="login-hero-bottom">
                    <div class="login-hero-footer">
                        <span>Siriraj Health Policy</span>
                        <span class="login-system-meta">
                            <span>Research Database Management System</span>
                            <small>bata 3.5.0</small>
                        </span>
                    </div>
                </div>
            </section>

            <section class="login-form-zone" aria-label="เข้าสู่ระบบ">
                <div class="login-card">
                    <div class="login-logo-lockup">
                        <img src="{{ asset('images/sihp-logo.png') }}" alt="SiHP Logo">
                        <div>
                            <strong>SiHP Research Database Management system</strong>
                        </div>
                    </div>

                    <h2>เข้าสู่ระบบ</h2>
                    <p>ใช้อีเมลและรหัสผ่านของหน่วยงานเพื่อเข้าสู่ระบบฐานข้อมูลงานวิจัย</p>

                    @if ($errors->any())
                        <div class="login-error-card" role="alert">
                            <div class="login-error-card__title">เข้าสู่ระบบไม่สำเร็จ</div>
                            <div class="login-error-card__body">
                                ตรวจสอบอีเมลหน่วยงานและรหัสผ่านให้ถูกต้อง
                                หากเป็นผู้ที่ออกจากหน่วยงานแล้ว ระบบจะไม่อนุญาตให้เข้าสู่ระบบ

                                <ul class="login-error-card__list">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @session('status')
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                            {{ $value }}
                        </div>
                    @endsession

                    <form method="POST" action="{{ route('login') }}" data-login-form class="mt-5">
                        @csrf

                        <div class="login-field">
                            <x-label for="email" value="อีเมล" />
                            <x-input id="email" class="block w-full {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" :value="old('email')"
                                required autofocus autocomplete="username" />
                        </div>

                        <div class="login-field">
                            <div class="flex items-center justify-between gap-3">
                                <x-label for="password" value="รหัสผ่าน" />

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-teal-700 underline underline-offset-4 hover:text-sky-700">
                                        ลืมรหัสผ่าน?
                                    </a>
                                @endif
                            </div>

                            <x-input id="password" class="block w-full {{ $errors->has('password') || $errors->has('email') ? 'is-invalid' : '' }}" type="password" name="password" required
                                autocomplete="current-password" />
                        </div>

                        <div class="login-remember mt-4 flex items-center justify-between gap-3">
                            <label for="remember_me" class="flex items-center">
                                <x-checkbox id="remember_me" name="remember" />
                                <span class="ms-2 text-sm text-slate-600">จดจำการเข้าสู่ระบบ</span>
                            </label>
                        </div>

                        <div class="login-actions">
                            <x-button class="login-submit-button" data-submit-button>
                                <span class="login-submit-content">
                                    <svg class="login-submit-spinner" viewBox="0 0 24 24" fill="none" data-submit-spinner
                                        aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="4"></circle>
                                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="4"
                                            stroke-linecap="round"></path>
                                    </svg>
                                    <span data-submit-label>เข้าสู่ระบบ</span>
                                </span>
                            </x-button>
                        </div>
                    </form>

                    <div class="login-card-terms">
                        โปรดอ่านและยอมรับ
                        <button type="button" class="login-terms-link" data-terms-open>
                            ข้อตกลงและเงื่อนไขการใช้งานระบบ
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <div class="login-terms-modal" data-terms-modal aria-hidden="true">
        <div class="login-terms-dialog" role="dialog" aria-modal="true" aria-labelledby="terms-title">
            <div class="login-terms-header">
                <div id="terms-title" class="login-terms-title">
                    ข้อตกลงและเงื่อนไขการใช้งานระบบ (Terms of Use & Disclaimer)
                </div>
                <button type="button" class="login-terms-close" data-terms-close aria-label="ปิดหน้าต่าง">
                    &times;
                </button>
            </div>

            <div class="login-terms-body">
                <p class="login-terms-intro">
                    ระบบฐานข้อมูลโครงการวิจัยและการติดตามงานนี้ มีวัตถุประสงค์เพื่อสนับสนุนการดำเนินงานภายในองค์กร
                    ได้แก่ การบริหารจัดการโครงการ การติดตามความก้าวหน้า การจัดสรรทรัพยากร
                    และการพัฒนาประสิทธิภาพการทำงานในภาพรวม
                </p>

                <ul class="login-terms-list">
                    <li>
                        <strong>วัตถุประสงค์ของข้อมูล</strong>
                        ข้อมูลที่จัดเก็บจะถูกนำไปใช้ในลักษณะภาพรวม (Aggregated Data)
                        เพื่อการวิเคราะห์เชิงระบบและการพัฒนาการดำเนินงานในระดับองค์กร
                        โดยมุ่งเน้นการสนับสนุนการทำงานของบุคลากร
                        และไม่ได้มีวัตถุประสงค์หลักเพื่อใช้ในการประเมินผลประสิทธิภาพการทำงานรายบุคคล
                    </li>
                    <li>
                        <strong>การคุ้มครองข้อมูลและความเป็นส่วนตัว</strong>
                        องค์กรให้ความสำคัญกับความเป็นส่วนตัวและความปลอดภัยของข้อมูล
                        โดยข้อมูลจะไม่ถูกเปิดเผยต่อบุคคลภายนอก เว้นแต่มีความจำเป็นตามภารกิจขององค์กร
                        หรือเป็นไปตามข้อกำหนดทางกฎหมาย
                    </li>
                    <li>
                        <strong>การเข้าถึงสิทธิ์</strong>
                        ผู้ใช้งานสามารถเข้าถึง ตรวจสอบ และจัดการข้อมูลของตนเองในระบบได้
                        ภายใต้มาตรการการยืนยันตัวตนและสิทธิ์การเข้าถึง (Access Rights) ที่องค์กรกำหนด
                    </li>
                    <li>
                        <strong>ความถูกต้องของข้อมูลและการจำกัดความรับผิดชอบ</strong>
                        ผู้ใช้งานมีหน้าที่ตรวจสอบและรับรองความถูกต้องของข้อมูลที่นำเข้าสู่ระบบ
                        ทั้งนี้ องค์กรขอสงวนสิทธิ์ในการรับผิดชอบต่อความเสียหายใด ๆ
                        ที่เกิดจากการนำข้อมูลไปใช้ผิดวัตถุประสงค์ หรือการเข้าถึงข้อมูลโดยไม่ได้รับอนุญาต
                        อันเกิดจากความประมาทเลินเล่อในการรักษาบัญชีผู้ใช้งาน (Username/Password)
                        หรือมาตรการรักษาความปลอดภัยส่วนบุคคลของผู้ใช้งานเอง
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="login-loading-overlay" data-login-overlay>
        <div class="login-loading-card">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="4"></circle>
                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="4"
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
            const termsModal = document.querySelector('[data-terms-modal]');
            const termsOpenButtons = document.querySelectorAll('[data-terms-open]');
            const termsCloseButtons = document.querySelectorAll('[data-terms-close]');
            let lastTermsTrigger = null;

            const openTermsModal = function (trigger) {
                if (!termsModal) {
                    return;
                }

                lastTermsTrigger = trigger || null;
                termsModal.classList.add('is-visible');
                termsModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                termsModal.querySelector('[data-terms-close]')?.focus();
            };

            const closeTermsModal = function () {
                if (!termsModal) {
                    return;
                }

                termsModal.classList.remove('is-visible');
                termsModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';

                if (lastTermsTrigger) {
                    lastTermsTrigger.focus();
                }
            };

            termsOpenButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    openTermsModal(button);
                });
            });

            termsCloseButtons.forEach(function (button) {
                button.addEventListener('click', closeTermsModal);
            });

            termsModal?.addEventListener('click', function (event) {
                if (event.target === termsModal) {
                    closeTermsModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && termsModal?.classList.contains('is-visible')) {
                    closeTermsModal();
                }
            });

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
