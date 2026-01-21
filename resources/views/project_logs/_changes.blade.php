@php
    $changes = $log->changes ?? [];
@endphp

@if(empty($changes))
    <span class="text-gray-400">-</span>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($changes as $field => $value)
            @php
                if (in_array($field, ['id', 'project_id', 'project', 'created_at', 'updated_at', 'deleted_at'], true)) {
                    continue;
                }
                $label = str_replace('_', ' ', $field);
            @endphp
            <div class="rounded border bg-white p-3">
                <div class="text-gray-700 font-medium mb-1">{{ $label }}</div>
                @if(is_array($value) && array_key_exists('from', $value) && array_key_exists('to', $value))
                    <div class="text-gray-600">
                        <div class="text-gray-500">เดิม</div>
                        <div class="mb-2 break-words">{{ is_array($value['from']) ? json_encode($value['from'], JSON_UNESCAPED_UNICODE) : ($value['from'] ?? '-') }}</div>
                        <div class="text-gray-700">ใหม่</div>
                        <div class="break-words">{{ is_array($value['to']) ? json_encode($value['to'], JSON_UNESCAPED_UNICODE) : ($value['to'] ?? '-') }}</div>
                    </div>
                @elseif(is_array($value))
                    <pre class="text-xs text-gray-600 bg-gray-50 p-2 rounded border whitespace-pre-wrap">{{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                @else
                    <div class="text-gray-600 break-words">{{ $value === '' ? '-' : $value }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endif
