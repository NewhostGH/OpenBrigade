import { PDFDocument, StandardFonts, rgb } from 'pdf-lib';

// ── Constants ──────────────────────────────────────────────────────────────────
const MM = 2.8346;

const C = {
    brand:   rgb(0.12, 0.23, 0.47),
    brandLt: rgb(0.92, 0.95, 0.98),
    gray:    rgb(0.45, 0.45, 0.45),
    grayLt:  rgb(0.95, 0.95, 0.95),
    dark:    rgb(0.08, 0.08, 0.08),
    white:   rgb(1, 1, 1),
    sep:     rgb(0.80, 0.80, 0.80),
};

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// ── Livret PDF ─────────────────────────────────────────────────────────────────

const A4W = 595.28;
const A4H = 841.89;
const MARGIN = 15 * MM;
const CW = A4W - 2 * MARGIN;

class LivetPdf {
    constructor(doc, regular, bold, italic) {
        this.doc = doc;
        this.regular = regular;
        this.bold = bold;
        this.italic = italic;
        this.page = null;
        this.topY = 0;
        this._hdrTitle = '';
    }

    setHeader(title) { this._hdrTitle = title; }

    newPage() {
        this.page = this.doc.addPage([A4W, A4H]);
        // Header bar
        this._rect(0, 0, A4W, 18 * MM, C.brand);
        this._text(this._hdrTitle, MARGIN, 5 * MM, 11, this.bold, C.white);
        // Footer
        const footTop = A4H - 8 * MM;
        this._line(MARGIN, footTop, A4W - MARGIN, footTop, C.sep, 0.4);
        const dateStr = 'Imprime le ' + new Date().toLocaleDateString('fr-FR');
        this._text(dateStr, MARGIN, footTop + 2 * MM, 7, this.regular, C.gray);
        const app = 'OpenBrigade';
        const appW = this.regular.widthOfTextAtSize(app, 7);
        this._text(app, A4W - MARGIN - appW, footTop + 2 * MM, 7, this.regular, C.gray);
        this.topY = 22 * MM;
    }

    allocate(h) {
        if (this.topY + h > A4H - 10 * MM) this.newPage();
    }

    sectionHeader(label) {
        this.topY += 2 * MM;
        this.allocate(8 * MM);
        this._rect(MARGIN, this.topY, CW, 7 * MM, C.brandLt);
        this._text(label, MARGIN + 3 * MM, this.topY + 1.2 * MM, 9, this.bold, C.brand);
        this.topY += 8 * MM;
    }

    table(headers, rows) {
        if (!rows.length) return;
        const ROW_H = 5.5 * MM;
        const HDR_H = 6 * MM;
        const widths = headers.map(h => h.width * CW);

        this.allocate(HDR_H + ROW_H);
        this._rect(MARGIN, this.topY, CW, HDR_H, C.brand);
        let x = MARGIN;
        headers.forEach((h, i) => {
            const lw = this.bold.widthOfTextAtSize(h.label, 7.5);
            this._text(h.label, x + (widths[i] - lw) / 2, this.topY + 1.1 * MM, 7.5, this.bold, C.white);
            x += widths[i];
        });
        this.topY += HDR_H;

        rows.forEach((row, rIdx) => {
            this.allocate(ROW_H);
            if (rIdx % 2 === 0) this._rect(MARGIN, this.topY, CW, ROW_H, C.grayLt);
            this._line(MARGIN, this.topY + ROW_H, MARGIN + CW, this.topY + ROW_H, C.sep, 0.25);
            x = MARGIN;
            row.forEach((cell, ci) => {
                const s = String(cell ?? '-');
                const SIZE = 7;
                const align = headers[ci]?.align || 'left';
                const sw = this.regular.widthOfTextAtSize(s, SIZE);
                let tx;
                if (align === 'right') tx = x + widths[ci] - sw - 1.5 * MM;
                else if (align === 'center') tx = x + (widths[ci] - sw) / 2;
                else tx = x + 1.5 * MM;
                this._text(s, Math.max(x + 0.5 * MM, tx), this.topY + 1.2 * MM, SIZE, this.regular, C.dark);
                x += widths[ci];
            });
            this.topY += ROW_H;
        });
        this.topY += 1.5 * MM;
    }

