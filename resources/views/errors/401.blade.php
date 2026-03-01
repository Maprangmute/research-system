<!DOCTYPE html>
<html lang="th">
    @php
        $homeUrl = auth()->check() ? url('/dashboard') : route('login');
        $primaryLabel = auth()->check() ? 'กลับหน้าหลัก' : 'ไปหน้าเข้าสู่ระบบ';
    @endphp
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>401 | ต้องเข้าสู่ระบบก่อน</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,#dcfce7_0%,#f8fafc_38%,#e2e8f0_100%)] text-slate-900">
        <main class="mx-auto flex min-h-screen max-w-5xl items-center justify-center px-4 py-10">
            <section class="w-full max-w-3xl overflow-hidden rounded-[28px] border border-white/70 bg-white/90 shadow-2xl shadow-slate-300/40 backdrop-blur">
                <div class="grid gap-0 md:grid-cols-[1.1fr_0.9fr]">
                    <div class="border-b border-slate-200/80 p-8 md:border-b-0 md:border-r">
                        <div class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold tracking-wide text-emerald-700">
                            Error 401
                        </div>

                        <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                            ต้องเข้าสู่ระบบก่อนใช้งานหน้านี้
                        </h1>

                        <p class="mt-4 text-sm leading-7 text-slate-600 md:text-base">
                            ระบบยังไม่สามารถยืนยันตัวตนของคุณได้ จึงไม่อนุญาตให้เข้าถึงหน้านี้ในตอนนี้
                            กรุณาเข้าสู่ระบบใหม่ หรือกลับไปเริ่มต้นจากหน้าหลักของระบบ
                        </p>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600">
                            หากคุณเพิ่งออกจากระบบหรือปล่อยหน้าไว้เป็นเวลานาน ระบบอาจขอให้ยืนยันตัวตนใหม่อีกครั้ง
                        </div>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <button type="button"
                                onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ $homeUrl }}'; }"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                กลับหน้าก่อนหน้า
                            </button>
                            <a href="{{ $homeUrl }}"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                {{ $primaryLabel }}
                            </a>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-center p-8">
                        <div class="absolute inset-0 bg-[linear-gradient(160deg,rgba(16,185,129,0.12),rgba(15,23,42,0.04))]"></div>
                        <div class="relative text-center">
                            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[24px] bg-emerald-100 text-emerald-600 shadow-inner shadow-emerald-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11V7a3 3 0 10-6 0v4m-1 0h8a2 2 0 012 2v5a2 2 0 01-2 2H8a2 2 0 01-2-2v-5a2 2 0 012-2z" />
                                </svg>
                            </div>
                            <div class="mt-6 text-6xl font-black tracking-tight text-slate-200">401</div>
                            <p class="mt-3 text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">
                                Unauthorized
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
