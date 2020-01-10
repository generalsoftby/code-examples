import React, {Component} from 'react';
import './ChildClusteredBarChart.scss';
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";

am4core.useTheme(am4themes_animated);

class ChildClusteredBarChart extends Component {

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

    updateChart = () => {
        if (this.chart) {
            this.chart.dispose();
        }

        let chart = am4core.create(this.props.chartId, am4charts.XYChart);
        this.chart = chart;

        if(this.props.chartData.length > 0) {

            this.chart.exporting.menu = new am4core.ExportMenu();

            // Add data
            this.chart.data = this.props.chartData;


           // let latitudeAxis = this.chart.yAxes.push(new am4charts.ValueAxis());
            //latitudeAxis.renderer.grid.template.disabled = true;
            //latitudeAxis.renderer.labels.template.disabled = true;


            // Create axes
            let categoryAxis =  this.chart.yAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "type";
            categoryAxis.numberFormatter.numberFormat = "#";
            categoryAxis.renderer.inversed = true;
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.cellStartLocation = 0.1;
            categoryAxis.renderer.cellEndLocation = 0.9;
            categoryAxis.renderer.grid.template.strokeOpacity = 0;
            //categoryAxis.renderer.grid.template.disabled = true;
            //categoryAxis.renderer.grid.template.opacity = 0;
           // categoryAxis.renderer.line.disabled = true;
            //categoryAxis.renderer.labels.template.disabled = true;

            let  valueAxis =  this.chart.xAxes.push(new am4charts.ValueAxis());
            valueAxis.renderer.grid.template.opacity = 0;
            valueAxis.renderer.opposite = true;
            valueAxis.renderer.grid.template.strokeOpacity = 0;
            valueAxis.renderer.labels.template.disabled = true;
            valueAxis.renderer.line.disabled = true;
            valueAxis.renderer.grid.template.stroke = am4core.color("#FFF");
            valueAxis.renderer.grid.template.strokeOpacity = 0;
            valueAxis.renderer.baseGrid.disabled = true;

            let ch =  this.chart;

            // Create series
            function createSeries(field, name) {
                let series =  ch.series.push(new am4charts.ColumnSeries());
                series.dataFields.valueX = field;
                series.dataFields.categoryY = "type";
                series.name = name;
                series.columns.template.height = am4core.percent(30);
                series.sequencedInterpolation = true;
                series.columns.template.tooltipText = "[bold]{valueX}[/]";


                var columnTemplate = series.columns.template;
                columnTemplate.column.cornerRadiusTopRight = 30;
                columnTemplate.column.cornerRadiusBottomRight = 30;
                columnTemplate.column.cornerRadiusTopLeft = 30;
                columnTemplate.column.cornerRadiusBottomLeft = 30;
                columnTemplate.strokeWidth = 0;

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

                let valueLabel = series.bullets.push(new am4charts.LabelBullet());
                valueLabel.label.text = "{valueX}";
                valueLabel.label.horizontalCenter = "left";
                valueLabel.label.dx = 10;
                valueLabel.label.hideOversized = false;
                valueLabel.label.truncate = false;

            }

            createSeries("value", "");
        }
    }


    render() {

        return (
            <div>
                <div id={this.props.chartId} style={{ width: "100%", height: "287px" }}></div>
            </div>
        );

    }
}

export default ChildClusteredBarChart;


