import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';

import { FiguresComponent } from './figures/figures.component';
import { StatisticsComponent } from './statistics/statistics.component';
import { AddFiguresComponent } from './add-figures/add-figures.component';

@NgModule({
  imports: [
    RouterModule.forRoot([
      {
        path: '',
        redirectTo: '/figures',
        pathMatch: 'full',
      },
      {
        path: 'figures', 
        component: FiguresComponent
      },
      {
        path: 'statistics', 
        component: StatisticsComponent
      }, 
      {
        path: 'addFigures',
        component: AddFiguresComponent
      },
      {
        path: '**',
        redirectTo: '/figures',
      },
    ])
  ],
  exports: [
    RouterModule
  ]

})

export class AppRoutingModule {

}