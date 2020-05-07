import React, {Component} from 'react';

import {Form, Alert} from 'react-bootstrap'

import './AddFigures.css';
import APP from '../../App-constants';
import Circle from '../../components/Circle/Circle';
import Square from '../../components/Square/Square';
import Rectangle from '../../components/Rectangle/Rectangle';
import Triangle from '../../components/Triangle/Triangle';


class AddFigures extends Component {

  constructor(props) {
    super(props);
    this.state = {
      currentFigure: APP.types.circle,
      showAlertMessage: false, 
      responseIsSuccess: false
    };

    this.handleChange = this.handleChange.bind(this);
    this.addFigures = this.addFigures.bind(this);
  }

  handleChange(event) {
    this.setState({currentFigure: event.target.value, showAlertMessage: false});
  }

  addFigures(type, area, event){
    event.preventDefault();
    this.setState({responseIsSuccess: true, showAlertMessage: false});

    fetch(`${APP.endpoints.baseUrl}${APP.endpoints.figures}`, {
      method: 'POST',
      headers:{'content-type': 'application/json'},
      body:JSON.stringify({
      type: type,
      area: area
      })
    })
      .then(response => {
        if(response.ok){
          this.setState({responseIsSuccess: false, showAlertMessage: true});
        }  
    })

  }

  render(){

    var {currentFigure, responseIsSuccess, showAlertMessage} = this.state;
    var currentFigureComponent; 

    switch(currentFigure){
      case APP.types.circle:{
        currentFigureComponent = <Circle addFigures={this.addFigures} responseIsSuccess={responseIsSuccess} />;
        break;
      }
      case APP.types.square:{
        currentFigureComponent = <Square addFigures={this.addFigures} responseIsSuccess={responseIsSuccess}/>;
        break;
      }
      case APP.types.rectangle:{
        currentFigureComponent = <Rectangle addFigures={this.addFigures} responseIsSuccess={responseIsSuccess}/>;
        break;
      }
      case APP.types.triangle:{
        currentFigureComponent = <Triangle addFigures={this.addFigures} responseIsSuccess={responseIsSuccess}/>;
        break;
      }
      default:{
        currentFigureComponent = <h1>Oh, ... I don't understand you</h1>;
        break;
      }
    }


    return(
      <div className="container add-figures">
        <div className="row">
          <div className="col-3">
            <Form.Group controlId="ControlSelect">
              <Form.Label className='h5 labelName'>Select the figure:</Form.Label>
              <Form.Control className='' as="select" value={currentFigure} onChange={this.handleChange}>
                <option>{APP.types.circle}</option>
                <option>{APP.types.square}</option>
                <option>{APP.types.rectangle}</option>
                <option>{APP.types.triangle}</option>
              </Form.Control>
            </Form.Group>
          </div>

          <div className="col-9">
            {currentFigureComponent}
            
            {
              showAlertMessage  &&
              <div className='d-flex justify-content-center'>
                <Alert dismissible variant="success" className='text-center addMessage col-9' onClose={() => {this.setState({showAlertMessage: false})}}>
                  {currentFigure} successfully added
                </Alert>
              </div>
              
            }
          </div>
        </div>
      </div>
    );
  }
}

export default AddFigures;