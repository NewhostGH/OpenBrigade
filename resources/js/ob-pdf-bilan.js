import { PDFDocument, StandardFonts, rgb } from 'pdf-lib';

// ── Constants ──────────────────────────────────────────────────────────────────
const MM = 2.8346;       // 1mm in points
const A4W = 595.28;
const A4H = 841.89;
const MARGIN = 15 * MM;
const CW = A4W - 2 * MARGIN;

const C = {
    brand:   rgb(0.09, 0.24, 0.47),
    brandLt: rgb(0.92, 0.95, 0.98),
    gray:    rgb(0.45, 0.45, 0.45),
    grayLt:  rgb(0.95, 0.95, 0.95),
    dark:    rgb(0.1, 0.1, 0.1),
    white:   rgb(1, 1, 1),
    sep:     rgb(0.82, 0.82, 0.82),
};

const MONTHS = ['Jan','Fev','Mar','Avr','Mai','Jun','Jul','Aou','Sep','Oct','Nov','Dec'];

// ── PDF helper class ───────────────────────────────────────────────────────────
class BilanPdf {
    constructor(doc, regular, bold) {
        this.doc = doc;
        this.regular = regular;
        this.bold = bold;
        this.page = null;
        this.topY = 0;       // cursor from page top (points)
        this._meta = {};
    }

    setMeta(title, subtitle, section, year) {
        this._meta = { title, subtitle, section, year };
    }

    // Add content page with header + footer, reset cursor
    newPage() {
        const { title, subtitle, section, year } = this._meta;
        this.page = this.doc.addPage([A4W, A4H]);

        // Header bar
        this._rect(0, 0, A4W, 22 * MM, C.brand);
        this._text(title || '', MARGIN, 5 * MM, 15, this.bold, C.white);
        if (subtitle) {
            const sw = this.bold.widthOfTextAtSize(subtitle, 9);
            this._text(subtitle, A4W - MARGIN - sw, 7 * MM, 9, this.regular, rgb(0.75, 0.85, 1));
        }

        // Footer
        const footTop = A4H - 10 * MM;
        this._line(MARGIN, footTop, A4W - MARGIN, footTop, C.sep, 0.4);
        const footL = (section || '') + (year ? '  —  ' + year : '');
        this._text(footL, MARGIN, footTop + 2 * MM, 7, this.regular, C.gray);
        const appTxt = 'OpenBrigade';
        const appW = this.regular.widthOfTextAtSize(appTxt, 7);
        this._text(appTxt, A4W - MARGIN - appW, footTop + 2 * MM, 7, this.regular, C.gray);

        this.topY = 26 * MM;
        return this;
    }

    // Check remaining space; add new page if height won't fit
    allocate(height) {
        if (this.topY + height > A4H - 12 * MM) {
            this.newPage();
        }
    }

    // Section heading
    sectionHeader(label) {
        this.topY += 2 * MM;
        this.allocate(9 * MM);
        this._rect(MARGIN, this.topY, CW, 8 * MM, C.brandLt);
        this._text(label, MARGIN + 3 * MM, this.topY + 1.5 * MM, 9, this.bold, C.brand);
        this.topY += 9 * MM;
    }

    // KPI cards row: [{label, value, sub?}]
    kpiRow(kpis) {
        const KH = 18 * MM;
        this.allocate(KH + 4 * MM);
        const n = kpis.length;
        const gap = 2 * MM;
        const kw = (CW - gap * (n - 1)) / n;
        kpis.forEach((k, i) => {
            const x = MARGIN + i * (kw + gap);
            this._rect(x, this.topY, kw, KH, C.grayLt);
            const valStr = String(k.value ?? '');
            const vs = Math.min(18, kw / (valStr.length * 0.55)); // adaptive font size
            const vw = this.bold.widthOfTextAtSize(valStr, vs);
            this._text(valStr, x + (kw - vw) / 2, this.topY + 2.5 * MM, vs, this.bold, C.brand);
            const lw = this.regular.widthOfTextAtSize(k.label, 8);
            this._text(k.label, x + (kw - lw) / 2, this.topY + 2.5 * MM + vs * 0.72 + 2 * MM, 8, this.regular, C.gray);
            if (k.sub) {
                const sw = this.regular.widthOfTextAtSize(k.sub, 7);
                this._text(k.sub, x + (kw - sw) / 2, this.topY + 2.5 * MM + vs * 0.72 + 5 * MM, 7, this.regular, C.gray);
            }
        });
        this.topY += KH + 4 * MM;
    }

