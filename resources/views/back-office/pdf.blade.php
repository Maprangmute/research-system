<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Back Office Report</title>
    <style>
        @page {
            header: html_Header;
            footer: html_Footer;
            margin-top: 86px;
            margin-bottom: 62px;
            margin-left: 24px;
            margin-right: 24px;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 12.8pt;
            line-height: 1.45;
            color: #1e293b;
        }
        h1, h2, h3, h4, p {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .muted { color: #64748b; }
        .header-line {
            border-bottom: 1px solid #dbe7f5;
            padding-bottom: 6px;
        }
        .report-kicker {
            font-size: 11pt;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
        }
        .report-title {
            margin-top: 6px;
            font-size: 23pt;
            font-weight: bold;
            line-height: 1.16;
            color: #0f172a;
        }
        .report-subtitle {
            margin-top: 6px;
            font-size: 12.4pt;
            color: #475569;
            line-height: 1.45;
        }
        .hero-shell {
            margin-top: 12px;
            border: 1px solid #dbe7f5;
            border-radius: 18px;
            background: linear-gradient(135deg, #f8fbff 0%, #ffffff 48%, #f9fcff 100%);
            padding: 16px 18px;
        }
        .hero-meta {
            margin-top: 10px;
            font-size: 11.6pt;
            color: #64748b;
            line-height: 1.55;
        }
        .chip {
            display: inline-block;
            margin-right: 6px;
            margin-bottom: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #dbe7f5;
            background: #f8fafc;
            font-size: 10.8pt;
            color: #334155;
        }
        .chip--leader {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }
        .chip--period {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 14px 0 16px;
        }
        .summary-grid td {
            width: 25%;
            border: 1px solid #dbe7f5;
            padding: 12px 14px;
            vertical-align: top;
            background-clip: padding-box;
        }
        .summary-label {
            color: #64748b;
            font-size: 11.2pt;
            font-weight: bold;
            line-height: 1.2;
        }
        .summary-value {
            margin-top: 10px;
            font-size: 18pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.18;
        }
        .summary-note {
            margin-top: 8px;
            color: #64748b;
            font-size: 11.2pt;
            line-height: 1.35;
        }
        .section-title {
            margin: 18px 0 8px;
            padding: 8px 14px 8px 12px;
            border-radius: 10px;
            border: 1px solid #dbe7f5;
            color: #0f172a;
            font-size: 15.2pt;
            font-weight: bold;
            line-height: 1.2;
        }
        .section-title--entries {
            border-left: 5px solid #2563eb;
            background: linear-gradient(90deg, #eff6ff 0%, #f8fbff 100%);
        }
        .section-title--outputs {
            border-left: 5px solid #059669;
            background: linear-gradient(90deg, #ecfdf5 0%, #f7fffb 100%);
        }
        .section-subtitle {
            margin: -2px 0 10px;
            color: #64748b;
            font-size: 12pt;
        }
        .entry-list,
        .output-list {
            margin-top: 10px;
        }
        .entry-card,
        .output-card {
            border: 1px solid #dbe7f5;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            background: #ffffff;
        }
        .entry-card:nth-child(even),
        .output-card:nth-child(even) {
            background: #fcfdff;
        }
        .entry-topline,
        .output-topline {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0 0 6px;
        }
        .entry-topline td,
        .output-topline td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .entry-index,
        .output-index {
            width: 30px;
            font-size: 13.4pt;
            font-weight: bold;
            color: #64748b;
            line-height: 1.4;
        }
        .entry-title,
        .output-title {
            font-size: 18.2pt;
            font-weight: bold;
            line-height: 1.32;
            color: #0f172a;
        }
        .entry-meta-inline,
        .output-meta-inline {
            margin-top: 4px;
            font-size: 12pt;
            line-height: 1.5;
            color: #475569;
        }
        .entry-meta-grid,
        .output-meta-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
        }
        .entry-meta-grid td,
        .output-meta-grid td {
            border: 0;
            padding: 0 14px 0 0;
            vertical-align: top;
        }
        .entry-meta-grid td:last-child,
        .output-meta-grid td:last-child {
            padding-right: 0;
        }
        .entry-meta-grid .col-main,
        .output-meta-grid .col-main {
            width: 58%;
        }
        .entry-meta-grid .col-side,
        .output-meta-grid .col-side {
            width: 42%;
        }
        .entry-block,
        .output-block {
            margin-top: 10px;
        }
        .output-block:first-child {
            margin-top: 0;
        }
        .entry-label,
        .output-label {
            margin-bottom: 5px;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #334155;
        }
        .entry-value,
        .output-value {
            font-size: 12.8pt;
            line-height: 1.6;
            color: #1e293b;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .detail-row td {
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }
        .special-value {
            font-size: 12.6pt;
            line-height: 1.6;
            color: #0f172a;
            word-break: break-word;
        }
        .special-list {
            margin: 0;
            padding-left: 18px;
        }
        .special-list li {
            margin: 0 0 6px;
        }
        .special-note {
            margin-top: 5px;
            font-size: 11.8pt;
            line-height: 1.5;
            color: #64748b;
        }
        .special-item {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }
        .special-item:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
        }
        .special-inline-label {
            font-weight: bold;
            color: #334155;
        }
        .badge {
            display: inline-block;
            margin-right: 6px;
            margin-top: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 10.8pt;
            font-weight: bold;
            line-height: 1.2;
        }
        .badge--ea101 { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
        .badge--ea103 { background: #ecfeff; border-color: #a5f3fc; color: #0f766e; }
        .badge--ea118 { background: #fffbeb; border-color: #fde68a; color: #a16207; }
        .badge--ea119 { background: #fff7ed; border-color: #fdba74; color: #c2410c; }
        .badge--default { background: #f8fafc; border-color: #dbe7f5; color: #475569; }
        .qr-wrap {
            width: 18px;
            margin: 0 auto;
            text-align: center;
        }
        .qr-wrap img {
            display: inline-block;
            width: 18px;
            height: 18px;
        }
        .qr-wrap.head {
            width: 22px;
            margin: 0 0 0 auto;
        }
        .qr-wrap.head img {
            width: 22px;
            height: 22px;
        }
        .empty-state {
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            color: #64748b;
            font-size: 12.4pt;
            background: #f8fafc;
        }
    </style>
</head>
<body>
    @php
        $reporter = $user ? trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')) : 'ผู้ใช้ระบบ';
        $authorRoleLabel = function ($value) {
            return match (trim((string) $value)) {
                'first', 'first-author' => 'First-author',
                'co', 'co-author' => 'Co-author',
                'corresponding', 'corresponding-author' => 'Corresponding author',
                default => $value ?: null,
            };
        };
        $formatDateBE = function ($value) {
            if (!$value) {
                return '-';
            }
            try {
                $d = \Carbon\Carbon::createFromFormat('d-m-Y', $value)->locale('th');
                return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
            } catch (\Exception $e) {
                try {
                    $d = \Carbon\Carbon::parse($value)->locale('th');
                    return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
                } catch (\Exception $e) {
                    return $value ?: '-';
                }
            }
        };

        $firstUrlOrNull = function (...$values) {
            foreach ($values as $value) {
                $text = trim((string) ($value ?? ''));
                if ($text === '') {
                    continue;
                }
                if (preg_match('/https?:\/\/[^\s<>"\']+/iu', $text, $match) === 1) {
                    return $match[0];
                }
                if (preg_match('/(?:^|[\s(])((?:www\.)[^\s<>"\')]+)/iu', $text, $match) === 1) {
                    return 'https://' . ltrim($match[1], '/');
                }
                if (filter_var($text, FILTER_VALIDATE_URL)) {
                    return $text;
                }
                if (str_starts_with(mb_strtolower($text), 'www.')) {
                    return 'https://' . ltrim($text, '/');
                }
            }
            return null;
        };

        $qrAsciiUrl = function ($text) {
            $value = trim((string) ($text ?? ''));
            if ($value === '') {
                return null;
            }
            $parts = @parse_url($value);
            if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
                return $value;
            }
            $host = $parts['host'];
            if (function_exists('idn_to_ascii')) {
                $asciiHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
                if (is_string($asciiHost) && $asciiHost !== '') {
                    $host = $asciiHost;
                }
            }
            $encodeUtf8 = static function ($part) {
                return preg_replace_callback('/[^\x21-\x7E]/u', static function ($match) {
                    return rawurlencode($match[0]);
                }, (string) $part);
            };
            $path = $parts['path'] ?? '';
            if ($path !== '') {
                $leadingSlash = str_starts_with($path, '/');
                $segments = array_map(static function ($segment) {
                    return rawurlencode(rawurldecode($segment));
                }, explode('/', ltrim($path, '/')));
                $path = ($leadingSlash ? '/' : '') . implode('/', $segments);
            }
            $query = isset($parts['query']) ? $encodeUtf8($parts['query']) : null;
            $fragment = isset($parts['fragment']) ? $encodeUtf8($parts['fragment']) : null;
            $authority = '';
            if (isset($parts['user'])) {
                $authority .= $parts['user'];
                if (isset($parts['pass'])) {
                    $authority .= ':' . $parts['pass'];
                }
                $authority .= '@';
            }
            $authority .= $host;
            if (isset($parts['port'])) {
                $authority .= ':' . $parts['port'];
            }
            return $parts['scheme'] . '://' . $authority . $path
                . ($query !== null && $query !== '' ? '?' . $query : '')
                . ($fragment !== null && $fragment !== '' ? '#' . $fragment : '');
        };

        $qrDataUri = function ($text) use ($qrAsciiUrl) {
            $value = $qrAsciiUrl($text);
            if ($value === '' || $value === null || !class_exists(\BaconQrCode\Writer::class)) {
                return null;
            }
            try {
                $renderer = new \BaconQrCode\Renderer\GDLibRenderer(80);
                $writer = new \BaconQrCode\Writer($renderer);
                $png = $writer->writeString($value);
                return 'data:image/png;base64,' . base64_encode($png);
            } catch (\Throwable $e) {
                return null;
            }
        };

        $generatedAt = now()->setTimezone('Asia/Bangkok');
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
        $headerLogoPath = public_path('images/sihp-logo.png');
        $headerLogo = file_exists($headerLogoPath) ? 'file://' . $headerLogoPath : null;
        $leadersText = !empty($task->leaders) ? implode(', ', $task->leaders) : '-';
        $periodText = $formatDateBE($task->start_date) . ' ถึง ' . $formatDateBE($task->end_date);
        $creatorText = trim((string) ($task->creator?->name ?? '-'));
        $updatedUser = $task->updated_by ? $task->updater : $task->creator;
        $updatedBy = $updatedUser ? trim((string) ($updatedUser->name ?? '-')) : '-';
        $updatedAtThai = $task->updated_at ? $generatedAt->copy()->parse($task->updated_at)->locale('th')->translatedFormat('j F') . ' ' . ($task->updated_at->year + 543) . $task->updated_at->format(' H:i') . ' น.' : '-';
        $getOutputBadgeClass = function ($type) {
            $type = trim((string) ($type ?? ''));
            if (str_starts_with($type, 'EA101')) return 'badge--ea101';
            if (str_starts_with($type, 'EA103')) return 'badge--ea103';
            if (str_starts_with($type, 'EA118')) return 'badge--ea118';
            if (str_starts_with($type, 'EA119')) return 'badge--ea119';
            return 'badge--default';
        };
    @endphp

    <htmlpageheader name="Header">
        <div class="header-line">
            <table style="width:100%; border-collapse:collapse; font-size:13.6pt; color:#0f172a;">
                <tr>
                    <td style="width:58%; text-align:left; vertical-align:middle;">
                        <table style="border-collapse:collapse;">
                            <tr>
                                @if ($headerLogo)
                                    <td style="vertical-align:middle; padding-right:7px;">
                                        <img src="{{ $headerLogo }}" alt="SiHP Logo" style="height:18px; width:auto; vertical-align:middle;">
                                    </td>
                                @endif
                                <td style="vertical-align:middle; font-size:13.2pt; color:#0f172a; font-weight:bold;">
                                    SiHP Research Database Management System
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:42%; text-align:right; vertical-align:middle; font-size:13.6pt; font-weight:bold; color:#0f172a;">
                        Back Office Report
                    </td>
                </tr>
            </table>
        </div>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <table style="width:100%; border-top:1px solid #dbe7f5; padding-top:6px; font-size:11.2pt; color:#64748b; border-collapse:collapse;">
            <tr>
                <td style="width:76%; text-align:left;">ผู้สร้างรายงาน {{ $reporter }} เวลา {{ $generatedLabel }}</td>
                <td style="width:24%; text-align:right; white-space:nowrap;">หน้า {PAGENO} / {nbpg}</td>
            </tr>
        </table>
    </htmlpagefooter>

    <div class="hero-shell">
        <div class="report-kicker">Back Office Report</div>
        <div class="report-title">รายงานงานนอกโครงการ: {{ $task->title }}</div>
        <div class="report-subtitle">
            สรุปภาระงาน ชั่วโมง FTE และผลงานที่เกิดขึ้นจากงานนอกโครงการ พร้อมรายละเอียดเฉพาะของแต่ละประเภทผลงาน
        </div>
        <div class="hero-meta">
            วันที่ออกรายงาน {{ $generatedLabel }}<br>
            ผู้สร้างรายงาน {{ $reporter }}<br>
            ผู้บันทึก {{ $creatorText }} • ข้อมูลล่าสุดโดย {{ $updatedBy }}
        </div>
        <div style="margin-top:10px;">
            <span class="chip chip--leader">หัวหน้างาน: {{ $leadersText }}</span>
            <span class="chip chip--period">ช่วงเวลา {{ $periodText }}</span>
        </div>
    </div>

    <table class="summary-grid">
        <tr>
            <td>
                <div class="summary-label">จำนวนภาระงาน</div>
                <div class="summary-value">{{ number_format($summary['entries']) }}</div>
                <div class="summary-note">รายการภาระงานทั้งหมดใต้ task นี้</div>
            </td>
            <td>
                <div class="summary-label">ชั่วโมงรวม</div>
                <div class="summary-value">{{ number_format($summary['hours'], 2) }}</div>
                <div class="summary-note">จำนวนชั่วโมงที่บันทึกไว้ทั้งหมด</div>
            </td>
            <td>
                <div class="summary-label">FTE รวม</div>
                <div class="summary-value">{{ number_format($summary['fte'], 6) }}</div>
                <div class="summary-note">สัดส่วน FTE จากภาระงานทั้งหมด</div>
            </td>
            <td>
                <div class="summary-label">จำนวนผลงาน</div>
                <div class="summary-value">{{ number_format($task->outputs->count()) }}</div>
                <div class="summary-note">ผลงานที่ผูกกับงานนอกโครงการนี้</div>
            </td>
        </tr>
    </table>

    <div class="section-title section-title--entries">A. ภาระงาน</div>
    <div class="section-subtitle">รายละเอียดภาระงาน ผู้รับผิดชอบ ช่วงเวลา ชั่วโมง และ FTE ของงานนอกโครงการนี้</div>
    <div class="entry-list">
        @forelse ($task->entries as $entry)
            <div class="entry-card">
                <table class="entry-topline">
                    <tr>
                        <td class="entry-index">{{ $loop->iteration }}.</td>
                        <td>
                            <div class="entry-title">{{ $entry->job_name ?: 'ภาระงานที่ ' . $loop->iteration }}</div>
                            <div class="entry-meta-inline">
                                {{ $entry->person_name }} · {{ $entry->category }} · {{ $formatDateBE($entry->start_date) }} ถึง {{ $formatDateBE($entry->end_date) }}
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="entry-meta-grid">
                    <tr>
                        <td class="col-main">
                            <div class="entry-block">
                                <div class="entry-label">รายละเอียดงาน</div>
                                <div class="entry-value">{{ $entry->job_detail ?: '-' }}</div>
                            </div>
                        </td>
                        <td class="col-side">
                            <div class="entry-block">
                                <div class="entry-label">สรุปตัวเลข</div>
                                <div class="entry-value">
                                    ชั่วโมง: {{ number_format($entry->hours, 2) }}<br>
                                    FTE: {{ number_format($entry->fte_value, 8) }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @empty
            <div class="empty-state">ไม่มีข้อมูลภาระงาน</div>
        @endforelse
    </div>

    <div class="section-title section-title--outputs">B. ผลงาน</div>
    <div class="section-subtitle">ผลงานที่เกิดขึ้นจากงานนอกโครงการ พร้อมประเภท ลิงก์ และรายละเอียดเฉพาะของ EA พิเศษ</div>
    <div class="output-list">
        @forelse ($task->outputs as $output)
            @php
                $isEA101 = str_starts_with((string) ($output->type ?? ''), 'EA101');
                $isEA103 = str_starts_with((string) ($output->type ?? ''), 'EA103');
                $isEA118 = str_starts_with((string) ($output->type ?? ''), 'EA118');
                $isEA119 = str_starts_with((string) ($output->type ?? ''), 'EA119');
                $authors = collect($output->resolvedAuthors());
                $bookAuthors = collect($output->resolvedBookAuthors());
                $conferences = collect($output->resolvedConferences());
                $awardees = collect($output->resolvedAwardRecipients());
                $outputUrl = $firstUrlOrNull($output->project_output);
                $outputQr = $outputUrl ? $qrDataUri($outputUrl) : null;
            @endphp
            <div class="output-card">
                <table class="output-topline">
                    <tr>
                        <td class="output-index">{{ $loop->iteration }}.</td>
                        <td style="width:80%; padding-right:10px;">
                            <div class="output-title">{{ $output->title ?: 'ผลงานที่ ' . $loop->iteration }}</div>
                            <div class="output-meta-inline">
                                <span class="badge {{ $getOutputBadgeClass($output->type) }}">{{ $output->type ?: 'ไม่ระบุประเภท' }}</span>
                                @if (!empty($output->year) && $output->year !== '-')
                                    <span class="chip">เผยแพร่ {{ $formatDateBE($output->year) }}</span>
                                @endif
                            </div>
                        </td>
                        <td style="width:20%; text-align:right; vertical-align:middle;">
                            @if ($outputQr)
                                <div class="qr-wrap head"><img src="{{ $outputQr }}" alt="QR Code"></div>
                            @endif
                        </td>
                    </tr>
                </table>

                <table class="output-meta-grid">
                    <tr>
                        <td class="col-main">
                            <div class="output-block">
                                <div class="output-label">รายละเอียด / ลิงก์ผลงาน</div>
                                <div class="output-value">{{ $output->project_output ?: '-' }}</div>
                            </div>

                            @if ($isEA101)
                                <div class="output-block">
                                    <div class="output-label">Authors</div>
                                    @if ($authors->count())
                                        <ul class="special-list special-value">
                                            @foreach ($authors as $author)
                                                @php
                                                    $authorRoleDisplay = $authorRoleLabel($author['role'] ?? null);
                                                @endphp
                                                <li>
                                                    {{ $author['name'] ?? '-' }}
                                                    @if (!empty($authorRoleDisplay))
                                                        ({{ $authorRoleDisplay }})
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="special-value">-</div>
                                    @endif
                                </div>
                            @elseif ($isEA103)
                                <div class="output-block">
                                    <div class="output-label">ผู้นำเสนอ / Conference</div>
                                    @if ($conferences->count())
                                        @foreach ($conferences as $conference)
                                            @php
                                                $presenterNames = collect($conference['presenters'] ?? [])->pluck('name')->filter()->values()->all();
                                                $presenterDisplay = !empty($presenterNames)
                                                    ? implode(', ', $presenterNames)
                                                    : ($conference['note'] ?? '-');
                                            @endphp
                                            <div class="special-item special-value">
                                                <div><span class="special-inline-label">Conference:</span> {{ $conference['name'] ?? '-' }}</div>
                                                <div class="special-note"><span class="special-inline-label">รูปแบบ:</span> {{ $conference['presentation_type'] ?: '-' }}</div>
                                                <div class="special-note"><span class="special-inline-label">สถานที่:</span> {{ $conference['location'] ?: '-' }}</div>
                                                <div class="special-note"><span class="special-inline-label">ผู้นำเสนอ:</span> {{ $presenterDisplay ?: '-' }}</div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="special-value">-</div>
                                    @endif
                                </div>
                            @elseif ($isEA118)
                                <div class="output-block">
                                    <div class="output-label">ผู้ได้รับรางวัล</div>
                                    @if ($awardees->count())
                                        <ul class="special-list special-value">
                                            @foreach ($awardees as $awardee)
                                                <li>
                                                    {{ $awardee['name'] ?? '-' }}
                                                    @if (!empty($awardee['person_type']))
                                                        ({{ match($awardee['person_type']) {
                                                            'unit' => 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ',
                                                            'internal' => 'คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ',
                                                            'external' => 'คนนอก',
                                                            default => $awardee['person_type'],
                                                        } }})
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="special-value">-</div>
                                    @endif
                                </div>
                            @elseif ($isEA119)
                                <div class="output-block">
                                    <div class="output-label">รายชื่อผู้เขียน</div>
                                    @if ($bookAuthors->count())
                                        <ul class="special-list special-value">
                                            @foreach ($bookAuthors as $author)
                                                <li>{{ $author['name'] ?? '-' }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="special-value">-</div>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="col-side">
                            @if ($isEA101)
                                <div class="output-block">
                                    <div class="output-label">Journal</div>
                                    <div class="special-value">{{ $output->journal ?: '-' }}</div>
                                    <div class="special-note"><span class="special-inline-label">Quartile:</span> {{ $output->quartile ?: '-' }}</div>
                                    <div class="special-note"><span class="special-inline-label">IF:</span> {{ $output->impact_factor ?: '-' }}</div>
                                </div>
                            @elseif ($isEA119)
                                <div class="output-block">
                                    <div class="output-label">ข้อมูลหนังสือ</div>
                                    <div class="special-note"><span class="special-inline-label">ชื่อหนังสือ:</span> {{ $output->book_title ?: '-' }}</div>
                                    <div class="special-note"><span class="special-inline-label">สำนักพิมพ์:</span> {{ $output->publisher_name ?: '-' }}</div>
                                    <div class="special-note"><span class="special-inline-label">บท / เลขหน้า:</span> {{ $output->chapter_pages ?: '-' }}</div>
                                </div>
                            @else
                                <div class="output-block">
                                    <div class="output-label">ประเภทผลงาน</div>
                                    <div class="output-value">{{ $output->type ?: '-' }}</div>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @empty
            <div class="empty-state">ไม่มีผลงานที่บันทึกไว้</div>
        @endforelse
    </div>
</body>
</html>
