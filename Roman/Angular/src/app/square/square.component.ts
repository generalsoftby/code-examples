import { Component, EventEmitter, Output, Input } from "@angular/core";
import { FormControl } from '@angular/forms';

import { FigureService } from '../services/figure.service';
import { APP } from '../application-constants';
import { Figure } from '../models/figure';

@Component({
  selector: 'app-square',
  templateUrl: 'square.component.html',
  styleUrls: ['square.component.css']
})

export class SquareComponent{

  @Output() addFigureEvent = new EventEmitter<Figure>();
  @Input() responseIsSuccess: boolean;

  private squareControl : FormControl;

  ngOnInit() {
    this.squareControl = new FormControl(10, [this.lengthValidator]);
  }

  private addSquare(): void {  
    const area = this.getSquareArea();

    this.addFigureEvent.emit({
      type: 'Square',
      area: area
    })
  }

  public getSquareArea(): number {
    return Math.pow(this.squareControl.value,2);
  }

  private lengthValidator(squareControl: FormControl){
    if(squareControl.value > 0){
      return null;
    }
    return { lengthValidator: {message: 'No, well, of course I can square it and get a square, but where have you seen it so long?'} };
  }

}  
