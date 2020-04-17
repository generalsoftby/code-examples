import { Component, Output, EventEmitter, Input } from '@angular/core';
import { FormGroup, ValidatorFn, ValidationErrors, FormControl, Validators } from '@angular/forms';

import { Figure } from '../models/figure';

@Component({
  selector: 'app-triangle',
  templateUrl: 'triangle.component.html',
  styleUrls: ['triangle.component.css']
})

export class TriangleComponent{

  @Output() addFigureEvent = new EventEmitter<Figure>();
  @Input() responseIsSuccess: boolean;

  private triangleControl: FormGroup;


  ngOnInit() {
    this.triangleControl = new FormGroup({
      a : new FormControl(3, [this.lengthValidator]),
      b : new FormControl(4, [this.lengthValidator]),
      c : new FormControl(5, [this.lengthValidator]),
    }, { validators: this.triangleValidator });
  }

  private addTriangle(): void {
    const area = this.getTriangleArea();

    this.addFigureEvent.emit({
      type: 'Triangle',
      area: area
    })
        
  }

  public getTriangleArea(): number {
    const points = this.triangleControl.value;

    const a = points['a'];
    const b = points['b'];
    const c = points['c'];

    const p = (a+b+c)/2;
    return Math.sqrt(p*(p-a)*(p-b)*(p-c));

  }

  private lengthValidator(control: FormControl){
    if(control.value > 0){
      return null;
    }
    return { lengthValidator: {message: 'Where did you see the negative side length of the triangle?'} };
  }

  triangleValidator: ValidatorFn = (triangleControl: FormGroup): ValidationErrors | null => {

    const a = triangleControl.get('a').value;
    const b = triangleControl.get('b').value;
    const c = triangleControl.get('c').value;
    
    if (a+b<=c || a+c<=b || b+c<=a){
      return {triangleValidator: {message: 'I can not build a triangle with such a side'}};
    }
  
    return null;
  }

}