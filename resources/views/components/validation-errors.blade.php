@if ($errors->any())
    <div {{ $attributes }}>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 text-lg leading-none text-rose-600">!</div>
                <div class="min-w-0 flex-1">
                    <div class="font-semibold text-rose-700">เข้าสู่ระบบไม่สำเร็จ</div>
                    <div class="mt-1 text-sm text-rose-600">กรุณาตรวจสอบข้อมูลแล้วลองใหม่อีกครั้ง</div>
                    <ul class="mt-3 space-y-1 text-sm text-rose-700">
            @foreach ($errors->all() as $error)
                        <li class="leading-6">• {{ $error }}</li>
            @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif
