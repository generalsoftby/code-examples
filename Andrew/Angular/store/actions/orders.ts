import { Action } from '@ngrx/store';

import { Order } from '../../models';

export const ADD_ONE = '[Orders] Add One';
export const UPDATE = '[Orders] Update One';

export class AddOne implements Action {
    readonly type = ADD_ONE;

    constructor(public payload: Order) { }
}

export class UpdateOne implements Action {
    readonly type = UPDATE;

    constructor(public payload: Order) {}
}

export type Action = AddOne | UpdateOne;


