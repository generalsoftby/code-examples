import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GridModule } from '@progress/kendo-angular-grid';

import { OrdersComponent } from './orders.component';

const routerConfig: Routes = [
  {
    path: '',
    component: OrdersComponent
  }
];

@NgModule({
  imports: [CommonModule, RouterModule.forChild(routerConfig), GridModule],
  declarations: [OrdersComponent]
})
export class OrdersModule {}
