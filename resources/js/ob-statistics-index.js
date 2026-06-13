import * as echarts from 'echarts';

const MONTH_LABELS = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
const PALETTE = ['#2B2350', '#6868b9', '#FA7070', '#16a34a', '#e67e22', '#0369a1', '#8950fc', '#1d6f42'];
const FONT = '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif';

const charts = [];

function grid(extra = {}) {
    return { top: 8, right: 8, bottom: 28, left: 8, containLabel: true, ...extra };
}

function xCat(data = MONTH_LABELS) {
    return { type: 'category', data, axisTick: { show: false }, axisLine: { lineStyle: { color: '#d3dae3' } }, axisLabel: { fontSize: 11, color: '#5b6575', fontFamily: FONT } };
}

function yVal() {
    return { type: 'value', minInterval: 1, axisLabel: { fontSize: 11, color: '#5b6575', fontFamily: FONT }, splitLine: { lineStyle: { color: '#eef1f5', type: 'dashed' } } };
}

function bar(data, color) {
    return { type: 'bar', data, barMaxWidth: 36, itemStyle: { color, borderRadius: [3, 3, 0, 0] } };
}

function init(id, height = 240) {
    const el = document.getElementById(id);
    if (!el) return null;
    el.style.height = height + 'px';
    const c = echarts.init(el, null, { renderer: 'canvas' });
    charts.push(c);
    return { el, c };
}

function renderEventsChart() {
    const r = init('chart-events-month');
    if (!r) return;
    const data = JSON.parse(r.el.dataset.values || '[]');
    r.c.setOption({
        grid: grid(),
        xAxis: xCat(),
        yAxis: yVal(),
        series: [bar(data, '#2B2350')],
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, textStyle: { fontFamily: FONT, fontSize: 12 } },
    });
}

function renderParticipantsChart() {
    const r = init('chart-participants-month');
    if (!r) return;
    const data = JSON.parse(r.el.dataset.values || '[]');
    r.c.setOption({
        grid: grid(),
        xAxis: xCat(),
        yAxis: yVal(),
        series: [bar(data, '#16a34a')],
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, textStyle: { fontFamily: FONT, fontSize: 12 } },
    });
}

function renderTypeChart() {
    const r = init('chart-events-type');
    if (!r) return;
    const raw = JSON.parse(r.el.dataset.values || '{}');
    const labels = Object.keys(raw);
    const values = Object.values(raw).map(Number);
    if (!labels.length) { r.el.closest('.ob-widget-card')?.remove(); return; }
    r.c.setOption({
        color: PALETTE,
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)', textStyle: { fontFamily: FONT, fontSize: 12 } },
        legend: { bottom: 0, textStyle: { fontSize: 11, color: '#5b6575', fontFamily: FONT }, itemWidth: 12, itemHeight: 12 },
        series: [{
            type: 'pie',
            radius: ['40%', '64%'],
            center: ['50%', '44%'],
            data: labels.map((name, i) => ({ name, value: values[i] })),
            itemStyle: { borderRadius: 4, borderWidth: 1, borderColor: '#fff' },
            label: { show: false },
            emphasis: { label: { show: true, fontSize: 13, fontWeight: 600, fontFamily: FONT } },
        }],
    });
}

function renderNewMembersChart() {
    const r = init('chart-new-members', 180);
    if (!r) return;
    const raw = JSON.parse(r.el.dataset.values || '{}');
    const years = Object.keys(raw);
    const values = Object.values(raw).map(Number);
    if (!years.length) return;
    r.c.setOption({
        grid: grid({ bottom: 24 }),
        xAxis: xCat(years),
        yAxis: yVal(),
        series: [bar(values, '#6868b9')],
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, textStyle: { fontFamily: FONT, fontSize: 12 } },
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderEventsChart();
    renderParticipantsChart();
    renderTypeChart();
    renderNewMembersChart();
    window.addEventListener('resize', () => charts.forEach(c => c.resize()));
});
