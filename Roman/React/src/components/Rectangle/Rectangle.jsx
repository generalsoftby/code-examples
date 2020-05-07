import React, {Component} from 'react';

import {Button, Form} from 'react-bootstrap';

import './Rectangle.css';
import rectangleImg from '../../images/rectangle.png';
import APP from '../../App-constants';

class Rectangle extends Component{

  MINCOORDINATE = 0;
  MAXCOORDINATE = 1000;
  ACCURACY = 1000;
  INITIALX1 = 10; 
  INITIALY1 = 10; 
  INITIALX2 = 20; 
  INITIALY2 = 20; 
  STEP = 0.001;

  constructor(props) {
    super(props);
    this.state = {
      x1: this.INITIALX1,
      y1: this.INITIALY1,
      x2: this.INITIALX2,
      y2: this.INITIALY2,
      x1Valid: true,
      y1Valid: true,
      x2Valid: true,
      y2Valid: true,
    };

    this.handleChange = this.handleChange.bind(this);
    this.addRectangle = this.addRectangle.bind(this);
  }

  handleChange(event) {
    const target = event.target;
    const name = target.name;
    const value = target.value;
  
    this.setState({
      [name]: value,
    });

    if(value > this.MINCOORDINATE && value < this.MAXCOORDINATE){
      this.setState({
        [name+'Valid']: true
      });
    }
    else this.setState({
      [name+'Valid']: false
    });
  }

  getRectangleArea() {
    const x1 = this.state.x1;
    const y1 = this.state.y1;
    const x2 = this.state.x2;
    const y2 = this.state.y2;

    const a = x2-x1;
    const b = y2-y1;

    const area = Math.abs(a*b)
    return Math.round(area * this.ACCURACY)/this.ACCURACY;
  }

  addRectangle(event) {
    const area = this.getRectangleArea();
    this.props.addFigures(APP.types.rectangle, area, event);
  }

  render(){
    const {x1, y1, x2, y2, x1Valid, y1Valid, x2Valid, y2Valid} = this.state;

    return(
      <div className="container row">
        <div className="col-md-12 mb-12">

          <h3 className="text-center">Calculation of the area of a {APP.types.rectangle}</h3>
          <hr />

          <div className="row form-content">
            <Form className='col-md-6 mb-6'>
              <Form.Group controlId="formCoordinates">
                <div className='row'>
                <div className="col-md-6 mb-6">
                  <Form.Label>Coordinate Х1:</Form.Label>
                  <Form.Control 
                    className={x1Valid?'is-valid':'is-invalid'}
                    type="number" 
                    step={this.STEP} 
                    name="x1" 
                    placeholder={this.INITIALX1}
                    min={this.MINCOORDINATE} 
                    max={this.MAXCOORDINATE} 
                    value={x1} 
                    onChange={this.handleChange} 
                    required 
                  />
                  {
                    !x1Valid && <div className='alert alert-danger'>
                      Incorrect coordinate
                    </div>
                  }
                </div>
                <div className="col-md-6 mb-6">
                  <Form.Label>Coordinate Y1:</Form.Label>
                  <Form.Control 
                    className={y1Valid?'is-valid':'is-invalid'}
                    type="number" 
                    step={this.STEP}  
                    name="y1" 
                    placeholder={this.INITIALXY}
                    min={this.MINCOORDINATE} 
                    max={this.MAXCOORDINATE} 
                    value={y1} 
                    onChange={this.handleChange} 
                    required 
                  />
                  {
                    !y1Valid && <div className='alert alert-danger'>
                      Incorrect coordinate
                    </div>
                  }
                  </div>
                </div>

                <div className="row inputs-top">
                <div className="col-md-6 mb-6">
                  <Form.Label>Coordinate Х2:</Form.Label>
                  <Form.Control 
                    className={x2Valid?'is-valid':'is-invalid'}
                    type="number" 
                    step={this.STEP}  
                    name="x2" 
                    placeholder={this.INITIALX2} 
                    min={this.MINCOORDINATE} 
                    max={this.MAXCOORDINATE} 
                    value={x2} 
                    onChange={this.handleChange} 
                    required 
                  />
                  {
                    !x2Valid && <div className='alert alert-danger'>
                      Incorrect coordinate
                    </div>
                  }
                </div>
                <div className="col-md-6 mb-6">
                  <Form.Label>Coordinate Y2:</Form.Label>
                  <Form.Control 
                    className={y2Valid?'is-valid':'is-invalid'}
                    type="number" 
                    step={this.STEP}  
                    name="y2" 
                    placeholder={this.INITIALY2}
                    min={this.MINCOORDINATE} 
                    max={this.MAXCOORDINATE} 
                    value={y2} 
                    onChange={this.handleChange} 
                    required 
                  />
                  {
                    !y2Valid && <div className='alert alert-danger'>
                      Incorrect coordinate
                    </div>
                  }
                </div>
                </div>
              </Form.Group>

              <Button variant="success" type="submit" 
                disabled={!x1Valid || !y1Valid || !x2Valid || !y2Valid || this.props.responseIsSuccess}
                onClick={ (event) => this.addRectangle(event) }
              >
                Add a new {APP.types.rectangle}
                {
                  this.props.responseIsSuccess &&
                  <span className="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                }
              </Button>
            </Form>
    
          <div className="col-md-6 mb-6 text-center">
            <h4>
              Formulas: 
            </h4>
            <p className="h4">
              <code>
                S=ab
                <br />
                a=x2-x1
                <br />
                b=y2-y1
              </code>
            </p>
            <img src={rectangleImg} alt={APP.types.rectangle} height="100"/> 
          </div>

        </div>
      </div>
    </div>
    );
  }

}

export default Rectangle;