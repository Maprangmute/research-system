<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Research Papers Report</title>
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
        .small { font-size: 11.2pt; }
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
        .section-title {
            margin: 18px 0 8px;
            padding: 8px 14px 8px 12px;
            border-radius: 10px;
            border: 1px solid #dbe7f5;
            border-left: 5px solid #4f46e5;
            background: linear-gradient(90deg, #eef2ff 0%, #f8faff 100%);
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
        .filters-wrap {
            margin-top: 6px;
        }
        .paper-list {
            margin-top: 10px;
        }
        .paper-card {
            border: 1px solid #dbe7f5;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            background: #ffffff;
        }
        .paper-card:nth-child(even) {
            background: #fcfdff;
        }
        .paper-topline {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0 0 6px;
        }
        .paper-topline td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .paper-index {
            width: 30px;
            font-size: 13.4pt;
            font-weight: bold;
            color: #64748b;
            line-height: 1.4;
        }
        .paper-title {
            font-size: 15.8pt;
            font-weight: bold;
            line-height: 1.42;
            color: #0f172a;
        }
        .paper-year {
            white-space: nowrap;
            text-align: right;
            font-size: 11.8pt;
            color: #64748b;
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
        .paper-meta-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
        }
        .paper-meta-grid td {
            border: 0;
            padding: 0 14px 0 0;
            vertical-align: top;
        }
        .paper-meta-grid td:last-child {
            padding-right: 0;
        }
        .paper-meta-grid .col-main { width: 56%; }
        .paper-meta-grid .col-side { width: 22%; }
        .paper-meta-grid .col-qr {
            width: 40px;
            text-align: center;
            vertical-align: top;
            padding-right: 0;
        }
        .paper-block {
            margin-top: 8px;
        }
        .paper-label {
            margin-bottom: 2px;
            font-size: 10.2pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .paper-value {
            font-size: 12.2pt;
            line-height: 1.58;
            color: #1e293b;
            word-break: break-word;
            white-space: normal;
        }
        .author-group {
            margin-top: 6px;
        }
        .author-role {
            font-size: 10.6pt;
            font-weight: bold;
            color: #334155;
        }
        .author-names {
            font-size: 12.2pt;
            color: #1e293b;
            line-height: 1.52;
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
            Research Papers and Journal Articles
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
        <table class="hero-grid">
            <tr>
                <td>
                    <div class="report-kicker">Outputs Report</div>
                    <div class="report-title">รายงาน Research Papers and Journal Articles</div>
                    <div class="report-subtitle">
                        สรุปผลงานตีพิมพ์เชิงวิชาการ พร้อมข้อมูลวารสาร ผู้แต่ง บทบาท และที่มาของผลงานในระบบ
                    </div>
                    <div class="hero-meta">
                        วันที่ออกรายงาน {{ $generatedLabel }}<br>
                        ผู้สร้างรายงาน {{ $reporter }}
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
    <div class="section-subtitle">ภาพรวมจำนวนบทความ ผู้แต่ง และวารสารที่เด่นที่สุดจากผลลัพธ์ปัจจุบัน</div>
    <table class="summary-grid">
        <tr>
            <td style="background:#eef6ff;">
                <div class="summary-label">จำนวน Papers</div>
                <div class="summary-value">{{ number_format($summary['total_papers']) }}</div>
                <div class="summary-note">รวมทุกแหล่งข้อมูลที่ผ่านเงื่อนไข</div>
            </td>
            <td style="background:#f5f3ff;">
                <div class="summary-label">Unique Authors</div>
                <div class="summary-value">{{ number_format($summary['unique_authors']) }}</div>
                <div class="summary-note">จำนวนผู้แต่งไม่ซ้ำทั้งหมด</div>
            </td>
            <td style="background:#ecfeff;">
                <div class="summary-label">Top Author</div>
                <div class="summary-value" style="font-size:13.2pt;">{{ $summary['top_author'] ?? '-' }}</div>
                <div class="summary-note">ผู้แต่งที่พบมากที่สุด</div>
            </td>
            <td style="background:#fff7ed;">
                <div class="summary-label">Top Journal</div>
                <div class="summary-value" style="font-size:13.2pt;">{{ $summary['top_journal'] ?? '-' }}</div>
                <div class="summary-note">วารสารที่พบมากที่สุด</div>
            </td>
        </tr>
    </table>

    <div class="section-title">รายการบทความ</div>
    <div class="section-subtitle">รายละเอียดบทความ วารสาร ค่า IF, Quartile, ผู้แต่ง และแหล่งอ้างอิงของแต่ละรายการ</div>
    <div class="paper-list">
        @forelse ($papers as $index => $paper)
            @php
                $sourceLabel = $paper->source_badge ?: 'โครงการวิจัย';
                $metaParts = collect([
                    $sourceLabel,
                    ($paper->quartile && $paper->quartile !== '-') ? strtoupper((string) $paper->quartile) : null,
                    !blank($paper->impact_factor)
                        ? 'IF: ' . rtrim(rtrim(number_format((float) $paper->impact_factor, 3, '.', ''), '0'), '.')
                        : null,
                ])->filter()->values();
                $qrLink = $firstUrlOrNull($paper->detail, $paper->project_link);
                $qrCode = $qrLink ? $qrDataUri($qrLink) : null;
            @endphp
            <div class="paper-card">
                <table class="paper-topline">
                    <tr>
                        <td class="paper-index">{{ $index + 1 }}.</td>
                        <td>
                            <div class="paper-title">{{ $paper->title ?: '-' }}</div>
                            <div class="paper-value" style="margin-top: 4px; color: #475569;">
                                {{ $metaParts->implode(' · ') ?: '-' }}
                            </div>
                        </td>
                        <td class="paper-year">
                            ปี {{ $paper->year_be ?: '-' }}
                        </td>
                    </tr>
                </table>

                <table class="paper-meta-grid">
                    <tr>
                        <td class="col-main">
                            <div class="paper-block">
                                <div class="paper-label">Authors</div>
                                @forelse ($paper->authors_by_role as $role => $names)
                                    <div class="author-group">
                                        <div class="author-role">
                                            {{ ['first' => 'First Author', 'co' => 'Co Author', 'corresponding' => 'Corresponding Author'][$role] ?? ucfirst($role) }}
                                        </div>
                                        <div class="author-names">{{ $names->implode(', ') }}</div>
                                    </div>
                                @empty
                                    <div class="paper-value">{{ collect($paper->authors)->isNotEmpty() ? collect($paper->authors)->implode(', ') : '-' }}</div>
                                @endforelse
                            </div>

                            @if (!empty($paper->detail))
                                <div class="paper-block">
                                    <div class="paper-label">รายละเอียด / ลิงก์ / โน้ต</div>
                                    <div class="paper-value">{{ $paper->detail }}</div>
                                </div>
                            @endif
                        </td>
                        <td class="col-side">
                            <div class="paper-block">
                                <div class="paper-label">Journal</div>
                                <div class="paper-value">{{ $paper->journal ?: '-' }}</div>
                            </div>
                            <div class="paper-block">
                                <div class="paper-label">Output Code</div>
                                <div class="paper-value">{{ $paper->output_code ?: '-' }}</div>
                            </div>
                        </td>
                        <td class="col-side">
                            <div class="paper-block">
                                <div class="paper-label">โครงการ / งานอ้างอิง</div>
                                <div class="paper-value">
                                    {{ $paper->project_label ?: '-' }}
                                    @if ($paper->project_code)
                                        <span class="muted">({{ $paper->project_code }})</span>
                                    @endif
                                </div>
                            </div>
                            @if (collect($paper->matched_author_roles ?? [])->isNotEmpty())
                                <div class="paper-block">
                                    <div class="paper-label">บทบาทที่ตรงกับฟิลเตอร์</div>
                                    <div class="paper-value">{{ collect($paper->matched_author_roles)->implode(', ') }}</div>
                                </div>
                            @endif
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
