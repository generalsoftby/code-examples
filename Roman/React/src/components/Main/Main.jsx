import React, {Component, lazy} from 'react';
import {Route, Switch, Redirect}  from 'react-router-dom';

const Figures = lazy(() => import('../../pages/Figures/Figures'));
const Statistics = lazy(() => import('../../pages/Statistics/Statistics'));
const AddFigures = lazy(() => import('../../pages/AddFigures/AddFigures'));


class Main extends Component {
  render(){
    return(
      <main>
        <Switch>
          <Route exact path="/" component={Figures}/>
          <Route path="/Statistics" component={Statistics}/>
          <Route path="/AddFigures" component={AddFigures}/>
          <Redirect to="/" />
        </Switch>
      </main>
    );
  }
}

export default Main;