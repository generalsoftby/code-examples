<template>
  <div class="container row">
    <div class="col-md-12 mb-12">

      <TitleMessage :typeFigures='APP.types.circle'></TitleMessage>

      <div class="row form-content">

        <div class='col-md-6 mb-6'>
          
          <b-form @submit="addCircle" class="form-row">
            <label for='radius' class="text-left">Radius length:</label>
            <b-form-input 
              id='radius' 
              v-model.number='radius'
              type='number'
              placeholder='10' 
              :min='minRadius' 
              :max='maxRadius' 
              step='0.1'   
              :state="radiusState"
            ></b-form-input>
            <b-form-invalid-feedback id="input-live-feedback" class="text-left">
              Plese enter valid radius
            </b-form-invalid-feedback>

            <b-button 
              type="submit"
              class="add-circle-button"
              :variant='radiusState ? "success": "danger"' 
              :disabled='!radiusState || responseIsSuccess'
            >
              Add new {{APP.types.circle}}
              <ButtonSpinner v-if="responseIsSuccess"></ButtonSpinner>
            </b-button>

          </b-form>
        </div>
        
        <div class="col-md-6 mb-6 text-center">
          <FiguresInformation formula='S=πr²' imageName='circle.png'></FiguresInformation>
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
  name: 'CircleComponent',
  components: {
    TitleMessage,
    FiguresInformation,
    ButtonSpinner
  },
  props: ['responseIsSuccess'],
  data() {
    return {
      radius: 10,
      minRadius: 0,
      maxRadius: 10000,
      APP
    }
  },
  computed: {
    radiusState() {
      if (this.radius > this.minRadius && this.radius <= this.maxRadius) {
        return true;
      }
      return false
    }
  },
  methods: {

    getCircleArea() {
      const area = Math.pow(Number(this.radius),2) * Math.PI;
      return Math.round(area * 10000)/10000;
    },

    addCircle(evt) {
      evt.preventDefault();
      const area = this.getCircleArea();
      const data = {
        radius: this.radius
      }
      this.$emit('addFigures', APP.types.circle, area, data);
    },
    
  }
}
</script>

<style scope>
  .form-content{
    margin: 25px;
  }
  .add-circle-button{
    margin-top: 15px;
  }
</style>
