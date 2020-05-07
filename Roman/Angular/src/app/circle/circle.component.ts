import { Component, Output, EventEmitter, Input } from '@angular/core';
import { FormControl} from '@angular/forms';

import { Figure } from '../models/figure';

@Component ({
  selector: 'app-circle',
  templateUrl: 'circle.component.html',
  styleUrls: ['circle.component.css'],
})

export class CircleComponent{

  @Output() addFigureEvent = new EventEmitter<Figure>();
  @Input() responseIsSuccess: boolean;

  private circleControl: FormControl;

  ngOnInit() {
    this.circleControl = new FormControl(10, [this.radiusValidator]);
  }
  
  private getCircleArea(): number {
    return Math.pow(this.circleControl.value,2) * Math.PI;
  }
  
  public addCircle(): void {
    const area = this.getCircleArea();

    this.addFigureEvent.emit({
      type: 'Circle',
      area: area
    })    
  }

  private radiusValidator(circleControl: FormControl){
    if(circleControl.value > 0){
      return null;
    }
    return { radiusValidator: {message: 'No, well, of course I can square it and get a square, but where have you seen it so long?'} };
  }

}