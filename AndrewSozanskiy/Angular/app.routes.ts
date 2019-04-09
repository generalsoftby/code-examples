import { Routes } from '@angular/router';

import { StartComponent } from './core/start/start.component';

export const appRoutes: Routes = [
  { path: '', component: StartComponent },
  {
    path: 'orders',
    loadChildren: './lazy/orders/orders.module#OrdersModule'
  },
  {
    path: 'new-order',
    loadChildren: './lazy/new-order/new-order.module#NewOrderModule'
  }
];
