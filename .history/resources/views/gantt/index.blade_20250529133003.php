<div class="max-w-6xl mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4">📅 แผนงานโครงการ: {{ $project->project_name_th ?? '-' }}</h2>

    <div class="overflow-auto border rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 text-left">ชื่อ</th>
                    <th class="p-2 text-left">วันที่เริ่ม</th>
                    <th class="p-2 text-left">วันที่สิ้นสุด</th>
                    <th class="p-2 text-left">สถานะ</th>
                    <th class="p-2 text-left">ระยะเวลา</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($tasks as $task)
                <tr class="{{ $task->parent_id ? 'bg-gray-50' : '' }}">
                    <td class="p-2">
                        @if($task->parent_id)
                            ↳ <span class="text-sm">{{ $task->title }}</span>
                        @else
                            <span class="font-semibold">{{ $task->title }}</span>
                        @endif
                    </td>
                    <td class="p-2">{{ $task->start }}</td>
                    <td class="p-2">{{ $task->end }}</td>
                    <td class="p-2">
                        <span class="px-2 py-1 text-xs rounded-full {{ $task->status === 'done' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                            {{ $task->status === 'done' ? 'เสร็จแล้ว' : 'กำลังทำ' }}
                        </span>
                    </td>
                    <td class="p-2">
                        <div class="h-4 bg-gray-200 rounded relative">
                            @php
                                $start = \Carbon\Carbon::parse($task->start);
                                $end = \Carbon\Carbon::parse($task->end);
                                $days = $end->diffInDays($start) + 1;
                                $percent = min($days * 5, 100); // scale width
                            @endphp
                            <div class="absolute left-0 top-0 h-4 bg-blue-500 rounded" style="width: {{ $percent }}%;"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
