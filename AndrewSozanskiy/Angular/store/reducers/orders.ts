import * as orderAction from '../actions/orders';

import { Order } from '../../models';

export interface State {
  ids: number[];
  orders: Order[];
}

export const initialState: State = {
  ids: [1, 2, 3],
  orders: [
    {
      id: 1,
      date: '01-11-15',
      userEmail: 'forexample@mail.co',
      userFirstName: 'Andrew',
      userLastName: 'Sozanskiy',
      userTel: '+380958702433',
      items: 'some_items',
      orderType: 'Wholesale',
      seller: 'Magic Tips Lab',
      orderId: 'W-151016',
      orderDate: '03-12-15',
      comment: 'some comment'
    },
    {
      id: 2,
      date: '03-12-15',
      userEmail: 'forexample@mail.co',
      userFirstName: 'Andrew',
      userLastName: 'Sozanskiy',
      userTel: '+380958702433',
      items: 'some_items',
      orderType: 'Wholesale',
      seller: 'Magic Tips Lab',
      orderId: 'W-151016',
      orderDate: '03-15-15',
      comment: 'some comment'
    },
    {
      id: 3,
      date: '03-12-17',
      userEmail: 'forexample@mail.co',
      userFirstName: 'Andrew',
      userLastName: 'Sozanskiy',
      userTel: '+380958702433',
      items: 'some_items',
      orderType: 'Wholesale',
      seller: 'Magic Tips Lab',
      orderId: 'W-151016',
      orderDate: '03-15-15',
      comment: 'some comment'
    }
  ]
};

export function reducer(state = initialState, action: orderAction.Action) {
  switch (action.type) {
    case orderAction.ADD_ONE: {
      const newOrder: Order = action.payload;
      newOrder.id = state.ids.length + 1;
      state.ids.push(state.ids.length + 1);
      state.orders.push(newOrder);
      return {
        ...state,
        ids: [...state.ids],
        orders: [...state.orders]
      };
    }

    case orderAction.UPDATE: {
      state.orders.forEach(el => {
        if (el.id === action.payload.id) {
          return { el, ...action.payload };
        }
      });
      return {
        ...state,
        ids: [...state.ids],
        orders: [...state.orders]
      };
    }

    default:
      return state;
  }
}

export const getIds = (state: State) => state.ids;
export const getOrders = (state: State) => state.orders;
