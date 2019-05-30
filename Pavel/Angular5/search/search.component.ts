import { AfterViewInit, Component, OnInit, ViewChild } from '@angular/core';
import { Subscription }                                from 'rxjs';
import { BsModalRef, BsModalService }                  from 'ngx-bootstrap';

import { FiltersComponent }                                 from '../../components/filters/filters.component';
import { ResultTableComponent }                             from '../../components/result-table/result-table.component';
import { ApiService }                                       from '../../services/api.service';
import { ApiResponse, Event, Filters, Pagination, Sorting } from '../../types';
import { WarningModalWindowComponent }                      from '../../../shared/components/warning-modal-window/warning-modal-window.component';
import { ProgressModalWindowComponent }                     from '../../../shared/components/progress-modal-window/progress-modal-window.component';
import { SearchRequest }                                    from '../../request/search.request';
import { FiltersStateService }                              from '../../services/filters-state.service';
import { TableSettingsService }                             from '../../services/tableSettings.service';

/**
 * Parent component for the Search page.
 */
@Component({
    selector: 'app-search',
    templateUrl: './search.component.html',
    styleUrls: ['./search.component.scss']
})
export class SearchComponent implements OnInit, AfterViewInit {
    /** Current state of filters, needed for the pagination **/
    currentRequest: SearchRequest;

    /** The request passed from Presets page, needed for the filters init **/
    presetRequest: SearchRequest;

    /** Main child components **/
    @ViewChild('filters') filtersComponent: FiltersComponent;
    @ViewChild('results') resultsComponent: ResultTableComponent;

    /** Currently shown progress modal window **/
    progressModal: BsModalRef;

    /** Currently running search request, needed for termination **/
    subscription: Subscription;

    constructor(private readonly apiService: ApiService,
                private readonly bsModalService: BsModalService,
                private readonly filtersState: FiltersStateService,
                private readonly tableSettings: TableSettingsService) {
        this.presetRequest = this.filtersState.pull();
    }

    ngOnInit(): void {
    }

    /**
     * Runs search if there is a stored request.
     */
    ngAfterViewInit(): void {
        if (this.presetRequest) {
            this.currentRequest = this.presetRequest;
            this.performSearch();
        }
    }

    /**
     * Called when the page has been changed.
     *
     * @param paginationEvent Pagination
     */
    paginate(paginationEvent: Pagination): void {
        this.performSearch(null, paginationEvent);
    }

    /**
     * Called when the sorting has been performed.
     *
     * @param sortingEvent Sorting
     */
    sort(sortingEvent: Sorting): void {
        this.performSearch(null, null, sortingEvent);
    }

    /**
     * Makes the API request and passes result data into the table.
     *
     * @param filters Filters
     * @param pagination Pagination
     * @param sorting Sorting
     */
    performSearch(filters?: Filters, pagination?: Pagination, sorting?: Sorting): void {
        this.currentRequest = filters ? new SearchRequest(filters) : this.currentRequest;

        // Calls after clicking on a search button
        if (filters) {
            this.currentRequest.setSort(this.tableSettings.defaultSorting);
        }

        // Called after clicking on any button in pagination
        if (pagination) {
            this.currentRequest.pagination = pagination;
        }

        // Called after clicking on column header
        if (sorting) {
            this.currentRequest.setSort(sorting);
        }

        this.progressModal = this.bsModalService.show(ProgressModalWindowComponent, {
            animated: false,
            ignoreBackdropClick: true,
            initialState: {
                onCancel: (): void => {
                    if (this.subscription) {
                        this.subscription.unsubscribe();
                        this.subscription = undefined;
                    }

                    this.progressModal.hide();
                }
            }
        });

        this.subscription = this.apiService
            .get<Event>('event', this.currentRequest)
            .subscribe((response: ApiResponse<Event>): void => {

                this.resultsComponent.setData(response);

                this.progressModal.hide();
                this.subscription = undefined;
            }, (data: any): void => {
                this.progressModal && this.progressModal.hide();
                this.subscription = undefined;
                this.bsModalService.show(WarningModalWindowComponent, {
                    initialState: {
                        bodyText: data.statusText
                    }
                });
            });
    }
}
