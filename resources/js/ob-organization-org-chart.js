import echarts from './echarts.js';

const FONT = '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif';
const MONO = 'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace';

// One accent colour per top-level branch (each site + the global-roles branch).
// The colour is inherited by the whole subtree so a branch reads as one unit.
const PALETTE = ['#4f7cff', '#12a5a0', '#8b5cf6', '#f59e0b', '#ef4444', '#10b981', '#0ea5e9', '#ec4899'];

/** hex → rgba() with the given alpha, for light branch-tinted fills. */
function tint(hex, alpha) {
    const h = hex.replace('#', '');
    const r = parseInt(h.slice(0, 2), 16);
    const g = parseInt(h.slice(2, 4), 16);
    const b = parseInt(h.slice(4, 6), 16);
    return `rgba(${r},${g},${b},${alpha})`;
}

function pluralMembers(n) {
    return `${n} membre${n !== 1 ? 's' : ''}`;
}

function memberNode(m) {
    return { kind: 'member', name: m.name, code: m.code, id: m.id };
}

function roleNode(r) {
    return {
        kind: 'role',
        name: r.name,
        count: r.count,
        collapsed: true,
        children: (r.children || []).map(memberNode),
    };
}

function rolesGroupNode(name, roles) {
    const count = roles.reduce((sum, r) => sum + (r.count || 0), 0);
    return {
        kind: 'rolesGroup',
        name,
        count,
        collapsed: true,
        children: roles.map(roleNode),
    };
}

function membersGroupNode(members) {
    return {
        kind: 'membersGroup',
        name: 'Membres',
        count: members.length,
        collapsed: true,
        children: members.map(memberNode),
    };
}

function convertSection(node, focusId) {
    const children = [];
    if (node.roles && node.roles.length) {
        children.push(rolesGroupNode('Rôles', node.roles));
    }
    if (node.members && node.members.length) {
        children.push(membersGroupNode(node.members));
    }
    (node.children || []).forEach(c => children.push(convertSection(c, focusId)));

    return {
        kind: 'section',
        name: node.section.S_CODE || '',
        id: node.section.S_ID,
        code: node.section.S_CODE || '',
        description: node.section.S_DESCRIPTION || '',
        count: node.count,
        _focus: node.section.S_ID === focusId,
        children,
    };
}

function buildRoot(phpTree, globalRoles, focusId) {
    const children = [];
    if (globalRoles && globalRoles.length) {
        children.push(rolesGroupNode('Rôles globaux', globalRoles));
    }
    (phpTree || []).forEach(n => children.push(convertSection(n, focusId)));

    if (children.length === 0) return null;

    const root = {
        kind: 'root',
        name: 'Organisation',
        children,
    };

    // Assign a branch colour to each top-level child and inherit it downward,
    // tracking depth so top-level sites can be emphasised.
    root._depth = 0;
    root.children.forEach((child, i) => paint(child, PALETTE[i % PALETTE.length], 1));
    walk(root, styleNode);

    return root;
}

function paint(node, color, depth) {
    node._branch = color;
    node._depth = depth;
    (node.children || []).forEach(c => paint(c, color, depth + 1));
}

function walk(node, fn) {
    fn(node);
    (node.children || []).forEach(c => walk(c, fn));
}

/**
 * Set the per-node shape (symbol), size, itemStyle and — for members — the
 * profile-photo avatar. Each item type gets a distinct shape:
 *   • section  → rounded card    • role / group → sharp-cornered card
 *   • member   → profile-photo avatar (label beneath)    • root → pill
 */
function styleNode(d) {
    const branch = d._branch || '#6868b9';
    switch (d.kind) {
        case 'root':
            d.symbol = 'roundRect';
            d.symbolSize = [184, 54];
            d.itemStyle = {
                color: '#2B2350', borderColor: '#2B2350', borderWidth: 0,
                shadowBlur: 10, shadowColor: 'rgba(43,35,80,0.28)', shadowOffsetY: 2,
            };
            break;
        case 'section': {
            const topLevel = d._depth <= 1;
            d.symbol = 'roundRect';
            d.symbolSize = [168, 66];
            d.itemStyle = d._focus
                ? {
                    color: tint(branch, 0.14), borderColor: branch, borderWidth: 3,
                    shadowBlur: 14, shadowColor: tint(branch, 0.35), shadowOffsetY: 2,
                }
                : {
                    color: '#ffffff', borderColor: branch, borderWidth: topLevel ? 2.5 : 1.5,
                    shadowBlur: 5, shadowColor: 'rgba(0,0,0,0.08)', shadowOffsetY: 1,
                };
            break;
        }
        case 'rolesGroup':
        case 'membersGroup':
            d.symbol = 'rect';
            d.symbolSize = [152, 48];
            d.itemStyle = { color: tint(branch, 0.1), borderColor: branch, borderWidth: 1.5 };
            break;
        case 'role':
            d.symbol = 'rect';
            d.symbolSize = [150, 46];
            d.itemStyle = { color: '#f3f0ff', borderColor: '#8b7fd6', borderWidth: 1.25 };
            break;
        case 'member':
            // Avatar: the profile-photo endpoint always returns an image (real
            // photo or a default-avatar SVG), same-origin so the PNG export
            // stays untainted. Name/code sit under the avatar.
            d.symbol = `image://${window.location.origin}/personnel/${d.id}/photo`;
            d.symbolSize = [52, 52];
            d.itemStyle = { borderColor: '#d3dae3', borderWidth: 1 };
            d.label = { position: 'bottom', distance: 7, formatter: labelFormatter };
            break;
    }
}

