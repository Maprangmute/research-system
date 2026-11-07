const UNIT = 'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
const AFFILIATION = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ คณะแพทยศาสตร์ศิริราชพยาบาล มหาวิทยาลัยมหิดล';
const clean = (value) => String(value ?? '').trim().replace(/\s+/g, ' ');

// Stable year colors: changing the selected range does not change a year's theme.
function yearTheme(year) {
    const hue = (((Number(year) - 2567) * 137.508 + 208) % 360 + 360) % 360;
    const color = (lightness) => {
        const amplitude = 0.55 * Math.min(lightness, 1 - lightness);
        const channel = (offset) => {
            const k = (offset + hue / 30) % 12;
            return Math.round(255 * (lightness - amplitude * Math.max(-1, Math.min(k - 3, 9 - k, 1))))
                .toString(16).padStart(2, '0');
        };
        return `FF${channel(0)}${channel(8)}${channel(4)}`.toUpperCase();
    };
    return { header: color(0.3), tint: color(0.96), accent: color(0.88) };
}

// Explicit external classifications always win over legacy affiliation text.
function isUnit(type, affiliation) {
    const value = clean(type);
    if (value) return value === UNIT || value === 'internal';
    return [AFFILIATION, UNIT, 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ', 'sihp']
        .includes(clean(affiliation).toLowerCase());
}

export function unitTeam(project) {
    const members = [];
    const add = (person, role, eligible) => {
        const name = clean(person.name);
        if (!eligible || !name) return;
        const existing = members.find((member) =>
            (person.user_id && member.userId && String(person.user_id) === String(member.userId))
            || member.name === name);
        if (existing) {
            existing.roles = [...new Set([...existing.roles, role])];
            existing.userId ||= person.user_id;
        } else members.push({ name, userId: person.user_id, roles: [role] });
    };
    add({ name: project.pi_name }, 'PI', isUnit(project.pi_person_type, project.pi_affiliation));
    (project.copis || []).forEach((p) => add(p, 'Co-PI', isUnit(p.person_type, p.affiliation)));
    (project.co_researches || []).forEach((p) => add(p, 'ผู้ร่วมวิจัย', isUnit(p.unit_status, p.affiliation)));
    (project.fte_roles || []).forEach((p) => add(p, clean(p.role_group) || 'ทีม FTE', p.is_unit_user === true));
    return members;
}

export function validateYears(from, to, currentYear) {
    const start = Number(from), end = Number(to);
    if (!Number.isInteger(start) || !Number.isInteger(end) || start < 2400 || end > currentYear || end < start) {
        throw new Error(`กรุณาระบุช่วงปี พ.ศ. 2400–${currentYear} โดยปีสิ้นสุดไม่น้อยกว่าปีเริ่มต้น`);
    }
    if (end - start >= 30) throw new Error('กรุณาเลือกไม่เกิน 30 ปีต่อครั้ง');
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
}

// Uses the same XLSX global already loaded by the dashboard; no bundler is needed.
export function buildYearlyWorkbook(groups, { scope = '', generatedAt = new Date() } = {}) {
    const XLSX = globalThis.XLSX;
    if (!XLSX) throw new Error('ไม่พบโมดูลสำหรับสร้างไฟล์ Excel');
    const workbook = XLSX.utils.book_new();
    workbook.Props = { Author: 'SiHP Research', CreatedDate: generatedAt };
    function sheet(name, title, widths, headers, note, theme = { header: 'FF173D51', tint: 'FFF0FDFA', accent: 'FFE2E8F0' }) {
        const ws = XLSX.utils.aoa_to_sheet([[], [title], [note], [], headers]);
        ws['!cols'] = widths.map((wch) => ({ wch }));
        ws['!rows'] = [{}, { hpt: 30 }, { hpt: 45 }, {}, { hpt: 30 }];
        ws['!merges'] = [1, 2].map((r) => ({ s: { r, c: 0 }, e: { r, c: widths.length - 1 } }));
        ws.reportTheme = theme;
        ws.reportYearColors = {};
        XLSX.utils.book_append_sheet(workbook, ws, name);
        return ws;
    }
    function row(ws, values) {
        const index = XLSX.utils.decode_range(ws['!ref']).e.r + 1;
        XLSX.utils.sheet_add_aoa(ws, [values], { origin: { r: index, c: 0 } });
        const height = Math.max(40, ...values.map((v, i) => String(v ?? '').split('\n')
            .reduce((lines, line) => lines + Math.max(1, Math.ceil(Array.from(line).length
                / Math.max(8, ws['!cols'][i].wch * 0.75))), 0) * 18 + 16));
        ws['!rows'][index] = { hpt: Math.min(409, height) };
        return index;
    }
    const summary = sheet('สรุป', 'โครงการรายปี • ทีม SiHP', [22, 34, 38],
        ['ปี พ.ศ.', 'โครงการใหม่', 'กำลังดำเนินการ'],
        `ตามตัวกรอง Dashboard: ${scope || 'ทั้งหมด'} • ส่งออก ${generatedAt.toLocaleString('th-TH', { timeZone: 'Asia/Bangkok' })}`);
    for (const group of groups) {
        const uniqueProjects = [...new Map([...group.newProjects, ...group.ongoingProjects].map((p) => [p.id, p])).values()];
        const theme = yearTheme(group.year);
        const summaryIndex = row(summary, [group.year, group.newProjects.length, group.ongoingProjects.length]);
        summary.reportYearColors[`A${summaryIndex + 1}`] = theme.header;
        const ws = sheet(String(group.year), `รายชื่อโครงการ ปี ${group.year} • ทีม SiHP`, [22, 14, 45, 53, 10],
            ['กลุ่ม', 'รหัสโครงการ', 'ชื่อโครงการ', 'ทีม SiHP · ชื่อและบทบาท', 'จำนวนคน'],
            'หนึ่งแถวต่อโครงการ ไม่ซ้ำในปี • โครงการที่เริ่มในปีนั้นแสดงเป็น “ใหม่” • ทีมเป็นข้อมูลปัจจุบัน', theme);
        const newIds = new Set(group.newProjects.map((p) => p.id));
        const sorted = [...uniqueProjects].sort((a, b) => Number(newIds.has(b.id)) - Number(newIds.has(a.id))
            || clean(a.project_name_th).localeCompare(clean(b.project_name_th), 'th'));
        for (const project of sorted) {
            const team = unitTeam(project);
            const names = team.length
                ? team.map((member, i) => `${i + 1}. ${member.name} (${member.roles.join(', ')})`).join('\n')
                : 'ไม่พบรายชื่อที่ยืนยันว่าอยู่ใน SiHP';
            row(ws, [newIds.has(project.id) ? 'ใหม่' : 'ongoing', clean(project.project_code), clean(project.project_name_th), names, team.length]);
        }
        if (XLSX.utils.decode_range(ws['!ref']).e.r === 4) row(ws, ['ไม่พบโครงการในช่วงปีและตัวกรองที่เลือก']);
        ws['!autofilter'] = { ref: `A5:E${XLSX.utils.decode_range(ws['!ref']).e.r + 1}` };
    }
    for (const values of [
        ['เกณฑ์การอ่าน', 'ใหม่ = เริ่มในปีนั้น; ongoing = เริ่มไม่เกินปีนั้นและยังไม่ปิด/ยกเลิกในข้อมูลปัจจุบัน'],
        ['การนับ', 'โครงการใหม่อาจอยู่ใน ongoing ด้วยตามกราฟ จึงไม่ควรบวกสองกลุ่มเป็นยอดโครงการไม่ซ้ำ'],
    ]) {
        const index = row(summary, values);
        summary['!merges'].push({ s: { r: index, c: 1 }, e: { r: index, c: 2 } });
        summary['!rows'][index] = { hpt: 64 };
    }
    return workbook;
}

// SheetJS CE writes the values, ranges and ZIP package. Its bundled CFB utility
// lets this report add the small OOXML presentation layer CE does not write.
// Only workbooks created above are supported: plain text/numbers, no date styles.
export function writeYearlyWorkbook(workbook) {
    const XLSX = globalThis.XLSX;
    const archive = XLSX.CFB.read(new Uint8Array(XLSX.write(workbook, { type: 'array', bookType: 'xlsx' })), { type: 'array' });
    const encoder = new TextEncoder(), decoder = new TextDecoder();
    const entry = (path) => {
        const index = archive.FullPaths.findIndex((name) => name === `${archive.FullPaths[0]}${path}`);
        if (index < 0) throw new Error(`Missing workbook part: ${path}`);
        return archive.FileIndex[index];
    };
    const replace = (path, xml) => {
        const part = entry(path);
        part.content = encoder.encode(xml);
        part.size = part.content.length;
    };
    const fills = ['<fill><patternFill patternType="none"/></fill>', '<fill><patternFill patternType="gray125"/></fill>'];
    const styles = ['<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'];
    const styleCache = new Map();
    const style = (font, color, border = 0, centered = false) => {
        const key = `${font}/${color}/${border}/${centered}`;
        if (styleCache.has(key)) return styleCache.get(key);
        const fill = color ? fills.push(`<fill><patternFill patternType="solid"><fgColor rgb="${color}"/><bgColor indexed="64"/></patternFill></fill>`) - 1 : 0;
        const id = styles.push(`<xf numFmtId="0" fontId="${font}" fillId="${fill}" borderId="${border}" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"${centered ? ' horizontal="center"' : ''}/></xf>`) - 1;
        styleCache.set(key, id);
        return id;
    };
    workbook.SheetNames.forEach((name, index) => {
        const ws = workbook.Sheets[name], theme = ws.reportTheme;
        const path = `xl/worksheets/sheet${index + 1}.xml`;
        let xml = decoder.decode(entry(path).content);
        xml = xml.replace(/<c\b([^>]*?)>/g, (tag, attrs) => {
            const address = attrs.match(/\br="([A-Z]+)(\d+)"/);
            if (!address) return tag;
            const [, col, line] = address, r = Number(line);
            let id;
            if (ws.reportYearColors[`${col}${line}`]) id = style(3, ws.reportYearColors[`${col}${line}`]);
            else if (r === 2) id = style(2, null);
            else if (r === 5) id = style(3, theme.header, 0, true);
            else if (r > 5) id = style(index > 0 && col === 'C' ? 1 : 0,
                index > 0 && col === 'A' ? theme.accent : (r % 2 === 0 ? theme.tint : 'FFFFFFFF'), index > 0 ? 1 : 0);
            else id = style(0, null);
            return `<c${attrs.replace(/\s+s="[^"]*"/, '')} s="${id}">`;
        });
        const properties = `<sheetPr><tabColor rgb="${theme.header}"/><pageSetUpPr fitToPage="1"/></sheetPr>`;
        if (/<sheetPr\b/.test(xml)) xml = xml.replace(/<sheetPr\b[^>]*\/>|<sheetPr\b[^>]*>[\s\S]*?<\/sheetPr>/, properties);
        else xml = xml.replace(/(<worksheet\b[^>]*>)/, `$1${properties}`);
        xml = xml.replace(/<sheetViews>[\s\S]*?<\/sheetViews>/,
            '<sheetViews><sheetView showGridLines="0" workbookViewId="0"><pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A6" sqref="A6"/></sheetView></sheetViews>');
        xml = xml.replace(/(<ignoredErrors\b|<\/worksheet>)/,
            '<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/><pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/>$1');
        replace(path, xml);
    });
    const font = (size, bold, color) => `<font><sz val="${size}"/><name val="Tahoma"/>${bold ? '<b/>' : ''}<color rgb="${color}"/></font>`;
    replace('xl/styles.xml', `<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="4">${font(11, false, 'FF173D51')}${font(11, true, 'FF173D51')}${font(16, true, 'FF173D51')}${font(11, true, 'FFFFFFFF')}</fonts><fills count="${fills.length}">${fills.join('')}</fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left/><right/><top/><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="${styles.length}">${styles.join('')}</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>`);
    return XLSX.CFB.write(archive, { type: 'array', fileType: 'zip' });
}

export function downloadYearlyWorkbook(groups, options) {
    const buffer = writeYearlyWorkbook(buildYearlyWorkbook(groups, options));
    const url = URL.createObjectURL(new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }));
    const link = document.createElement('a');
    link.href = url;
    link.download = `SiHP-projects-${groups[0].year}-${groups.at(-1).year}.xlsx`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}
