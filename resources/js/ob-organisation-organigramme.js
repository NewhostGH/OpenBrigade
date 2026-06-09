import * as echarts from 'echarts';

const FONT = '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif';
const MONO = 'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace';

function convertNode(node, currentId) {
    const isCurrent = node.section.S_ID === currentId;
    return {
        name: node.section.S_CODE || '',
        id: node.section.S_ID,
        code: node.section.S_CODE || '',
        description: node.section.S_DESCRIPTION || '',
        count: node.count,
        itemStyle: isCurrent ? { color: '#eef4ff', borderColor: '#6868b9', borderWidth: 2 } : undefined,
        children: (node.children || []).map(c => convertNode(c, currentId)),
    };
}

function buildRoot(phpTree, currentId) {
    if (!phpTree || phpTree.length === 0) return null;
    if (phpTree.length === 1) return convertNode(phpTree[0], currentId);
    return {
        name: 'Organisation',
        id: null,
        code: null,
        description: 'Organisation',
        count: null,
        itemStyle: { color: '#2B2350', borderColor: '#2B2350', borderWidth: 0 },
        children: phpTree.map(n => convertNode(n, currentId)),
    };
}

function labelFormatter(params) {
    const d = params.data;
    // Virtual root — white text on dark background
    if (!d.id) {
        return `{root|${d.name}}`;
    }
    const lines = [`{code|${d.code}}`];
    if (d.description) lines.push(`{desc|${d.description}}`);
    if (d.count !== null && d.count !== undefined) {
        lines.push(`{cnt|${d.count} membre${d.count !== 1 ? 's' : ''}}`);
    }
    return lines.join('\n');
}

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('ob-org-tree');
    if (!container) return;

    const phpTree = window.__OB_ORG_TREE__ || [];
    const currentId = window.__OB_CURRENT_SECTION__ || 0;
    const root = buildRoot(phpTree, currentId);

    if (!root) {
        container.innerHTML = '<p style="text-align:center;color:#9ca3af;padding:48px 24px;">Aucune section configurée.</p>';
        return;
    }

    container.style.height = '520px';
    const chart = echarts.init(container, null, { renderer: 'canvas' });

    chart.setOption({
        tooltip: {
            trigger: 'item',
            triggerOn: 'mousemove',
            backgroundColor: '#ffffff',
            borderColor: '#d3dae3',
            borderWidth: 1,
            padding: [8, 12],
            textStyle: { fontFamily: FONT, fontSize: 12, color: '#1f2937' },
            formatter(params) {
                const d = params.data;
                if (!d.id) return `<strong>${d.name}</strong>`;
                let html = `<strong style="font-family:${MONO}">${d.code}</strong>`;
                if (d.description) html += `<br><span style="color:#5b6575">${d.description}</span>`;
                if (d.count !== null) html += `<br><span style="color:#9ca3af">${d.count} membre${d.count !== 1 ? 's' : ''}</span>`;
                return html;
            },
        },
        series: [{
            type: 'tree',
            layout: 'orthogonal',
            orient: 'TB',
            edgeShape: 'polyline',
            roam: true,
            initialTreeDepth: -1,
            expandAndCollapse: false,
            symbol: 'rect',
            symbolSize: [152, 72],
            top: '32px',
            bottom: '32px',
            left: '60px',
            right: '60px',
            itemStyle: {
                color: '#ffffff',
                borderColor: '#d3dae3',
                borderWidth: 1,
                shadowBlur: 4,
                shadowColor: 'rgba(0,0,0,0.07)',
                shadowOffsetY: 1,
            },
            emphasis: {
                focus: 'ancestor',
                itemStyle: { borderColor: '#6868b9', shadowBlur: 10, shadowColor: 'rgba(104,104,185,0.22)' },
                lineStyle: { color: '#6868b9' },
            },
            lineStyle: { color: '#d3dae3', width: 1.5, curveness: 0 },
            label: {
                show: true,
                position: 'inside',
                fontFamily: FONT,
                formatter: labelFormatter,
                rich: {
                    root: { fontSize: 13, fontWeight: 700, color: '#ffffff', fontFamily: MONO, lineHeight: 22 },
                    code: { fontSize: 11, fontWeight: 700, color: '#1f2937', fontFamily: MONO, lineHeight: 20 },
                    desc: { fontSize: 10, color: '#5b6575', fontFamily: FONT, lineHeight: 14 },
                    cnt:  { fontSize: 9,  color: '#9ca3af', fontFamily: FONT, lineHeight: 14 },
                },
            },
            leaves: { label: { show: true, position: 'inside' } },
            data: [root],
            animationDuration: 300,
            animationDurationUpdate: 400,
        }],
    });

    chart.on('click', params => {
        if (params.data?.id) {
            window.location.href = `/organisation/sections/${params.data.id}`;
        }
    });

    window.addEventListener('resize', () => chart.resize());
});
