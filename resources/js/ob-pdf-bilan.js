import { PDFDocument, StandardFonts, rgb } from 'pdf-lib';

// ── Constants ──────────────────────────────────────────────────────────────────
const MM = 2.8346;       // 1mm in points
const A4W = 595.28;
const A4H = 841.89;
const MARGIN = 15 * MM;
const CW = A4W - 2 * MARGIN;

const C = {
    brand: rgb(0.09, 0.24, 0.47),
    brandLt: rgb(0.92, 0.95, 0.98),
    gray: rgb(0.45, 0.45, 0.45),
    grayLt: rgb(0.95, 0.95, 0.95),
    dark: rgb(0.1, 0.1, 0.1),
    white: rgb(1, 1, 1),
    sep: rgb(0.82, 0.82, 0.82),
};

const MONTHS = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

const L = {
    appName:          'OpenBrigade',
    genere:           'Généré le ',
    bilanAnnuel:      'BILAN ANNUEL ',
    bilanPrefix:      'BILAN ',
    generation:       'Génération...',
    telechargerPdf:   '<i class="fas fa-file-pdf me-1"></i>Télécharger PDF',

    // Tab labels (cover + tabLabels map)
    tabGeneralites:   'Généralités, personnels et moyens',
    tabActivites:     'Activités opérationnelles',
    tabFormations:    'Formations',

    // setMeta subtitles
    metaGeneralites:  'Généralités',
    metaActivites:    'Activités',
    metaFormations:   'Formations',

    // Section headers
    shPersonnel:      'Personnel',
    shNouveauxEng:    'Nouveaux engagements (5 ans)',
    shVehicules:      'Véhicules',
    shMateriel:       'Matériel',
    shConsommables:   'Consommables',
    shActivites:      'Activités opérationnelles',
    shRepartMens:     'Répartition mensuelle',
    shRepartType:     'Répartition par type',
    shTop10:          'Top 10 participants',
    shFormations:     'Formations',
    shDetailSessions: 'Détail des sessions',

    // KPI labels
    membresActifs:    'Membres actifs',
    totalVehicules:   'Total véhicules',
    articlesInv:      'Articles inventoriés',
    activites:        'Activités',
    participations:   'Participations',
    heuresBenevoles:  'Heures bénévoles',
    sessions:         'Sessions',
    stagiaires:       'Stagiaires',
    heuresDisp:       'Heures dispensées',
    cumules:          'cumulés',
    partCumulees:     'participations cumulées',
    enAnnee:          'en ',

    // Table column headers
    groupe:           'Groupe',
    membres:          'Membres',
    annee:            'Année',
    engagements:      'Engagements',
    type:             'Type',
    quantite:         'Quantité',
    categorie:        'Catégorie',
    mois:             'Mois',
    participants:     'Participants',
    nom:              'Nom',
    prenom:           'Prénom',
    date:             'Date',
    intitule:         'Intitulé',
    lieu:             'Lieu',
    dureeH:           'Durée h',
    stag:             'Stag.',
};

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

// ── PDF helper class ───────────────────────────────────────────────────────────
class BilanPdf {
    /**
     * opts: {
     *   letterhead: embedded PDF page used as page background (or null),
     *   margeLeft, texteTop, texteBottom: text zone in points
     *     (from section settings: Marge gauche/droite, Début/Fin zone de texte)
     * }
     */
    constructor(doc, regular, bold, opts = {}) {
        this.doc = doc;
        this.regular = regular;
        this.bold = bold;
        this.page = null;
        this.topY = 0;       // cursor from page top (points)
        this._meta = {};

        this.letterhead = opts.letterhead || null;
        this.marginX = opts.margeLeft ?? MARGIN;
        this.cw = A4W - 2 * this.marginX;
        this.topStart = opts.texteTop ?? 26 * MM;
        this.bottomLimit = A4H - (opts.texteBottom ?? 12 * MM);
    }

    setMeta(title, subtitle, section, year) {
        this._meta = { title, subtitle, section, year };
    }

