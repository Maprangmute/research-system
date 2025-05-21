<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดโครงการ (PDF)</title>


    
</head>
<body>

<h2>A: ข้อมูลโครงการ</h2>
<ul>
    <li><strong>ชื่อโครงการ (TH):</strong> {{ $project->project_name_th }}</li>
    <li><strong>ชื่อโครงการ (EN):</strong> {{ $project->project_name_en }}</li>
    <li><strong>ประเภท:</strong> {{ $project->project_type }}</li>
    <li><strong>Theme:</strong> {{ $project->theme }}</li>
    <li><strong>Shortcut:</strong> {{ $project->shortcut_link }}</li>
    <li><strong>ขนาด:</strong> {{ $project->size }}</li>
    <li><strong>รหัสโครงการ R:</strong> {{ $project->r_code }}</li>
    <li><strong>ปีโปรเจค:</strong> {{ $project->start_year }}</li>
</ul>

<h2>B: ข้อมูลแหล่งทุน</h2>
<table>
    <tr><th>DA102 - ประเภทแหล่งทุน</th><td>{{ $project->funding_type }}</td></tr>
    <tr><th>DA103 - ชื่อแหล่งทุน</th><td>{{ $project->funding_source }}</td></tr>
    <tr><th>DA104 - รัฐบาล / เอกชน / มูลนิธิ</th><td>{{ $project->funding_org_type }}</td></tr>
    <tr><th>DA105 - ชื่อทุน</th><td>{{ $project->funding_name }}</td></tr>
    <tr><th>DA106 - งบประมาณ (บาท)</th><td>{{ number_format($project->budget, 2) }}</td></tr>
</table>

<h2>C: รายชื่อผู้ร่วมวิจัย (COPI)</h2>
<table>
    <thead><tr><th>#</th><th>ชื่อ</th><th>สังกัด</th></tr></thead>
    <tbody>
        @foreach ($project->copis as $i => $copi)
            <tr><td>{{ $i+1 }}</td><td>{{ $copi->name }}</td><td>{{ $copi->affiliation }}</td></tr>
        @endforeach
    </tbody>
</table>

<h2>D: รหัส IRB</h2>
<table>
    <thead><tr><th>#</th><th>รหัส</th><th>หน่วยงาน</th><th>วันหมดอายุ</th></tr></thead>
    <tbody>
        @foreach ($project->irbs as $i => $irb)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $irb->code }}</td>
                <td>{{ $irb->source }}</td>
                <td>{{ $irb->exp_date }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>E: รายชื่อผู้มีส่วนร่วม (FTE)</h2>
<table>
    <thead><tr><th>#</th><th>ชื่อ</th><th>กลุ่ม</th><th>FTE</th></tr></thead>
    <tbody>
        @foreach ($project->fteRoles as $i => $fte)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $fte->name }}</td>
                <td>{{ $fte->role_group }}</td>
                <td>{{ $fte->fte_value }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>F: ผลงานวิจัย (Outputs)</h2>
<table>
    <thead><tr><th>#</th><th>ชื่อผลงาน</th><th>ประเภท</th><th>รายละเอียด</th><th>ปี</th></tr></thead>
    <tbody>
        @foreach ($project->outputs as $i => $output)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $output->title }}</td>
                <td>{{ $output->type }}</td>
                <td>{{ $output->note }}</td>
                <td>{{ $output->year }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>G: ยุทธศาสตร์ (Siriraj Health Policy Strategy)</h2>
<ul>
    @php
        $map = [
            '1' => [
                '1' => '1.1 การสรรหานักวิจัยฯ',
                '2' => '1.2 การเรียนรู้จากการทำงานจริง',
                '3' => '1.3 การศึกษาต่อเนื่อง',
                '4' => '1.4 การบูรณาการการสอน',
                '5' => '1.5 บุคลากรศิริราช',
            ],
            '2' => [
                '1' => '2.1 บทบาทหน่วยวิจัยฯ',
                '2' => '2.2 ความสัมพันธ์กับผู้กำหนดนโยบาย',
                '3' => '2.3 หัวข้อวิจัยสำคัญ',
            ],
            '3' => [
                '1' => '3.1 การสังเคราะห์งานวิจัยต้นแบบ',
                '2' => '3.2 งานวิจัยคุณภาพ',
                '3' => '3.3 ผลักดันสู่นโยบาย',
                '4' => '3.4 นวัตกรรมสู่นโยบาย',
                '5' => '3.5 การสื่อสารสาธารณะ',
            ],
            '4' => [
                '1' => '4.1 เครือข่ายงานวิจัย',
                '2' => '4.2 ความร่วมมือภายนอก',
            ],
            '5' => [
                '1' => '5.1 ระบบสนับสนุนงานวิจัย',
                '2' => '5.2 เส้นทางอาชีพนักวิจัย',
            ],
        ];
    @endphp
    @foreach ($map as $main => $subs)
        @foreach ($subs as $sub => $label)
            @php $field = "strategy_{$main}_{$sub}"; @endphp
            @if (!empty($project->$field))
                <li>{{ $label }}</li>
            @endif
        @endforeach
    @endforeach
</ul>

</body>
</html>
