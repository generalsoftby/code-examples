import { Component } from '@angular/core';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Figure } from '../models/figure'
import { FigureService } from '../services/figure.service';

@Component({
  selector: 'app-figures',
  templateUrl: './figures.component.html',
  styleUrls: ['./figures.component.css'],
})

export class FiguresComponent {
  
  public figures: Figure[];
  private figuresId: number;
  public showSpinner: boolean = true;
  public showDeleteSpinner: boolean = false;
  public showAlertMessage: boolean = false;
  public alertMessage: string;

  public page: number = 1;
  public pageSize: number = 5;
  public maxSize: number = 5;
  public collectionSize: number;

  constructor (
    private figureService: FigureService,
    private modalService: NgbModal, 
  ) {}

  open(content, id) {
    this.modalService.open(content);
    this.figuresId = id;
  }

  ngOnInit() {
    this.getFigures();
  }
  
  private getFigures(): void {
    this.figureService.getFigures()
      .subscribe(figures => {
        this.figures = figures.sort((first,second) => first.area-second.area);
        this.showSpinner = false;
        this.collectionSize = this.figures.length;
      });
  }
  
  public deleteFigures(): void {
    const id = this.figuresId;
    console.log(id);
    this.showDeleteSpinner = true;

    this.figureService.deleteFigure(id)
      .subscribe( response => {
        if(response.success){
          const index = this.figures.findIndex(figure => figure.id === id);

          this.figures.splice(index,1);
          
          this.showDeleteSpinner = false;
          this.modalService.dismissAll();

          this.alertMessage = `${id}`;
          this.showAlertMessage = true;
        }
      });
    
    

  }

}