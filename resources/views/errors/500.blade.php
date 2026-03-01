<!DOCTYPE html>
<html lang="th">
    @php
        $homeUrl = auth()->check() ? url('/dashboard') : route('login');
    @endphp
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>500 | ระบบเกิดข้อผิดพลาด</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,#fee2e2_0%,#fff7ed_36%,#f8fafc_100%)] text-slate-900">
        <main class="mx-auto flex min-h-screen max-w-5xl items-center justify-center px-4 py-10">
            <section class="w-full max-w-3xl overflow-hidden rounded-[28px] border border-white/70 bg-white/90 shadow-2xl shadow-slate-300/40 backdrop-blur">
                <div class="grid gap-0 md:grid-cols-[1.1fr_0.9fr]">
                    <div class="border-b border-slate-200/80 p-8 md:border-b-0 md:border-r">
                        <div class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold tracking-wide text-rose-700">
                            Error 500
                        </div>

                        <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                            ระบบเกิดข้อผิดพลาดภายใน
                        </h1>

                        <p class="mt-4 text-sm leading-7 text-slate-600 md:text-base">
                            ระบบไม่สามารถประมวลผลคำขอนี้ได้ในขณะนี้ อาจเกิดจากข้อผิดพลาดชั่วคราวของเซิร์ฟเวอร์หรือการประมวลผลข้อมูลบางส่วน
                        </p>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600">
                            คุณสามารถลองโหลดหน้าใหม่อีกครั้ง หากปัญหายังเกิดซ้ำ กรุณาติดต่อผู้ดูแลระบบพร้อมแจ้งขั้นตอนที่ทำก่อนเกิดข้อผิดพลาด
                        </div>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <button type="button"
                                onclick="window.location.reload()"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                ลองโหลดใหม่
                            </button>
                            <a href="{{ $homeUrl }}"
                                class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">
                                กลับหน้าหลัก
                            </a>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-center p-8">
                        <div class="absolute inset-0 bg-[linear-gradient(160deg,rgba(244,63,94,0.1),rgba(15,23,42,0.05))]"></div>
                        <div class="relative text-center">
                            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[24px] bg-rose-100 text-rose-600 shadow-inner shadow-rose-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4m0 4h.01M5.455 19h13.09c1.054 0 1.714-1.14 1.19-2.05L13.19 5.95c-.527-.915-1.854-.915-2.38 0L4.265 16.95C3.74 17.86 4.4 19 5.455 19z" />
                                </svg>
                            </div>
                            <div class="mt-6 text-6xl font-black tracking-tight text-slate-200">500</div>
                            <p class="mt-3 text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">
                                Server Error
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
