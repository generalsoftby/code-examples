import { Component } from '@angular/core';
import { Figure } from '../models/figure';
import { FigureService } from '../services/figure.service';

@Component({
  selector: 'app-add-figures',
  templateUrl: 'add-figures.component.html',
  styleUrls: ['add-figures.component.css'],
})


export class AddFiguresComponent {
  public alertMessage: string;
  public showAlertMessage = false;
  public responseIsSuccess = false;

  constructor (
    private figureService: FigureService
  ) {}

  public reactOnAddFigureEvent(figure: Figure){
    this.responseIsSuccess = true;

    this.figureService.addFigure(figure)
      .subscribe( response => {
        if(response.success){
          this.showAlertMessage = true;
          this.alertMessage = `${figure.type} #${(response as Figure).id} with ${Math.round(figure.area * 1000) / 1000} area successfully added`;
          this.responseIsSuccess = false;
        }
      });
  }
}