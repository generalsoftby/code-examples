<template>
  <div class="container row">
    <div class="col-md-12 mb-12">

      <TitleMessage :typeFigures='APP.types.triangle'></TitleMessage>

      <div class="row form-content">

        <div class='col-md-6 mb-6'>
          <b-form @submit="addTriangle" class="form-row">

            <b-form-group>
            <label for='length1' class="text-left">Side length 1:</label>
            <b-form-input 
              id='length1' 
              v-model.number='length1'
              type='number'
              placeholder='3' 
              :min='minLength' 
              :max='maxLength' 
              step='0.1'   
              :state="length1State && triangleIsValid"
            ></b-form-input>
            <b-form-invalid-feedback id="input-live-feedback" class="text-left">
              Plese enter valid length
            </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group>
            <label for='length2' class="text-left">Side length 2:</label>
            <b-form-input 
              id='length2' 
              v-model.number='length2'
              type='number'
              placeholder='4' 
              :min='minLength' 
              :max='maxLength' 
              step='0.1'   
              :state="length2State && triangleIsValid"
            ></b-form-input>
            <b-form-invalid-feedback id="input-live-feedback" class="text-left">
              Plese enter valid length
            </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group>
            <label for='length3' class="text-left">Side length 3:</label>
            <b-form-input 
              id='length3' 
              v-model.number='length3'
              type='number'
              placeholder='5' 
              :min='minLength' 
              :max='maxLength' 
              step='0.1'   
              :state="length3State && triangleIsValid"
            ></b-form-input>
            <b-form-invalid-feedback id="input-live-feedback" class="text-left">
              Plese enter valid length
            </b-form-invalid-feedback>
            </b-form-group>

            <b-button 
              type="submit"
              class="add-triangle-button"
              :variant='length1State && length2State && length3State && triangleIsValid ? "success": "danger"' 
              :disabled='!length1State || !length2State || !length3State || !triangleIsValid || responseIsSuccess'
            >
              Add new {{ APP.types.triangle }}
              <ButtonSpinner v-if="responseIsSuccess"></ButtonSpinner>
            </b-button>

          </b-form>
        </div>
        
        <div class="col-md-6 mb-6 text-center">
          <FiguresInformation formula='S=√(p·(p-a)·(p-b)·(p-c))' imageName='triangle.png'></FiguresInformation>
        </div>

      </div>
      
      <b-alert v-if="!triangleIsValid" variant="danger" show class="dangerAlert text-center">I can not build a triangle with such a side</b-alert>


    </div>
  </div>
</template>

<script>
import TitleMessage from '@/components/Figures/TitleMessage'
import FiguresInformation from '@/components/Figures/FiguresInformation'
import ButtonSpinner from '@/components/ButtonSpinner'
import { APP } from '@/application-constants'

export default {
  name: 'Triangle',
  components: {
    TitleMessage,
    FiguresInformation,
    ButtonSpinner
  },
  props: ['responseIsSuccess'],
  data() {
    return {
      length1: 3,
      length2: 4,
      length3: 5,
      minLength: 0,
      maxLength: 10000,
      APP
    }
  },
  computed: {
    length1State() {
      if (this.length1 > this.minLength && this.length1 <= this.maxLength) {
        return true;
      }
      return false
    },
    length2State() {
      if (this.length2 > this.minLength && this.length2 <= this.maxLength) {
        return true;
      }
      return false
    },
    length3State() {
      if (this.length3 > this.minLength && this.length3 <= this.maxLength) {
        return true;
      }
      return false
    },
    triangleIsValid(){
      if (this.length1+this.length2<=this.length3 || this.length1+this.length3<=this.length2 || this.length2+this.length3<=this.length1){
        return false;
      }
      return true;
    }
  },
  methods: {

    getTriangleArea() {
      const p = (Number(this.length1) + Number(this.length2) + Number(this.length3))/2;
      const area = Math.sqrt(p * ( p - Number(this.length1) ) * ( p - Number(this.length2) ) * ( p - Number(this.length3) ) );
      return Math.round(area * 10000)/10000;
    },

    addTriangle(evt) {
      evt.preventDefault();
      const area = this.getTriangleArea();
      const data = {
        length1: this.length1,
        length2: this.length2,
        length3: this.length3,
      }
      this.$emit('addFigures', APP.types.triangle, area, data);
    },
    
  }
}
</script>

<style scope>

  .form-content{
    margin: 25px;
  }
  .dangerAlert{
    margin: 0 50px;
  }

</style>
