@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4">📅 Gantt Chart: {{ $project->project_name_th }}</h2>
    <div id="gantt_here"></div>
</div>

<!-- ✅ Gantt CSS + JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.gantt/1.2.1/css/style.min.css" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                itemsPerPage: 20,
                onItemClick: function(task) {
                    alert("คลิก: " + task.name);
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
