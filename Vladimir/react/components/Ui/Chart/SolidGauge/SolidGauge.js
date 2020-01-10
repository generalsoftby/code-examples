import React, {Component} from 'react';
import './SolidGauge.scss';
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";

am4core.useTheme(am4themes_animated);

class SolidGauge extends Component {

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
        let chart = am4core.create(this.props.chartId, am4charts.RadarChart);
        this.chart = chart;

        if(this.props.chartData.length > 0) {

            this.chart.exporting.menu = new am4core.ExportMenu();

            switch (this.props.colorScheme) {
                case 'VALUES':
                    chart.colors.list = [];
                    this.props.chartData.forEach ((item, key) => {
                        switch (item.category) {
                            case 'second_hand':
                                chart.colors.list.push(am4core.color("#FF7200"));
                                break;
                            case 'removed':
                                chart.colors.list.push(am4core.color("#304156"));
                                break;
                            case 'no_infringements':
                                chart.colors.list.push(am4core.color("#F2C57C"));
                                break;
                            case 'authorized':
                                chart.colors.list.push(am4core.color("#2EC4B6"));
                                break;
                            case 'pending':
                                chart.colors.list.push(am4core.color("#3693FF"));
                                break;
                            case 'detected':
                                chart.colors.list.push(am4core.color("#EA526F"));
                                break;
                            default:
                                break;
                        }
                    });
                    break;
                default:
                    chart.colors.list = [
                        am4core.color("#FF7200"),
                        am4core.color("#2EC4B6"),
                        am4core.color("#F2C57C"),
                        am4core.color("#3693FF"),
                        am4core.color("#82BCFF"),
                        am4core.color("#EA526F")
                    ];
                    break;
            }

            // Add data
            this.chart.data = this.props.chartData;

            // Make chart not full circle
            this.chart.startAngle = -90;
            this.chart.endAngle = 180;
            this.chart.innerRadius = am4core.percent(20);

            // Set number format
            this.chart.numberFormatter.numberFormat = "#.#'%'";

            // Create axes
            let categoryAxis =  this.chart.yAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "category";
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.grid.template.strokeOpacity = 0;
            categoryAxis.renderer.labels.template.horizontalCenter = "right";
            categoryAxis.renderer.labels.template.fontWeight = 500;

            let chh = this.chart;
            categoryAxis.renderer.labels.template.adapter.add("fill", function(fill, target) {
                return (target.dataItem.index >= 0) ?  chh.colors.getIndex(target.dataItem.index) : fill;
            });
            categoryAxis.renderer.minGridDistance = 10;

            let valueAxis =  this.chart.xAxes.push(new am4charts.ValueAxis());
            valueAxis.renderer.grid.template.strokeOpacity = 0;
            valueAxis.min = 0;
            valueAxis.max = 100;
            valueAxis.strictMinMax = true;

            // Create series
            let series1 =  this.chart.series.push(new am4charts.RadarColumnSeries());
            series1.dataFields.valueX = "full";
            series1.dataFields.categoryY = "category";
            series1.clustered = false;
            series1.columns.template.fill = new am4core.InterfaceColorSet().getFor("alternativeBackground");
            series1.columns.template.fillOpacity = 0.08;
            series1.columns.template.cornerRadiusTopLeft = 20;
            series1.columns.template.strokeWidth = 0;
            series1.columns.template.radarColumn.cornerRadius = 20;

            let series2 =  this.chart.series.push(new am4charts.RadarColumnSeries());
            series2.dataFields.valueX = "value";
            series2.dataFields.categoryY = "category";
            series2.clustered = false;
            series2.columns.template.strokeWidth = 0;
            series2.columns.template.tooltipText = "{category}: [bold]{value}[/]";
            series2.columns.template.radarColumn.cornerRadius = 20;

            let ch = this.chart;

            series2.columns.template.adapter.add("fill", function(fill, target) {
                return  ch.colors.getIndex(target.dataItem.index);
            });

            categoryAxis.renderer.labels.template.adapter.add("textOutput", function(text) {
                switch (text) {
                    case 'second_hand':
                        return 'Second Hand';
                    case 'removed':
                        return 'Removed';
                    case 'no_infringements':
                        return 'No infringement';
                    case 'authorized':
                        return 'Authorized';
                    case 'pending':
                        return 'Pending';
                    case 'detected':
                        return 'Detected';
                    default:
                        return text;
                }
            });

            // Add cursor
            this.chart.cursor = new am4charts.RadarCursor();

        }
    }


    render() {

        return (
            <div>
                <div id={this.props.chartId} style={{ width: "100%", height: "500px" }}></div>
            </div>
        );

    }
}

export default SolidGauge;


