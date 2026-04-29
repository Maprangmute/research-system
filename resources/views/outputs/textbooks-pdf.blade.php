<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Textbooks Report</title>
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
        .section-title {
            margin: 18px 0 8px;
            padding: 8px 14px 8px 12px;
            border-radius: 10px;
            border: 1px solid #dbe7f5;
            border-left: 5px solid #b45309;
            background: linear-gradient(90deg, #fff7ed 0%, #fffbf7 100%);
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
        .textbook-list {
            margin-top: 10px;
        }
        .textbook-card {
            border: 1px solid #dbe7f5;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            background: #ffffff;
        }
        .textbook-card:nth-child(even) {
            background: #fcfdff;
        }
        .textbook-topline {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0 0 6px;
        }
        .textbook-topline td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .textbook-index {
            width: 30px;
            font-size: 13.4pt;
            font-weight: bold;
            color: #64748b;
            line-height: 1.4;
        }
        .textbook-title {
            font-size: 18.4pt;
            font-weight: bold;
            line-height: 1.34;
            color: #0f172a;
        }
        .textbook-head-main {
            width: 80%;
            padding-right: 10px;
        }
        .textbook-head-qr {
            width: 20%;
            text-align: right;
            vertical-align: middle;
        }
        .textbook-meta-inline {
            margin-top: 4px;
            font-size: 12pt;
            line-height: 1.5;
            color: #475569;
        }
        .textbook-meta-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
        }
        .textbook-meta-grid td {
            border: 0;
            padding: 0 14px 0 0;
            vertical-align: top;
        }
        .textbook-meta-grid td:last-child {
            padding-right: 0;
        }
        .textbook-meta-grid .col-main { width: 58%; }
        .textbook-meta-grid .col-side { width: 42%; }
        .textbook-block {
            margin-top: 10px;
        }
        .textbook-detail-row td {
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }
        .textbook-label {
            margin-bottom: 5px;
            font-size: 12.2pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #334155;
        }
        .textbook-value {
            font-size: 13.3pt;
            line-height: 1.62;
            color: #1e293b;
            word-break: break-word;
            white-space: normal;
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
                $renderer = new \BaconQrCode\Renderer\GDLibRenderer(80);
                $writer = new \BaconQrCode\Writer($renderer);
                $png = $writer->writeString($value);
                return 'data:image/png;base64,' . base64_encode($png);
            } catch (\Throwable $e) {
                return null;
            }
        };

        $generated = ($generatedAt ?? now())->setTimezone('Asia/Bangkok')->locale('th');
        $generatedLabel = $generated->translatedFormat('j F') . ' ' . ($generated->year + 543) . $generated->translatedFormat(' เวลา H:i');
        $headerLogoPath = public_path('images/sihp-logo.png');
        $headerLogo = file_exists($headerLogoPath) ? 'file://' . $headerLogoPath : null;
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
                        Textbook / Book Chapter
                    </td>
                </tr>
            </table>
        </div>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <table style="width:100%; border-top:1px solid #dbe7f5; padding-top:6px; font-size:11.2pt; color:#64748b; border-collapse:collapse;">
            <tr>
                <td style="width:76%; text-align:left;">ผู้สร้างรายงาน {{ $reporter ?? 'ผู้ใช้ระบบ' }} เวลา {{ $generatedLabel }}</td>
                <td style="width:24%; text-align:right; white-space:nowrap;">หน้า {PAGENO} / {nbpg}</td>
            </tr>
        </table>
    </htmlpagefooter>

    <div class="hero-shell">
        <div class="report-kicker">Outputs Report</div>
        <div class="report-title">รายงาน Textbook / Book Chapter</div>
        <div class="report-subtitle">
            สรุปรายการหนังสือและบทในหนังสือ พร้อมผู้เขียน สำนักพิมพ์ ชื่อหนังสือ และโครงการหรือแหล่งอ้างอิงที่เกี่ยวข้อง
        </div>
        <div class="hero-meta">
            วันที่ออกรายงาน {{ $generatedLabel }}<br>
            ผู้สร้างรายงาน {{ $reporter ?? 'ผู้ใช้ระบบ' }}
        </div>
    </div>

    @if (!empty($filters) && count($filters))
        <div class="section-title">เงื่อนไขการค้นหา</div>
        <div class="section-subtitle">รายการฟิลเตอร์ที่ถูกใช้ในการสร้างรายงานชุดนี้</div>
        <div style="margin-top:6px;">
            @foreach (collect($filters) as $filter)
                <span class="chip">{{ $filter }}</span>
            @endforeach
        </div>
    @endif

    <div class="section-title">สรุปภาพรวม</div>
    <div class="section-subtitle">ภาพรวมจำนวนรายการ ผู้เขียน และสำนักพิมพ์ที่เด่นที่สุดจากผลลัพธ์ปัจจุบัน</div>
    <table class="summary-grid">
        <tr>
            <td style="background:#fff7ed;">
                <div class="summary-label">Textbooks / Chapters</div>
                <div class="summary-value">{{ number_format($summary['total_textbooks'] ?? 0) }}</div>
                <div class="summary-note">จำนวนรายการทั้งหมด</div>
            </td>
            <td style="background:#eef6ff;">
                <div class="summary-label">Unique Authors</div>
                <div class="summary-value">{{ number_format($summary['unique_authors'] ?? 0) }}</div>
                <div class="summary-note">จำนวนผู้เขียนไม่ซ้ำ</div>
            </td>
            <td style="background:#f5f3ff;">
                <div class="summary-label">Top Author</div>
                <div class="summary-value" style="font-size:13.2pt;">{{ $summary['top_author'] ?? '-' }}</div>
                <div class="summary-note">ผู้เขียนที่พบมากที่สุด</div>
            </td>
            <td style="background:#ecfeff;">
                <div class="summary-label">Top Publisher</div>
                <div class="summary-value" style="font-size:13.2pt;">{{ $summary['top_publisher'] ?? '-' }}</div>
                <div class="summary-note">สำนักพิมพ์ที่พบมากที่สุด</div>
            </td>
        </tr>
    </table>

    <div class="section-title">รายการหนังสือและบทในหนังสือ</div>
    <div class="section-subtitle">รายละเอียดชื่อผลงาน หนังสือ ผู้เขียน สำนักพิมพ์ ปี และโครงการหรือแหล่งอ้างอิงของแต่ละรายการ</div>
    <div class="textbook-list">
        @forelse ($textbooks as $index => $row)
            @php
                $sourceLabel = $row->project_code
                    ? 'โครงการวิจัย'
                    : (($row->output_code ? 'Extra Outputs' : 'Mission'));
                $metaLine = collect([
                    $sourceLabel,
                    $row->publisher_name && $row->publisher_name !== '-' ? $row->publisher_name : null,
                    $row->chapter_pages && $row->chapter_pages !== '-' ? 'หน้า ' . $row->chapter_pages : null,
                ])->filter()->implode(' · ');
                $qrLink = $firstUrlOrNull($row->detail, $row->project_link);
                $qrCode = $qrLink ? $qrDataUri($qrLink) : null;
            @endphp
            <div class="textbook-card">
                <table class="textbook-topline">
                    <tr>
                        <td class="textbook-index">
                            {{ $index + 1 }}.
                        </td>
                        <td class="textbook-head-main">
                            <div class="textbook-title">{{ $row->title ?: '-' }}@if($row->year_be) ({{ $row->year_be }})@endif</div>
                            <div class="textbook-meta-inline">{{ $metaLine ?: '-' }}</div>
                        </td>
                        <td class="textbook-head-qr">
                            @if ($qrCode)
                                <div class="qr-wrap head"><img src="{{ $qrCode }}" alt="QR"></div>
                            @endif
                        </td>
                    </tr>
                </table>

                <table class="textbook-meta-grid">
                    <tr>
                        <td class="col-main">
                            <div class="textbook-block">
                                <div class="textbook-label">Book Title</div>
                                <div class="textbook-value">{{ $row->book_title ?: '-' }}</div>
                            </div>
                            <div class="textbook-block">
                                <div class="textbook-label">Authors</div>
                                <div class="textbook-value">{{ collect($row->authors)->isEmpty() ? '-' : collect($row->authors)->implode(', ') }}</div>
                            </div>
                        </td>
                        <td class="col-side">
                            <div class="textbook-block">
                                <div class="textbook-label">Publisher</div>
                                <div class="textbook-value">{{ $row->publisher_name ?: '-' }}</div>
                            </div>
                            <div class="textbook-block">
                                <div class="textbook-label">Chapter Pages</div>
                                <div class="textbook-value">{{ $row->chapter_pages ?: '-' }}</div>
                            </div>
                            <div class="textbook-block">
                                <div class="textbook-label">Output Code</div>
                                <div class="textbook-value">{{ $row->output_code ?: '-' }}</div>
                            </div>
                            <div class="textbook-block">
                                <div class="textbook-label">โครงการ / งานอ้างอิง</div>
                                <div class="textbook-value">
                                    {{ $row->project_label ?: '-' }}
                                    @if ($row->project_code)
                                        <span class="muted">({{ $row->project_code }})</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @if (!empty($row->detail))
                        <tr class="textbook-detail-row">
                            <td colspan="2">
                                <div class="textbook-label">รายละเอียด / ลิงก์ / โน้ต</div>
                                <div class="textbook-value">{{ $row->detail }}</div>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        @empty
            <div class="empty-state">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
        @endforelse
    </div>
</body>
</html>
