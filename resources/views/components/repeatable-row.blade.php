@props([
  'id',
  'containerId' => null,
  'templateId' => null,
  'addText' => '+ เพิ่ม',
  'addClass' => 'px-3 py-1 bg-blue-600 text-white rounded',
  'startIndex' => 1,
])

@php
  $containerId = $containerId ?: "{$id}-container";
  $templateId  = $templateId  ?: "{$id}-row-template";
@endphp

<div
  data-repeatable
  data-id="{{ $id }}"
  data-container="{{ $containerId }}"
  data-template="{{ $templateId }}"
  data-index="{{ $startIndex }}"
>
  {{-- container ของ rows --}}
  <div id="{{ $containerId }}" class="space-y-3">
    {{ $slot }}
  </div>

  {{-- ปุ่มเพิ่ม --}}
  <button
    type="button"
    class="{{ $addClass }} mt-3"
    data-repeatable-add="{{ $id }}"
  >
    {{ $addText }}
  </button>
</div>

{{-- template สำหรับ clone --}}
<template id="{{ $templateId }}">
  {{ $template }}
</template>
