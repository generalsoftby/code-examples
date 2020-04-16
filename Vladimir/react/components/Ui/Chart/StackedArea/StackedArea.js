import React, {Component} from 'react';
import './StackedArea.scss';
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";

am4core.useTheme(am4themes_animated);

class StackedArea extends Component {

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

        if(this.props.chartData.length > 0){

            this.chart.exporting.menu = new am4core.ExportMenu();

            this.chart.paddingRight = 60;
            this.chart.data = this.props.chartData;

            let categoryAxis = this.chart.xAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = 'point';
            categoryAxis.renderer.minGridDistance = 40;
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.startLocation = 0.5;
            categoryAxis.endLocation = 0.5;
            categoryAxis.renderer.labels.template.adapter.add("textOutput", function(text) {
                if(text)
                    return text.replace(/ \(.*/, "");
                return text;
            });

            this.chart.yAxes.push(new am4charts.ValueAxis());
            //series1.labels.template.disabled = true;

            let series = this.chart.series.push(new am4charts.LineSeries());
            series.dataFields.valueY = "detected";
            series.dataFields.categoryX = "point";
            series.tooltipHTML = "<span style='font-size:14px; color:#FFFFFF;'><b>{valueY.value}</b> - Detected</span>";
            series.stacked = true;
            series.strokeWidth = 1;
            series.stroke = '#EA526F';
            series.fill = '#EA526F';
            series.fillOpacity = 0.6;
            series.legendSettings.labelText = "Detected";

            let series1 = this.chart.series.push(new am4charts.LineSeries());
            series1.dataFields.valueY = "pending";
            series1.dataFields.categoryX = "point";
            series1.tooltipHTML = "<span style='font-size:14px; color:#FFFFFF;'><b>{valueY.value}</b> - Pending</span>";
            series1.stacked = true;
            series1.strokeWidth = 1;
            series1.stroke = '#3693FF';
            series1.fill = '#3693FF';
            series1.fillOpacity = 0.6;
            series1.legendSettings.labelText = "Pending";

            let series2 = this.chart.series.push(new am4charts.LineSeries());
            series2.dataFields.valueY = "authorized";
            series2.dataFields.categoryX = "point";
            series2.tooltipHTML = "<span style='font-size:14px; color:#FFFFFF;'><b>{valueY.value}</b> - Authorized</span>";
            series2.stacked = true;
            series2.strokeWidth = 1;
            series2.stroke = '#2EC4B6';
            series2.fill = '#2EC4B6';
            series2.fillOpacity = 0.6;
            series2.legendSettings.labelText = "Authorized";

            let series3 = this.chart.series.push(new am4charts.LineSeries());
            series3.dataFields.valueY = "no_infringements";
            series3.dataFields.categoryX = "point";
            series3.tooltipHTML = "<span style='font-size:14px; color:#FFFFFF;'><b>{valueY.value}</b> - No infringement</span>";
            series3.stacked = true;
            series3.strokeWidth = 1;
            series3.stroke = '#F2C57C';
            series3.fill = '#F2C57C';
            series3.fillOpacity = 0.6;
            series3.legendSettings.labelText = "No infringement";

            let series4 = this.chart.series.push(new am4charts.LineSeries());
            series4.dataFields.valueY = "removed";
            series4.dataFields.categoryX = "point";
            series4.tooltipHTML = "<span style='font-size:14px; color:#FFFFFF;'><b>{valueY.value}</b> - Removed</span>";
            series4.stacked = true;
            series4.strokeWidth = 1;
            series4.stroke = '#304156';
            series4.fill = '#304156';
            series4.fillOpacity = 0.6;
            series4.legendSettings.labelText = "Removed";

            let series5 = this.chart.series.push(new am4charts.LineSeries());
            series5.dataFields.valueY = "second_hand";
            series5.dataFields.categoryX = "point";
            series5.tooltipHTML = "<span style='font-size:14px; color:#FFFFFF;'><b>{valueY.value}</b> - Second Hand</span>";
            series5.stacked = true;
            series5.strokeWidth = 1;
            series5.stroke = '#FF7200';
            series5.fill = '#FF7200';
            series5.fillOpacity = 0.6;
            series5.legendSettings.labelText = "Second Hand";

            this.chart.cursor = new am4charts.XYCursor();
            this.chart.scrollbarX = new am4core.Scrollbar();

            // Add a legend
            this.chart.legend = new am4charts.Legend();
            this.chart.legend.position = "bottom";

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

export default StackedArea;


