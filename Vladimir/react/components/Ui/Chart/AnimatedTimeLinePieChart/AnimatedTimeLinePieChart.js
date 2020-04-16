import React, {Component} from 'react';
import './AnimatedTimeLinePieChart.scss';
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";

am4core.useTheme(am4themes_animated);

class AnimatedTimeLinePieChart extends Component {

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

            // Add label
            this.chart.innerRadius = 100;
            let label = this.chart.seriesContainer.createChild(am4core.Label);
            label.text = "";
            label.horizontalCenter = "middle";
            label.verticalCenter = "middle";
            label.fontSize = 50;

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

            pieSeries.dataFields.value = "size";
            pieSeries.dataFields.category = "sector";

            pieSeries.labels.template.disabled = true;

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

export default AnimatedTimeLinePieChart;


