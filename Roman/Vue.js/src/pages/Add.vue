<template>
  <div class="container">
    <div class="add-figures-section">
      <div class="row">
        <div class="col-3">
          <b-list-group class="text-center">
            <b-list-group-item router-link :to='APP.routes.pathAddFigures+"/"+APP.types.circle'>{{ APP.types.circle }}</b-list-group-item>
            <b-list-group-item router-link :to='APP.routes.pathAddFigures+"/"+APP.types.square'>{{ APP.types.square }}</b-list-group-item>
            <b-list-group-item router-link :to='APP.routes.pathAddFigures+"/"+APP.types.rectangle'>{{ APP.types.rectangle }}</b-list-group-item>
            <b-list-group-item router-link :to='APP.routes.pathAddFigures+"/"+APP.types.triangle'>{{ APP.types.triangle }}</b-list-group-item>
          </b-list-group>
        </div>
        <div class="col-9 text-center">
          <!-- <keep-alive> -->
            <router-view v-on:addFigures='addFigures' :responseIsSuccess='responseIsSuccess'></router-view>
          <!-- </keep-alive> -->
          
        </div>
      </div>
      
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { APP } from '../application-constants'
import swal from 'sweetalert'

export default {
  name: 'add',
  data() {
    return {
      responseIsSuccess: false,
      APP
    }
  },
  methods: {
    addFigures(type, area, data){
      this.responseIsSuccess = true;
      const newFigure = {
        type: type,
        area: area,
        data: data
      };
      
      axios
      .post(`${APP.endpoints.baseUrl}${APP.endpoints.figures}`, newFigure)
      .then(response => {
        if(response.data.success){
          this.responseIsSuccess = false;
          swal(`${type} #${response.data.id} added`, `Area: ${area}`, 'success');
        }
      })
    },

  }
}
</script>

<style scoped>
  .add-figures-section{
    margin-top: 35px;   
  }
  .addAlert{
    margin: 0 50px; 
  }
  .addAlert p{
    margin-bottom: 0px;
  }
</style>
