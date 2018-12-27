import { Sorting } from '../../audit/types';

export default class SortingState {

    /**
     * The column used for sorting
     */
    private column: string;

    /**
     * Current direction of ordering
     */
    private order: string;

    /**
     *
     * @param defaultColumn
     * @param defaultOrder
     */
    constructor(private readonly defaultColumn: string, private readonly defaultOrder: string) {
        this.column = defaultColumn;
        this.order = defaultOrder;
    }

    /**
     * Changes the sorting and returns new state.
     *
     * @param column
     */
    public change(column: string): Sorting {

        if (column !== this.column) {
            this.order = this.defaultOrder;
        } else {
            this.order = this.order === 'asc' ? 'desc' : 'asc';
        }

        this.column = column;

        return {
            field: this.column,
            type: this.order
        };
    }

    /**
     * Return current column to sort by
     */
    public getColumn(): string {
        return this.column;
    }

    /**
     * Return current order to sort by
     */
    public getOrder(): string {
        return this.order;
    }
}
