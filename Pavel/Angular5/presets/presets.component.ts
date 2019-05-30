import { Component, Injectable, OnInit } from '@angular/core';
import { BsModalService, ModalOptions }  from 'ngx-bootstrap';
import * as forEach                      from 'lodash/forEach';
import { Router }                        from '@angular/router';

import { Action, ApiResponse, Preset, Query } from '../../types';
import { PresetService }                      from '../../services/preset.service';
import { WarningModalWindowComponent }        from '../../../shared/components/warning-modal-window/warning-modal-window.component';
import { SearchRequest }                      from '../../request/search.request';
import { FiltersStateService }                from '../../services/filters-state.service';
import { FiltersFormAutocompleteService }     from '../../services/filters-form-autocomplete.service';
import { BaseAutocompleteComponent }          from '../../../shared/components/base-autocomplete/base-autocomplete.component';

/**
 * Component to display and search with predefined search.
 */
@Component({
    selector: 'presets',
    templateUrl: './presets.component.html',
    styleUrls: ['./presets.component.scss']
})
@Injectable()
export class PresetsComponent implements OnInit {

    presets: Preset[] = [];

    readonly values: any[] = [];

    readonly actions: string[] = Object.values(Action);

    readonly autocompleteData: string[][] = [];

    readonly autocompletes: BaseAutocompleteComponent[] = [];

    private readonly errorModalConfig: ModalOptions = {
        initialState: {
            bodyText: 'PRESETS.EMPTY_SEARCH_MSG'
        }
    };

    /**
     * @param presetService
     * @param modalService
     * @param filtersState
     * @param router
     * @param autocompleteService
     */
    constructor(private readonly presetService: PresetService,
                private readonly modalService: BsModalService,
                private readonly filtersState: FiltersStateService,
                private readonly router: Router,
                private readonly autocompleteService: FiltersFormAutocompleteService) {
    }

    /**
     * Gets the list of presets
     */
    ngOnInit(): void {
        let i = 0;
        this.presetService.get().subscribe((data: ApiResponse<Preset>): void => {
            this.presets = data.data.map((preset: Preset): Preset => {
                preset.variables = preset.query.match(/\$([^\$]*)\$/g);
                this.values.push({});
                this.autocompleteData[i++] = [];

                return preset;
            });
        });
    }

    /**
     * Perfoms search action
     *
     * @param {Preset} preset
     * @param {number} index
     */
    search(preset: Preset, index: number): void {
        const value = this.values[index];

        if (Object.values(value).filter((item) => item).length !== preset.variables.length) {
            this.modalService.show(WarningModalWindowComponent, this.errorModalConfig);

            return;
        }

        let query = preset.query;

        forEach(value, (value: string, key: string): void => {
            query = query.replace(key, value);
        });

        const esQuery: Query = JSON.parse(query),
            request: SearchRequest = SearchRequest.fromQuery(esQuery)
        ;

        this.filtersState.set(request);

        this.router.navigate(['search']);
    }

    /**
     * Clears the input of the row
     *
     * @param {number} index
     */
    clear(index: number): void {
        this.values[index] = {};
    }

    /**
     * Returns the placeholder for a field
     *
     * @param {Preset} preset
     * @param {number} index
     *
     * @return string
     */
    getPlaceholder(preset: Preset, index: number): string {
        return preset.placeHolder.split(', ')[index];
    }

    /**
     * Saves the autocomplete component to storage for further usages.
     *
     * @param component
     * @param index
     */
    autocompleteCreated(component: BaseAutocompleteComponent, index: number): void {
        this.autocompletes[index] = component;
    }

    /**
     * Autocomplete input has been changed.
     *
     * @param preset
     * @param $event
     * @param index
     */
    change(preset: Preset, $event, index: number): void {
        $event.stopPropagation();
        $event.preventDefault();

        let query = preset.query;

        const esQuery: Query = JSON.parse(query),
            value: string = $event.target.value
        ;

        if (!value) {
            return;
        }

        this.autocompleteService.getPresetSuggestions({
                name: Object.keys(esQuery.query.match).shift(),
                value: value
            }
        ).subscribe((res) => {
            this.autocompleteData[index] = res;
            this.autocompletes[index].open();
        });
    }

    /**
     * Called upon a value in a autocomplete being selected.
     *
     * @param value
     * @param index
     * @param field
     */
    selectValue(value, index: number, field: any): void {
        this.values[index][field] = value;
    }
}
