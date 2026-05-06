@php
    $dataClassificationOptions = [
        'Real-World data',
        'Primary data collection',
    ];
    $currentProject = $project ?? null;
@endphp

<div class="md:col-span-2 rounded-lg border border-sky-200 bg-sky-50/70 p-4">
    <label class="block w-full">
        <span class="block text-sm font-semibold text-sky-900 mb-1">แหล่งที่มาของข้อมูล (Data source) และวิธีการใช้ข้อมูล (Method / approach)</span>
        <select name="data_source" class="w-full border border-sky-200 bg-white p-2 rounded">
            <option value="">กรุณาเลือกแหล่งที่มาของข้อมูลและวิธีการใช้ข้อมูล</option>
            @foreach ($dataClassificationOptions as $option)
                <option value="{{ $option }}" {{ old('data_source', data_get($currentProject, 'data_source')) === $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    </label>

    <details open class="mt-3 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-slate-700">
        <summary class="cursor-pointer select-none font-semibold text-sky-900">
            คำอธิบายตัวเลือก Data source / Method
        </summary>

        <div class="mt-3 grid grid-cols-1 lg:grid-cols-2 gap-3">
            <section class="rounded-md border border-sky-100 bg-white p-3">
                <h4 class="font-semibold text-slate-900">Real-World data</h4>
                <p class="mt-2 text-xs font-semibold text-blue-800">ตัวอย่าง</p>
                <p class="mt-1">NHES, claims data สปสช., ข้อมูลคนไข้ในโรงพยาบาลศิริราช, prescription data โครงการ RDU</p>
            </section>

            <section class="rounded-md border border-sky-100 bg-white p-3">
                <h4 class="font-semibold text-slate-900">Primary data collection</h4>
                <p class="mt-2 text-xs font-semibold text-emerald-800">ตัวอย่าง</p>
                <p class="mt-1">cohort study ใหม่, intervention study เช่น เดินดี, RCT, survey ใหม่เฉพาะกลุ่ม</p>
            </section>
        </div>
    </details>
</div>