    _text(str, x, topY, size, font, color) {
        try {
            this.page.drawText(str, { x, y: A4H - topY - size * 0.72, size, font, color });
        } catch (_) {}
    }
    _rect(x, topY, width, height, color) {
        this.page.drawRectangle({ x, y: A4H - topY - height, width, height, color });
    }
    _line(x1, y1, x2, y2, color, thickness = 0.5) {
        this.page.drawLine({ start: { x: x1, y: A4H - y1 }, end: { x: x2, y: A4H - y2 }, thickness, color });
    }
}

async function buildLivretPdf(data) {
    const doc = await PDFDocument.create();
    const [regular, bold, italic] = await Promise.all([
        doc.embedFont(StandardFonts.Helvetica),
        doc.embedFont(StandardFonts.HelveticaBold),
        doc.embedFont(StandardFonts.HelveticaOblique),
    ]);

    const pdf = new LivetPdf(doc, regular, bold, italic);
    const nom = (data.nom || '').toUpperCase();
    const prenom = data.prenom || '';
    const hdrTitle = 'Passeport benevole  —  ' + nom + ' ' + prenom;
    pdf.setHeader(hdrTitle);

    // ── Cover / identity page ──────────────────────────────────────────────────
    pdf.page = doc.addPage([A4W, A4H]);
    // Cover header
    pdf._rect(0, 0, A4W, 60 * MM, C.brand);
    const coverTitle = 'PASSEPORT DU BENEVOLE';
    const ctW = bold.widthOfTextAtSize(coverTitle, 20);
    pdf._text(coverTitle, (A4W - ctW) / 2, 15 * MM, 20, bold, C.white);
    const nameLine = nom + '  ' + prenom;
    const nlW = regular.widthOfTextAtSize(nameLine, 13);
    pdf._text(nameLine, (A4W - nlW) / 2, 15 * MM + 20 * 0.72 + 5 * MM, 13, regular, rgb(0.75, 0.85, 1));

    // Identity fields
    pdf.topY = 68 * MM;
    const fields = [
        ['Date de naissance', data.birthdate],
        ['Lieu de naissance', data.birthplace],
        ['Adresse', data.address],
        ['', data.zip_city],
        ['Telephone', data.phone],
        ['Email', data.email],
        ['Date engagement', data.date_engagement],
        ['Section', data.section],
        ['Grade', data.grade],
        ['N° adherent', data.code],
    ].filter(([, v]) => v);

    const labelX = MARGIN;
    const valX = MARGIN + 45 * MM;

    fields.forEach(([label, value]) => {
        pdf.allocate(6 * MM);
        if (label) {
            pdf._text(label + ' :', labelX, pdf.topY, 9, bold, C.gray);
        }
        pdf._text(String(value || ''), valX, pdf.topY, 9, regular, C.dark);
        pdf.topY += 6 * MM;
    });

    // ── Medals ──────────────────────────────────────────────────────────────────
    if (data.medals?.length) {
        pdf.sectionHeader('Decorations collectives');
        pdf.table(
            [
                { label: 'Medaille', width: 0.38 },
                { label: 'Date', width: 0.15, align: 'center' },
                { label: 'Agrafe', width: 0.25, align: 'center' },
                { label: 'Decernee a', width: 0.22, align: 'center' },
            ],
            data.medals.map(m => [m.TA_DESCRIPTION, m.A_DEBUT, m.A_COMMENT, m.S_DESCRIPTION])
        );
    }

    if (data.indiv_medals?.length) {
        pdf.sectionHeader('Medailles et Recompenses individuelles');
        pdf.table(
            [
                { label: 'Medaille', width: 0.7 },
                { label: 'Remise a', width: 0.3, align: 'center' },
            ],
            data.indiv_medals.map(m => [m.DESCRIPTION, nom + ' ' + prenom])
        );
    }

    // ── Diplomes ────────────────────────────────────────────────────────────────
    if (data.diplomes?.length) {
        pdf.sectionHeader('Diplomes officiels');
        pdf.table(
            [
                { label: 'Code', width: 0.10 },
                { label: 'Qualification', width: 0.28 },
                { label: 'Date', width: 0.12, align: 'center' },
                { label: 'N° diplome', width: 0.17, align: 'center' },
                { label: 'Delivre par', width: 0.21 },
                { label: 'Lieu', width: 0.12 },
            ],
            data.diplomes.map(d => [d.TYPE, d.DESCRIPTION, d.PF_DATE, d.PF_DIPLOME, d.PF_RESPONSABLE, d.PF_LIEU])
        );
    }

    // ── Qualifications ──────────────────────────────────────────────────────────
    if (data.qualifications?.length) {
        pdf.sectionHeader('Competences valides');
        pdf.table(
            [
                { label: 'Categorie', width: 0.28 },
                { label: 'Type', width: 0.15, align: 'center' },
                { label: 'Description', width: 0.42 },
                { label: 'Expiration', width: 0.15, align: 'center' },
            ],
            data.qualifications.map(q => [q.EQ_NOM, q.TYPE, q.DESCRIPTION, q.Q_EXPIRATION || 'Illimitee'])
        );
    }

    // ── Activity summaries ───────────────────────────────────────────────────────
    const actCols = (type) => type === 'formation'
        ? [
            { label: 'Date', width: 0.11 }, { label: 'Type', width: 0.17 },
            { label: 'Pour', width: 0.10 }, { label: 'Description', width: 0.28 },
            { label: 'Lieu', width: 0.18 }, { label: 'H', width: 0.08, align: 'right' }, { label: 'Role', width: 0.08 },
          ]
        : [
            { label: 'Date', width: 0.11 }, { label: 'Activite', width: 0.18 },
            { label: 'Description', width: 0.32 }, { label: 'Lieu', width: 0.23 },
            { label: 'H', width: 0.08, align: 'right' }, { label: 'Role', width: 0.08 },
          ];

    const actRows = (activities, type) => activities.map(a => type === 'formation'
        ? [a.datedeb, a.TE_LIBELLE, a.TF_CODE, a.E_LIBELLE, a.E_LIEU, a.EP_DUREE > 0 ? a.EP_DUREE : a.EH_DUREE, a.TP_LIBELLE]
        : [a.datedeb, a.TE_LIBELLE, a.E_LIBELLE, a.E_LIEU, a.EP_DUREE > 0 ? a.EP_DUREE : a.EH_DUREE, a.TP_LIBELLE]
    );

    if (data.formations?.length) {
        pdf.sectionHeader('Formations (12 derniers mois)');
        pdf.table(actCols('formation'), actRows(data.formations, 'formation'));
    }
    if (data.secours?.length) {
        pdf.sectionHeader('Operations de secours (12 derniers mois)');
        pdf.table(actCols('other'), actRows(data.secours, 'other'));
    }
    if (data.operations?.length) {
        pdf.sectionHeader('Activites operationnelles (12 derniers mois)');
        pdf.table(actCols('other'), actRows(data.operations, 'other'));
    }

    // ── 5-year summary ───────────────────────────────────────────────────────────
    if (data.summary_5y?.categories?.length) {
        pdf.sectionHeader('Bilan participations benevole sur 5 ans');
        const yr = new Date().getFullYear();
        const years = [yr-4, yr-3, yr-2, yr-1, yr];
        const yearFrac = 0.55 / 5;
        pdf.table(
            [
                { label: 'Activite', width: 0.45 },
                ...years.map(y => ({ label: String(y), width: yearFrac, align: 'right' })),
            ],
            data.summary_5y.categories.map(cat => [
                cat.label,
                ...years.map(y => data.summary_5y.data?.[cat.code]?.[y] ?? 0),
            ])
        );
    }

    return doc.save();
}

