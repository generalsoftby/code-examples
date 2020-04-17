import React, { Component } from 'react';
import './Spinner.css';

class Spinner extends Component {
  render () {
    return (
      <div className="d-flex justify-content-center align-items-center spinner">
        <div className="spinner-border text-primary" role="status">
          <span className="sr-only">Loading...</span>
        </div>
      </div>
    );
  }
}

export default Spinner;