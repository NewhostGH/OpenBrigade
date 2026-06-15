// Tree-shaken ECharts build. Registers only the chart types and components the
// statistics, report, and org-chart screens actually use, instead of pulling in
// the full ~1 MB library via `import * as echarts from 'echarts'`.
import * as echarts from 'echarts/core';
import { BarChart, PieChart, TreeChart } from 'echarts/charts';
import {
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent,
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
    BarChart,
    PieChart,
    TreeChart,
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent,
    CanvasRenderer,
]);

export default echarts;
