import echarts from './echarts.js';

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

function barSeries(data, color) {
    return { type: 'bar', data, barMaxWidth: 36, itemStyle: { color, borderRadius: [3, 3, 0, 0] } };
}

function donutSeries(labels, values) {
    return {
        type: 'pie',
        radius: ['40%', '64%'],
        center: ['50%', '44%'],
        data: labels.map((name, i) => ({ name, value: values[i] })),
        itemStyle: { borderRadius: 4, borderWidth: 1, borderColor: '#fff' },
        label: { show: false },
        emphasis: { label: { show: true, fontSize: 13, fontWeight: 600, fontFamily: FONT } },
    };
}

function initChart(id, height = 240) {
    const el = document.getElementById(id);
    if (!el) return null;
    el.style.height = height + 'px';
    const c = echarts.init(el, null, { renderer: 'canvas' });
    charts.push(c);
    return { el, c };
}

// ── Activités page ────────────────────────────────────────────────────────────

function renderBilanEvents() {
    const data = window.__BILAN_EVENTS_DATA__;
    if (!data) return;
    const r = initChart('bilan-chart-events');
    if (!r) return;
    r.c.setOption({
        grid: grid(),
        xAxis: xCat(),
        yAxis: yVal(),
        series: [barSeries(data, '#2B2350')],
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, textStyle: { fontFamily: FONT, fontSize: 12 } },
    });
}

function renderBilanParticipants() {
    const data = window.__BILAN_PARTICIPANTS_DATA__;
    if (!data) return;
    const r = initChart('bilan-chart-participants');
    if (!r) return;
    r.c.setOption({
        grid: grid(),
        xAxis: xCat(),
        yAxis: yVal(),
        series: [barSeries(data, '#16a34a')],
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, textStyle: { fontFamily: FONT, fontSize: 12 } },
    });
}

function renderBilanType() {
    const raw = window.__BILAN_TYPE_DATA__;
    if (!raw) return;
    const r = initChart('bilan-chart-type');
    if (!r) return;
    const labels = Object.keys(raw);
    const values = Object.values(raw).map(Number);
    if (!labels.length) return;
    r.c.setOption({
        color: PALETTE,
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)', textStyle: { fontFamily: FONT, fontSize: 12 } },
        legend: { bottom: 0, textStyle: { fontSize: 11, color: '#5b6575', fontFamily: FONT }, itemWidth: 12, itemHeight: 12 },
        series: [donutSeries(labels, values)],
    });
}

// ── Généralités page ──────────────────────────────────────────────────────────

function renderMembersGroup() {
    const labels = window.__BILAN_MEMBERS_LABELS__;
    const values = window.__BILAN_MEMBERS_GROUP__;
    if (!labels || !values || !labels.length) return;
    const r = initChart('bilan-chart-members-group');
    if (!r) return;
    r.c.setOption({
        color: PALETTE,
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)', textStyle: { fontFamily: FONT, fontSize: 12 } },
        legend: { bottom: 0, textStyle: { fontSize: 11, color: '#5b6575', fontFamily: FONT }, itemWidth: 12, itemHeight: 12 },
        series: [donutSeries(labels, values)],
    });
}

function renderMembersNew() {
    const labels = window.__BILAN_MEMBERS_YEARS__;
    const values = window.__BILAN_MEMBERS_NEW__;
    if (!labels || !values || !labels.length) return;
    const r = initChart('bilan-chart-new-members', 200);
    if (!r) return;
    r.c.setOption({
        grid: grid({ bottom: 24 }),
        xAxis: xCat(labels.map(String)),
        yAxis: yVal(),
        series: [barSeries(values, '#6868b9')],
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, textStyle: { fontFamily: FONT, fontSize: 12 } },
    });
}

function renderVehicles() {
    const labels = window.__BILAN_VEHICLES_LABELS__;
    const values = window.__BILAN_VEHICLES_VALUES__;
    if (!labels || !values || !labels.length) return;
    const r = initChart('bilan-chart-vehicles', 200);
    if (!r) return;
    r.c.setOption({
        color: PALETTE,
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)', textStyle: { fontFamily: FONT, fontSize: 12 } },
        legend: { bottom: 0, textStyle: { fontSize: 11, color: '#5b6575', fontFamily: FONT }, itemWidth: 12, itemHeight: 12 },
        series: [donutSeries(labels, values)],
    });
}

// ── Formations page ───────────────────────────────────────────────────────────

function renderFormEvents() {
    const data = window.__BILAN_FORM_EVENTS_DATA__;
    if (!data) return;
    const r = initChart('bilan-chart-form-events');
    if (!r) return;
    r.c.setOption({
        grid: grid(),
        xAxis: xCat(),
        yAxis: yVal(),
        series: [barSeries(data, '#0369a1')],
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, textStyle: { fontFamily: FONT, fontSize: 12 } },
    });
}

function renderFormType() {
    const raw = window.__BILAN_FORM_TYPE_DATA__;
    if (!raw) return;
    const r = initChart('bilan-chart-form-type');
    if (!r) return;
    const labels = Object.keys(raw);
    const values = Object.values(raw).map(Number);
    if (!labels.length) return;
    r.c.setOption({
        color: PALETTE,
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)', textStyle: { fontFamily: FONT, fontSize: 12 } },
        legend: { bottom: 0, textStyle: { fontSize: 11, color: '#5b6575', fontFamily: FONT }, itemWidth: 12, itemHeight: 12 },
        series: [donutSeries(labels, values)],
    });
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    // activites
    renderBilanEvents();
    renderBilanParticipants();
    renderBilanType();
    // generalites
    renderMembersGroup();
    renderMembersNew();
    renderVehicles();
    // formations
    renderFormEvents();
    renderFormType();

    window.addEventListener('resize', () => charts.forEach(c => c.resize()));
});
