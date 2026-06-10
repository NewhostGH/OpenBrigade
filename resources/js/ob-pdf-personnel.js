import { PDFDocument, StandardFonts, rgb } from 'pdf-lib';

// ── Constants ──────────────────────────────────────────────────────────────────
const MM = 2.8346;

const C = {
    brand: rgb(0.12, 0.23, 0.47),
    brandLt: rgb(0.92, 0.95, 0.98),
    ident: rgb(0.05, 0.21, 0.58),
    gray: rgb(0.45, 0.45, 0.45),
    grayLt: rgb(0.95, 0.95, 0.95),
    dark: rgb(0.08, 0.08, 0.08),
    white: rgb(1, 1, 1),
    sep: rgb(0.80, 0.80, 0.80),
};

const L = {
    // Print line / header
    imprime: 'Imprimé le ',
    page: 'Page ',
    passeportHeader: 'Passeport du bénévole  —  ',
    passeportTitle: 'Passeport du bénévole',

    // Identity block labels
    identite: 'Identité:',
    dateNaissance: 'Date de naissance:',
    lieuNaissance: 'Lieu de naissance:',
    adresse: 'Adresse:',
    telephone: 'Téléphone:',
    email: 'Email:',
    departement: 'Département:',
    antenne: 'Antenne:',
    dateEngagement: 'Date engagement:',

    // Section headers
    shDecorations: 'Décorations collectives',
    shMedailles: 'Médailles et Récompenses',
    shDiplomes: 'Diplômes officiels',
    shCompetences: 'Compétences valides au ',
    shFormations: 'Formations depuis 1 an',
    shSecours: 'Opérations de secours depuis 1 an',
    shOperations: 'Activités opérationnelles depuis 1 an',
    shBilan5y: 'Bilan participations bénévole sur 5 ans',

    // Table column headers
    medaille: 'Médaille',
    date: 'Date',
    agrafe: 'Agrafe',
    decerneeA: 'Décernée à',
    remiseA: 'Remise à',
    code: 'Code',
    qualification: 'Qualification',
    nDiplome: 'N° diplôme',
    delivrePar: 'Délivré par',
    lieu: 'Lieu',
    categorie: 'Catégorie',
    type: 'Type',
    description: 'Description',
    expiration: 'Expiration',
    illimitee: 'Illimitée',
    activite: 'Activité',
    pour: 'Pour',
    role: 'Rôle',
    h: 'H',
    total: 'TOTAL',

    // Carte
    carteMembre: 'Carte de membre  ',

    // UI
    generation: 'Génération...',
    erreurPdf: 'Erreur lors de la génération du PDF.',
    livretBtn: '<i class="fas fa-file-pdf me-2 text-danger"></i> Livret (PDF)',
    carteBtn: '<i class="fas fa-id-card me-2 text-danger"></i> Carte adhérent (PDF)',
    erreurHttp: 'Erreur ',
};

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// Fetch an image and embed it into the document (PNG or JPG).
async function embedImage(doc, url) {
    const resp = await fetch(url);
    if (!resp.ok) return null;
    const bytes = await resp.arrayBuffer();
    const ct = resp.headers.get('content-type') || '';
    if (ct.includes('png') || /\.png(\?.*)?$/i.test(url)) {
        return doc.embedPng(bytes);
    }
    return doc.embedJpg(bytes);
}

// Fetch the section letterhead PDF ("Papier à entête") and embed its first page.
async function embedLetterhead(doc, letterhead) {
    if (!letterhead?.pdf_url) return null;
    try {
        const resp = await fetch(letterhead.pdf_url);
        if (!resp.ok) return null;
        const bytes = await resp.arrayBuffer();
        const [page] = await doc.embedPdf(bytes);
        return page;
    } catch (e) {
        console.warn('Letterhead unavailable, rendering without it.', e);
        return null;
    }
}

// ── Livret PDF ─────────────────────────────────────────────────────────────────

const A4W = 595.28;
const A4H = 841.89;

class LivetPdf {
    /**
     * opts: {
     *   letterhead: embedded PDF page used as page background (or null),
     *   margeLeft, texteTop, texteBottom: text zone in points
     *     (from section settings: Marge gauche/droite, Début/Fin zone de texte)
     * }
     */
    constructor(doc, regular, bold, italic, opts = {}) {
        this.doc = doc;
        this.regular = regular;
        this.bold = bold;
        this.italic = italic;
        this.page = null;
        this.topY = 0;
        this.pageNo = 0;
        this._hdrTitle = '';

        this.letterhead = opts.letterhead || null;
        this.marginX = opts.margeLeft ?? 15 * MM;
        this.cw = A4W - 2 * this.marginX;
        this.topStart = opts.texteTop ?? 22 * MM;
        this.bottomLimit = A4H - (opts.texteBottom ?? 10 * MM);
    }

