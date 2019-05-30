import {
  ActionReducerMap,
  createSelector,
  createFeatureSelector,
  ActionReducer,
  MetaReducer
} from '@ngrx/store';

import * as formOrders from './orders';

export interface State {
  orders: formOrders.State;
}

export const reducers: ActionReducerMap<State> = {
  orders: formOrders.reducer
};

export function logger(reducer: ActionReducer<State>): ActionReducer<State> {
  return function(state: State, action: any): State {
    console.log('state', state);
    console.log('action', action);
    return reducer(state, action);
  };
}

export const metaReducers: MetaReducer<State>[] = [logger];

export const getOrderState = createFeatureSelector<formOrders.State>('orders');

export const getIds = createSelector(getOrderState, formOrders.getIds);

export const getOrders = createSelector(getOrderState, formOrders.getOrders);

