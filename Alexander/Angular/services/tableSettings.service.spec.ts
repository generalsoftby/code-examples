import { inject, TestBed } from '@angular/core/testing';

import { TableSettingsService }              from './tableSettings.service';
import { TranslateModule, TranslateService } from '@ngx-translate/core';

describe('TableSettingsService', () => {
    beforeEach(() => {
        TestBed.configureTestingModule({
            imports: [TranslateModule.forRoot()],
            providers: [TableSettingsService, TranslateService]
        });
    });

    it('should be created', inject([TableSettingsService], (service: TableSettingsService) => {
        expect(service).toBeTruthy();
    }));
});