    setHeader(title) { this._hdrTitle = title; }

    newPage() {
        this.page = this.doc.addPage([A4W, A4H]);
        this.pageNo += 1;

        if (this.letterhead) {
            // Letterhead PDF as full-page background; the header/footer artwork
            // belongs to the template, text stays inside the configured zone.
            this.page.drawPage(this.letterhead, { x: 0, y: 0, width: A4W, height: A4H });
        } else {
            // Fallback drawn header when the entity has no letterhead.
            this._rect(0, 0, A4W, 18 * MM, C.brand);
            this._text(this._hdrTitle, this.marginX, 5 * MM, 11, this.bold, C.white);
        }

        // Print line just below the text zone
        const footTop = this.bottomLimit + 2 * MM;
        const dateStr = L.imprime + new Date().toLocaleDateString('fr-FR');
        this._text(dateStr, this.marginX, footTop, 6, this.italic, C.gray);
        const pageTxt = L.page + this.pageNo;
        const ptW = this.italic.widthOfTextAtSize(pageTxt, 6);
        this._text(pageTxt, A4W - this.marginX - ptW, footTop, 6, this.italic, C.gray);

        this.topY = this.topStart;
    }

    allocate(h) {
        if (this.topY + h > this.bottomLimit) this.newPage();
    }

    sectionHeader(label) {
        this.topY += 2 * MM;
        this.allocate(8 * MM + 12 * MM); // header + at least one row
        this._rect(this.marginX, this.topY, this.cw, 7 * MM, C.brandLt);
        this._text(label, this.marginX + 3 * MM, this.topY + 1.2 * MM, 9, this.bold, C.brand);
        this.topY += 8 * MM;
    }

    table(headers, rows, totals = null) {
        if (!rows.length) return;
        const ROW_H = 5.5 * MM;
        const HDR_H = 6 * MM;
        const widths = headers.map(h => h.width * this.cw);

        const drawHeader = () => {
            this._rect(this.marginX, this.topY, this.cw, HDR_H, C.brand);
            let x = this.marginX;
            headers.forEach((h, i) => {
                const lw = this.bold.widthOfTextAtSize(h.label, 7.5);
                this._text(h.label, x + (widths[i] - lw) / 2, this.topY + 1.1 * MM, 7.5, this.bold, C.white);
                x += widths[i];
            });
            this.topY += HDR_H;
        };

        this.allocate(HDR_H + ROW_H);
        drawHeader();

        const drawRow = (row, fill, font) => {
            if (this.topY + ROW_H > this.bottomLimit) {
                this.newPage();
                drawHeader();
            }
            if (fill) this._rect(this.marginX, this.topY, this.cw, ROW_H, fill);
            this._line(this.marginX, this.topY + ROW_H, this.marginX + this.cw, this.topY + ROW_H, C.sep, 0.25);
            let x = this.marginX;
            row.forEach((cell, ci) => {
                const s = String(cell ?? '-');
                const SIZE = 7;
                const align = headers[ci]?.align || 'left';
                const sw = font.widthOfTextAtSize(s, SIZE);
                let tx;
                if (align === 'right') tx = x + widths[ci] - sw - 1.5 * MM;
                else if (align === 'center') tx = x + (widths[ci] - sw) / 2;
                else tx = x + 1.5 * MM;
                this._text(s, Math.max(x + 0.5 * MM, tx), this.topY + 1.2 * MM, SIZE, font, C.dark);
                x += widths[ci];
            });
            this.topY += ROW_H;
        };

        rows.forEach((row, rIdx) => drawRow(row, rIdx % 2 === 0 ? C.grayLt : null, this.regular));
        if (totals) drawRow(totals, C.brandLt, this.bold);

        this.topY += 1.5 * MM;
    }

