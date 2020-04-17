import React, { Component } from 'react';
import { NavLink } from 'react-router-dom';

import './Header.css';

class Header extends Component{
  render(){
    return(
      <header>
        <nav className="navbar navbar-dark navbar-expand-lg bg-primary">
          <div className="container">
            <NavLink className="navbar-brand" to='/'>Figures</NavLink>
            <button className="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
              <span className="navbar-toggler-icon"></span>
            </button>
            <div id="navbarText" className="collapse navbar-collapse text-center">
              <ul className="navbar-nav ml-auto">
                <li className="nav-item">
                  <NavLink exact={true} to='/' activeClassName='active' className="nav-link">List figures</NavLink>
                </li>
                <li className="nav-item">
                  <NavLink to='/AddFigures' activeClassName='active' className="nav-link">Add figures</NavLink>
                </li>
                <li className="nav-item">
                  <NavLink to='/Statistics' activeClassName='active' className="nav-link">Statistics</NavLink>
                </li>
              </ul>
            </div>
          </div>
        </nav>
      </header>

    );
  }
}

export default Header;