@extends('layouts.app')

@section('content')
<div class="max-w-full px-4 py-6">
    <h2 class="text-2xl font-bold mb-4">📅 Gantt Chart: {{ $project->project_name_th }}</h2>
    <div id="gantt_here" class="overflow-x-auto w-full border border-gray-300 rounded-lg"></div>
</div>

<!-- ✅ Gantt CSS + JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.gantt/1.2.1/css/style.min.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.gantt/1.2.1/js/jquery.fn.gantt.min.js"></script>

<script>
    $(function () {
        const ganttUrl = "/projects/{{ $project->id }}/tasks";

        $.getJSON(ganttUrl, function (data) {
            console.log("[DEBUG] Gantt data loaded:", data);

            if (!Array.isArray(data) || data.length === 0) {
                $('#gantt_here').html('<p class="text-gray-500">ยังไม่มีงานในโครงการนี้</p>');
                return;
            }

            $("#gantt_here").gantt({
                source: data,
                navigate: "scroll",
                scale: "days",
                maxScale: "months",
                minScale: "hours",
                itemsPerPage: 30,
                useCookie: false,
                scrollToToday: true,
                onItemClick: function(task) {
                    alert("📌 คลิก: " + task.name + "\nสถานะ: " + (task.status || 'ยังไม่กำหนด'));
                },
                onRender: function() {
                    console.log("✅ Gantt rendered successfully.");
                },
                onResize: function(task, newStart, newEnd) {
                    console.log("[DEBUG] Resized:", task, newStart, newEnd);
                    $.ajax({
                        url: '/api/tasks/' + task.id,
                        method: 'PUT',
                        data: {
                            start: newStart,
                            end: newEnd,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function () {
                            console.log("[INFO] Task updated successfully.");
                        },
                        error: function () {
                            alert("❌ เกิดข้อผิดพลาดในการอัปเดตข้อมูล");
                        }
                    });
                }
            });
        }).fail(function(err) {
            console.error("[ERROR] Failed to load Gantt data", err);
            $('#gantt_here').html('<p class="text-red-600">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>');
        });
    });
</script>
@endsection