    // Table: headers = [{label, width (fraction of CW), align?}], rows = string[][]
    table(headers, rows) {
        if (!rows.length) return;
        const ROW_H = 5.5 * MM;
        const HDR_H = 6.5 * MM;
        const widths = headers.map(h => h.width * CW);

        // Header
        this.allocate(HDR_H + ROW_H);
        this._rect(MARGIN, this.topY, CW, HDR_H, C.brand);
        let x = MARGIN;
        headers.forEach((h, i) => {
            const lw = this.bold.widthOfTextAtSize(h.label, 8);
            this._text(h.label, x + (widths[i] - lw) / 2, this.topY + 1.2 * MM, 8, this.bold, C.white);
            x += widths[i];
        });
        this.topY += HDR_H;

        rows.forEach((row, rIdx) => {
            this.allocate(ROW_H);
            if (rIdx % 2 === 0) {
                this._rect(MARGIN, this.topY, CW, ROW_H, C.grayLt);
            }
            this._line(MARGIN, this.topY + ROW_H, MARGIN + CW, this.topY + ROW_H, C.sep, 0.3);
            x = MARGIN;
            row.forEach((cell, ci) => {
                const cellStr = cell !== null && cell !== undefined ? String(cell) : '-';
                const SIZE = 7.5;
                const align = headers[ci]?.align || 'left';
                const cw = this.regular.widthOfTextAtSize(cellStr, SIZE);
                let tx;
                if (align === 'right') {
                    tx = x + widths[ci] - cw - 2 * MM;
                } else if (align === 'center') {
                    tx = x + (widths[ci] - cw) / 2;
                } else {
                    tx = x + 2 * MM;
                }
                const clampX = Math.max(x + 0.5 * MM, Math.min(tx, x + widths[ci] - cw - 0.5 * MM));
                this._text(cellStr, clampX, this.topY + 1.3 * MM, SIZE, this.regular, C.dark);
                x += widths[ci];
            });
            this.topY += ROW_H;
        });
        this.topY += 2 * MM;
    }

    // ── Low-level drawing ──────────────────────────────────────────────────────

    // topY = distance from page top; size * 0.72 ≈ cap height
    _text(str, x, topY, size, font, color) {
        try {
            this.page.drawText(str, {
                x: Math.round(x * 100) / 100,
                y: A4H - topY - size * 0.72,
                size,
                font,
                color,
            });
        } catch (_) { /* skip unencodable chars */ }
    }

    _rect(x, topY, width, height, color) {
        this.page.drawRectangle({ x, y: A4H - topY - height, width, height, color });
    }

    _line(x1, y1, x2, y2, color, thickness = 0.5) {
        this.page.drawLine({
            start: { x: x1, y: A4H - y1 },
            end:   { x: x2, y: A4H - y2 },
            thickness,
            color,
        });
    }
}

// ── Cover page ─────────────────────────────────────────────────────────────────
function buildCover(pdf, year, sectionName, tabLabel) {
    pdf.page = pdf.doc.addPage([A4W, A4H]);

    // Header block (~38% of page height)
    const hH = A4H * 0.38;
    pdf._rect(0, 0, A4W, hH, C.brand);

    // Main title
    const t1 = 'BILAN ANNUEL ' + year;
    const t1size = 26;
    const t1w = pdf.bold.widthOfTextAtSize(t1, t1size);
    pdf._text(t1, (A4W - t1w) / 2, hH * 0.28, t1size, pdf.bold, C.white);

    // Subtitle
    if (tabLabel) {
        const t2size = 13;
        const t2w = pdf.regular.widthOfTextAtSize(tabLabel, t2size);
        pdf._text(tabLabel, (A4W - t2w) / 2, hH * 0.28 + t1size * 0.72 + 8 * MM, t2size, pdf.regular, rgb(0.75, 0.85, 1));
    }

    // Separator
    pdf._rect(MARGIN, hH + 9 * MM, CW, 0.8, C.sep);

    // Section name
    const secName = sectionName || 'OpenBrigade';
    const snSize = 13;
    const snW = pdf.bold.widthOfTextAtSize(secName, snSize);
    pdf._text(secName, (A4W - snW) / 2, hH + 14 * MM, snSize, pdf.bold, C.brand);

    // Date
    const dateStr = 'Genere le ' + new Date().toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'long', year: 'numeric',
    });
    const dateSize = 9;
    const dateW = pdf.regular.widthOfTextAtSize(dateStr, dateSize);
    pdf._text(dateStr, (A4W - dateW) / 2, hH + 14 * MM + snSize * 0.72 + 4 * MM, dateSize, pdf.regular, C.gray);
}