    _text(str, x, topY, size, font, color) {
        try {
            this.page.drawText(str, { x, y: A4H - topY - size * 0.72, size, font, color });
        } catch (_) { }
    }
    _rect(x, topY, width, height, color) {
        this.page.drawRectangle({ x, y: A4H - topY - height, width, height, color });
    }
    _rectBorder(x, topY, width, height, color, borderWidth = 0.8) {
        this.page.drawRectangle({
            x, y: A4H - topY - height, width, height,
            borderColor: color, borderWidth,
        });
    }
    _line(x1, y1, x2, y2, color, thickness = 0.5) {
        this.page.drawLine({ start: { x: x1, y: A4H - y1 }, end: { x: x2, y: A4H - y2 }, thickness, color });
    }
}

async function buildLivretPdf(doc, data) {
    const [regular, bold, italic] = await Promise.all([
        doc.embedFont(StandardFonts.Helvetica),
        doc.embedFont(StandardFonts.HelveticaBold),
        doc.embedFont(StandardFonts.HelveticaOblique),
    ]);

    const lh = data.letterhead || {};
    const letterhead = await embedLetterhead(doc, lh);

    const pdf = new LivetPdf(doc, regular, bold, italic, letterhead ? {
        letterhead,
        margeLeft: (lh.marge_left ?? 15) * MM,
        texteTop: (lh.texte_top ?? 40) * MM,
        texteBottom: (lh.texte_bottom ?? 25) * MM,
    } : {});

    const nom = (data.nom || '').toUpperCase();
    const prenom = data.prenom || '';
    pdf.setHeader(L.passeportHeader + nom + ' ' + prenom);
    pdf.newPage();

    // ── Title ───────────────────────────────────────────────────────────────────
    const titleW = 100 * MM;
    const titleX = (A4W - titleW) / 2;
    pdf._rectBorder(titleX, pdf.topY, titleW, 15 * MM, C.dark);
    const tW = bold.widthOfTextAtSize(L.passeportTitle, 20);
    pdf._text(L.passeportTitle, (A4W - tW) / 2, pdf.topY + 4.5 * MM, 20, bold, C.dark);
    pdf.topY += 15 * MM + 6 * MM;

    // ── Identité ────────────────────────────────────────────────────────────────
    const idTop = pdf.topY;

    // Photo on the left, like the legacy livret
    if (data.photo_url) {
        try {
            const img = await embedImage(doc, data.photo_url);
            if (img) {
                const box = { w: 28 * MM, h: 36 * MM };
                const scale = Math.min(box.w / img.width, box.h / img.height);
                pdf.page.drawImage(img, {
                    x: pdf.marginX,
                    y: A4H - idTop - img.height * scale,
                    width: img.width * scale,
                    height: img.height * scale,
                });
            }
        } catch (_) { }
    }

    const labelX = pdf.marginX + 45 * MM;
    const valX = pdf.marginX + 79 * MM;
    const lineH = 7 * MM;

    pdf._text(L.identite, labelX, pdf.topY, 12, bold, C.dark);
    pdf._text([data.civilite, nom, prenom].filter(Boolean).join(' '), valX, pdf.topY, 12, bold, C.ident);
    pdf.topY += lineH;

    const idRows = [
        [L.dateNaissance, data.birthdate],
        [L.lieuNaissance, data.birthplace],
        [L.adresse, data.address],
        ['', data.zip_city],
        [L.telephone, data.phone],
        [L.email, data.email],
        [L.departement, data.departement],
        [L.antenne, data.antenne],
        [L.dateEngagement, data.date_engagement],
    ];
    idRows.forEach(([label, value]) => {
        if (label === '' && !value) return;
        pdf.allocate(lineH);
        if (label) pdf._text(label, labelX, pdf.topY, 10, regular, C.dark);
        pdf._text(String(value || ''), valX, pdf.topY, 10, regular, C.dark);
        pdf.topY += lineH;
    });

    // Make sure following sections start below the photo
    pdf.topY = Math.max(pdf.topY, idTop + 38 * MM);

    // ── Décorations collectives ─────────────────────────────────────────────────
    if (data.medals?.length) {
        pdf.sectionHeader(L.shDecorations);
        pdf.table(
            [
                { label: L.medaille, width: 0.38 },
                { label: L.date, width: 0.15, align: 'center' },
                { label: L.agrafe, width: 0.25, align: 'center' },
                { label: L.decerneeA, width: 0.22, align: 'center' },
            ],
            data.medals.map(m => [m.TA_DESCRIPTION, m.A_DEBUT, m.A_COMMENT, m.S_DESCRIPTION])
        );
    }

    if (data.indiv_medals?.length) {
        pdf.sectionHeader(L.shMedailles);
        pdf.table(
            [
                { label: L.medaille, width: 0.7 },
                { label: L.remiseA, width: 0.3, align: 'center' },
            ],
            data.indiv_medals.map(m => [m.DESCRIPTION, nom + ' ' + prenom])
        );
    }

    // ── Diplômes ────────────────────────────────────────────────────────────────
    if (data.diplomes?.length) {
        pdf.sectionHeader(L.shDiplomes);
        pdf.table(
            [
                { label: L.code, width: 0.10 },
                { label: L.qualification, width: 0.28 },
                { label: L.date, width: 0.12, align: 'center' },
                { label: L.nDiplome, width: 0.17, align: 'center' },
                { label: L.delivrePar, width: 0.21 },
                { label: L.lieu, width: 0.12 },
            ],
            data.diplomes.map(d => [d.TYPE, d.DESCRIPTION, d.PF_DATE, d.PF_DIPLOME, d.PF_RESPONSABLE, d.PF_LIEU])
        );
    }

    // ── Compétences ─────────────────────────────────────────────────────────────
    if (data.qualifications?.length) {
        pdf.sectionHeader(L.shCompetences + new Date().toLocaleDateString('fr-FR'));
        pdf.table(
            [
                { label: L.categorie, width: 0.28 },
                { label: L.type, width: 0.15, align: 'center' },
                { label: L.description, width: 0.42 },
                { label: L.expiration, width: 0.15, align: 'center' },
            ],
            data.qualifications.map(q => [q.EQ_NOM, q.TYPE, q.DESCRIPTION, q.Q_EXPIRATION || L.illimitee])
        );
    }

    // ── Activités sur 12 mois ────────────────────────────────────────────────────
    const actCols = (type) => type === 'formation'
        ? [
            { label: L.date, width: 0.11 }, { label: L.type, width: 0.17 },
            { label: L.pour, width: 0.10 }, { label: L.description, width: 0.28 },
            { label: L.lieu, width: 0.18 }, { label: L.h, width: 0.08, align: 'right' }, { label: L.role, width: 0.08 },
        ]
        : [
            { label: L.date, width: 0.11 }, { label: L.activite, width: 0.18 },
            { label: L.description, width: 0.32 }, { label: L.lieu, width: 0.23 },
            { label: L.h, width: 0.08, align: 'right' }, { label: L.role, width: 0.08 },
        ];

    const actRows = (activities, type) => activities.map(a => type === 'formation'
        ? [a.datedeb, a.TE_LIBELLE, a.TF_CODE, a.E_LIBELLE, a.E_LIEU, a.EP_DUREE > 0 ? a.EP_DUREE : a.EH_DUREE, a.TP_LIBELLE]
        : [a.datedeb, a.TE_LIBELLE, a.E_LIBELLE, a.E_LIEU, a.EP_DUREE > 0 ? a.EP_DUREE : a.EH_DUREE, a.TP_LIBELLE]
    );

    if (data.formations?.length) {
        pdf.sectionHeader(L.shFormations);
        pdf.table(actCols('formation'), actRows(data.formations, 'formation'));
    }
    if (data.secours?.length) {
        pdf.sectionHeader(L.shSecours);
        pdf.table(actCols('other'), actRows(data.secours, 'other'));
    }
    if (data.operations?.length) {
        pdf.sectionHeader(L.shOperations);
        pdf.table(actCols('other'), actRows(data.operations, 'other'));
    }

    // ── Bilan participations bénévole sur 5 ans ─────────────────────────────────
    if (data.summary_5y?.categories?.length) {
        pdf.sectionHeader(L.shBilan5y);
        const yr = new Date().getFullYear();
        const years = [yr - 4, yr - 3, yr - 2, yr - 1, yr];
        const yearFrac = 0.55 / 5;
        const cellHours = (code, y) => Number(data.summary_5y.data?.[code]?.[y] ?? 0);
        const totals = years.map(y =>
            data.summary_5y.categories.reduce((sum, cat) => sum + cellHours(cat.code, y), 0)
        );
        pdf.table(
            [
                { label: L.activite, width: 0.45 },
                ...years.map(y => ({ label: String(y), width: yearFrac, align: 'right' })),
            ],
            data.summary_5y.categories.map(cat => [
                cat.label,
                ...years.map(y => cellHours(cat.code, y)),
            ]),
            [L.total, ...totals.map(t => t + ' h')]
        );
    }

    return doc.save();
}