    // Add content page with header + footer, reset cursor
    newPage() {
        const { title, subtitle, section, year } = this._meta;
        this.page = this.doc.addPage([A4W, A4H]);

        if (this.letterhead) {
            // Letterhead PDF as full-page background; the header/footer artwork
            // belongs to the template, text stays inside the configured zone.
            this.page.drawPage(this.letterhead, { x: 0, y: 0, width: A4W, height: A4H });

            // Discreet context line just below the text zone
            const footTop = this.bottomLimit + 2 * MM;
            const footL = (title || '') + (subtitle ? '  —  ' + subtitle : '');
            this._text(footL, this.marginX, footTop, 6, this.regular, C.gray);
        } else {
            // Fallback drawn header when the entity has no letterhead
            this._rect(0, 0, A4W, 22 * MM, C.brand);
            this._text(title || '', this.marginX, 5 * MM, 15, this.bold, C.white);
            if (subtitle) {
                const sw = this.bold.widthOfTextAtSize(subtitle, 9);
                this._text(subtitle, A4W - this.marginX - sw, 7 * MM, 9, this.regular, rgb(0.75, 0.85, 1));
            }

            // Footer
            const footTop = A4H - 10 * MM;
            this._line(this.marginX, footTop, A4W - this.marginX, footTop, C.sep, 0.4);
            const footL = (section || '') + (year ? '  —  ' + year : '');
            this._text(footL, this.marginX, footTop + 2 * MM, 7, this.regular, C.gray);
            const appW = this.regular.widthOfTextAtSize(L.appName, 7);
            this._text(L.appName, A4W - this.marginX - appW, footTop + 2 * MM, 7, this.regular, C.gray);
        }

        this.topY = this.topStart;
        return this;
    }

    // Check remaining space; add new page if height won't fit
    allocate(height) {
        if (this.topY + height > this.bottomLimit) {
            this.newPage();
        }
    }

    // Section heading
    sectionHeader(label) {
        this.topY += 2 * MM;
        this.allocate(9 * MM);
        this._rect(this.marginX, this.topY, this.cw, 8 * MM, C.brandLt);
        this._text(label, this.marginX + 3 * MM, this.topY + 1.5 * MM, 9, this.bold, C.brand);
        this.topY += 9 * MM;
    }

    // KPI cards row: [{label, value, sub?}]
    kpiRow(kpis) {
        const KH = 18 * MM;
        this.allocate(KH + 4 * MM);
        const n = kpis.length;
        const gap = 2 * MM;
        const kw = (this.cw - gap * (n - 1)) / n;
        kpis.forEach((k, i) => {
            const x = this.marginX + i * (kw + gap);
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
        const widths = headers.map(h => h.width * this.cw);

        // Header
        this.allocate(HDR_H + ROW_H);
        this._rect(this.marginX, this.topY, this.cw, HDR_H, C.brand);
        let x = this.marginX;
        headers.forEach((h, i) => {
            const lw = this.bold.widthOfTextAtSize(h.label, 8);
            this._text(h.label, x + (widths[i] - lw) / 2, this.topY + 1.2 * MM, 8, this.bold, C.white);
            x += widths[i];
        });
        this.topY += HDR_H;

        rows.forEach((row, rIdx) => {
            this.allocate(ROW_H);
            if (rIdx % 2 === 0) {
                this._rect(this.marginX, this.topY, this.cw, ROW_H, C.grayLt);
            }
            this._line(this.marginX, this.topY + ROW_H, this.marginX + this.cw, this.topY + ROW_H, C.sep, 0.3);
            x = this.marginX;
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
            end: { x: x2, y: A4H - y2 },
            thickness,
            color,
        });
    }
}

// ── Cover page ─────────────────────────────────────────────────────────────────
function buildCover(pdf, year, sectionName, tabLabel) {
    pdf.page = pdf.doc.addPage([A4W, A4H]);

    if (pdf.letterhead) {
        // Letterhead as cover background; titles centred inside the text zone
        pdf.page.drawPage(pdf.letterhead, { x: 0, y: 0, width: A4W, height: A4H });

        const zoneTop = pdf.topStart;
        const zoneH = pdf.bottomLimit - zoneTop;

        const t1 = L.bilanAnnuel + year;
        const t1size = 26;
        const t1w = pdf.bold.widthOfTextAtSize(t1, t1size);
        let y = zoneTop + zoneH * 0.30;
        pdf._text(t1, (A4W - t1w) / 2, y, t1size, pdf.bold, C.brand);
        y += t1size * 0.72 + 8 * MM;

        if (tabLabel) {
            const t2size = 13;
            const t2w = pdf.regular.widthOfTextAtSize(tabLabel, t2size);
            pdf._text(tabLabel, (A4W - t2w) / 2, y, t2size, pdf.regular, C.gray);
            y += t2size * 0.72 + 10 * MM;
        }

        pdf._rect(pdf.marginX, y, pdf.cw, 0.8, C.sep);
        y += 6 * MM;

        const secName = sectionName || L.appName;
        const snSize = 13;
        const snW = pdf.bold.widthOfTextAtSize(secName, snSize);
        pdf._text(secName, (A4W - snW) / 2, y, snSize, pdf.bold, C.brand);
        y += snSize * 0.72 + 4 * MM;

        const dateStr = L.genere + new Date().toLocaleDateString('fr-FR', {
            day: '2-digit', month: 'long', year: 'numeric',
        });
        const dateSize = 9;
        const dateW = pdf.regular.widthOfTextAtSize(dateStr, dateSize);
        pdf._text(dateStr, (A4W - dateW) / 2, y, dateSize, pdf.regular, C.gray);
        return;
    }

    // Fallback drawn cover when the entity has no letterhead
    const hH = A4H * 0.38;
    pdf._rect(0, 0, A4W, hH, C.brand);

    // Main title
    const t1 = L.bilanAnnuel + year;
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
    const secName = sectionName || L.appName;
    const snSize = 13;
    const snW = pdf.bold.widthOfTextAtSize(secName, snSize);
    pdf._text(secName, (A4W - snW) / 2, hH + 14 * MM, snSize, pdf.bold, C.brand);

    // Date
    const dateStr = L.genere + new Date().toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'long', year: 'numeric',
    });
    const dateSize = 9;
    const dateW = pdf.regular.widthOfTextAtSize(dateStr, dateSize);
    pdf._text(dateStr, (A4W - dateW) / 2, hH + 14 * MM + snSize * 0.72 + 4 * MM, dateSize, pdf.regular, C.gray);
}

