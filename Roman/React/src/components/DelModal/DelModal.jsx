import React, { Component } from 'react'; 

import {Button, Modal} from 'react-bootstrap';

class DelModal extends Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.onHide = this.onHide.bind(this);
  }
  onHide(){
    if(!this.props.deleteSuccess){
      return this.props.modalDelClose();
    }
  }
  render() {
    return (
      <>
        <Modal 
          show={this.props.show} 
          onHide={this.onHide}
          aria-labelledby="contained-modal-title-vcenter"
          centered
        >
          <Modal.Header closeButton>
            <Modal.Title>Figure deletion</Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <p>
              <strong>Are you sure you want to delete this figure?</strong>
            </p>
            <p>
              All information associated to this figure will be permanently deleted. <span className="text-danger">This operation can not be undone.</span>
            </p>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={this.props.modalDelClose} disabled={this.props.deleteSuccess}>
              Close
            </Button>
            <Button variant="outline-danger" onClick={this.props.deleteFigures} disabled={this.props.deleteSuccess}>
              Delete
              {
                this.props.deleteSuccess &&
                <span className="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
              }
            </Button>
          </Modal.Footer>
        </Modal>
      </>
    );
  }
}

export default DelModal;