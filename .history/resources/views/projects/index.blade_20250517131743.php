@extends('layouts.app')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


@if(session('error'))
<script>
    Swal.fire({
        toast: true,
        position: 'bottom-end',
        icon: 'error',
        title: '{{ session('error') }}',
        showConfirmButton: false,
        timer: 7000,
        timerProgressBar: true,
        showClass: {
            popup: 'animate__animated animate__fadeInUp'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutDown'
        }
    });
</script>
@endif



<div class="max-w-7xl mx-auto mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 รายการโครงการวิจัย</h2>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('projects.index') }}" class="bg-white p-4 rounded shadow mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="search" placeholder="ค้นหาชื่อโครงการ" value="{{ request('search') }}" class="border p-2 rounded">
        <input type="text" name="type" placeholder="ประเภทโครงการ" value="{{ request('type') }}" class="border p-2 rounded">
        <input type="text" name="year" placeholder="ปี (พ.ศ.)" value="{{ request('year') }}" class="border p-2 rounded">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">🔍 ค้นหา</button>
    </form>

    <!-- Project List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">ชื่อโครงการ</th>
                    <th class="px-4 py-2">ประเภท</th>
                    <th class="px-4 py-2">Theme</th>
                    <th class="px-4 py-2">ปี</th>
                    <th class="px-4 py-2">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $project->id }}</td>
                    <td class="px-4 py-2">{{ $project->project_name_th }}</td>
                    <td class="px-4 py-2">{{ $project->project_type }}</td>
                    <td class="px-4 py-2">{{ $project->theme }}</td>
                    <td class="px-4 py-2">{{ $project->start_year }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('projects.edit', $project->id) }}" class="text-blue-600 hover:underline">✏️ แก้ไข</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4">ไม่มีข้อมูล</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
</div>
@endsection
