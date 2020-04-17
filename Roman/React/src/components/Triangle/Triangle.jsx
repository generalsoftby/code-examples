import React, {Component} from 'react';

import {Button, Form, Alert} from 'react-bootstrap/';

import './Triangle.css';
import triangleImg from '../../images/triangle.png';
import APP from '../../App-constants';

class Triangle extends Component {

  MINLENGTH = 0;
  MAXLENGTH = 1000;
  ACCURACY = 1000;
  INITIALLENGTH1 = 3;
  INITIALLENGTH2 = 4;
  INITIALLENGTH3 = 5;
  STEP = 0.001;

  constructor(props) {
    super(props);
    this.state = {
      length1: this.INITIALLENGTH1,
      length2: this.INITIALLENGTH2,
      length3: this.INITIALLENGTH3,
      length1Valid: true,
      length2Valid: true,
      length3Valid: true,
      triangleValid: true
    };

    this.handleChange = this.handleChange.bind(this);
    this.addTriangle = this.addTriangle.bind(this);
  }

  handleChange(event) {
    const target = event.target;
    const name = target.name;
    const value = target.value;
  
    this.setState({
      [name]: value,
    });

    let names = ['length1', 'length2', 'length3'];
    names = names.filter( item => item !== name );

    const a = Number(value);
    const b = Number(this.state[names[0]]);
    const c = Number(this.state[names[1]]); 

    if(value > this.MINLENGTH && value < this.MAXLENGTH){
      this.setState({
        [name+'Valid']: true
      });
      
      if (a<b+c && b<a+c && c<a+b){
        this.setState({triangleValid: true});
      }
      else this.setState({triangleValid: false});
    }
    else {
      this.setState({
        [name+'Valid']: false
      });
      return;
    };    
  }

  getTriangleArea() {
    const a = this.state.length1;
    const b = this.state.length2;
    const c = this.state.length3;

    const p = (a+b+c)/2;
    let area = Math.sqrt(p*(p-a)*(p-b)*(p-c));
    return Math.round(area * this.ACCURACY)/this.ACCURACY;
  }

  addTriangle(event) {
    const area = this.getTriangleArea();
    this.props.addFigures(APP.types.triangle, area, event);        
  }

  render(){
    const {length1, length2, length3, length1Valid, length2Valid, length3Valid, triangleValid} = this.state;
    return(
      <div className="container row">
        <div className="col-md-12 mb-12">

          <h3 className="text-center">Calculation of the area of a {APP.types.triangle}</h3>
          <hr />

          <div className="row form-content">
            <Form className='col-md-6 mb-6'>
              <Form.Group controlId="formLengths">
                
                <Form.Label>Side length 1:</Form.Label>
                <Form.Control 
                  className={length1Valid && triangleValid?'is-valid':'is-invalid'}
                  type="number" 
                  step={this.STEP} 
                  name="length1" 
                  placeholder={this.INITIALLENGTH1} 
                  min={this.minCoordinate} 
                  max={this.maxCoordinate} 
                  value={length1} 
                  onChange={this.handleChange} 
                  required 
                />
                {
                  !length1Valid && <div className='alert alert-danger'>
                    Incorrect length
                  </div>
                }

                <Form.Label className='triangle-label'>Side length 2:</Form.Label>
                <Form.Control 
                  className={length2Valid && triangleValid?'is-valid':'is-invalid'}
                  type="number" 
                  step={this.STEP}  
                  name="length2" 
                  placeholder={this.INITIALLENGTH2}
                  min={this.minCoordinate} 
                  max={this.maxCoordinate} 
                  value={length2} 
                  onChange={this.handleChange} 
                  required 
                />
                {
                  !length2Valid && <div className='alert alert-danger'>
                    Incorrect length
                  </div>
                }

                <Form.Label className='triangle-label'>Side length 3:</Form.Label>
                <Form.Control 
                  className={length3Valid && triangleValid?'is-valid':'is-invalid'}
                  type="number" 
                  step={this.STEP} 
                  name="length3" 
                  placeholder={this.INITIALLENGTH3}
                  min={this.minCoordinate} 
                  max={this.maxCoordinate} 
                  value={length3} 
                  onChange={this.handleChange} 
                  required 
                />
                {
                  !length3Valid && <div className='alert alert-danger'>
                    Incorrect length
                  </div>
                }
                </Form.Group>


                <Button variant="success" type="submit" 
                disabled={!length1Valid || !length2Valid || !length3Valid || !triangleValid || this.props.responseIsSuccess}
                onClick={ (event) => this.addTriangle(event)}
                >
                  Add a new {APP.types.triangle}
                  {
                    this.props.responseIsSuccess &&
                    <span className="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                  }
                </Button>
              </Form>


            <div className="col-md-6 mb-6 text-center">
              <h4>
                Formula:
                <br />
                <code>S=√(p·(p-a)·(p-b)·(p-c))</code>
              </h4>    
              <img src={triangleImg} alt={APP.types.triangle} height="100"/> 
            </div>

          </div>

          {
            !triangleValid && 
            <Alert variant="danger" className='text-center'>
              I can not build a triangle with such a side
            </Alert>
          }

        </div>
      </div>
    );
  }
}

export default Triangle;