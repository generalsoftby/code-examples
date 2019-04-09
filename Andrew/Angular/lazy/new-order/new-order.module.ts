import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatSnackBarModule } from '@angular/material/snack-bar';
import { RouterModule, Routes } from '@angular/router';
import { DateInputsModule } from '@progress/kendo-angular-dateinputs';
import { GridModule } from '@progress/kendo-angular-grid';
import { IntlModule } from '@progress/kendo-angular-intl';
import { Ng2TelInputModule } from 'ng2-tel-input';

import { NewOrderComponent } from './new-order.component';

const routerConfig: Routes = [
  {
    path: '',
    component: NewOrderComponent
  }
];

@NgModule({
  imports: [
    RouterModule.forChild(routerConfig),
    GridModule,
    DateInputsModule,
    IntlModule,
    ReactiveFormsModule,
    FormsModule,
    CommonModule,
    Ng2TelInputModule,
    MatSnackBarModule
  ],
  declarations: [NewOrderComponent]
})
export class NewOrderModule {}