// ── Tab builders ───────────────────────────────────────────────────────────────

function buildGeneralites(pdf, data) {
    pdf.setMeta('BILAN ' + data.year, 'Generalites', data.section?.name || '', data.year);
    pdf.newPage();

    // Personnel
    pdf.sectionHeader('Personnel');
    pdf.kpiRow([{ label: 'Membres actifs', value: data.totalMembers }]);

    const groupEntries = Object.entries(data.membersByGroup || {});
    if (groupEntries.length) {
        pdf.table(
            [{ label: 'Groupe', width: 0.75 }, { label: 'Membres', width: 0.25, align: 'right' }],
            groupEntries.map(([l, n]) => [l, n])
        );
    }

    const yearEntries = Object.entries(data.newMembersByYear || {});
    if (yearEntries.length) {
        pdf.sectionHeader('Nouveaux engagements (5 ans)');
        pdf.table(
            [{ label: 'Annee', width: 0.5 }, { label: 'Engagements', width: 0.5, align: 'right' }],
            yearEntries.map(([yr, n]) => [yr, n])
        );
    }

    // Vehicules
    pdf.sectionHeader('Vehicules');
    pdf.kpiRow([{ label: 'Total vehicules', value: data.totalVehicles }]);
    if (data.vehiclesByType?.length) {
        pdf.table(
            [{ label: 'Type', width: 0.75 }, { label: 'Quantite', width: 0.25, align: 'right' }],
            data.vehiclesByType.map(r => [r.label, r.nb])
        );
    }

    // Materiel
    pdf.sectionHeader('Materiel');
    pdf.kpiRow([{ label: 'Articles inventories', value: data.totalMateriels }]);
    if (data.materielsByType?.length) {
        pdf.table(
            [{ label: 'Categorie', width: 0.75 }, { label: 'Quantite', width: 0.25, align: 'right' }],
            data.materielsByType.map(r => [r.label, r.nb])
        );
    }

    // Consommables
    pdf.sectionHeader('Consommables');
    pdf.kpiRow([{ label: 'Articles inventories', value: data.totalConsommables }]);
    if (data.consommablesByType?.length) {
        pdf.table(
            [{ label: 'Categorie', width: 0.75 }, { label: 'Quantite', width: 0.25, align: 'right' }],
            data.consommablesByType.map(r => [r.label, r.nb])
        );
    }
}

function buildActivites(pdf, data) {
    pdf.setMeta('BILAN ' + data.year, 'Activites', data.section?.name || '', data.year);
    pdf.newPage();

    pdf.sectionHeader('Activites operationnelles');
    pdf.kpiRow([
        { label: 'Activites', value: data.totalEvents, sub: 'en ' + data.year },
        { label: 'Participations', value: data.totalParticipants, sub: 'cumules' },
        { label: 'Heures benevoles', value: (data.totalHours || 0).toLocaleString('fr-FR') },
    ]);

    pdf.sectionHeader('Repartition mensuelle');
    pdf.table(
        [
            { label: 'Mois', width: 0.25 },
            { label: 'Activites', width: 0.375, align: 'right' },
            { label: 'Participants', width: 0.375, align: 'right' },
        ],
        MONTHS.map((m, i) => [m, data.eventsData?.[i] ?? 0, data.participantData?.[i] ?? 0])
    );

    const typeEntries = Object.entries(data.eventsByType || {});
    if (typeEntries.length) {
        pdf.sectionHeader('Repartition par type');
        pdf.table(
            [{ label: 'Type', width: 0.75 }, { label: 'Activites', width: 0.25, align: 'right' }],
            typeEntries.map(([l, n]) => [l, n])
        );
    }

    if (data.topParticipants?.length) {
        pdf.sectionHeader('Top 10 participants');
        pdf.table(
            [
                { label: '#', width: 0.07, align: 'center' },
                { label: 'Prenom', width: 0.31 },
                { label: 'Nom', width: 0.38 },
                { label: 'Activites', width: 0.24, align: 'right' },
            ],
            data.topParticipants.map((p, i) => [i + 1, p.prenom, p.nom, p.nb])
        );
    }
}

