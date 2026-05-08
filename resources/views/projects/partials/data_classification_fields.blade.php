@php
    $dataClassificationOptions = [
        'Real-World Data',
        'Primary Data',
        'Real-World Data + Primary Data',
    ];
    $currentProject = $project ?? null;
    $selectedDataSource = old('data_source', data_get($currentProject, 'data_source'));
    $selectedDataSourceLower = mb_strtolower(trim((string) $selectedDataSource), 'UTF-8');
    $selectedDataSource = match (true) {
        $selectedDataSourceLower === 'real-world data + primary data'
            || (str_contains($selectedDataSourceLower, 'real') && str_contains($selectedDataSourceLower, 'primary')) => 'Real-World Data + Primary Data',
        in_array($selectedDataSourceLower, ['real-world data', 'real world data'], true) => 'Real-World Data',
        in_array($selectedDataSourceLower, ['primary data', 'primary data collection'], true) => 'Primary Data',
        default => $selectedDataSource,
    };
@endphp

<div class="md:col-span-2 rounded-lg border border-sky-200 bg-sky-50/70 p-4">
    <label class="block w-full">
        <span class="block text-sm font-semibold text-sky-900 mb-1">แหล่งที่มาของข้อมูล (Data source) และวิธีการใช้ข้อมูล (Method / approach)</span>
        <select name="data_source" class="w-full border border-sky-200 bg-white p-2 rounded">
            <option value="">กรุณาเลือกแหล่งที่มาของข้อมูลและวิธีการใช้ข้อมูล</option>
            @foreach ($dataClassificationOptions as $option)
                <option value="{{ $option }}" {{ $selectedDataSource === $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    </label>

    <details open class="mt-3 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-slate-700">
        <summary class="cursor-pointer select-none font-semibold text-sky-900">
            คำอธิบายตัวเลือก Data source / Method
        </summary>

        <div class="mt-3 grid grid-cols-1 lg:grid-cols-3 gap-3">
            <section class="rounded-md border border-sky-100 bg-white p-3">
                <h4 class="font-semibold text-slate-900">Real-World Data</h4>
                <p class="mt-1 text-slate-600">ข้อมูลผู้ป่วยที่มีอยู่แล้ว (existing data) จากระบบบริการสุขภาพ</p>
                <p class="mt-2 text-xs font-semibold text-blue-800">ตัวอย่าง</p>
                <ul class="mt-1 list-disc space-y-1 pl-4">
                    <li>ข้อมูลผู้ป่วยจากโรงพยาบาลศิริราช หรือที่ศิริราชเป็นเจ้าของ เช่น Si-Data plus, case review จากระบบ SIT โรงพยาบาล, EHRs</li>
                    <li>ข้อมูลจากหน่วยงานภายนอก เช่น สปสช., กระทรวงสาธารณสุข, สำนักงานสถิติแห่งชาติ (Health Examination Survey)</li>
                    <li>ข้อมูลจากโรงพยาบาลอื่น เช่น มข., มช., มอ.</li>
                </ul>
            </section>

            <section class="rounded-md border border-sky-100 bg-white p-3">
                <h4 class="font-semibold text-slate-900">Primary Data</h4>
                <p class="mt-1 text-slate-600">ข้อมูลที่เก็บใหม่ (newly collected data) เพื่อการวิจัยโดยเฉพาะ</p>
                <p class="mt-2 text-xs font-semibold text-emerald-800">ตัวอย่าง</p>
                <ul class="mt-1 list-disc space-y-1 pl-4">
                    <li>Cohort study</li>
                    <li>Survey</li>
                    <li>Interview</li>
                    <li>Intervention Study</li>
                </ul>
            </section>

            <section class="rounded-md border border-sky-100 bg-white p-3">
                <h4 class="font-semibold text-slate-900">Real-World Data + Primary Data</h4>
                <p class="mt-1 text-slate-600">โครงการที่ใช้ทั้งข้อมูล existing data และข้อมูลที่เก็บใหม่ร่วมกัน</p>
                <p class="mt-2 text-xs font-semibold text-violet-800">ใช้เมื่อ</p>
                <p class="mt-1">มีการเชื่อมข้อมูลจากระบบบริการสุขภาพกับ survey, interview, cohort หรือ intervention ที่เก็บเพิ่มในโครงการเดียวกัน</p>
            </section>
        </div>
    </details>
</div>
