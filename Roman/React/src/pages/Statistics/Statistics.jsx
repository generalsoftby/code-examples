import React, { Component } from 'react';

import ProgressBar from 'react-bootstrap/ProgressBar'

import './Statistics.css';
import Spinner from '../../components/Spinner/Spinner'
import APP from '../../App-constants';

class Statistics extends Component {
  
  constructor(props) {
    super(props);
    this.state = {
      items: [],
      isLoaded: false,
      statistic: [],
    }
  }

  componentDidMount() {
    fetch(`${APP.endpoints.baseUrl}${APP.endpoints.figures}`)
      .then(res => res.json())
      .then(json => {
        this.setState({
          isLoaded: true,
          items: json,
        })
      });
  }
  
  getStatistic(items){

    let totalArea = 0; 
    let circlesArea = 0; 
    let squaresArea = 0; 
    let rectanglesArea = 0; 
    let trianglesArea = 0; 

    const Circles = items.filter(figure => figure.type === APP.types.circle); 
    for (let item of Circles){ 
      circlesArea += item.area; 
      totalArea += item.area; 
    } 

    const Squares = items.filter(figure => figure.type === APP.types.square); 
    for (let item of Squares){ 
      squaresArea += item.area; 
      totalArea += item.area; 
    } 

    const Rectangles = items.filter(figure => figure.type === APP.types.rectangle); 
    for (let item of Rectangles){ 
      rectanglesArea += item.area; 
      totalArea += item.area; 
    } 

    const Triangles = items.filter(figure => figure.type === APP.types.triangle); 
    for (let item of Triangles){ 
      trianglesArea += item.area; 
      totalArea += item.area; 
    } 

    const circlePercent = 100*circlesArea/totalArea; 
    const squarePercent = 100*squaresArea/totalArea; 
    const rectanglePercent = 100*rectanglesArea/totalArea; 
    const trianglePercent = 100*trianglesArea/totalArea; 

    let statistic = [
      {
        type: APP.types.circle,
        area: circlesArea,
        percent: Math.round(circlePercent * 1000) / 1000 
      },
      {
        type: APP.types.square,
        area: squaresArea,
        percent: Math.round(squarePercent * 1000) / 1000 
      },
      {
        type: APP.types.rectangle,
        area: rectanglesArea,
        percent:  Math.round(rectanglePercent * 1000) / 1000  
      },
      {
        type: APP.types.triangle,
        area: trianglesArea,
        percent:  Math.round(trianglePercent * 1000) / 1000 
      },
    ]
    
    return statistic;
  }

  checkType(type){
    switch (type) {
      case APP.types.circle:
        return 'warning';

      case APP.types.square:
        return 'danger';

      case APP.types.rectangle:
        return 'primary';

      case APP.types.triangle:
        return 'success'; 

      default:
        return 'secondary';
    }

  }

  render(){
    
    var { isLoaded, items } = this.state;

    if (!isLoaded) {
      return <Spinner />
    } else {
      let statistic = this.getStatistic(items);

      return(
        <div className="container text-center statistic">
          <table className="table table-striped table-bordered table-hover">
            <caption>Statistics of added figures</caption>

            <thead>
              <tr>
                <th scope="col">Type figures</th>
                <th scope="col">Total area</th>
                <th scope="col">Percent of total area</th>
              </tr>
            </thead>
            
            <tbody>
              {
                statistic.map(figure  => (
                  <tr key={figure.type}>
                    <td> { figure.type } </td>
                    <td> { figure.area }</td>
                    <td> { figure.percent }%</td>
                  </tr>
                ))
              }
              
            </tbody>

          </table>

          <div>
            {
              statistic.map(figure => (
                <ProgressBar key={figure.type} className='progressBar' animated variant={this.checkType(figure.type)} now={figure.percent} label={`${figure.type}: ${figure.percent}%`}/>
              ))
            }
          </div>

        </div>
      );
    }
  }
}

export default Statistics;