function labelFormatter(params) {
    const d = params.data;
    switch (d.kind) {
        case 'root':
            return `{root|${d.name}}`;
        case 'rolesGroup':
        case 'membersGroup':
        case 'role':
            return `{grp|${d.name}}\n{gcnt|${pluralMembers(d.count)}}`;
        case 'member': {
            const lines = [`{name|${d.name}}`];
            if (d.code) lines.push(`{mcode|${d.code}}`);
            return lines.join('\n');
        }
        default: {
            const lines = [`{code|${d.code}}`];
            if (d.description && d.description !== d.code) lines.push(`{desc|${d.description}}`);
            if (d.count !== null && d.count !== undefined) {
                lines.push(`{cnt2|${pluralMembers(d.count)}}`);
            }
            return lines.join('\n');
        }
    }
}

function tooltipFormatter(params) {
    const d = params.data;
    switch (d.kind) {
        case 'root':
            return `<strong>${d.name}</strong>`;
        case 'rolesGroup':
        case 'membersGroup':
        case 'role':
            return `<strong>${d.name}</strong><br><span style="color:#9ca3af">${pluralMembers(d.count)}</span>`;
        case 'member': {
            let html = `<strong style="font-family:${MONO}">${d.name}</strong>`;
            if (d.code) html += `<br><span style="color:#9ca3af">${d.code}</span>`;
            html += '<br><span style="color:#9ca3af;font-size:11px">Clic pour ouvrir la fiche</span>';
            return html;
        }
        default: {
            let html = `<strong style="font-family:${MONO}">${d.code}</strong>`;
            if (d.description) html += `<br><span style="color:#5b6575">${d.description}</span>`;
            if (d.count !== null) html += `<br><span style="color:#9ca3af">${pluralMembers(d.count)}</span>`;
            html += '<br><span style="color:#9ca3af;font-size:11px">Double-clic pour ouvrir la section</span>';
            return html;
        }
    }
}

/** Recursively set the `collapsed` flag on every node that has children. */
function setCollapsedRecursive(node, collapsed) {
    if (!node.children || !node.children.length) return;
    node.collapsed = collapsed;
    node.children.forEach(c => setCollapsedRecursive(c, collapsed));
}

/** Leaves of the currently visible (non-collapsed) tree — drives the width. */
function visibleLeaves(node) {
    if (node.collapsed || !node.children || !node.children.length) return 1;
    return node.children.reduce((sum, c) => sum + visibleLeaves(c), 0);
}

