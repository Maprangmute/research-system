<!DOCTYPE html>
<html lang="th">
    @php
        $homeUrl = auth()->check() ? url('/dashboard') : route('login');
    @endphp
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>503 | ระบบไม่พร้อมให้บริการชั่วคราว</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,#fce7f3_0%,#f8fafc_38%,#e2e8f0_100%)] text-slate-900">
        <main class="mx-auto flex min-h-screen max-w-5xl items-center justify-center px-4 py-10">
            <section class="w-full max-w-3xl overflow-hidden rounded-[28px] border border-white/70 bg-white/90 shadow-2xl shadow-slate-300/40 backdrop-blur">
                <div class="grid gap-0 md:grid-cols-[1.1fr_0.9fr]">
                    <div class="border-b border-slate-200/80 p-8 md:border-b-0 md:border-r">
                        <div class="inline-flex items-center rounded-full border border-pink-200 bg-pink-50 px-3 py-1 text-xs font-semibold tracking-wide text-pink-700">
                            Error 503
                        </div>

                        <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                            ระบบไม่พร้อมให้บริการชั่วคราว
                        </h1>

                        <p class="mt-4 text-sm leading-7 text-slate-600 md:text-base">
                            ขณะนี้ระบบอาจอยู่ระหว่างการปรับปรุง ดูแลเซิร์ฟเวอร์ หรือมีภาระงานสูงเกินกว่าที่จะตอบสนองคำขอได้ในตอนนี้
                        </p>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600">
                            กรุณารอสักครู่แล้วลองใหม่อีกครั้ง หากปัญหายังเกิดต่อเนื่องค่อยกลับไปยังหน้าหลักของระบบ
                        </div>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <button type="button"
                                onclick="window.location.reload()"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                ลองใหม่อีกครั้ง
                            </button>
                            <a href="{{ $homeUrl }}"
                                class="inline-flex items-center justify-center rounded-xl bg-pink-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-pink-700">
                                กลับหน้าหลัก
                            </a>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-center p-8">
                        <div class="absolute inset-0 bg-[linear-gradient(160deg,rgba(236,72,153,0.12),rgba(15,23,42,0.04))]"></div>
                        <div class="relative text-center">
                            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[24px] bg-pink-100 text-pink-600 shadow-inner shadow-pink-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19h14" />
                                </svg>
                            </div>
                            <div class="mt-6 text-6xl font-black tracking-tight text-slate-200">503</div>
                            <p class="mt-3 text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">
                                Service Unavailable
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
