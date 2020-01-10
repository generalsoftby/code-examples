import React, {Component} from 'react';
import './ClusteredBarChart.scss';
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";

am4core.useTheme(am4themes_animated);

class ClusteredBarChart extends Component {

    componentDidMount() {
        this.updateChart();
    }

    componentDidUpdate(oldProps) {
        if (oldProps.chartData !== this.props.chartData) {
            this.updateChart();
        }
    }

    componentWillUnmount() {
        if (this.chart) {
            this.chart.dispose();
        }
    }

    updateClickColumn = (data) => {
        this.props.needColumnClick(data);
    }

    updateChart = () => {
        if (this.chart) {
            this.chart.dispose();
        }

        let chart = am4core.create(this.props.chartId, am4charts.XYChart);


        if(this.props.chartData.length > 0) {

              chart.exporting.menu = new am4core.ExportMenu();
            chart.data = this.props.chartData;

            var categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "typeY";
            categoryAxis.numberFormatter.numberFormat = "#";
            categoryAxis.renderer.inversed = true;
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.cellStartLocation = 0.1;
            categoryAxis.renderer.cellEndLocation = 0.9;
            categoryAxis.renderer.grid.template.stroke = am4core.color("#FFF");

            var  valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
            valueAxis.renderer.opposite = true;
            valueAxis.renderer.grid.template.stroke = am4core.color("#FFF");
            valueAxis.renderer.grid.template.strokeOpacity = 0;
            valueAxis.renderer.baseGrid.disabled = true;
            valueAxis.renderer.labels.template.disabled = true;

            var ch =  chart;
            var f = this.updateClickColumn.bind(this);
            function clickColumn(ev) {
                var col = ev.target;
                f(col.dataItem.index);
            }


            // Create series
            function createSeries(field, name) {
                var series =  chart.series.push(new am4charts.ColumnSeries());
                series.dataFields.valueX = field;
                series.dataFields.categoryY = "typeY";
                series.name = name;
                //series.columns.template.tooltipText = "[bold]{valueX}[/]";
                series.columns.template.height = am4core.percent(20);
                series.sequencedInterpolation = true;



                var columnTemplate = series.columns.template;
                columnTemplate.column.cornerRadiusTopRight = 30;
                columnTemplate.column.cornerRadiusBottomRight = 30;
                columnTemplate.column.cornerRadiusTopLeft = 30;
                columnTemplate.column.cornerRadiusBottomLeft = 30;
                columnTemplate.strokeWidth = 0;

                series.columns.template.events.on("hit", clickColumn, this);
                columnTemplate.adapter.add("fill", function (fill, target) {
                    switch (target.dataItem.index) {
                        case 0:
                            return am4core.color("#EA526F");
                        case 1:
                            return am4core.color("#82BCFF");
                        case 2:
                            return am4core.color("#3693FF");
                        case 3:
                            return am4core.color("#F2C57C");
                        case 4:
                            return am4core.color("#2EC4B6");
                        case 5:
                            return am4core.color("#FF7200");
                        default:
                            return chart.colors.getIndex(0);
                    }
                })

                var valueLabel = series.bullets.push(new am4charts.LabelBullet());
                valueLabel.label.text = "{valueX}";
                valueLabel.label.horizontalCenter = "left";
                valueLabel.label.dx = 10;
                valueLabel.label.hideOversized = false;
                valueLabel.label.truncate = false;

                var categoryLabel = series.bullets.push(new am4charts.LabelBullet());
                categoryLabel.label.text = "{name}";
                categoryLabel.label.horizontalCenter = "right";
                categoryLabel.label.dx = -10;
                categoryLabel.label.fill = am4core.color("#fff");
                categoryLabel.label.hideOversized = false;
                categoryLabel.label.truncate = false;
            }

            createSeries("value", "");
            //createSeries("expenses", "Expenses");

        }


        this.chart = chart;
    }


    render() {

        return (
            <div>
                <div id={this.props.chartId} style={{ width: "100%", height: "360px" }}></div>
            </div>
        );

    }
}

export default ClusteredBarChart;


