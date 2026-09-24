# Brand identity

How OpenBrigade looks and sounds: words, symbols, colors, icons, images. Code structure is in
[conventions.md](conventions.md). **Consistency above all**: when a case is not covered, copy
the most common existing pattern, then add the rule here.

## 1. Tone

Professional but friendly: clear, calm, respectful. Never cold, never casual.

- Always **vous**, never "tu".
- Short, active sentences. Say what happened, then what to do.
- Direct imperatives ("Choisissez…") over repeated "Veuillez…".
- No blame, no exclamation marks, no capitals for emphasis, no "avec succès".

## 2. French UI copy

All UI text lives in `lang/fr/*.php` (Blade hardcoding is blocked by the i18n lint).

- **Sentence case** everywhere: `Journal d'activité`. Acronyms and names keep their case.
- **Patterns**: success `Événement enregistré.` · error `Le fichier est trop volumineux. La taille maximale est de 10 Mo.` · confirm `Voulez-vous vraiment supprimer ce véhicule ? Cette action est irréversible.` · empty `Aucun événement pour cette période.` · loading `Chargement…`
- **Vocabulary**: Supprimer (not Effacer), Enregistrer (not Sauvegarder), Modifier (not Éditer), Ajouter, Rechercher, Exporter, Annuler, Personnel, Section.
- **Typography**: space before `: ; ? !` (`&nbsp;` in HTML); guillemets `« :name »`; ellipsis `…` (never `...`); accented capitals (`État`).
- **Formats**: date `d/m/Y`, time `H:i`, range `08:00 - 12:00`, decimals `12,5`, money `Money::format()` → `12,50 €`, empty value `__('common.empty_value')` → `-`. Storage stays ISO.

## 3. English (code, docs, commits)

- US spelling (`organization`, `color`), matching identifiers.
- Docs: "you", sentence-case headings, short paragraphs, tables for reference data.
- Comments explain *why* in one short sentence.
- Doc files are lowercase kebab-case (`brand-identity.md`), except conventional names
  (`README`, `CHANGELOG`, `AGENTS`, `CLAUDE`, `CONTRIBUTING`, `TODO`, `IDEAS`).
- Product name: **OpenBrigade**. Legacy: **eBrigade**.

## 4. Symbols

- **Em dash** `—`: use sparingly.
- **En dash** (U+2013): never. Use `-`.
- **Hyphen** `-`: joins values (`CODE - Libellé`), ranges (`1-5`), empty value.
- **Pipe** `|`: page titles (`Événements | OpenBrigade`).
- **Arrow** `→`: docs only, for paths and mappings.
- **Guillemets** `« »`: quoting a name in French copy.
- **Emoji**: never.

## 5. Colors

- Every color is a token in `resources/css/variables.css`, named for its role. No raw hex in
  module CSS or Blade (legacy hex values: move them when you touch the file).
- Bootstrap classes carry meaning: `primary` main action (one per view), `secondary` neutral,
  `success` done/valid, `danger` destructive/error, `warning` attention, `info` neutral info.
- Brand: navy `--brand-bg` for brand surfaces, coral `--accent` for active states only (never
  text on white, never danger).
- Color never carries meaning alone; text meets WCAG AA contrast.

## 6. Icons

Font Awesome 6 Free, `fas` only. One icon per action: add `fa-plus`, edit `fa-pen` (not
`fa-edit`), delete `fa-trash`, save `fa-save`, cancel `fa-times`, confirm `fa-check`, view
`fa-eye`, back `fa-arrow-left`, search `fa-search`, refresh `fa-sync`, Excel `fa-file-excel`,
download `fa-download`, print `fa-print`.

- Icon before text, `me-1`, `aria-hidden="true"`.
- Icon-only buttons get `title` and `aria-label` (same French text).

## 7. Images

- Static images in `public/images/`. SVG for logos, icons, insignia; PNG/WebP for photos.
- Meaningful images get a short French `alt`; decorative ones `alt=""`. Never omit `alt`.
- Avatars via `HasAvatar::getAvatarUrl()`. No hotlinking; external URLs go through `config/`.
- Nothing above 500 KB in the repo.

## 8. Enforcement

- `ConventionsTest`: no en dash.
- `.husky/lint-blade-i18n.mjs`: no hardcoded Blade text.
- The rest: review, and AI agents via [AGENTS.md](../../AGENTS.md).
