#!/usr/bin/env node
/**
 * Blade i18n lint — fails when a Blade template contains a hardcoded
 * human-readable string instead of routing it through Laravel localization
 * (`__('...')`, `@lang('...')` or `trans('...')`).
 *
 * It flags two things:
 *   1. Text nodes between tags that contain real words (2+ letters), once
 *      Blade echoes/directives and <script>/<style> blocks are removed.
 *   2. User-facing attributes (title, placeholder, alt, aria-label) whose
 *      value is a literal rather than a translation call.
 *
 * Escape hatches:
 *   - Put `i18n-ignore` anywhere on a line (e.g. in a `{{-- i18n-ignore --}}`
 *     comment) to skip that line.
 *   - Add a literal string (one per line) to .husky/i18n-lint-allow.txt to
 *     whitelist it everywhere. Lines starting with `#` are comments.
 *
 * Usage:  npm run lint-i18n   (node .husky/lint-blade-i18n.mjs)
 * Exit:   0 = clean, 1 = hardcoded strings found.
**/

import { readFileSync, existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join, relative } from 'node:path';
import { globSync } from 'node:fs';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const VIEWS_GLOB = 'resources/views/**/*.blade.php';

const TARGET_ATTRS = [
    'title',
    'placeholder',
    'alt',
    'aria-label',
];

const ATTR_RE = new RegExp(
    `\\b(${TARGET_ATTRS.join('|')})\\s*=\\s*("([^"]*)"|'([^']*)')`,
    'gi'
);

const WORD_RE = /\p{L}{2,}/u;

function blank(src, re) {
    return src.replace(re, (m) => m.replace(/[^\n]/g, ' '));
}

// Blank Blade directives `@name(...)`, consuming a balanced parenthesis group
// (quote-aware) so PHP expressions with nested parens or `=>` arrows don't leak
// into the text-node scan. Directives with no parens (e.g. @csrf) are blanked too.
function blankDirectives(src) {
    const out = src.split('');
    const re = /@\w+/g;
    let m;
    while ((m = re.exec(src)) !== null) {
        let i = m.index + m[0].length;
        // Optional whitespace then an opening paren → consume balanced group.
        let j = i;
        while (j < src.length && (src[j] === ' ' || src[j] === '\t')) j++;
        let end = i;
        if (src[j] === '(') {
            let depth = 0, quote = null;
            let k = j;
            for (; k < src.length; k++) {
                const c = src[k];
                if (quote) {
                    if (c === '\\') { k++; continue; }
                    if (c === quote) quote = null;
                } else if (c === '"' || c === "'") {
                    quote = c;
                } else if (c === '(') {
                    depth++;
                } else if (c === ')') {
                    depth--;
                    if (depth === 0) { k++; break; }
                }
            }
            end = k;
        }
        for (let p = m.index; p < end; p++) {
            if (out[p] !== '\n') out[p] = ' ';
        }
        re.lastIndex = end;
    }
    return out.join('');
}

function loadAllowlist() {
    const file = join(ROOT, '.husky', 'i18n-lint-allow.txt');

    if (!existsSync(file)) {
        return new Set();
    }

    return new Set(
        readFileSync(file, 'utf8')
            .split(/\r?\n/)
            .map((line) => line.trim())
            .filter((line) => line && !line.startsWith('#'))
    );
}

const ALLOW = loadAllowlist();

