<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Outputs Report</title>
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
        .hero-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }
        .hero-grid td {
            border: 0;
            padding: 0;
            vertical-align: top;
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
        .chip-project {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }
        .chip-mission {
            border-color: #5eead4;
            background: #ecfdf5;
            color: #0f766e;
        }
        .chip-extra {
            border-color: #f9a8d4;
            background: #fdf2f8;
            color: #9d174d;
        }
        .filters-wrap {
            margin-top: 6px;
        }
        .section-title {
            margin: 18px 0 8px;
            padding: 8px 14px 8px 12px;
            border-radius: 10px;
            border: 1px solid #dbe7f5;
            border-left: 5px solid #0f766e;
            background: linear-gradient(90deg, #ecfeff 0%, #f7feff 100%);
            color: #0f172a;
            font-size: 15.2pt;
            font-weight: bold;
            line-height: 1.2;
        }
        .section-subtitle {
            margin: -2px 0 10px;
            color: #64748b;
            font-size: 12pt;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0 0 14px;
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
        .output-list {
            margin-top: 10px;
        }
        .output-card {
            border: 1px solid #dbe7f5;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            background: #ffffff;
        }
        .output-card:nth-child(even) {
            background: #fcfdff;
        }
        .output-topline {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0 0 6px;
        }
        .output-topline td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .output-index {
            width: 30px;
            font-size: 13.4pt;
            font-weight: bold;
            color: #64748b;
            line-height: 1.4;
        }
        .output-head-main {
            width: 84%;
            padding-right: 10px;
        }
        .output-head-qr {
            width: 16%;
            text-align: right;
            vertical-align: middle;
        }
        .output-title {
            font-size: 18.4pt;
            font-weight: bold;
            line-height: 1.34;
            color: #0f172a;
        }
        .output-meta {
            margin-top: 4px;
            color: #475569;
            font-size: 12.4pt;
            line-height: 1.45;
        }
        .output-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
        }
        .output-grid td {
            border: 0;
            padding: 0 14px 0 0;
            vertical-align: top;
        }
        .output-grid td:last-child {
            padding-right: 0;
        }
        .output-grid .col-main { width: 58%; }
        .output-grid .col-side { width: 42%; }
        .block {
            margin-top: 8px;
        }
        .label {
            margin-bottom: 4px;
            font-size: 11.8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #334155;
        }
        .value {
            font-size: 12.6pt;
            line-height: 1.62;
            color: #1e293b;
            word-break: break-word;
            white-space: normal;
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
        .special-panel {
            margin-top: 12px;
            border: 1px solid #dbe7f5;
            border-radius: 12px;
            padding: 10px 12px;
            page-break-inside: avoid;
            background: #f8fafc;
        }
        .special-paper {
            border-color: #bfdbfe;
            background: #f8fbff;
        }
        .special-presentation {
            border-color: #fed7aa;
            background: #fffaf0;
        }
        .special-award {
            border-color: #ddd6fe;
            background: #fbf7ff;
        }
        .special-textbook {
            border-color: #bbf7d0;
            background: #f0fdf4;
        }
        .special-heading {
            margin-bottom: 8px;
            color: #075985;
            font-size: 11.6pt;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .special-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }
        .special-table td {
            border: 0;
            padding: 0 8px 8px 0;
            vertical-align: top;
        }
        .special-field {
            border: 1px solid #dbe7f5;
            border-radius: 10px;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.78);
        }
        .special-label {
            margin-bottom: 3px;
            color: #64748b;
            font-size: 10.8pt;
            font-weight: bold;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .special-value {
            color: #0f172a;
            font-size: 12.4pt;
            font-weight: bold;
            line-height: 1.45;
            word-break: break-word;
        }
        .special-people {
            border: 1px solid #dbe7f5;
            border-radius: 10px;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.78);
        }
        .special-person-list {
            color: #0f172a;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.6;
        }
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
                $directory = storage_path('framework/cache/output-qrcodes');
                if (!is_dir($directory)) {
                    @mkdir($directory, 0775, true);
                }
                if (!is_dir($directory) || !is_writable($directory)) {
                    return null;
                }
                $path = $directory . '/' . md5($value) . '.png';
                if (!file_exists($path)) {
                    $renderer = new \BaconQrCode\Renderer\GDLibRenderer(80);
                    $writer = new \BaconQrCode\Writer($renderer);
                    $writer->writeFile($value, $path);
                }

                return 'file://' . $path;
            } catch (\Throwable $e) {
                try {
                    // Small reports can still use inline QR if the cache path is not available.
                $renderer = new \BaconQrCode\Renderer\GDLibRenderer(80);
                $writer = new \BaconQrCode\Writer($renderer);
                $png = $writer->writeString($value);
                return 'data:image/png;base64,' . base64_encode($png);
                } catch (\Throwable $fallbackException) {
                    return null;
                }
            }
        };

        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
        $headerLogoPath = public_path('images/sihp-logo.png');
        $headerLogo = file_exists($headerLogoPath) ? 'file://' . $headerLogoPath : null;
        $filters = collect(explode('|', (string) $filterLabel))
            ->map(fn ($filter) => trim($filter))
            ->filter(fn ($filter) => $filter !== '' && $filter !== 'ทั้งหมด')
            ->values();
        $sourceLabel = static fn($row) => trim((string) ($row->source_badge ?? '')) !== '' ? trim((string) $row->source_badge) : 'Project';
        $sourceBadgeClass = static function ($row): string {
            return match (mb_strtolower(trim((string) ($row->source_badge ?? '')))) {
                'mission' => 'badge chip-mission',
                'extra' => 'badge chip-extra',
                default => 'badge chip-project',
            };
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
                        All Outputs
                    </td>
                </tr>
            </table>
        </div>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <table style="width:100%; border-top:1px solid #dbe7f5; padding-top:6px; font-size:11.2pt; color:#64748b; border-collapse:collapse;">
            <tr>
                <td style="width:76%; text-align:left;">ผู้สร้างรายงาน {{ $generatedBy }} เวลา {{ $generatedLabel }}</td>
                <td style="width:24%; text-align:right; white-space:nowrap;">หน้า {PAGENO} / {nbpg}</td>
            </tr>
        </table>
    </htmlpagefooter>

    <div class="hero-shell">
        <table class="hero-grid">
            <tr>
                <td>
                    <div class="report-kicker">Outputs Report</div>
                    <div class="report-title">รายงาน Outputs ทั้งหมด</div>
                    <div class="report-subtitle">
                        สรุปผลงานจาก Project, Mission และ Extra พร้อมประเภทผลงาน ปี รายการอ้างอิง และรายละเอียดที่เกี่ยวข้อง
                    </div>
                    <div class="hero-meta">
                        วันที่ออกรายงาน {{ $generatedLabel }}<br>
                        ผู้สร้างรายงาน {{ $generatedBy }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if ($filters->isNotEmpty())
        <div class="section-title">เงื่อนไขการค้นหา</div>
        <div class="section-subtitle">รายการฟิลเตอร์ที่ถูกใช้ในการสร้างรายงานชุดนี้</div>
        <div class="filters-wrap">
            @foreach ($filters as $filter)
                <span class="chip">{{ $filter }}</span>
            @endforeach
        </div>
    @endif

    <div class="section-title">สรุปภาพรวม</div>
    <div class="section-subtitle">ภาพรวมจำนวน output และแหล่งที่มาของข้อมูลจากผลลัพธ์ปัจจุบัน</div>
    <table class="summary-grid">
        <tr>
            <td style="background:#eef6ff;">
                <div class="summary-label">จำนวน Output</div>
                <div class="summary-value">{{ number_format($summary['total']) }}</div>
                <div class="summary-note">รวมทุกแหล่งข้อมูล</div>
            </td>
            <td style="background:#eff6ff;">
                <div class="summary-label">Project</div>
                <div class="summary-value">{{ number_format($summary['project']) }}</div>
                <div class="summary-note">ผลงานในโครงการวิจัย</div>
            </td>
            <td style="background:#ecfdf5;">
                <div class="summary-label">Mission</div>
                <div class="summary-value">{{ number_format($summary['mission']) }}</div>
                <div class="summary-note">ผลงานจากพันธกิจ</div>
            </td>
            <td style="background:#fdf2f8;">
                <div class="summary-label">Extra</div>
                <div class="summary-value">{{ number_format($summary['extra']) }}</div>
                <div class="summary-note">ผลงานภายนอก</div>
            </td>
        </tr>
    </table>

    <div class="section-title">รายการ Output</div>
    <div class="section-subtitle">รายละเอียดชื่อผลงาน ประเภท ปี ที่มา รายการอ้างอิง และลิงก์หรือข้อมูลประกอบ</div>
    <div class="output-list">
        @forelse ($rows as $index => $row)
            @php
                $metaParts = collect([
                    $row->type ?: null,
                    $row->year_be ? 'ปี ' . $row->year_be : null,
                    $sourceLabel($row),
                ])->filter()->values();
                $qrLink = $firstUrlOrNull($row->related_link ?? null, $row->link ?? null, $row->detail ?? null);
                $qrCode = $qrLink ? $qrDataUri($qrLink) : null;
            @endphp
            <div class="output-card">
                <table class="output-topline">
                    <tr>
                        <td class="output-index">{{ $index + 1 }}.</td>
                        <td class="output-head-main">
                            <div class="output-title">{{ $row->title ?: '-' }}</div>
                            <div class="output-meta">{{ $metaParts->implode(' · ') ?: '-' }}</div>
                            <span class="{{ $sourceBadgeClass($row) }}">{{ $sourceLabel($row) }}</span>
                        </td>
                        <td class="output-head-qr">
                            @if ($qrCode)
                                <div class="qr-wrap head"><img src="{{ $qrCode }}" alt="QR"></div>
                            @endif
                        </td>
                    </tr>
                </table>

                @if (!empty($row->special))
                    @php
                        $special = $row->special;
                        $specialFields = collect($special['fields'] ?? [])->values();
                        $specialPeople = collect($special['people'] ?? [])->values();
                    @endphp
                    <div class="special-panel special-{{ $special['tone'] ?? 'paper' }}">
                        <div class="special-heading">{{ $special['heading'] ?? 'ข้อมูลเฉพาะ' }}</div>
                        <table class="special-table">
                            <tr>
                                <td style="width: 34%;">
                                    @forelse ($specialFields as $field)
                                        <div class="special-field" style="margin-bottom: 8px;">
                                            <div class="special-label">{{ $field['label'] }}</div>
                                            <div class="special-value">{{ $field['value'] }}</div>
                                        </div>
                                    @empty
                                        <div class="special-field">
                                            <div class="special-label">ข้อมูล</div>
                                            <div class="special-value">-</div>
                                        </div>
                                    @endforelse
                                </td>
                                <td style="width: 66%;">
                                    <div class="special-people">
                                        <div class="special-label">{{ $special['people_label'] ?? 'People' }}</div>
                                        <div class="special-person-list">
                                            @forelse ($specialPeople as $personIndex => $person)
                                                {{ $personIndex + 1 }}. {{ $person }}@if (!$loop->last)&nbsp;&nbsp;&nbsp;@endif
                                            @empty
                                                -
                                            @endforelse
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif

                <table class="output-grid">
                    <tr>
                        <td class="col-main">
                            <div class="block">
                                <div class="label">Reference</div>
                                <div class="value">{{ $row->project_label ?: '-' }}</div>
                            </div>
                            @if (!empty($row->detail))
                                <div class="block">
                                    <div class="label">Detail</div>
                                    <div class="value">{{ $row->detail }}</div>
                                </div>
                            @endif
                        </td>
                        <td class="col-side">
                            <div class="block">
                                <div class="label">Type</div>
                                <div class="value">{{ $row->type ?: '-' }}</div>
                            </div>
                            <div class="block">
                                <div class="label">Year</div>
                                <div class="value">{{ $row->year_be ?: '-' }}</div>
                            </div>
                            @if (!empty($row->related_link))
                                <div class="block">
                                    <div class="label">Link</div>
                                    <div class="value">{{ $row->related_link }}</div>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @empty
            <div class="empty-state">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
        @endforelse
    </div>
</body>
</html>
