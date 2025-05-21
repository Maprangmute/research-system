@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        toast: true,
        position: 'bottom-start',
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

        @include('projects._form', ['project' => $project])

        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">📅 บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<script>
    function removeRow(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    let copiIndex = {{ count($project->copis ?? []) }};
    function addCopiRow() {
        const container = document.getElementById('copi-container');
        const id = `copi-${copiIndex}`;
        container.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="grid grid-cols-2 gap-2 items-center">
                <input name="copis[${copiIndex}][name]" class="w-full border p-2 rounded" placeholder="ชื่อ COPI" />
                <div class="flex gap-2">
                    <input name="copis[${copiIndex}][affiliation]" class="w-full border p-2 rounded" placeholder="สังกัด COPI" />
                    <button type="button" onclick="removeRow('${id}')" class="text-red-600 mt-2">✕</button>
                </div>
            </div>
        `);
        copiIndex++;
    }

    let irbIndex = {{ count($project->irbs ?? []) }};
    function addIRBRow() {
        const container = document.getElementById('irb-code-container');
        const id = `irb-${irbIndex}`;
        container.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="grid grid-cols-2 gap-2">
                <input name="irbs[${irbIndex}][code]" placeholder="ECxxxx" class="w-full border p-2 rounded">
                <div class="flex gap-2">
                    <input name="irbs[${irbIndex}][source]" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
                    <button type="button" onclick="removeRow('${id}')" class="text-red-600 mt-2">✕</button>
                </div>
            </div>
        `);
        irbIndex++;
    }
</script>
@endsection
