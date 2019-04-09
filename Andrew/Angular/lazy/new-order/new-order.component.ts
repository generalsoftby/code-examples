import { Component, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Store } from '@ngrx/store';
import * as moment from 'moment';
import { Subscription } from 'rxjs';

import { EmailValidator } from '../../../validators/custom.validators';
import { SuccessSnackComponent } from '../../shared/success-snack/success-snack';
import * as orderAction from '../../store/actions/orders';
import * as fromRoot from '../../store/reducers';

@Component({
  selector: 'app-new-order',
  templateUrl: './new-order.component.html',
  styleUrls: ['./new-order.component.scss']
})
export class NewOrderComponent implements OnInit, OnDestroy {
  currentdate: Date = new Date();
  orderDate: Date = new Date();
  form: FormGroup;
  phoneNumberValid: boolean;
  orderNumberMonth: number;
  private subscriptions: Subscription[] = [];
  orderId: string;

  constructor(
    public snackBar: MatSnackBar,
    private formBuilder: FormBuilder,
    private store: Store<fromRoot.State>
  ) {}

  ngOnInit() {
    this.initForm();
    this.subscriptions.push(
      this.form.valueChanges.subscribe(el => {
        this.form.patchValue(
          { orderId: this.calculateOrderId(el.orderType, el.orderDate) },
          { emitEvent: false }
        );
      })
    );
    this.subscriptions.push(
      this.store.select(store => store).subscribe(el => {
        this.orderNumberMonth = el.orders.ids.length + 1;
      })
    );
  }

  onChangeDate(value) {
    this.form.patchValue({
      orderDate: moment(value, 'YYYY-MM-DD').format('YYYY-MM-DD')
    });
  }

  hasError(error) {
    this.phoneNumberValid = !error;
  }

  getNumber(tel) {
    this.form.patchValue({
      userTel: tel
    });
  }

  save() {
    if (this.form.valid) {
      this.form.value.orderId = this.orderId;
      this.form.value.date = moment(this.currentdate, 'YYYY-MM-DD').format('YYYY-MM-DD');
      this.store.dispatch(new orderAction.AddOne(this.form.value));
      this.snackBar.openFromComponent(SuccessSnackComponent, {
        duration: 1000
      });
      this.form.reset();
      this.orderDate = new Date();
    }
  }

  private calculateOrderId(orderType, orderDate) {
    // tslint:disable-next-line:curly
    if (orderType && orderDate)
      this.orderId = `${orderType.charAt(0)}-${moment(orderDate).format(
        'YY'
      )}${moment(orderDate).format('MM')}${this.orderNumberMonth}`;
    return this.orderId;
  }

  private initForm() {
    this.form = this.formBuilder.group({
      userEmail: [null, [Validators.required, EmailValidator.isValid]],
      userFirstName: [null, Validators.required],
      userLastName: [null, Validators.required],
      userTel: [null, Validators.required],
      items: [null, Validators.required],
      orderType: [null, Validators.required],
      seller: [null, Validators.required],
      orderId: [{ value: null, disabled: true }, Validators.required],
      orderDate: [
        moment(this.orderDate, 'YYYY-MM-DD').format('YYYY-MM-DD'),
        Validators.required
      ],
      comment: [null]
    });
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => {
      subscription.unsubscribe();
    });
  }
}