function buildFormations(pdf, data) {
    pdf.setMeta('BILAN ' + data.year, 'Formations', data.section?.name || '', data.year);
    pdf.newPage();

    pdf.sectionHeader('Formations');
    pdf.kpiRow([
        { label: 'Sessions', value: data.totalFormations, sub: 'en ' + data.year },
        { label: 'Stagiaires', value: data.totalTrained, sub: 'participations cumulees' },
        { label: 'Heures dispensees', value: (data.totalHours || 0).toLocaleString('fr-FR') },
    ]);

    pdf.sectionHeader('Repartition mensuelle');
    pdf.table(
        [{ label: 'Mois', width: 0.5 }, { label: 'Sessions', width: 0.5, align: 'right' }],
        MONTHS.map((m, i) => [m, data.eventsData?.[i] ?? 0])
    );

    const typeEntries = Object.entries(data.eventsByType || {});
    if (typeEntries.length) {
        pdf.sectionHeader('Repartition par type');
        pdf.table(
            [{ label: 'Type', width: 0.75 }, { label: 'Sessions', width: 0.25, align: 'right' }],
            typeEntries.map(([l, n]) => [l, n])
        );
    }

    if (data.formationsList?.length) {
        pdf.sectionHeader('Detail des sessions');
        pdf.table(
            [
                { label: 'Date', width: 0.12 },
                { label: 'Intitule', width: 0.34 },
                { label: 'Type', width: 0.18 },
                { label: 'Lieu', width: 0.18 },
                { label: 'Duree h', width: 0.09, align: 'right' },
                { label: 'Stag.', width: 0.09, align: 'right' },
            ],
            data.formationsList.map(f => [
                f.date ? new Date(f.date).toLocaleDateString('fr-FR') : '-',
                f.label || '-',
                f.type || '-',
                f.lieu || '-',
                f.duree_h ?? '-',
                f.nb,
            ])
        );
    }
}

// ── Public download function ───────────────────────────────────────────────────
export async function downloadBilanPdf() {
    const data = window.__BILAN_PDF_DATA__;
    if (!data) {
        console.error('__BILAN_PDF_DATA__ not set');
        return;
    }

    const btn = document.getElementById('btn-download-pdf');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Génération...';
    }

    try {
        const doc = await PDFDocument.create();
        const [regular, bold] = await Promise.all([
            doc.embedFont(StandardFonts.Helvetica),
            doc.embedFont(StandardFonts.HelveticaBold),
        ]);

        const tabLabels = {
            generalites: 'Generalites, personnels et moyens',
            activites:   'Activites operationnelles',
            formations:  'Formations',
        };

        const pdf = new BilanPdf(doc, regular, bold);
        buildCover(pdf, data.year, data.section?.name, tabLabels[data.tab]);

        if (data.tab === 'generalites')     buildGeneralites(pdf, data);
        else if (data.tab === 'activites')  buildActivites(pdf, data);
        else if (data.tab === 'formations') buildFormations(pdf, data);

        const bytes = await doc.save();
        const blob = new Blob([bytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `bilan-${data.tab}-${data.year}.pdf`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-pdf me-1"></i>Télécharger PDF';
        }
    }
}

window.__downloadBilanPdf = downloadBilanPdf;

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-download-pdf');
    if (btn) btn.addEventListener('click', downloadBilanPdf);
});
