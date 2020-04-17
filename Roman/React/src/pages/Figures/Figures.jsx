import React, { Component } from 'react';

import {Button, Alert} from 'react-bootstrap';

import './Figures.css';
import Spinner from '../../components/Spinner/Spinner';
import DelModal from '../../components/DelModal/DelModal';
import APP from '../../App-constants';

class Figures extends Component{
  
  

  constructor(props) {
    super(props);
    this.state = {
      items: [],
      isLoaded: false,
      deleteSuccess: false,
      showAlert: false,
      showDelModal: false,
      alertSuccessDeleteText: '',
    };

    this.modalDelClose = this.modalDelClose.bind(this);
  }

  componentDidMount() {
    fetch(`${APP.endpoints.baseUrl}${APP.endpoints.figures}`)
      .then(res => res.json())
      .then(json => {
        this.setState({
          isLoaded: true,
          items: json,
          currentIdDeleteFigure: Number
        })
      });
  }


  deleteFigures(id){
    this.setState({deleteSuccess: true});
    
    fetch(`${APP.endpoints.baseUrl}${APP.endpoints.figures}?id=${id}`, {
      method: 'DELETE'
    })
      .then(response => {
        if(response.ok){
          this.setState({deleteSuccess: false});
          var figures = this.state.items;
          var index = figures.findIndex(figure => figure.id === id);
          figures.splice(index,1);
          this.setState({items: figures});
          this.setState({ showDelModal: false })
          this.setState({alertSuccessDeleteText: `Figure number ${id} successfully removed`, showAlert: true});
        }
      });
  }

  modalDelClose() {
    this.setState({ showDelModal: false })
  }

  render(){

    var { isLoaded, items, deleteSuccess, showAlert, alertSuccessDeleteText, showDelModal, currentIdDeleteFigure } = this.state;

    if (!isLoaded) {
      return <Spinner />
    } else {
      items = items.sort((first,second) => first.area-second.area);
      return(
      <div className="container text-center table-responsive list-figures">

      {/* вынести в отдельный компонент
          https://ru.react.js.org/docs/conditional-rendering.html#Предотвращение-отрисовки-компонента
      */}
      {
        showAlert  &&
        <Alert dismissible variant="success" onClose={() => {this.setState({showAlert: false})}}>
          {alertSuccessDeleteText}
        </Alert>
      }
      


        <table className="table table-bordered table-hover table-striped">
          <caption>List added figures</caption>

          <thead>
            <tr>
              <th scope="col">id</th>
              <th scope="col">Type figures</th>
              <th scope="col">Area</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>

          <tbody>
            {
              items.map(figure => (
                <tr key={figure.id}>
                  <th scope="row"> {figure.id} </th>
                  <td> {figure.type} </td>
                  <td> {figure.area} </td>
                  <td> 
                    {/* спросить про байнд */}
                    <Button variant="outline-danger btn-sm" onClick={() => {this.setState({showDelModal: true, currentIdDeleteFigure: figure.id})}} disabled={deleteSuccess}>
                      Delete
                    </Button> 
                  </td>
                </tr>
              ))
            }
          </tbody>

        </table>

        {
          this.state.showDelModal &&
          <DelModal 
          show={showDelModal} 
          modalDelClose={this.modalDelClose} 
          deleteFigures={this.deleteFigures.bind(this, currentIdDeleteFigure)}
          deleteSuccess = {deleteSuccess}
          />
        }

      </div>
    );  
  }
  }
}

export default Figures;