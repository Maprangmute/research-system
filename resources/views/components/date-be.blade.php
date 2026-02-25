@props([
    'name',
    'label',
    'value' => old($name),
    'placeholder' => 'กรุณาเลือกวันที่',
    'required' => false,
    'noPast' => false,
])

@php
    // ✅ สร้าง id จาก name (กันซ้ำ / กัน [])
    $id = 'date_' . str_replace(['[',']'], '_', $name);
    $errorKey = trim(str_replace(['][', '[', ']'], ['.', '.', ''], $name), '.');
@endphp

<div class="w-full">
    <label class="block text-sm text-gray-700 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>

    <div class="relative">
        <input
            id="{{ $id }}"
            type="text"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            readonly
            @if($required) required @endif
            @if($noPast) data-no-past="1" @endif
            class="js-date-be w-full border rounded-lg px-3 py-2 pr-10 bg-white cursor-pointer
                   focus:ring-2 focus:ring-blue-500 @error($errorKey) border-red-500 bg-red-50 @enderror"
        >

        <!-- 📅 icon -->
        <button
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
            data-open-picker
            data-target="{{ $id }}"
        >
            📅
        </button>
    </div>

    @error($errorKey)
        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
    @enderror
</div>
