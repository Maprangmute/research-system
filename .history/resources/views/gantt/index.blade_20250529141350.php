@extends('layouts.app')



@section('content')

<!-- ✅ jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ jQuery Gantt Plugin (Full) -->
<link href="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/css/style.css" rel="stylesheet"/>
<script src="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/js/jquery.fn.gantt.js"></script>



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