/** Depth of the currently visible tree — drives the height. */
function visibleDepth(node) {
    if (node.collapsed || !node.children || !node.children.length) return 1;
    return 1 + Math.max(...node.children.map(visibleDepth));
}

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('ob-org-tree');
    if (!container) return;

    const phpTree = window.__OB_ORG_TREE__ || [];
    const globalRoles = window.__OB_GLOBAL_ROLES__ || [];
    const focusId = window.__OB_FOCUS_SECTION__ || 0;
    const root = buildRoot(phpTree, globalRoles, focusId);

    if (!root) {
        container.innerHTML = '<p style="text-align:center;color:#9ca3af;padding:48px 24px;">Aucune section configurée.</p>';
        return;
    }

    const LEAF_W = 190;  // widest card + horizontal gap
    const LEVEL_H = 125; // row height incl. vertical gap

    /**
     * Automatic spacing: the ECharts tree layout squeezes all nodes into the
     * canvas area, so siblings overlap once the visible tree outgrows it.
     * Instead, the canvas grows with the visible tree (leaves × LEAF_W wide,
     * depth × LEVEL_H tall) and the wrapper scrolls when it exceeds the
     * viewport (centred horizontally on resize).
     */
    function sizeChart() {
        const wrap = container.parentElement; // .ob-org-chart-wrap
        const availW = wrap.clientWidth;
        const top = wrap.getBoundingClientRect().top;
        const availH = Math.max(420, window.innerHeight - top - 24);
        const w = Math.max(availW, visibleLeaves(root) * LEAF_W);
        const h = Math.max(availH, visibleDepth(root) * LEVEL_H + 90);
        container.style.width = w + 'px';
        container.style.height = h + 'px';
        chart.resize();
        wrap.scrollLeft = Math.max(0, (w - availW) / 2);
    }

    const chart = echarts.init(container, null, { renderer: 'canvas' });
    // ECharts hands event handlers a CLONE of the data node, so mutating
    // params.data does nothing. Key every node so the click handler can find
    // and toggle the real node in `root` (the clone keeps plain properties).
    const nodeByKey = new Map();
    let nextKey = 0;
    walk(root, n => { n._key = ++nextKey; nodeByKey.set(n._key, n); });

    function render() {
        chart.setOption({
            // Animation is disabled on purpose: the tree symbol enter-animation
            // leaves image symbols (member avatars) stuck at scale 0 — they
            // never grow to full size and their photo is never requested. This
            // also affects native node-click expansion, so it must be global.
            animation: false,
            tooltip: {
                trigger: 'item',
                triggerOn: 'mousemove',
                backgroundColor: '#ffffff',
                borderColor: '#d3dae3',
                borderWidth: 1,
                padding: [8, 12],
                textStyle: { fontFamily: FONT, fontSize: 12, color: '#1f2937' },
                formatter: tooltipFormatter,
            },
            series: [{
                type: 'tree',
                layout: 'orthogonal',
                orient: 'TB',
                edgeShape: 'polyline',
                edgeForkPosition: '55%',
                roam: true,
                initialTreeDepth: -1,
                // Collapse/expand is handled by our own click handler (below):
                // the native toggle leaves stale edge lines behind when
                // animation is off, so every toggle goes through renderFresh().
                expandAndCollapse: false,
                symbol: 'roundRect',
                symbolSize: [168, 66],
                nodePadding: 24,
                top: '36px',
                bottom: '36px',
                left: '7%',
                right: '7%',
                itemStyle: {
                    color: '#ffffff',
                    borderColor: '#d3dae3',
                    borderWidth: 1,
                },
                emphasis: {
                    focus: 'descendant',
                    itemStyle: { shadowBlur: 12, shadowColor: 'rgba(104,104,185,0.22)' },
                    lineStyle: { color: '#6868b9', width: 2 },
                },
                lineStyle: { color: '#c9d2e0', width: 1.5, curveness: 0 },
                label: {
                    show: true,
                    position: 'inside',
                    fontFamily: FONT,
                    formatter: labelFormatter,
                    rich: {
                        root: { fontSize: 13, fontWeight: 700, color: '#ffffff', fontFamily: MONO, lineHeight: 24, align: 'center' },
                        code: { fontSize: 12, fontWeight: 700, color: '#1f2937', fontFamily: MONO, lineHeight: 20 },
                        desc: { fontSize: 10, color: '#5b6575', fontFamily: FONT, lineHeight: 14 },
                        cnt2: { fontSize: 9, color: '#9ca3af', fontFamily: FONT, lineHeight: 15 },
                        grp: { fontSize: 11, fontWeight: 700, color: '#1f2937', fontFamily: FONT, lineHeight: 18 },
                        gcnt: { fontSize: 9, color: '#6868b9', fontFamily: FONT, lineHeight: 14 },
                        name: { fontSize: 11, fontWeight: 600, color: '#1f2937', fontFamily: MONO, lineHeight: 16 },
                        mcode: { fontSize: 9, color: '#9ca3af', fontFamily: FONT, lineHeight: 14 },
                    },
                },
                leaves: { label: { show: true, position: 'inside' } },
                data: [root],
            }],
        });
    }

    /**
     * Full rebuild after any expand/collapse change. A merge (or even a
     * notMerge) setOption leaves the removed subtree's edge lines behind when
     * animation is off, so the chart is cleared and rebuilt from the data —
     * the `collapsed` flags on the nodes are the single source of truth.
     */
    function renderFresh() {
        chart.clear();
        sizeChart();
        render();
    }

    sizeChart();
    render();

    let lastClickTime = 0;
    chart.on('click', params => {
        const d = params.data;
        if (!d) return;
        if (d.kind === 'member') {
            window.location.href = `/personnel/${d.id}`;
            return;
        }
        if (d.kind === 'section') {
            const now = Date.now();
            if (now - lastClickTime < 350) {
                window.location.href = `/organization/sections/${d.id}`;
                return;
            }
            lastClickTime = now;
        }
        // Toggle collapse on any node that has children (replaces the native
        // expandAndCollapse behaviour — see the series comment). params.data
        // is a clone, so resolve the real node through its key.
        if (d.children && d.children.length) {
            const node = nodeByKey.get(d._key);
            if (!node) return;
            node.collapsed = !node.collapsed;
            renderFresh();
        }
    });

    document.getElementById('obOrgExpandAll')?.addEventListener('click', () => {
        setCollapsedRecursive(root, false);
        renderFresh();
    });

    document.getElementById('obOrgCollapseAll')?.addEventListener('click', () => {
        setCollapsedRecursive(root, true);
        root.collapsed = false; // keep the top level itself visible
        renderFresh();
    });

    document.getElementById('obOrgExportImage')?.addEventListener('click', () => {
        const url = chart.getDataURL({ type: 'png', pixelRatio: 2, backgroundColor: '#ffffff' });
        const a = document.createElement('a');
        a.href = url;
        a.download = 'organigramme.png';
        a.click();
    });

    window.addEventListener('resize', sizeChart);
});
