import React, { Component, Suspense } from 'react';
import {BrowserRouter as Router }  from 'react-router-dom';

import './App.css';

import Header from './components/Header/Header';
import Main from './components/Main/Main';
import Spinner from './components/Spinner/Spinner'


class App extends Component {
  render() {
    return (
      <Router>
        <Suspense fallback={<Spinner />}>
          <Header />
          <Main />
        </Suspense>
      </Router>
    );
  }
}

export default App;
