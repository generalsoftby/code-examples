import React, {Component} from 'react';

import {Button, Form} from 'react-bootstrap';

import './Circle.css';
import circleImg from '../../images/circle.png';
import APP from '../../App-constants';

class Circle extends Component{

  MINRADIUS = 0;
  MAXRADIUS = 1000;
  ACCURACY = 1000;
  INITIALRADIUS = 10; 
  STEP = 0.001;

  constructor(props) {
    super(props);
    this.state = {
      radius: this.INITIALRADIUS,
      radiusValid: true,
    };

    this.handleChange = this.handleChange.bind(this);
    this.addCircle = this.addCircle.bind(this);
  }

  handleChange(event) {
    this.setState({radius: event.target.value});
    if(event.target.value > this.MINRADIUS && event.target.value < this.MAXRADIUS){
      this.setState({radiusValid: true});
    }
    else this.setState({radiusValid: false});
  }

  getCircleArea() {
    const area = Math.pow(Number(this.state.radius),2) * Math.PI;
    return Math.round(area * this.ACCURACY)/this.ACCURACY;
  }

  addCircle(event) {
    const area = this.getCircleArea();
    this.props.addFigures(APP.types.circle, area, event);
  }


  render(){
    const {radiusValid, radius} = this.state;

    return(
      <div className="container row">
        <div className="col-md-12 mb-12">

          <h3 className="text-center">Calculation of the area of a {APP.types.circle}</h3>
          <hr />

          <div className="row form-content">

            <Form className='col-md-6 mb-6'>
              <Form.Group controlId="formRadius">
                <Form.Label>Radius length:</Form.Label>
                <Form.Control 
                  className={radiusValid?'is-valid':'is-invalid'}
                  type="number"
                  step={this.STEP} 
                  name="radius" 
                  placeholder={this.INITIALRADIUS}
                  min={this.MINRADIUS} 
                  max={this.MAXRADIUS} 
                  value={radius} 
                  onChange={this.handleChange} 
                  required 
                />
                {
                  !radiusValid && <div className='alert alert-danger'>
                    Incorrect radius
                  </div>
                }
              </Form.Group>

              <Button variant="success" type="submit" disabled={!radiusValid || this.props.responseIsSuccess} onClick={ (event) => this.addCircle(event) }>
                Add a new {APP.types.circle}
                {
                  this.props.responseIsSuccess &&
                  <span className="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                }
              </Button>
            </Form>
            
            <div className="col-md-6 mb-6 text-center">
              <h4>
                Formula: 
                <code> S=πR²</code>
              </h4>    
              <img src={circleImg} alt={APP.types.circle} height="100"/> 
            </div>
            
          </div>
          
        </div>
      </div>
    );
  }
}

export default Circle;