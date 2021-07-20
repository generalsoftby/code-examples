import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import * as uniq       from 'lodash/uniq';
import * as map        from 'lodash/map';
import * as get        from 'lodash/get';
import * as startsWith from 'lodash/startsWith';

import { SearchRequest }                               from '../request/search.request';
import { ElasticSearchHit, Event, Query, SubjectType } from '../types';
import { ApiService }                                  from './api.service';

/**
 * Filters form autocomplete service
 * Process autocomplete information
 */
@Injectable()
export class FiltersFormAutocompleteService {

    autocompleteFields: string[] = [
        'destination',
        'source',
        'subjects'
    ];

    /**
     * @param {ApiService} apiService
     */
    constructor(private readonly apiService: ApiService) {
    }

    /**
     * Gets suggestions from from server and parse it into an array
     *
     * @param {{name: string, value: string}} formField Filters form field for autocomplete
     *
     * @return {Observable<string[]>} An observable with suggestions
     */
    getSuggestions(formField: { name: string, value: string }): Observable<string[]> {
        const esField = SearchRequest.fieldsMap[formField.name];

        return this.apiService.getSuggestions('event', this.createQuery(formField), {size: '100'})
            .map(res => {
                const records = res.hits.hits;
                let suggestions = [];

                switch (formField.name) {
                    case 'destination':
                        suggestions = this.parseDestination(records);

                        break;
                    case 'subjects':
                        suggestions = this.parseSubjects(records, formField.value);

                        break;
                    default:
                        suggestions = this.parseField(records, esField);

                        break;
                }

                return uniq(suggestions);
            });
    }

    /**
     * Gets suggestions for presets
     *
     * @param {{name: string, value: string}} formField Presets form field for autocomplete
     *
     * @return {Observable<string[]>} An observable with suggestions
     */
    getPresetSuggestions(formField: { name: string, value: string }): Observable<string[]> {
        const query: Query = {
            query: {
                multi_match: {
                    query: formField.value,
                    fields: [formField.name]
                }
            }
        };

        return this.apiService.getSuggestions('event', query, {size: '100'})
            .map(res => {
                return uniq(res.hits.hits.map(({_source}) => {
                    const parts = formField.name.split('.');

                    return _source[parts[0]]
                        .map((subject) => {
                            return subject[parts[1]];
                        })
                        .filter((item: string): string => item).shift()
                        ;
                }));
            });
    }

    /**
     * Creates an query for elastic search
     *
     * @param {string} name
     * @param {string} value
     *
     * @return {Query} ES query
     */
    protected createQuery({name, value}): Query {
        const esField = SearchRequest.fieldsMap[name];
        let fields = [];

        if (name === 'subjects') {
            fields = [
                ...SearchRequest.subjectsMap[SubjectType.DOCUMENT],
                ...SearchRequest.subjectsMap[SubjectType.PATIENT],
                ...SearchRequest.subjectsMap[SubjectType.STUDY]
            ];
        } else {
            fields = [esField];
        }

        return {
            query: {
                multi_match: {
                    query: value,
                    fields
                }
            }
        };
    }

    /**
     * Parses destination form field
     *
     * @param {ElasticSearchHit<Event>[]} records A data from server
     *
     * @return {string[]} An array of suggestions
     */
    protected parseDestination(records: ElasticSearchHit<Event>[]): string[] {
        const suggestions = [];

        records.forEach(record => {
            const destinations = get(record, '_source.destinators');
            suggestions.push(...map(destinations, 'id'));
        });

        return suggestions;
    }

    /**
     * Parses destination form field
     *
     * @param {ElasticSearchHit<Event>[]} records A data from server
     * @param {string} filterString Value for suggestions to search for relevant information
     *
     * @return {string[]} An array of suggestions
     */
    protected parseSubjects(records: ElasticSearchHit<Event>[], filterString: string): string[] {
        const suggestions = [],
            props = [].concat(
                SearchRequest.subjectsMap[SubjectType.PATIENT],
                SearchRequest.subjectsMap[SubjectType.DOCUMENT],
                SearchRequest.subjectsMap[SubjectType.STUDY]
            ).map((prop) => {
                return prop.split('.').pop();
            });

        records.forEach(record => {
            const subjects = get(record, '_source.subjects');
            props.forEach(prop => {
                const filtered = map(subjects, prop).filter(suggestion => startsWith(suggestion, filterString));
                suggestions.push(...filtered);
            });
        });

        return suggestions;
    }

    /**
     * Parses any field except source and destination
     *
     * @param {ElasticSearchHit<Event>[]} records A data from server
     * @param {string} field Field that's needed to be parsed
     *
     * @return {string[]} An array of suggestions
     */
    protected parseField(records: ElasticSearchHit<Event>[], field: string): string[] {
        const suggestions = [];

        const path = '_source.' + field;

        records.forEach(record => {
            suggestions.push(get(record, path));
        });

        return suggestions;
    }
}
