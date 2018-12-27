import { inject, TestBed }              from '@angular/core/testing';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { of }                           from 'rxjs/observable/of';

import { ApiService }    from './api.service';
import { ApiResponse }   from '../types';
import { SearchRequest } from '../request/search.request';

describe('ApiService', () => {
    let apiService: ApiService;

    const promisedData = require('../../../assets/events.json');

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [ApiService],
            imports: [HttpClientModule]
        });

        apiService = TestBed.get(ApiService);

        let httpClient = TestBed.get(HttpClient);
        spyOn(httpClient, 'post').and.returnValue(of(promisedData));
    });

    it('should be created', inject([ApiService], (service: ApiService) => {
        expect(service).toBeTruthy();
        expect(service).toBe(apiService);
    }));

    it('should retrieve data', inject([ApiService], (service: ApiService) => {
        const request = new SearchRequest({});

        service.get('event', request).subscribe((response: ApiResponse<Event>) => {
            expect(response.totalPages).toBe(367);
            expect(response.total).toBe(5492);
            expect(response.data.length).toBe(10);
        });
    }));

    it('should retrieve suggestions', inject([ApiService], (service: ApiService) => {
        service.getSuggestions('event', null).subscribe((response: ApiResponse<Event>) => {
            expect(response).toBeTruthy();
        });
    }));
});
