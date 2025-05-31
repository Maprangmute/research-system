@extends('layouts.app')



@section('content')

    <!-- HEAD -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.gantt/1.2.1/css/style.min.css" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.gantt/1.2.1/js/jquery.fn.gantt.min.js"></script>


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
