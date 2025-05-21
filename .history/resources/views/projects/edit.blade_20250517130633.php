@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '{{ session('success') }}',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        toast: true,
        position: 'top-end',
        title: 'เกิดข้อผิดพลาด',
        text: '{{ session('error') }}'
    });
</script>
@endif
<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800">✏️ แก้ไขข้อมูลโครงการ</h2>

    

    <form action="{{ route('projects.update', $project->id) }}" method="POST">
        @csrf
        @method('PUT')

        <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <label>ชื่อโครงการ (ภาษาไทย)*
                <input name="project_name_th" value="{{ old('project_name_th', $project->project_name_th) }}" required class="w-full border p-2 rounded">
            </label>
            <label>ชื่อโครงการ (English)
                <input name="project_name_en" value="{{ old('project_name_en', $project->project_name_en) }}" class="w-full border p-2 rounded">
            </label>
            <label>ประเภทของโครงการ*
                <input name="project_type" value="{{ old('project_type', $project->project_type) }}" required class="w-full border p-2 rounded">
            </label>
            <label>กลุ่มเนื้อหา (Theme)*
                <input name="theme" value="{{ old('theme', $project->theme) }}" required class="w-full border p-2 rounded">
            </label>
            <label>Shortcut / Google Drive
                <input name="shortcut_link" value="{{ old('shortcut_link', $project->shortcut_link) }}" class="w-full border p-2 rounded">
            </label>
            <label>ขนาดโครงการ
                <input name="size" value="{{ old('size', $project->size) }}" class="w-full border p-2 rounded">
            </label>
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <label>เดือนเริ่มต้น
                <input name="start_month" value="{{ old('start_month', $project->start_month) }}" class="w-full border p-2 rounded">
            </label>
            <label>ปีเริ่มต้น
                <input name="start_year" value="{{ old('start_year', $project->start_year) }}" class="w-full border p-2 rounded">
            </label>
            <label>เดือนสิ้นสุด
                <input name="end_month" value="{{ old('end_month', $project->end_month) }}" class="w-full border p-2 rounded">
            </label>
            <label>ปีสิ้นสุด
                <input name="end_year" value="{{ old('end_year', $project->end_year) }}" class="w-full border p-2 rounded">
            </label>
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">C: รหัสโครงการ R</h3>
        <div id="r-code-container" class="space-y-2 mb-4">
            @foreach ($project->r_codes as $i => $code)
                <input name="r_codes[]" value="{{ $code }}" class="w-full border p-2 rounded">
            @endforeach
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">C: รหัส IRB</h3>
        <div id="irb-code-container" class="space-y-2 mb-4">
            @foreach ($project->irb_codes as $i => $code)
                <div class="grid grid-cols-2 gap-2">
                    <input name="irb_codes[]" value="{{ $code }}" class="w-full border p-2 rounded">
                    <input name="irb_sources[]" value="{{ $project->irb_sources[$i] ?? '' }}" class="w-full border p-2 rounded">
                </div>
            @endforeach
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">D: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
        <div id="fte-container" class="space-y-2 mb-4">
            @foreach ($project->fteRoles as $index => $fte)
                <div class="grid grid-cols-3 gap-4">
                    <input name="fte_roles[{{ $index }}][role_group]" value="{{ $fte->role_group }}" class="w-full border p-2 rounded">
                    <input name="fte_roles[{{ $index }}][name]" value="{{ $fte->name }}" class="w-full border p-2 rounded">
                    <input name="fte_roles[{{ $index }}][fte_value]" value="{{ $fte->fte_value }}" class="w-full border p-2 rounded">
                </div>
            @endforeach
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">E: ผลงานวิจัย (Output)</h3>
        <div id="output-container" class="space-y-2 mb-4">
            @foreach ($project->outputs as $index => $output)
                <div class="grid grid-cols-3 gap-4">
                    <input name="outputs[{{ $index }}][type]" value="{{ $output->type }}" class="w-full border p-2 rounded">
                    <input name="outputs[{{ $index }}][title]" value="{{ $output->title }}" class="w-full border p-2 rounded">
                    <input name="outputs[{{ $index }}][year]" value="{{ $output->year }}" class="w-full border p-2 rounded">
                </div>
            @endforeach
        </div>

        <!-- ยุทธศาสตร์ checkbox -->
        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">F: ยุทธศาสตร์</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-6">
            @for ($i = 1; $i <= 5; $i++)
                @for ($j = 1; $j <= 5; $j++)
                    @php $field = "strategy_{$i}_{$j}"; @endphp
                    @if(property_exists($project, $field))
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="{{ $field }}" value="1" {{ $project->$field ? 'checked' : '' }} class="mr-2">
                        {{ $field }}
                    </label>
                    @endif
                @endfor
            @endfor
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">💾 บันทึกการแก้ไข</button>
        </div>
    </form>
</div>
@endsection
