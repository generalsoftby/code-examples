import { Observable } from 'rxjs';

import { FiltersFormAutocompleteService }                 from './filters-form-autocomplete.service';
import { ElasticSearchHit, ElasticSearchResponse, Event } from '../types';
import createSpy = jasmine.createSpy;

describe('FiltersFormAutocompleteService', () => {
    let service;
    let apiService;

    const records: ElasticSearchHit<Event>[] = [
        {
            _id: '',
            _index: '',
            _type: '',
            _source: {
                destinators: [{id: 'destinatorsId'}],
                subjects: [{
                    accessionNumber: 'testNumber',
                    sopClass: 'testClass',
                    studyIuid: 'testUid',
                    documentId: 42
                }],
                auditSource: 'sourceId'
            }
        }
    ];

    const apiResponse: ElasticSearchResponse<Event> = {
        hits: {
            total: records.length,
            hits: records
        }
    };

    beforeEach(() => {
        apiService = {
            getSuggestions: createSpy().and.callFake(() => {
                return Observable.of(apiResponse);
            })
        };

        service = new FiltersFormAutocompleteService(apiService);
    });

    it('should contain autocomplete fields', () => {
        expect(service.autocompleteFields).toBeDefined();
    });

    it('should create query', () => {
        let query = service.createQuery({
            name: 'source',
            value: 'testValue'
        });

        expect(query).toEqual({
            query: {
                multi_match: {
                    query: 'testValue',
                    fields: ['auditSource']
                }
            }
        });

        query = service.createQuery({
            name: 'subjects',
            value: 'testValue'
        });

        expect(query.query.multi_match.query).toEqual('testValue');
        expect(query.query.multi_match.fields).toContain('subjects.studyIuid');
        expect(query.query.multi_match.fields).toContain('subjects.accessionNumber');
        expect(query.query.multi_match.fields).toContain('subjects.sopClass');
        expect(query.query.multi_match.fields).toContain('subjects.patientId');
        expect(query.query.multi_match.fields).toContain('subjects.patientName');
        expect(query.query.multi_match.fields).toContain('subjects.documentId');
    });

    it('should parse suggestions', () => {
        let result = service.parseDestination(records);
        expect(result).toContain('destinatorsId');

        result = service.parseField(records, 'auditSource');
        expect(result).toContain('sourceId');

        result = service.parseSubjects(records, 'test');
        expect(result).toContain('testNumber');
    });

    it('should process a response', () => {
        service.getSuggestions({
            name: 'source',
            value: 'sourceId'
        }).subscribe(suggestions => {
            expect(suggestions).toContain('sourceId');
        });

        service.getSuggestions({
            name: 'destination',
            value: 'destinatorsId'
        }).subscribe(suggestions => {
            expect(suggestions).toContain('destinatorsId');
        });

        service.getSuggestions({
            name: 'subjects',
            value: 'test'
        }).subscribe(suggestions => {
            expect(suggestions).toContain('testNumber');
            expect(suggestions).toContain('testClass');
            expect(suggestions).toContain('testUid');
        });
    });

    it('should process presets response', () => {
        service.getPresetSuggestions({
            name: 'subjects.documentId',
            value: '42'
        }).subscribe(suggestions => {
            expect(suggestions).toContain(42);
        });

        service.getPresetSuggestions({
            name: 'destinators.id',
            value: 'destinatorsId'
        }).subscribe(suggestions => {
            expect(suggestions).toContain('destinatorsId');
        });
    });
});