function isCopy(text) {
    const cleaned = text
        .replace(/&[a-zA-Z]+;|&#\d+;/g, ' ')
        .trim();

    if (!cleaned) {
        return false;
    }

    if (!WORD_RE.test(cleaned)) {
        return false;
    }

    return !ALLOW.has(cleaned);
}

function lineAt(src, index) {
    let line = 1;
    for (let i = 0; i < index && i < src.length; i++) {
        if (src[i] === '\n') {
            line++;
        }
    }

    return line;
}

function githubError(file, line, kind, text) {
    const escaped = text
        .replace(/\r/g, ' ')
        .replace(/\n/g, ' ')
        .replace(/::/g, ':');
    const BOLD = '\x1b[1m';
    const RED = '\x1b[31m';
    const RESET = '\x1b[0m';

    console.error(
        `::error ${file}:${line}::${BOLD}${RED}[${kind}]${RESET} ${escaped}`
    );
}

function lintFile(absPath) {
    const src = readFileSync(absPath, 'utf8');
    const findings = [];

    const ignoredLines = new Set();

    src.split('\n').forEach((line, index) => {
        if (line.includes('i18n-ignore')) {
            ignoredLines.add(index + 1);
        }
    });

    let s = blank(src, /\{\{--[\s\S]*?--\}\}/g);
    s = blank(s, /<script\b[\s\S]*?<\/script>/gi);
    s = blank(s, /<style\b[\s\S]*?<\/style>/gi);
    // Raw PHP blocks are code, not UI copy.
    s = blank(s, /@php\b[\s\S]*?@endphp/gi);
    s = blank(s, /@verbatim\b[\s\S]*?@endverbatim/gi);

    // -------------------------
    // Attribute pass
    // -------------------------

    for (const match of s.matchAll(ATTR_RE)) {
        const raw =
            match[3] !== undefined
                ? match[3]
                : match[4];

        if (!raw) {
            continue;
        }

        if (
            raw.includes('{{') ||
            raw.includes('__(') ||
            raw.includes('@lang') ||
            raw.includes('trans(') ||
            raw.includes('$')
        ) {
            continue;
        }

        if (!isCopy(raw)) {
            continue;
        }

        const line = lineAt(s, match.index);

        if (ignoredLines.has(line)) {
            continue;
        }

        findings.push({
            line,
            kind: match[1].toLowerCase(),
            text: raw.trim(),
        });
    }

    // -------------------------
    // Text-node pass
    // -------------------------

    let t = s;

    // Remove Blade echoes
    t = blank(t, /\{\{[\s\S]*?\}\}/g);
    t = blank(t, /\{!![\s\S]*?!!\}/g);

    // Remove Blade directives (balanced, quote-aware paren handling)
    t = blankDirectives(t);

    // Remove Blade components entirely. Non-greedy so `=>` arrows inside
    // array-valued attributes (which contain `>`) don't cut the match short.
    t = blank(t, /<x-[\w.:-]+[\s\S]*?\/>/gi);
    t = blank(t, /<x-[\w.:-]+[\s\S]*?>[\s\S]*?<\/x-[\w.:-]+>/gi);

    // Only inspect clean text nodes
    const TEXT_RE = />([^<{]+)</g;

    for (const match of t.matchAll(TEXT_RE)) {
        const text = match[1];

        if (!isCopy(text)) {
            continue;
        }

        const line = lineAt(t, match.index + 1);

        if (ignoredLines.has(line)) {
            continue;
        }

        findings.push({
            line,
            kind: 'text',
            text: text.trim().replace(/\s+/g, ' '),
        });
    }

    findings.sort((a, b) => a.line - b.line);

    return findings;

}

const files = globSync(VIEWS_GLOB, {
    cwd: ROOT,
}).map((file) => join(ROOT, file));

let total = 0;
const report = [];

for (const file of files.sort()) {
    const findings = lintFile(file);

    if (findings.length === 0) {
        continue;
    }

    total += findings.length;

    report.push({
        file: relative(ROOT, file).replace(/\\/g, '/'),
        findings,
    });

}

for (const { file, findings } of report) {
    for (const finding of findings) {
        githubError(
            file,
            finding.line,
            finding.kind,
            finding.text
        );
    }
}

const RED = '\x1b[31m';
const GREEN = '\x1b[32m';
const BOLD = '\x1b[1m';
const RESET = '\x1b[0m';

if (total === 0) {
    console.log(
        `${GREEN}${BOLD}✓${RESET} blade-i18n: ${files.length} templates checked`
    );
    process.exit(0);
}

console.error(
    `${RED}${BOLD}✗${RESET} blade-i18n: ${total} hardcoded string(s) found in ${report.length} file(s)`
);
process.exit(1);
