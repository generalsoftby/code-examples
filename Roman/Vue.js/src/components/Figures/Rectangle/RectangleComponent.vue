<template>
  <div class="container row">
    <div class="col-md-12 mb-12">

      <TitleMessage :typeFigures='APP.types.rectangle'></TitleMessage>

      <div class="row form-content">

        <div class='col-md-6 mb-6'>
          <b-form @submit="addRectangle" class="form-row">

            <div class="form-row">
              <div class="col-md-6 mb-6">
                <label for="x1" class="text-left">Coordinate Х1:</label>
                <b-form-input 
                  id='x1' 
                  v-model.number='x1'
                  type='number'
                  placeholder='10' 
                  :min='minCoordinate' 
                  :max='maxCoordinate' 
                  step='0.1'   
                  :state="x1State"
                ></b-form-input>
                <b-form-invalid-feedback id="input-live-feedback" class="text-left">
                  Plese enter valid coordinate
                </b-form-invalid-feedback>
              </div>
              
              <div class="col-md-6 mb-6">
                <label for="y1" class="text-left">Coordinate Y1:</label>
                <b-form-input 
                  id='y1' 
                  v-model.number='y1'
                  type='number'
                  placeholder='10' 
                  :min='minCoordinate' 
                  :max='maxCoordinate' 
                  step='0.1'   
                  :state="y1State"
                ></b-form-input>
                <b-form-invalid-feedback id="input-live-feedback" class="text-left">
                  Plese enter valid coordinate
                </b-form-invalid-feedback>
              </div>
            </div>

            <div class="form-row inputs-top">
              <div class="col-md-6 mb-6">
                <label for="x2" class="text-left">Coordinate Х2:</label>
                <b-form-input 
                  id='x2' 
                  v-model.number='x2'
                  type='number'
                  placeholder='20' 
                  :min='minCoordinate' 
                  :max='maxCoordinate' 
                  step='0.1'   
                  :state="x2State"
                ></b-form-input>
                <b-form-invalid-feedback id="input-live-feedback" class="text-left">
                  Plese enter valid coordinate
                </b-form-invalid-feedback>
              </div>

              <div class="col-md-6 mb-6">
                <label for="y2" class="text-left">Coordinate Y2:</label>
                <b-form-input 
                  id='y2' 
                  v-model.number='y2'
                  type='number'
                  placeholder='20' 
                  :min='minCoordinate' 
                  :max='maxCoordinate' 
                  step='0.1'   
                  :state="y2State"
                ></b-form-input>
                <b-form-invalid-feedback id="input-live-feedback" class="text-left">
                  Plese enter valid coordinate
                </b-form-invalid-feedback>
              </div>
            </div>

            <b-button 
              type="submit"
              class="add-rectangle-button"
              :variant='x1State && y1State && x2State && y2State ? "success": "danger"' 
              :disabled='!x1State || !y1State || !x2State || !y2State || responseIsSuccess'
            >
              Add new {{ APP.types.rectangle }}
              <ButtonSpinner v-if="responseIsSuccess"></ButtonSpinner>
            </b-button>         

          </b-form>
        </div>
        
        <div class="col-md-6 mb-6 text-center">
          <FiguresInformation formula='S=a·b' imageName='rectangle.png'></FiguresInformation>
        </div>
        
      </div>
      
    </div>
  </div>
</template>

<script>
import TitleMessage from '@/components/Figures/TitleMessage'
import FiguresInformation from '@/components/Figures/FiguresInformation'
import ButtonSpinner from '@/components/ButtonSpinner'
import { APP } from '@/application-constants'

export default {
  name: 'Rectangle',
  components: {
    TitleMessage,
    FiguresInformation,
    ButtonSpinner
  },
  props: ['responseIsSuccess'],
  data() {
    return {
      x1: 10,
      y1: 10,
      x2: 20,
      y2: 20,
      minCoordinate: -10000,
      maxCoordinate: 10000,
      APP
    }
  },
  computed: {
    x1State() {
      if (this.x1 > this.minCoordinate && this.x1 <= this.maxCoordinate && this.x1 != 0) {
        return true;
      }
      return false
    },
    y1State() {
      if (this.y1 > this.minCoordinate && this.y1 <= this.maxCoordinate && this.y1 != 0) {
        return true;
      }
      return false
    },
    x2State() {
      if (this.x2 > this.minCoordinate && this.x2 <= this.maxCoordinate && this.x2 != 0) {
        return true;
      }
      return false
    },
    y2State() {
      if (this.y2 > this.minCoordinate && this.y2 <= this.maxCoordinate && this.y2 != 0) {
        return true;
      }
      return false
    },
  },

  methods: {

    getRectangleArea() {
      const a = Number(this.x2) - Number(this.x1);
      const b = Number(this.y2) - Number(this.y1);
      const area = Math.abs(a*b);
      return Math.round(area * 10000)/10000;
    },

    addRectangle(evt) {
      evt.preventDefault();
      const area = this.getRectangleArea();
      const data = {
        x1: this.x1,
        y1: this.y1,
        x2: this.x2,
        y2: this.y2,
      }
      this.$emit('addFigures', APP.types.rectangle, area, data);
    },
    
  }
  
}
</script>

<style scope>
  .form-content{
    margin: 25px;
  }
  .text-left{
    float: left;
  }
  .inputs-top{
    margin-top: 15px;
  }
  .add-rectangle-button{
    margin-top: 15px;
  }
</style>