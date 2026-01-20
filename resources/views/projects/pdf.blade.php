<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
   {{-- คนที่ดาวน์โหลด (user ปัจจุบัน) --}}
<meta name="author" content="{{ $user ? trim(($user->prefix ?? '') . ' ' . $user->name) : '' }}">
{{-- ผู้สร้างโครงการ --}}
<meta name="subject" content="{{ $project->createdBy ? trim(($project->createdBy->prefix ?? '') . ' ' . $project->createdBy->name) : '' }}">

    <title>{{ $project->project_name_th }}</title>
    <style>
        @page {
            header: html_Header;
            footer: html_Footer;
            margin-top: 90px;
            margin-bottom: 70px;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 13pt;
            color: #222;
        }
        .section-title {
            background: #FEF9E7;
            color: #C47F00;
            padding: 7px 18px 7px 16px;
            margin: 24px 0 10px;
            font-weight: bold;
            border-radius: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th, td {
            border: 1px solid #dde6f6;
            padding: 8px 13px;
        }
        thead { background: #E3ECFC; }
        .nowrap { white-space: nowrap; }
        .check {
            text-align: center;
            font-size: 1.2em;
            color: #009900;
        }
    </style>
</head>
<body>
@php
    function th_date_or_dash($value) {
        if (!$value) return '-';
        try {
            \Carbon\Carbon::setLocale('th');
            $d = \Carbon\Carbon::parse($value);
            return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
        } catch (\Exception $e) {
            return $value ?: '-';
        }
    }
    function text_or_dash($value) {
        $txt = trim((string)($value ?? ''));
        return $txt !== '' ? $txt : '-';
    }
@endphp
{{-- HEADER --}}
<htmlpageheader name="Header">
    <div style="font-size: 16pt; color: black; padding-left: 10px;">
        {{ $project->project_name_th }}
    </div>
</htmlpageheader>

{{-- FOOTER --}}
<htmlpagefooter name="Footer">
    @php
        use Carbon\Carbon;
        $carbon = Carbon::now()->setTimezone('Asia/Bangkok')->locale('th');
        $carbon->settings(['formatFunction' => 'translatedFormat']);
        $thaiDateTime = $carbon->format('j F Y เวลา H:i');
    @endphp

    <div style="width: 100%; font-size: 12pt; border: none; display: flex; justify-content: space-between; align-items: flex-end; padding: 4px 0;">
        <div style="">
           
        </div>
        <div style="text-align: right;">
            หน้า {PAGENO} จาก {nbpg} <br>
            โดย {{ $user->prefix}} {{ $user->name ?? 'ผู้ใช้ระบบ' }} : {{ $thaiDateTime }}<br>
        </div>
    </div>
</htmlpagefooter>



    {{-- SECTION A --}}
    <div class="section-title">A: ข้อมูลโครงการ</div>
    <ul>
        <li><strong>ชื่อโครงการ (TH):</strong> {{ text_or_dash($project->project_name_th) }}</li>
        <li><strong>ชื่อโครงการ (EN):</strong> {{ text_or_dash($project->project_name_en) }}</li>
        <li><strong>ประเภท:</strong> {{ text_or_dash($project->project_type) }}</li>
        <li><strong>ลักษณะ:</strong> {{ text_or_dash($project->project_list) }}</li>
        <li><strong>Theme:</strong> {{ text_or_dash($project->theme) }}</li>
        <li><strong>Shortcut:</strong> {{ text_or_dash($project->shortcut_link) }}</li>
        <li><strong>ขนาด:</strong> {{ text_or_dash($project->size) }}</li>
        <li><strong>รหัสโครงการ R:</strong> {{ text_or_dash($project->r_code) }}</li>
        <li><strong>ปีโปรเจค:</strong> {{ text_or_dash($project->start_year) }}</li>
        <li><strong>สถานะ:</strong> {{ text_or_dash($project->planning_status) }}</li>
    </ul>

    {{-- SECTION B --}}
    <div class="section-title">B: ระยะเวลาดำเนินโครงการ</div>
    <table>
        <thead>
            <tr>
                <th>วันที่โครงการเริ่มทำงานของทีมวิจัย</th>
                <th>วันที่สิ้นสุดการทำงานของทีมวิจัย</th>
                <th>BA001 - วันที่เริ่มเขียน Proposal</th>
                <th>BC101 - วันที่สัญญาอนุมัติ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ th_date_or_dash($project->team_start_date) }}</td>
                <td>{{ th_date_or_dash($project->team_end_date) }}</td>
                <td>{{ th_date_or_dash($project->proposal_date) }}</td>
                <td>{{ th_date_or_dash($project->contract_date) }}</td>
            </tr>
        </tbody>
    </table>
    <table>
        <thead>
            <tr>
                <th>BC102 - วันที่โครงการเริ่ม (ตามสัญญา)</th>
                <th>BC103 - วันที่สิ้นสุด (ตามสัญญา)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ th_date_or_dash($project->start_date) }}</td>
                <td>{{ th_date_or_dash($project->end_date) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- SECTION C --}}
    <div class="section-title">C: ข้อมูลแหล่งทุน</div>
    <table>
        <thead>
            <tr>
                <th>DA102 - ประเภทแหล่งทุน</th>
                <th>DA103 - ชื่อแหล่งทุน</th>
                <th>DA104 - รัฐบาล / เอกชน / มูลนิธิ</th>
                <th>DA105 - ชื่อทุน</th>
                <th>DA106 - งบประมาณ (บาท)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ text_or_dash($project->funding_type) }}</td>
                <td>{{ text_or_dash($project->funding_source) }}</td>
                <td>{{ text_or_dash($project->funding_org_type) }}</td>
                <td>{{ text_or_dash($project->funding_name) }}</td>
                <td>{{ $project->budget !== null ? number_format($project->budget, 2) : '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- SECTION D: Roles and COPI --}}
    <div class="section-title">D: รายชื่อผู้ร่วมวิจัย</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">ลำดับ</th>
                <th>ชื่อ</th>
                <th>สังกัด</th>
                <th>บทบาท</th>
            </tr>
        </thead>
        <tbody>
            {{-- PI --}}
            <tr>
                <td class="nowrap">1</td>
                <td>{{ text_or_dash($project->pi_name) }}</td>
                <td>{{ text_or_dash($project->pi_affiliation) }}</td>
                <td>PI</td>
            </tr>
            {{-- COPI --}}
            @forelse ($project->copis as $i => $copi)
                <tr>
                    <td class="nowrap">{{ $i+2 }}</td>
                    <td>{{ text_or_dash($copi->name) }}</td>
                    <td>{{ text_or_dash($copi->affiliation) }}</td>
                    <td>Co-PI</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="check">ไม่มี Co-PI</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION D2: Co-Researcher --}}
    <div class="section-title">D2: Co-Researcher</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">ลำดับ</th>
                <th>ชื่อ</th>
                <th>สังกัด</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @forelse ($project->coResearches as $co)
                <tr>
                    <td class="nowrap">{{ $i++ }}</td>
                    <td>{{ text_or_dash($co->name) }}</td>
                    <td>{{ text_or_dash($co->affiliation) }}</td>
                    <td>{{ text_or_dash($co->unit_status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION E: FTE --}}
    <div class="section-title">E: รายชื่อผู้มีส่วนร่วม (FTE)</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">#</th>
                <th>ชื่อ</th>
                <th>กลุ่ม</th>
                <th>ภาระงาน</th>
                <th>รายละเอียดภารกิจ</th>
                <th>วันเริ่ม</th>
                <th>วันสิ้นสุด</th>
                <th class="nowrap">FTE</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->fteRoles as $i => $fte)
                <tr>
                    <td class="nowrap">{{ $i+1 }}</td>
                    <td>{{ text_or_dash($fte->name) }}</td>
                    <td>{{ text_or_dash($fte->role_group) }}</td>
                    <td>{{ text_or_dash($fte->job_detail) }}</td>
                    <td>{{ text_or_dash($fte->task_detail) }}</td>
                    <td>
                        {{ th_date_or_dash($fte->start_date) }}
                    </td>
                    <td>
                        {{ th_date_or_dash($fte->end_date) }}
                    </td>
                    <td class="nowrap">{{ text_or_dash($fte->fte_value) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION F: IRB --}}
    <div class="section-title">F: รหัส IRB</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">#</th>
                <th>รหัส</th>
                <th>หน่วยงาน</th>
                <th>วันหมดอายุ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->irbs as $i => $irb)
                <tr>
                    <td class="nowrap">{{ $i+1 }}</td>
                    <td>{{ text_or_dash($irb->code) }}</td>
                    <td>{{ text_or_dash($irb->source) }}</td>
                    <td>
                        {{ th_date_or_dash($irb->exp_date) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION G: Outputs --}}
    <div class="section-title">G: ผลงานวิจัย (Outputs)</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">#</th>
                <th>ชื่อผลงาน</th>
                <th>ประเภท</th>
                <th>รายละเอียด/ลิงก์</th>
                <th>วันที่เผยแพร่</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->outputs as $i => $output)
                <tr>
                    <td class="nowrap">{{ $i+1 }}</td>
                    <td>{{ text_or_dash($output->title) }}</td>
                    <td>{{ text_or_dash($output->type) }}</td>
                    <td>{{ text_or_dash($output->project_output ?? $output->output ?? '') }}</td>
                    <td>{{ th_date_or_dash($output->year) }}</td>
                </tr>
                @if (!empty($output->journal) || !empty($output->quartile) || $output->impact_factor !== null)
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <strong>Journal:</strong> {{ text_or_dash($output->journal) }} |
                            <strong>Quartile:</strong> {{ text_or_dash($output->quartile) }} |
                            <strong>Impact Factor:</strong> {{ text_or_dash($output->impact_factor) }}
                        </td>
                    </tr>
                @endif
                @if (!empty($output->authors) && $output->authors->count())
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <strong>Authors:</strong>
                            {{ $output->authors->pluck('name')->filter()->implode(', ') }}
                        </td>
                    </tr>
                @endif
                @if (!empty($output->conferences) && $output->conferences->count())
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <strong>Conferences:</strong>
                            {{ $output->conferences->pluck('name')->filter()->implode(', ') }}
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="5" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION H: ยุทธศาสตร์ --}}
    <div class="section-title">H: ยุทธศาสตร์ Siriraj Health Policy</div>
    <table>
        <thead>
            <tr>
                <th>หมวด</th>
                <th>รายการ</th>
                <th>มี</th>
            </tr>
        </thead>
        <tbody>
            @php
                $strategies = [
                    '1' => [
                        'label' => 'AA501: Capacity Building',
                        'items' => [
                            '1' => '1.1 การสรรหานักวิจัยด้านนโยบายสุขภาพที่มีความสามารถ',
                            '2' => '1.2 การพัฒนาศักยภาพฯ ผ่านการเรียนรู้จากการปฏิบัติงานจริง',
                            '3' => '1.3 การพัฒนาศักยภาพฯ ผ่านการศึกษาต่อเนื่อง',
                            '4' => '1.4 การบูรณาการกับการเรียนการสอนนักศึกษาแพทย์',
                            '5' => '1.5 การพัฒนาศักยภาพฯ ให้แก่บุคลากรศิริราช',
                        ],
                    ],
                    '2' => [
                        'label' => 'AA502: Excellence Research',
                        'items' => [
                            '1' => '2.1 การเพิ่มบทบาทของหน่วยวิจัยฯ',
                            '2' => '2.2 การสร้างความสัมพันธ์กับผู้กำหนดนโยบาย',
                            '3' => '2.3 การสรรหาและกำหนดหัวข้อวิจัย',
                        ],
                    ],
                    '3' => [
                        'label' => 'AA503: Health Policy Linkage',
                        'items' => [
                            '1' => '3.1 การสังเคราะห์องค์ความรู้ฯ จากงานวิจัยต้นแบบ',
                            '2' => '3.2 การดำเนินงานวิจัยที่มีคุณภาพ',
                            '3' => '3.3 ผลักดันให้งานวิจัยใช้ตัดสินใจนโยบาย',
                            '4' => '3.4 เชื่อมโยงนวัตกรรมกับการพัฒนานโยบาย',
                            '5' => '3.5 การสื่อสารงานวิจัยต่อสาธารณะ',
                        ],
                    ],
                    '4' => [
                        'label' => 'AA504: Partnership Engagement',
                        'items' => [
                            '1' => '4.1 การสร้างเครือข่ายงานวิจัย',
                            '2' => '4.2 การสร้างความร่วมมือฯ',
                        ],
                    ],
                    '5' => [
                        'label' => 'AA505: System Support',
                        'items' => [
                            '1' => '5.1 การพัฒนาระบบสนับสนุนการดำเนินงานวิจัย',
                            '2' => '5.2 การพัฒนาระบบสนับสนุนเส้นทางอาชีพในหน่วยวิจัย',
                        ],
                    ],
                ];
            @endphp
            @foreach ($strategies as $sKey => $strategy)
                @foreach ($strategy['items'] as $iKey => $desc)
                <tr>
                    @if ($loop->first)
                        <td rowspan="{{ count($strategy['items']) }}" style="font-weight:bold;">{{ $strategy['label'] }}</td>
                    @endif
                    <td>{{ $desc }}</td>
              <td class="check font-weight-bold" style="color: red;">
    {{-- Check if the project has this strategy item --}}
    {!! !empty($project->{'strategy_'.$sKey.'_'.$iKey}) ? '<strong style="color:red;">X</strong>' : '' !!}
</td>

                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- PAGE NUMBER (for wkhtmltopdf auto replace) --}}
    <script type="text/javascript">
        (function() {
            function subst() {
                var vars = {};
                var x = window.location.search.substring(1).split('&');
                for (var i in x) {
                    var z = x[i].split('=', 2);
                    vars[z[0]] = unescape(z[1]);
                }
                var y = ['page', 'topage', 'frompage', 'topage'];
                for (var i in y) {
                    var els = document.getElementsByClassName(y[i]);
                    for (var j = 0; j < els.length; ++j) {
                        if (y[i] === 'page') {
                            els[j].textContent = vars['page'];
                        }
                        if (y[i] === 'topage') {
                            els[j].textContent = vars['topage'];
                        }
                    }
                }
            }
            window.onload = subst;
        })();
    </script>
</body>
</html>
