@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4">📅 Gantt Chart: {{ $project->project_name_th }}</h2>
    <div id="gantt_here"></div>
</div>

<script>
$(function () {
    $('#gantt_here').gantt({
        source: function() {
            return $.getJSON('/api/projects/{{ $project->id }}/tasks');
        },
        navigate: "scroll",
        scale: "days",
        maxScale: "months",
        minScale: "hours",
        itemsPerPage: 20,
        onItemClick: function(data) {
            alert("คลิกงาน: " + data.name);
        },
        onRender: function() {
            console.log("Gantt rendered");
        },
        onResize: function(data, newStart, newEnd) {
            $.ajax({
                url: '/api/tasks/' + data.id,
                method: 'PUT',
                data: {
                    start: newStart,
                    end: newEnd,
                    _token: '{{ csrf_token() }}'
                }
            });
        }
    });
});
</script>
@endsection