// ── Carte adhérent PDF ─────────────────────────────────────────────────────────

async function buildCartePdf(data) {
    const CW = 85.6 * MM;   // 242.6 pt
    const CH = 53.98 * MM;  // 153.1 pt

    const doc = await PDFDocument.create();
    const [regular, bold, italic] = await Promise.all([
        doc.embedFont(StandardFonts.Helvetica),
        doc.embedFont(StandardFonts.HelveticaBold),
        doc.embedFont(StandardFonts.HelveticaOblique),
    ]);

    const page = doc.addPage([CW, CH]);

    // Background
    page.drawRectangle({ x: 0, y: 0, width: CW, height: CH, color: rgb(0.86, 0.90, 0.96) });

    // Header bar (12mm)
    const hdrH = 12 * MM;
    page.drawRectangle({ x: 0, y: CH - hdrH, width: CW, height: hdrH, color: C.brand });
    const appName = data.app_name || 'OpenBrigade';
    const appW = bold.widthOfTextAtSize(appName, 8);
    page.drawText(appName, {
        x: (CW - appW) / 2, y: CH - hdrH + (hdrH - 8 * 0.72) / 2,
        size: 8, font: bold, color: C.white,
    });

    // Footer bar (7mm)
    const ftrH = 7 * MM;
    page.drawRectangle({ x: 0, y: 0, width: CW, height: ftrH, color: C.brand });
    const footTxt = 'Carte de membre  ' + new Date().getFullYear();
    const ftW = italic.widthOfTextAtSize(footTxt, 6);
    page.drawText(footTxt, {
        x: (CW - ftW) / 2, y: (ftrH - 6 * 0.72) / 2,
        size: 6, font: italic, color: C.white,
    });

    // Photo area (embedded if available): 20mm × 28mm, at x=3mm, top=14mm from card top
    if (data.photo_url) {
        try {
            const resp = await fetch(data.photo_url);
            if (resp.ok) {
                const bytes = await resp.arrayBuffer();
                const ct = resp.headers.get('content-type') || '';
                const img = (ct.includes('png') || data.photo_url.match(/\.png$/i))
                    ? await doc.embedPng(bytes)
                    : await doc.embedJpg(bytes);
                const photoH = 26 * MM;
                const photoW = 20 * MM;
                const photoX = 3 * MM;
                const photoY_top = 14 * MM; // from card top
                page.drawImage(img, {
                    x: photoX,
                    y: CH - photoY_top - photoH,
                    width: photoW,
                    height: photoH,
                });
            }
        } catch (_) {}
    }

    // Member info (right of photo)
    const infoX = 26 * MM;
    const infoTopFromCardTop = 15 * MM; // top of info block from card top (just below header)

    const nom = (data.nom || '').toUpperCase();
    const prenom = data.prenom || '';

    const drawAt = (text, size, font, color, topFromCardTop) => {
        page.drawText(text, {
            x: infoX,
            y: CH - topFromCardTop - size * 0.72,
            size, font, color,
        });
    };

    drawAt(nom, 9, bold, rgb(0, 0, 0.23), infoTopFromCardTop);
    drawAt(prenom, 8, regular, rgb(0, 0, 0.23), infoTopFromCardTop + 9 * 0.72 + 2 * MM);
    if (data.grade) {
        drawAt(data.grade, 7, italic, C.gray, infoTopFromCardTop + 9 * 0.72 + 5 * MM);
    }
    drawAt('N\xb0 ' + data.code, 7, regular, C.gray, infoTopFromCardTop + 9 * 0.72 + 9 * MM);
    if (data.section) {
        const secStr = String(data.section).substring(0, 32);
        drawAt(secStr, 7, regular, C.gray, infoTopFromCardTop + 9 * 0.72 + 13 * MM);
    }

    return doc.save();
}