// ── Tab builders ───────────────────────────────────────────────────────────────

function buildGeneralites(pdf, data) {
    pdf.setMeta(L.bilanPrefix + data.year, L.metaGeneralites, data.section?.name || '', data.year);
    pdf.newPage();

    // Personnel
    pdf.sectionHeader(L.shPersonnel);
    pdf.kpiRow([{ label: L.membresActifs, value: data.totalMembers }]);

    const groupEntries = Object.entries(data.membersByGroup || {});
    if (groupEntries.length) {
        pdf.table(
            [{ label: L.groupe, width: 0.75 }, { label: L.membres, width: 0.25, align: 'right' }],
            groupEntries.map(([l, n]) => [l, n])
        );
    }

    const yearEntries = Object.entries(data.newMembersByYear || {});
    if (yearEntries.length) {
        pdf.sectionHeader(L.shNouveauxEng);
        pdf.table(
            [{ label: L.annee, width: 0.5 }, { label: L.engagements, width: 0.5, align: 'right' }],
            yearEntries.map(([yr, n]) => [yr, n])
        );
    }

    // Véhicules
    pdf.sectionHeader(L.shVehicules);
    pdf.kpiRow([{ label: L.totalVehicules, value: data.totalVehicles }]);
    if (data.vehiclesByType?.length) {
        pdf.table(
            [{ label: L.type, width: 0.75 }, { label: L.quantite, width: 0.25, align: 'right' }],
            data.vehiclesByType.map(r => [r.label, r.nb])
        );
    }

    // Matériel
    pdf.sectionHeader(L.shMateriel);
    pdf.kpiRow([{ label: L.articlesInv, value: data.totalMateriels }]);
    if (data.materielsByType?.length) {
        pdf.table(
            [{ label: L.categorie, width: 0.75 }, { label: L.quantite, width: 0.25, align: 'right' }],
            data.materielsByType.map(r => [r.label, r.nb])
        );
    }

    // Consommables
    pdf.sectionHeader(L.shConsommables);
    pdf.kpiRow([{ label: L.articlesInv, value: data.totalConsommables }]);
    if (data.consommablesByType?.length) {
        pdf.table(
            [{ label: L.categorie, width: 0.75 }, { label: L.quantite, width: 0.25, align: 'right' }],
            data.consommablesByType.map(r => [r.label, r.nb])
        );
    }
}

