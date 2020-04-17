import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { Observable } from 'rxjs';

import { Figure } from '../models/figure';
import { APP } from '../application-constants';
import { ResponseMessage } from '../models/response-message.mode';

@Injectable({
  providedIn: 'root'
})

export class FigureService {

  constructor(
    private http: HttpClient
  ) {}

  public getFigures(): Observable<Figure[]> {
    return this.http.get<Figure[]>(`${APP.endpoints.baseUrl}${APP.endpoints.figures}`);
  }

  public addFigure(figure: Figure): Observable<ResponseMessage> {
    return this.http.post<ResponseMessage>(`${APP.endpoints.baseUrl}${APP.endpoints.figures}`, figure);
  }

  public deleteFigure(figureId: number): Observable<ResponseMessage> {
    return this.http.delete<ResponseMessage>(`${APP.endpoints.baseUrl}${APP.endpoints.figures}?id=${figureId}`);
  }

}

