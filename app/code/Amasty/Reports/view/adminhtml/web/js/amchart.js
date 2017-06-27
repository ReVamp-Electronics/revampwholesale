function getChart(categoryField) {
    // SERIAL CHART
    chart = new AmCharts.AmSerialChart();

    chart.dataProvider = window.chartData;
    chart.categoryField = categoryField;
    chart.titleField = categoryField;
    chart.balloon.bulletSize = 5;

    return chart;
}

function getPieChart(categoryField) {
    var chart = new AmCharts.AmPieChart();
    chart.valueField = "value";
    chart.titleField = "title";
    chart.dataProvider = window.chartData;
    chart.write("chartdiv");
}


function addCursorToGraph(chart) {
    // CURSOR
    var chartCursor = new AmCharts.ChartCursor();
    chart.addChartCursor(chartCursor);

    // SCROLLBAR
    var chartScrollbar = new AmCharts.ChartScrollbar();
    chart.addChartScrollbar(chartScrollbar);

    chart.creditsPosition = "bottom-right";
    
    return chart;
}

function addGraph(chart, type, valueField, title, fillAlphas) {
    // GRAPH
    var graph = new AmCharts.AmGraph();
    graph.title = title;
    graph.valueField = valueField;
    graph.fillAlphas = fillAlphas;
    graph.type = type;
    chart.addGraph(graph);
    return chart;
}

function addValueAxis(chart, title) {
    // value
    var valueAxis = new AmCharts.ValueAxis();
    valueAxis.title = title;
    valueAxis.axisAlpha = 0;
    valueAxis.dashLength = 1;
    valueAxis.axisAlpha = 0;
    valueAxis.minimum = 0;
    chart.addValueAxis(valueAxis);
    return chart;
}

function addCategoryAxis(chart, title, isDate) {
    // AXES
    // category
    var categoryAxis = chart.categoryAxis;
    categoryAxis.axisColor = "#DADADA";
    categoryAxis.title = title;
    if (isDate) {
        categoryAxis.parseDates = true;
    } else {
        categoryAxis.parseDates = false;
    }
    return chart;
}

// this method is called when chart is first inited as we listen for "dataUpdated" event
function zoomChart() {
    // different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues
    chart.zoomToIndexes(chartData.length - 40, chartData.length - 1);
}