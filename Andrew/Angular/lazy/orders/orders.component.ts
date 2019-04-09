import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { Store } from '@ngrx/store';
import {
  DataStateChangeEvent,
  GridDataResult
} from '@progress/kendo-angular-grid';
import { process, State } from '@progress/kendo-data-query';
import * as moment from 'moment';

import { Order } from '../../models';
import * as orderAction from '../../store/actions/orders';
import * as fromRoot from '../../store/reducers';

@Component({
  selector: 'app-orders',
  templateUrl: './orders.component.html',
  styleUrls: ['./orders.component.scss']
})
export class OrdersComponent implements OnInit {
  gridData: GridDataResult;
  orders: Order[];
  formGroup: FormGroup;

  state: State = {
    skip: 0,
    take: 5
  };
  private editedRowIndex: number;

  constructor(private store: Store<fromRoot.State>) {}

  ngOnInit() {
    this.store.select(store => store).subscribe(el => {
      this.orders = el.orders.orders;
      this.gridData = process(this.orders, this.state);
    });
  }

  dataStateChange(state: DataStateChangeEvent): void {
    this.state = state;
    this.gridData = process(this.orders, this.state);
  }

  cancelHandler({ sender, rowIndex }) {
    this.closeEditor(sender, rowIndex);
  }

  saveHandler({ sender, rowIndex, formGroup }) {
    const updatedOrder = (this.orders[rowIndex] = {
      ...this.orders[rowIndex],
      ...formGroup.value
    });
    this.store.dispatch(new orderAction.UpdateOne(updatedOrder));

    sender.closeRow(rowIndex);
  }

  editHandler({ sender, rowIndex, dataItem }) {
    this.closeEditor(sender);
    if (
      moment(dataItem.orderDate).diff(dataItem.date, 'days') !== 3 &&
      moment(dataItem.date).isBefore(dataItem.orderDate, 'days')
    ) {
      this.formGroup = new FormGroup({
        userFirstName: new FormControl(
          dataItem.userFirstName,
          Validators.required
        ),
        orderType: new FormControl(dataItem.orderType, Validators.required),
        seller: new FormControl(dataItem.seller, Validators.required)
      });

      this.editedRowIndex = rowIndex;

      sender.editRow(rowIndex, this.formGroup);
    } else {
      return;
    }
  }

  private closeEditor(grid, rowIndex = this.editedRowIndex) {
    grid.closeRow(rowIndex);
    this.editedRowIndex = undefined;
    this.formGroup = undefined;
  }
}
