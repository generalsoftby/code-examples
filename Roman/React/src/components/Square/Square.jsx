import React, {Component} from 'react';

import {Button, Form} from 'react-bootstrap';

import './Square.css';
import squareImg from '../../images/square.jpg';
import APP from '../../App-constants';


class Square extends Component{

  MINLENGTH = 0;
  MAXLENGTH = 1000;
  ACCURACY = 1000;
  INITIALLENGTH = 10; 
  STEP = 0.001;

  constructor(props) {
    super(props);
    this.state = {
      length: this.INITIALLENGTH,
      lengthValid: true,
    };

    this.handleChange = this.handleChange.bind(this);
    this.addSquare = this.addSquare.bind(this);
  }

  handleChange(event) {
    this.setState({length: event.target.value});
    if(event.target.value > this.MINLENGTH && event.target.value < this.MAXLENGTH){
      this.setState({lengthValid: true});
    }
    else this.setState({lengthValid: false});
  }

  getSquareArea() {
    const area = Math.pow(this.state.length,2)
    return Math.round(area * this.ACCURACY)/this.ACCURACY;
  }

  addSquare(event) {
    const area = this.getSquareArea();
    this.props.addFigures(APP.types.square, area, event);
  }

  render(){
    const {lengthValid, length} = this.state;

    return(
      <div className="container row">
        <div className="col-md-12 mb-12">

          <h3 className="text-center">Calculation of the area of a {APP.types.square}</h3>
          <hr />

          <div className="row form-content">

            <Form className='col-md-6 mb-6'>
              <Form.Group controlId="formLength">
                <Form.Label>Side length:</Form.Label>
                <Form.Control 
                  className={lengthValid?'is-valid':'is-invalid'}
                  type="number" 
                  step={this.STEP}
                  name="length" 
                  placeholder={this.INITIALLENGTH} 
                  min={this.MINLENGTH} 
                  max={this.MAXLENGTH} 
                  value={length} 
                  onChange={this.handleChange} 
                  required 
                />
                {
                  !lengthValid && <div className='alert alert-danger'>
                    Incorrect length
                  </div>
                }
              </Form.Group>
              <Button variant="success" type="submit" disabled={!lengthValid || this.props.responseIsSuccess} onClick={ (event) => this.addSquare(event) }>
                Add a new {APP.types.square}
                {
                  this.props.responseIsSuccess &&
                  <span className="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                }
              </Button>
            </Form>


            <div className="col-md-6 mb-6 text-center">
              <h4>
                Formula: 
                <code> S=a²</code>
              </h4>    
              <img src={squareImg} alt={APP.types.square} height="100"/> 
            </div>

          </div>
          
        </div>
      </div>
    );
  }
}

export default Square;