// ── Carte adhérent PDF ─────────────────────────────────────────────────────────

async function buildCartePdf(doc, data) {
    const CW = 85.6 * MM;   // 242.6 pt
    const CH = 53.98 * MM;  // 153.1 pt

    const [regular, bold, italic] = await Promise.all([
        doc.embedFont(StandardFonts.Helvetica),
        doc.embedFont(StandardFonts.HelveticaBold),
        doc.embedFont(StandardFonts.HelveticaOblique),
    ]);

    const page = doc.addPage([CW, CH]);

    // "Image de fond du badge" from the section settings, full-card background.
    let badge = null;
    if (data.badge_url) {
        try {
            badge = await embedImage(doc, data.badge_url);
        } catch (_) { }
    }

    if (badge) {
        page.drawImage(badge, { x: 0, y: 0, width: CW, height: CH });
    } else {
        // Fallback drawn design when the entity has no badge background
        page.drawRectangle({ x: 0, y: 0, width: CW, height: CH, color: rgb(0.86, 0.90, 0.96) });

        const hdrH = 12 * MM;
        page.drawRectangle({ x: 0, y: CH - hdrH, width: CW, height: hdrH, color: C.brand });
        const appName = data.app_name || 'OpenBrigade';
        const appW = bold.widthOfTextAtSize(appName, 8);
        page.drawText(appName, {
            x: (CW - appW) / 2, y: CH - hdrH + (hdrH - 8 * 0.72) / 2,
            size: 8, font: bold, color: C.white,
        });

        const ftrH = 7 * MM;
        page.drawRectangle({ x: 0, y: 0, width: CW, height: ftrH, color: C.brand });
        const footTxt = L.carteMembre + new Date().getFullYear();
        const ftW = italic.widthOfTextAtSize(footTxt, 6);
        page.drawText(footTxt, {
            x: (CW - ftW) / 2, y: (ftrH - 6 * 0.72) / 2,
            size: 6, font: italic, color: C.white,
        });
    }

    // Photo area: 20mm × 26mm, at x=3mm, top=14mm from card top
    if (data.photo_url) {
        try {
            const img = await embedImage(doc, data.photo_url);
            if (img) {
                const photoH = 26 * MM;
                const photoW = 20 * MM;
                page.drawImage(img, {
                    x: 3 * MM,
                    y: CH - 14 * MM - photoH,
                    width: photoW,
                    height: photoH,
                });
            }
        } catch (_) { }
    }

    // Member info (right of photo)
    const infoX = 26 * MM;
    const infoTopFromCardTop = 15 * MM;

    const nom = (data.nom || '').toUpperCase();
    const prenom = data.prenom || '';

    const drawAt = (text, size, font, color, topFromCardTop) => {
        try {
            page.drawText(text, {
                x: infoX,
                y: CH - topFromCardTop - size * 0.72,
                size, font, color,
            });
        } catch (_) { }
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
    if (!resp.ok) throw new Error(L.erreurHttp + resp.status);
    return resp.json();
}

export async function downloadLivretPdf(personnelId) {
    const btn = document.querySelector('[data-livret-btn]');
    if (btn) { btn.disabled = true; btn.textContent = L.generation; }
    try {
        const data = await fetchPersonnelData(`/personnel/${personnelId}/livret-data`);
        const doc = await PDFDocument.create();
        const bytes = await buildLivretPdf(doc, data);
        triggerDownload(bytes, `livret-${(data.nom || personnelId).toLowerCase()}.pdf`);
    } catch (e) {
        console.error(e);
        alert(L.erreurPdf);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = L.livretBtn;
        }
    }
}

export async function downloadCartePdf(personnelId) {
    const btn = document.querySelector('[data-carte-btn]');
    if (btn) { btn.disabled = true; btn.textContent = L.generation; }
    try {
        const data = await fetchPersonnelData(`/personnel/${personnelId}/carte-data`);
        const doc = await PDFDocument.create();
        const bytes = await buildCartePdf(doc, data);
        triggerDownload(bytes, `carte-${(data.nom || personnelId).toLowerCase()}.pdf`);
    } catch (e) {
        console.error(e);
        alert(L.erreurPdf);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = L.carteBtn;
        }
    }
}

window.__downloadLivretPdf = downloadLivretPdf;
window.__downloadCartePdf = downloadCartePdf;
