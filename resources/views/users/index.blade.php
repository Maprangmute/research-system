@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-6xl mx-auto mt-10 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">รายชื่อผู้เข้าใช้ระบบ</h2>
            {{-- <p class="text-sm text-gray-500">A list of all the users in your account including their name, title, email and role.</p> --}}
        </div>
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded hover:bg-purple-700">
            เพิ่มผู้เข้าใช้งาน
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ตำแหน่งงาน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สิทธิ์</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap flex items-center">
                        <img class="h-10 w-10 rounded-full object-cover mr-4"
                             src="{{ asset('storage/' . $user->profile_photo_path) }}?v={{ time() }}"
                             onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                             alt="{{ $user->name }}">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $user->prefix }} {{ $user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $user->position ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $user->position ? 'Department' : '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $roleColor = match($user->role) {
                                'admin' => 'bg-blue-100 text-blue-800',
                                'exp' => 'bg-red-100 text-red-800',
                                default => 'bg-green-100 text-green-800'
                            };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $roleColor }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <button onclick="confirmDelete({{ $user->id }}, '{{ $user->prefix }}{{ $user->name }}')"
                                class="text-red-600 hover:text-red-800">Delete</button>
                        <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณกำลังจะลบผู้ใช้: " + name,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endsection