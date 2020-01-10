import React, {Component} from 'react';
import './SimplePieChart.scss';
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";

am4core.useTheme(am4themes_animated);

class SimplePieChart extends Component {

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
        let chart = am4core.create(this.props.chartId, am4charts.PieChart);
        this.chart = chart;

        if(this.props.chartData.length > 0) {

            this.chart.exporting.menu = new am4core.ExportMenu();

            // Add data
            this.chart.data = this.props.chartData;

            // Add and configure Series
            let pieSeries = this.chart.series.push(new am4charts.PieSeries());

            switch (this.props.colorScheme) {
                case 'VALUES':
                    pieSeries.colors.list = [
                        am4core.color("#EA526F"),
                        am4core.color("#3693FF"),
                        am4core.color("#2EC4B6"),
                        am4core.color("#F2C57C"),
                        am4core.color("#304156"),
                        am4core.color("#FF7200"),
                    ];
                    break;
                default:
                    pieSeries.colors.list = [
                        am4core.color("#EA526F"),
                        am4core.color("#82BCFF"),
                        am4core.color("#3693FF"),
                        am4core.color("#F2C57C"),
                        am4core.color("#2EC4B6"),
                        am4core.color("#FF7200"),
                    ];
                    break;
            }



            pieSeries.dataFields.value = "value";
            pieSeries.dataFields.category = "parameter";
            pieSeries.slices.template.stroke = am4core.color("#fff");
            pieSeries.slices.template.strokeWidth = 2;
            pieSeries.slices.template.strokeOpacity = 1;

            pieSeries.labels.template.disabled = true;

            // This creates initial animation
            pieSeries.hiddenState.properties.opacity = 1;
            pieSeries.hiddenState.properties.endAngle = -90;
            pieSeries.hiddenState.properties.startAngle = -90;


            this.chart.legend = new am4charts.Legend();
            this.chart.legend.position = "right";
            this.chart.legend.width = 300;
            this.chart.legend.useDefaultMarker = true;
            let marker = this.chart.legend.markers.template.children.getIndex(0);
            marker.cornerRadius(12, 12, 12, 12);
            marker.strokeWidth = 1;
            marker.strokeOpacity = 1;
            let markerTemplate = this.chart.legend.markers.template;
            markerTemplate.width = 14;
            markerTemplate.height = 14;
            this.chart.legend.itemContainers.template.paddingTop = 25;
            this.chart.legend.itemContainers.template.paddingBottom = 25;
            //this.chart.legend.itemContainers.template.borderColor = am4core.color("red");

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

export default SimplePieChart;


