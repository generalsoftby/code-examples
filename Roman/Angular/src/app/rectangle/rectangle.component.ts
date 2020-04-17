import { Component, EventEmitter, Output, Input } from '@angular/core';
import { FormGroup, FormControl, Validators } from '@angular/forms';

import { Figure } from '../models/figure';

@Component({
  selector: 'app-rectangle',
  templateUrl: 'rectangle.component.html',
  styleUrls: ['rectangle.component.css']
})

export class RectangleComponent{
  
  @Output() addFigureEvent = new EventEmitter<Figure>();
  @Input() responseIsSuccess: boolean;

  private rectangleControl : FormGroup;
  
  ngOnInit() {
    this.rectangleControl = new FormGroup({
      X1: new FormControl(10, [Validators.required]),
      Y1: new FormControl(10, [Validators.required]),
      X2: new FormControl(20, [Validators.required]),
      Y2: new FormControl(20, [Validators.required]),
    });
  }


  private addRectangle(): void {
    const area = this.getRectangleArea();

    this.addFigureEvent.emit({
      type: 'Rectangle',
      area: area
    })
  }

  public getRectangleArea(): number {
    const points = this.rectangleControl.value;

    const x1 = points['X1'];
    const y1 = points['Y1'];
    const x2 = points['X2'];
    const y2 = points['Y2'];

    const a = x2-x1;
    const b = y2-y1;

    return Math.abs(a*b);
  }

}