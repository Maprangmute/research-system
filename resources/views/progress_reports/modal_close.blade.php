<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>กำลังปิดหน้าต่าง</title>
</head>
<body style="font-family: sans-serif; padding: 24px;">
    <div>กำลังปิดหน้าต่าง...</div>

    <script>
        (() => {
            if (!window.parent || window.parent === window) return;

            window.parent.postMessage({
                type: 'progress-report:updated',
                projectId: @json($project->id),
                message: @json($message),
                summary: @json($summary),
            }, window.location.origin);
        })();
    </script>
</body>
</html>