// ── Download helpers ───────────────────────────────────────────────────────────

function triggerDownload(bytes, filename) {
    const blob = new Blob([bytes], { type: 'application/pdf' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

async function fetchPersonnelData(url) {
    const resp = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    });
    if (!resp.ok) throw new Error('Erreur ' + resp.status);
    return resp.json();
}

export async function downloadLivretPdf(personnelId) {
    const btn = document.querySelector('[data-livret-btn]');
    if (btn) { btn.disabled = true; btn.textContent = 'Generation...'; }
    try {
        const data = await fetchPersonnelData(`/personnel/${personnelId}/livret-data`);
        const bytes = await buildLivretPdf(data);
        triggerDownload(bytes, `livret-${(data.nom || personnelId).toLowerCase()}.pdf`);
    } catch (e) {
        console.error(e);
        alert('Erreur lors de la generation du PDF.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-pdf me-2 text-danger"></i> Livret (PDF)';
        }
    }
}

export async function downloadCartePdf(personnelId) {
    const btn = document.querySelector('[data-carte-btn]');
    if (btn) { btn.disabled = true; btn.textContent = 'Generation...'; }
    try {
        const data = await fetchPersonnelData(`/personnel/${personnelId}/carte-data`);
        const bytes = await buildCartePdf(data);
        triggerDownload(bytes, `carte-${(data.nom || personnelId).toLowerCase()}.pdf`);
    } catch (e) {
        console.error(e);
        alert('Erreur lors de la generation du PDF.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-id-card me-2 text-danger"></i> Carte adherent (PDF)';
        }
    }
}

window.__downloadLivretPdf = downloadLivretPdf;
window.__downloadCartePdf = downloadCartePdf;
