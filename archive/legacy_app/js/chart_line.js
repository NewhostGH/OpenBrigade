var ctx = document.getElementById('myChart').getContext('2d');
ctx.canvas.width = 800;
ctx.canvas.height = 400;

var cfg = {
    data: {
        datasets: [{
            label: myChartLabel,
            backgroundColor: color(window.chartColors.purple).alpha(0.5).rgbString(),
            borderColor: window.chartColors.purple,
            data: generateData(),
            type: 'line',
            pointRadius: 0,
            fill: true,
            lineTension: 0,
            borderWidth: 2
        }]
    },
    options: {
        title: {
            display: true,
            text: myChartTitle
        },
        animation: {
            duration: 0
        },
        scales: {
            xAxes: [{
                type: 'time',
                distribution: 'series',
                offset: true,
                ticks: {
                    major: {
                        enabled: true,
                        fontStyle: 'bold'
                    },
                    source: 'data',
                    autoSkip: true,
                    autoSkipPadding: 75,
                    maxRotation: 0,
                    sampleSize: 100
                },
                afterBuildTicks: function(scale, ticks) {
                    var majorUnit = scale._majorUnit;
                    var firstTick = ticks[0];
                    var i, ilen, val, tick, currMajor, lastMajor;
                    val = moment(ticks[0].value);
                    firstTick.major = true;
                    lastMajor = val.get(majorUnit);

                    for (i = 1, ilen = ticks.length; i < ilen; i++) {
                        tick = ticks[i];
                        val = moment(tick.value);
                        currMajor = val.get(majorUnit);
                        tick.major = currMajor !== lastMajor;
                        lastMajor = currMajor;
                    }
                    return ticks;
                }
            }],
            yAxes: [{
                gridLines: {
                    drawBorder: false
                },
                scaleLabel: {
                    display: true,
                    labelString: 'Nombre ' + myChartLabel
                }
            }]
        },
        tooltips: {
            intersect: false,
            mode: 'index',
            callbacks: {
                label: function(tooltipItem, myData) {
                    var label = myData.datasets[tooltipItem.datasetIndex].label || '';
                    if (label) {
                        label += ': ';
                    }
                    label += parseFloat(tooltipItem.value).toFixed(0);
                    return label;
                }
            }
        }
    }
};

var chart = new Chart(ctx, cfg);

function change_chart_type() {
    var type = document.getElementById('typechart').value;
    var dataset = chart.config.data.datasets[0];
    dataset.type = type;
    dataset.data = generateData();
    chart.update();
};