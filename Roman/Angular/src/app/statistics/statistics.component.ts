import { Component } from '@angular/core';

import { Figure } from '../models/figure';
import { FigureService } from '../services/figure.service';

@Component({
  selector: 'app-statistics',
  templateUrl: './statistics.component.html',
  styleUrls: ['./statistics.component.css'],
})

export class StatisticsComponent {
  public showSpinner: boolean = true;
  public figures: Figure[];
  public statistic: {}[];

  constructor (
    private figureService: FigureService
  ) {}

  ngOnInit() {
    this.getFigures()
  }
  
  private getFigures(): void {
    this.figureService.getFigures()
      .subscribe(figures => {
        this.figures = figures;
        this.getStatistic();
      });
  }
  
  public getStatistic(): void{
    let totalArea = 0; 
    let circlesArea = 0; 
    let squaresArea = 0; 
    let rectanglesArea = 0; 
    let trianglesArea = 0; 

    const Circles = this.figures.filter(figure => figure.type === 'Circle'); 
    for (let item of Circles){ 
      circlesArea += item.area; 
      totalArea += item.area; 
    } 

    const Squares = this.figures.filter(figure => figure.type === 'Square'); 
    for (let item of Squares){ 
      squaresArea += item.area; 
      totalArea += item.area; 
    } 

    const Rectangles = this.figures.filter(figure => figure.type === 'Rectangle'); 
    for (let item of Rectangles){ 
      rectanglesArea += item.area; 
      totalArea += item.area; 
    } 

    const Triangles = this.figures.filter(figure => figure.type === 'Triangle'); 
    for (let item of Triangles){ 
      trianglesArea += item.area; 
      totalArea += item.area; 
    } 

    const circlePercent = 100*circlesArea/totalArea; 
    const squarePercent = 100*squaresArea/totalArea; 
    const rectanglePercent = 100*rectanglesArea/totalArea; 
    const trianglePercent = 100*trianglesArea/totalArea; 

    this.statistic = [
      {
        type: 'Circle',
        area: circlesArea,
        percent: Math.round(circlePercent * 1000) / 1000 
      },
      {
        type: 'Square',
        area: squaresArea,
        percent: Math.round(squarePercent * 1000) / 1000 
      },
      {
        type: 'Rectangle',
        area: rectanglesArea,
        percent:  Math.round(rectanglePercent * 1000) / 1000  
      },
      {
        type: 'Triangle',
        area: trianglesArea,
        percent:  Math.round(trianglePercent * 1000) / 1000 
      },
    ]

    this.showSpinner = false;

  }

  public checkType(type: string){
    switch (type) {
      case 'Circle':
        return 'warning';

      case 'Square':
        return 'danger';

      case 'Rectangle':
        return 'primary';

      case 'Triangle':
        return 'success'; 

      default:
        return 'secondary';
    }

  }

}