function buildActivites(pdf, data) {
    pdf.setMeta(L.bilanPrefix + data.year, L.metaActivites, data.section?.name || '', data.year);
    pdf.newPage();

    pdf.sectionHeader(L.shActivites);
    pdf.kpiRow([
        { label: L.activites,       value: data.totalEvents,        sub: L.enAnnee + data.year },
        { label: L.participations,  value: data.totalParticipants,  sub: L.cumules },
        { label: L.heuresBenevoles, value: (data.totalHours || 0).toLocaleString('fr-FR') },
    ]);

    pdf.sectionHeader(L.shRepartMens);
    pdf.table(
        [
            { label: L.mois,          width: 0.25 },
            { label: L.activites,     width: 0.375, align: 'right' },
            { label: L.participants,  width: 0.375, align: 'right' },
        ],
        MONTHS.map((m, i) => [m, data.eventsData?.[i] ?? 0, data.participantData?.[i] ?? 0])
    );

    const typeEntries = Object.entries(data.eventsByType || {});
    if (typeEntries.length) {
        pdf.sectionHeader(L.shRepartType);
        pdf.table(
            [{ label: L.type, width: 0.75 }, { label: L.activites, width: 0.25, align: 'right' }],
            typeEntries.map(([l, n]) => [l, n])
        );
    }

    if (data.topParticipants?.length) {
        pdf.sectionHeader(L.shTop10);
        pdf.table(
            [
                { label: '#',       width: 0.07, align: 'center' },
                { label: L.prenom,  width: 0.31 },
                { label: L.nom,     width: 0.38 },
                { label: L.activites, width: 0.24, align: 'right' },
            ],
            data.topParticipants.map((p, i) => [i + 1, p.prenom, p.nom, p.nb])
        );
    }
}

function buildFormations(pdf, data) {
    pdf.setMeta(L.bilanPrefix + data.year, L.metaFormations, data.section?.name || '', data.year);
    pdf.newPage();

    pdf.sectionHeader(L.shFormations);
    pdf.kpiRow([
        { label: L.sessions,   value: data.totalFormations, sub: L.enAnnee + data.year },
        { label: L.stagiaires, value: data.totalTrained,    sub: L.partCumulees },
        { label: L.heuresDisp, value: (data.totalHours || 0).toLocaleString('fr-FR') },
    ]);

    pdf.sectionHeader(L.shRepartMens);
    pdf.table(
        [{ label: L.mois, width: 0.5 }, { label: L.sessions, width: 0.5, align: 'right' }],
        MONTHS.map((m, i) => [m, data.eventsData?.[i] ?? 0])
    );

    const typeEntries = Object.entries(data.eventsByType || {});
    if (typeEntries.length) {
        pdf.sectionHeader(L.shRepartType);
        pdf.table(
            [{ label: L.type, width: 0.75 }, { label: L.sessions, width: 0.25, align: 'right' }],
            typeEntries.map(([l, n]) => [l, n])
        );
    }

    if (data.formationsList?.length) {
        pdf.sectionHeader(L.shDetailSessions);
        pdf.table(
            [
                { label: L.date,     width: 0.12 },
                { label: L.intitule, width: 0.34 },
                { label: L.type,     width: 0.18 },
                { label: L.lieu,     width: 0.18 },
                { label: L.dureeH,   width: 0.09, align: 'right' },
                { label: L.stag,     width: 0.09, align: 'right' },
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
        btn.textContent = L.generation;
    }

    try {
        const doc = await PDFDocument.create();
        const [regular, bold] = await Promise.all([
            doc.embedFont(StandardFonts.Helvetica),
            doc.embedFont(StandardFonts.HelveticaBold),
        ]);

        const tabLabels = {
            generalites: L.tabGeneralites,
            activites:   L.tabActivites,
            formations:  L.tabFormations,
        };

        const lh = data.letterhead || {};
        const letterhead = await embedLetterhead(doc, lh);

        const pdf = new BilanPdf(doc, regular, bold, letterhead ? {
            letterhead,
            margeLeft: (lh.marge_left ?? 15) * MM,
            texteTop: (lh.texte_top ?? 40) * MM,
            texteBottom: (lh.texte_bottom ?? 25) * MM,
        } : {});
        buildCover(pdf, data.year, data.section?.name, tabLabels[data.tab]);

        if (data.tab === 'generalites') buildGeneralites(pdf, data);
        else if (data.tab === 'activites') buildActivites(pdf, data);
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
            btn.innerHTML = L.telechargerPdf;
        }
    }
}

window.__downloadBilanPdf = downloadBilanPdf;

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-download-pdf');
    if (btn) btn.addEventListener('click', downloadBilanPdf);
});
