<x-guest-layout>
    <style>
        .terms-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 3vw, 2.5rem);
            background:
                radial-gradient(circle at 18% 12%, rgba(15, 118, 110, 0.1), transparent 24rem),
                radial-gradient(circle at 82% 18%, rgba(51, 65, 85, 0.1), transparent 24rem),
                linear-gradient(135deg, #f8fafc 0%, #eef2f7 52%, #f8fafc 100%);
        }

        .terms-card {
            width: min(100%, 48rem);
            max-height: min(90vh, 52rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 26px 80px rgba(15, 23, 42, 0.18);
        }

        .terms-header {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            flex-shrink: 0;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.2rem 1.35rem;
        }

        .terms-header img {
            width: 3.4rem;
            height: 3.4rem;
            object-fit: contain;
        }

        .terms-title {
            color: #0f172a;
            font-family: "Mitr", "IBM Plex Sans Thai", sans-serif;
            font-size: 1.2rem;
            font-weight: 500;
            line-height: 1.45;
        }

        .terms-subtitle {
            margin-top: 0.15rem;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .terms-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 1.25rem 1.35rem;
            color: #334155;
            font-size: 0.92rem;
            line-height: 1.8;
        }

        .terms-intro {
            border-left: 4px solid #0f766e;
            border-radius: 0.85rem;
            background: #f8fafc;
            padding: 0.95rem 1rem;
        }

        .terms-list {
            margin-top: 1rem;
            display: grid;
            gap: 0.8rem;
        }

        .terms-list li {
            list-style: none;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            background: #ffffff;
            padding: 0.9rem 1rem;
        }

        .terms-list strong {
            display: block;
            margin-bottom: 0.25rem;
            color: #0f172a;
            font-weight: 800;
        }

        .terms-footer {
            flex-shrink: 0;
            border-top: 1px solid #e2e8f0;
            padding: 0.95rem 1.35rem 1rem;
            background: #ffffff;
        }

        .terms-consent {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            color: #1e293b;
            font-size: 0.9rem;
            font-weight: 800;
            line-height: 1.6;
        }

        .terms-consent.is-disabled {
            color: #94a3b8;
        }

        .terms-consent input {
            margin-top: 0.33rem;
            flex-shrink: 0;
        }

        .terms-scroll-hint {
            margin-top: 0.5rem;
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .terms-scroll-hint.is-complete {
            color: #0f766e;
        }

        .terms-error {
            margin-top: 0.45rem;
            color: #c2410c;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .terms-submit {
            margin-top: 1rem;
            min-height: 3rem;
            width: 100%;
            justify-content: center;
            border-radius: 0.9rem;
            background: #0f766e;
            box-shadow: 0 16px 35px rgba(15, 118, 110, 0.18);
        }

        .terms-submit:hover {
            background: #115e59;
        }

        .terms-submit:disabled {
            cursor: not-allowed;
            background: #94a3b8;
            box-shadow: none;
            opacity: 0.75;
        }

        @media (max-width: 640px) {
            .terms-page {
                align-items: stretch;
                padding: 0;
                overflow: hidden;
            }

            .terms-card {
                max-height: none;
                min-height: 100dvh;
                height: 100dvh;
                border: 0;
                border-radius: 0;
            }

            .terms-header,
            .terms-body,
            .terms-footer {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .terms-body {
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto;
                padding-bottom: 1rem;
            }

            .terms-footer {
                position: sticky;
                bottom: 0;
                box-shadow: 0 -14px 28px rgba(15, 23, 42, 0.08);
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .terms-page {
                padding: clamp(1rem, 3vw, 2rem);
            }

            .terms-card {
                width: min(100%, 44rem);
                max-height: calc(100dvh - 2rem);
                border-radius: 1.15rem;
            }

            .terms-header {
                padding: 1rem 1.15rem;
            }

            .terms-title {
                font-size: 1.05rem;
            }

            .terms-body {
                padding: 1rem 1.15rem;
                font-size: 0.9rem;
                line-height: 1.75;
            }

            .terms-footer {
                padding: 0.9rem 1.15rem 1rem;
            }
        }

        @media (max-width: 480px) {
            .terms-header {
                align-items: flex-start;
                gap: 0.75rem;
                padding: 0.9rem 1rem;
            }

            .terms-header img {
                width: 2.7rem;
                height: 2.7rem;
            }

            .terms-title {
                font-size: 0.95rem;
                line-height: 1.45;
            }

            .terms-subtitle {
                font-size: 0.72rem;
            }

            .terms-body {
                padding-top: 0.9rem;
                padding-bottom: 0.9rem;
                font-size: 0.86rem;
                line-height: 1.72;
            }

            .terms-intro,
            .terms-list li {
                border-radius: 0.75rem;
                padding: 0.8rem 0.85rem;
            }

            .terms-consent {
                font-size: 0.84rem;
            }

            .terms-submit {
                min-height: 2.9rem;
            }
        }

        @media (max-height: 720px) and (min-width: 768px) {
            .terms-page {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }

            .terms-card {
                max-height: calc(100dvh - 2rem);
            }

            .terms-header {
                padding-top: 0.85rem;
                padding-bottom: 0.85rem;
            }

            .terms-body {
                padding-top: 0.9rem;
                padding-bottom: 0.9rem;
            }

            .terms-footer {
                padding-top: 0.8rem;
                padding-bottom: 0.85rem;
            }

            .terms-submit {
                margin-top: 0.75rem;
                min-height: 2.8rem;
            }
        }
    </style>

    <main class="terms-page">
        <form method="POST" action="{{ route('terms.accept.store') }}" class="terms-card">
            @csrf

            <header class="terms-header">
                <img src="{{ asset('images/sihp-logo.png') }}" alt="SiHP Logo">
                <div>
                    <h1 class="terms-title">ข้อตกลงและเงื่อนไขการใช้งานระบบ (Terms of Use & Disclaimer)</h1>
                    <div class="terms-subtitle">Version {{ $termsVersion }}</div>
                </div>
            </header>

            <section class="terms-body" data-terms-body>
                <p class="terms-intro">
                    ระบบฐานข้อมูลโครงการวิจัยและการติดตามงานนี้ มีวัตถุประสงค์เพื่อสนับสนุนการดำเนินงานภายในองค์กร
                    ได้แก่ การบริหารจัดการโครงการ การติดตามความก้าวหน้า การจัดสรรทรัพยากร
                    และการพัฒนาประสิทธิภาพการทำงานในภาพรวม
                </p>

                <ul class="terms-list">
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
            </section>

            <footer class="terms-footer">
                <label for="terms_accepted" class="terms-consent is-disabled" data-terms-consent-label>
                    <x-checkbox id="terms_accepted" name="terms_accepted" value="1" :checked="old('terms_accepted')" disabled data-terms-checkbox />
                    <span>ข้าพเจ้าได้อ่าน รับทราบ และยอมรับข้อตกลงและเงื่อนไขการใช้งานระบบ</span>
                </label>
                <div class="terms-scroll-hint" data-terms-scroll-hint>
                    กรุณาเลื่อนอ่านข้อตกลงและเงื่อนไขให้ครบถ้วนก่อนติ๊กยอมรับ
                </div>
                @error('terms_accepted')
                    <div class="terms-error">{{ $message }}</div>
                @enderror

                <x-button class="terms-submit" disabled data-terms-submit>
                    ยอมรับและเข้าสู่ระบบ
                </x-button>
            </footer>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.querySelector('[data-terms-body]');
            const checkbox = document.querySelector('[data-terms-checkbox]');
            const submit = document.querySelector('[data-terms-submit]');
            const hint = document.querySelector('[data-terms-scroll-hint]');
            const consentLabel = document.querySelector('[data-terms-consent-label]');

            if (!body || !checkbox || !submit) {
                return;
            }

            const unlockConsent = function () {
                checkbox.disabled = false;
                consentLabel?.classList.remove('is-disabled');
                hint?.classList.add('is-complete');

                if (hint) {
                    hint.textContent = 'อ่านครบแล้ว กรุณาติ๊กยอมรับเพื่อดำเนินการต่อ';
                }
            };

            const updateSubmit = function () {
                submit.disabled = !checkbox.checked;
            };

            const hasReadToEnd = function () {
                return body.scrollTop + body.clientHeight >= body.scrollHeight - 8;
            };

            const checkScroll = function () {
                if (hasReadToEnd()) {
                    unlockConsent();
                    body.removeEventListener('scroll', checkScroll);
                }
            };

            if (body.scrollHeight <= body.clientHeight + 8) {
                unlockConsent();
            } else {
                checkScroll();
                body.addEventListener('scroll', checkScroll, { passive: true });
            }

            updateSubmit();
            checkbox.addEventListener('change', updateSubmit);
        });
    </script>
</x-guest-layout>
