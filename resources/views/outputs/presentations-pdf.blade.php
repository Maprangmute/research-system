<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Presentations Report</title>
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
        .presentation-list {
            margin-top: 10px;
        }
        .presentation-card {
            border: 1px solid #dbe7f5;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            background: #ffffff;
        }
        .presentation-card:nth-child(even) {
            background: #fcfdff;
        }
        .presentation-topline {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0 0 6px;
        }
        .presentation-topline td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .presentation-index {
            width: 30px;
            font-size: 13.4pt;
            font-weight: bold;
            color: #64748b;
            line-height: 1.4;
        }
        .presentation-title {
            font-size: 15.8pt;
            font-weight: bold;
            line-height: 1.42;
            color: #0f172a;
        }
        .presentation-year {
            white-space: nowrap;
            text-align: right;
            font-size: 11.8pt;
            color: #64748b;
        }
        .presentation-meta-inline {
            margin-top: 4px;
            font-size: 12pt;
            line-height: 1.5;
            color: #475569;
        }
        .presentation-meta-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
        }
        .presentation-meta-grid td {
            border: 0;
            padding: 0 14px 0 0;
            vertical-align: top;
        }
        .presentation-meta-grid td:last-child {
            padding-right: 0;
        }
        .presentation-meta-grid .col-main { width: 48%; }
        .presentation-meta-grid .col-side { width: 26%; }
        .presentation-meta-grid .col-qr {
            width: 40px;
            text-align: center;
            vertical-align: top;
            padding-right: 0;
        }
        .presentation-block {
            margin-top: 10px;
        }
        .presentation-label {
            margin-bottom: 4px;
            font-size: 11.1pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .presentation-value {
            font-size: 13.3pt;
            line-height: 1.66;
            color: #1e293b;
            word-break: break-word;
            white-space: normal;
        }
        .qr-wrap {
            width: 28px;
            margin: 2px auto 0;
            text-align: center;
        }
        .qr-wrap img {
            display: inline-block;
            width: 28px;
            height: 28px;
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
                $renderer = new \BaconQrCode\Renderer\GDLibRenderer(120);
                $writer = new \BaconQrCode\Writer($renderer);
                $png = $writer->writeString($value);
                return 'data:image/png;base64,' . base64_encode($png);
            } catch (\Throwable $e) {
                return null;
            }
        };

        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
    @endphp

    <htmlpageheader name="Header">
        <div class="header-line" style="font-size:13.6pt; color:#0f172a;">
            Presentations in Academic Conferences
        </div>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <div style="width:100%; font-size:11.2pt; color:#64748b; border-top:1px solid #dbe7f5; padding-top:6px;">
            <div style="float:left;">
                ผู้สร้างรายงาน {{ $reporter }}
            </div>
            <div style="float:right; text-align:right;">
                หน้า {PAGENO} / {nbpg}<br>
                {{ $generatedLabel }}
            </div>
        </div>
    </htmlpagefooter>

    <div class="hero-shell">
        <div class="report-kicker">Outputs Report</div>
        <div class="report-title">รายงาน Presentations in Academic Conferences</div>
        <div class="report-subtitle">
            สรุปรายการนำเสนอผลงานวิชาการ พร้อมรูปแบบกิจกรรม การประชุม สถานที่ และแหล่งอ้างอิงของรายการในระบบ
        </div>
        <div class="hero-meta">
            วันที่ออกรายงาน {{ $generatedLabel }}<br>
            ผู้สร้างรายงาน {{ $reporter }}
        </div>
    </div>

    @if ($filters->isNotEmpty())
        <div class="section-title">เงื่อนไขการค้นหา</div>
        <div class="section-subtitle">รายการฟิลเตอร์ที่ถูกใช้ในการสร้างรายงานชุดนี้</div>
        <div style="margin-top:6px;">
            @foreach ($filters as $filter)
                <span class="chip">{{ $filter }}</span>
            @endforeach
        </div>
    @endif

    <div class="section-title">สรุปภาพรวม</div>
    <div class="section-subtitle">ภาพรวมจำนวนรายการ ผู้นำเสนอ และเวทีการประชุมที่พบมากที่สุดจากผลลัพธ์ปัจจุบัน</div>
    <table class="summary-grid">
        <tr>
            <td style="background:#ecfeff;">
                <div class="summary-label">Presentations</div>
                <div class="summary-value">{{ number_format($summary['total_presentations']) }}</div>
                <div class="summary-note">จำนวนรายการนำเสนอทั้งหมด</div>
            </td>
            <td style="background:#eef6ff;">
                <div class="summary-label">Presenters</div>
                <div class="summary-value">{{ number_format($summary['unique_presenters']) }}</div>
                <div class="summary-note">จำนวนผู้นำเสนอไม่ซ้ำ</div>
            </td>
            <td style="background:#f5f3ff;">
                <div class="summary-label">Top Presenter</div>
                <div class="summary-value" style="font-size:13.2pt;">{{ $summary['top_presenter'] ?? '-' }}</div>
                <div class="summary-note">ผู้นำเสนอที่พบมากที่สุด</div>
            </td>
            <td style="background:#fff7ed;">
                <div class="summary-label">Top Conference</div>
                <div class="summary-value" style="font-size:13.2pt;">{{ $summary['top_conference'] ?? '-' }}</div>
                <div class="summary-note">เวทีหรือการประชุมที่พบมากที่สุด</div>
            </td>
        </tr>
    </table>

    <div class="section-title">รายการการนำเสนอ</div>
    <div class="section-subtitle">รายละเอียดชื่อผลงาน กิจกรรม ผู้นำเสนอ Conference สถานที่ และโครงการอ้างอิงของแต่ละรายการ</div>
    <div class="presentation-list">
        @forelse ($presentations as $index => $row)
            @php
                $sourceLabel = $row->source_badge ?: 'โครงการวิจัย';
                $metaLine = collect([
                    $sourceLabel,
                    $row->presentation_type && $row->presentation_type !== '-' ? $row->presentation_type : null,
                    $row->presenter_name && $row->presenter_name !== '-' ? 'Presenter: ' . $row->presenter_name : null,
                ])->filter()->implode(' · ');
                $qrLink = $firstUrlOrNull($row->detail, $row->project_link);
                $qrCode = $qrLink ? $qrDataUri($qrLink) : null;
            @endphp
            <div class="presentation-card">
                <table class="presentation-topline">
                    <tr>
                        <td class="presentation-index">{{ $index + 1 }}.</td>
                        <td>
                            <div class="presentation-title">{{ $row->title ?: '-' }}</div>
                            <div class="presentation-meta-inline">{{ $metaLine ?: '-' }}</div>
                        </td>
                        <td class="presentation-year">ปี {{ $row->year_be ?: '-' }}</td>
                    </tr>
                </table>

                <table class="presentation-meta-grid">
                    <tr>
                        <td class="col-main">
                            <div class="presentation-block">
                                <div class="presentation-label">Conference / Event</div>
                                <div class="presentation-value">{{ $row->conference_name ?: '-' }}</div>
                            </div>
                            @if (!empty($row->detail))
                                <div class="presentation-block">
                                    <div class="presentation-label">รายละเอียด / ลิงก์ / โน้ต</div>
                                    <div class="presentation-value">{{ $row->detail }}</div>
                                </div>
                            @endif
                        </td>
                        <td class="col-side">
                            <div class="presentation-block">
                                <div class="presentation-label">Activity</div>
                                <div class="presentation-value">{{ $row->presentation_type ?: '-' }}</div>
                            </div>
                            <div class="presentation-block">
                                <div class="presentation-label">Presenter</div>
                                <div class="presentation-value">{{ $row->presenter_name ?: '-' }}</div>
                            </div>
                            <div class="presentation-block">
                                <div class="presentation-label">Location</div>
                                <div class="presentation-value">{{ $row->location ?: '-' }}</div>
                            </div>
                        </td>
                        <td class="col-side">
                            <div class="presentation-block">
                                <div class="presentation-label">โครงการ / งานอ้างอิง</div>
                                <div class="presentation-value">
                                    {{ $row->project_label ?: '-' }}
                                    @if ($row->project_code)
                                        <span class="muted">({{ $row->project_code }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="presentation-block">
                                <div class="presentation-label">Output Code</div>
                                <div class="presentation-value">{{ $row->output_code ?: '-' }}</div>
                            </div>
                        </td>
                        @if ($qrCode)
                            <td class="col-qr">
                                <div class="qr-wrap"><img src="{{ $qrCode }}" alt="QR"></div>
                            </td>
                        @endif
                    </tr>
                </table>
            </div>
        @empty
            <div class="empty-state">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
        @endforelse
    </div>
</body>
</html>
