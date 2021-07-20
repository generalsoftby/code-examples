import { inject, TestBed } from '@angular/core/testing';

import { FiltersStateService } from './filters-state.service';
import { Action, Filters }     from '../types';
import { SearchRequest }       from '../request/search.request';

describe('FiltersStateService', () => {
    let filtersStateService: FiltersStateService;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [FiltersStateService],
            imports: []
        });

        filtersStateService = TestBed.get(FiltersStateService);
    });

    it('should be created', inject([FiltersStateService], (service: FiltersStateService) => {
        expect(service).toBeTruthy();
        expect(service).toBe(filtersStateService);
    }));

    it('should be undefined after creation', () => {
        expect(filtersStateService.get()).toBeUndefined();
    });

    it('should be defined after setting', () => {
        const filters: Filters = {
            action: Action.CREATE
        };

        filtersStateService.set(new SearchRequest(filters));

        expect(filtersStateService.get().filters).toBe(filters);
    });

    it('should be undefined after pulling', () => {
        const filters: Filters = {
            action: Action.CREATE
        };

        filtersStateService.set(new SearchRequest(filters));

        expect(filtersStateService.pull().filters).toBe(filters);
        expect(filtersStateService.get()).toBeUndefined();
    });
});
