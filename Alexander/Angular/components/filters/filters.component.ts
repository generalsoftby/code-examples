import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormControl, FormGroup }            from '@angular/forms';
import { Subscription }                                   from 'rxjs';

import * as forEach       from 'lodash/forEach';
import { BsModalService } from 'ngx-bootstrap/modal';

import { WarningModalWindowComponent }    from '../../../shared/components/warning-modal-window/warning-modal-window.component';
import { FiltersFormAutocompleteService } from '../../services/filters-form-autocomplete.service';
import { FiltersFormValidatorService }    from '../../services/filters-form-validator.service';
import { SearchRequest }                  from '../../request/search.request';
import { Action, Filters, SubjectType }   from '../../types';

@Component({
    selector: 'filters-component',
    templateUrl: './filters.component.html',
    styleUrls: ['./filters.component.scss']
})
export class FiltersComponent implements OnInit {

    @Input() filters: Filters;
    @Input() autoCompleteConfig: { [key: string]: string[] } = {};

    @Output() changed = new EventEmitter<{ name: string, value: string }>();
    @Output() performSearch = new EventEmitter<Filters>();

    filtersForm: FormGroup;
    autocompleteData: { [key: string]: string[] } = {};

    readonly subjectTypes: string[] = Object.values(SubjectType);
    readonly actions: string[] = Object.values(Action);

    /**
     * @param formBuilder
     * @param modalService
     * @param filtersFormValidatorService
     * @param filtersFormAutocompleteService
     */
    constructor(private readonly formBuilder: FormBuilder,
                private readonly modalService: BsModalService,
                private readonly filtersFormValidatorService: FiltersFormValidatorService,
                private readonly filtersFormAutocompleteService: FiltersFormAutocompleteService) {
    }

    /**
     * Creates the form, optionally setting values from the predefined search
     */
    ngOnInit(): void {
        this.filtersForm = this.formBuilder.group({});

        forEach(SearchRequest.fieldsMap, (value: string, key: string): void => {
            const filter = this.filters ? this.filters[key] : undefined;

            this.filtersForm.addControl(key, new FormControl(filter));
        });

        this.filtersFormValidatorService.setValidators(this.filtersForm);

        this.filtersFormAutocompleteService.autocompleteFields.forEach(field => {
            let subscription: Subscription = undefined;

            this.filtersForm.get(field).valueChanges.subscribe(value => {
                subscription && subscription.unsubscribe();

                if (!value) {
                    this.autocompleteData[field] = [];

                    return;
                }

                subscription = this.filtersFormAutocompleteService.getSuggestions({value, name: field})
                    .subscribe(suggestions => {
                        this.autocompleteData[field] = suggestions;
                    });
            });
        });

    }

    /**
     * Called upon submitting the search form
     */
    onSubmit(): void {
        if (!this.filtersForm.valid) {
            this.modalService.show(WarningModalWindowComponent, {
                initialState: {
                    bodyText: Object.values(this.filtersForm.errors).shift()
                }
            });

            return;
        }

        this.performSearch.emit(this.filtersForm.value);
    }

    /**
     * Clears all filters
     */
    clear(): void {
        this.filtersForm.reset();
    }

}
