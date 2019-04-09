import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule } from '@angular/router';
import { GridModule } from '@progress/kendo-angular-grid';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';

import { AppComponent } from './app.component';
import { appRoutes } from './app.routes';
import { StartComponent } from './core/start/start.component';
import { SuccessSnackComponent } from './shared/success-snack/success-snack';
import { StoreModule } from '@ngrx/store';
import { reducers, metaReducers } from './store/reducers';

@NgModule({
  declarations: [AppComponent, StartComponent, SuccessSnackComponent],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    CommonModule,
    RouterModule.forRoot(appRoutes),
    GridModule,
    StoreModule.forRoot(reducers, { metaReducers })
  ],
  entryComponents: [SuccessSnackComponent],
  bootstrap: [AppComponent]
})
export class AppModule {}
