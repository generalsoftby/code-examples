<template>

  <Spinner v-if='!getFiguresIsSuccess'></Spinner>

  <div v-else class="container statistics-section">

    <NoFiguresAlert v-if="figures.length === 0"></NoFiguresAlert>

    <div v-else class="text-center">
      <b-table 
        bordered
        hover
        striped
        fixed
        caption-top
        :fields="fields"
        :items="getStatistics"
      >
        <template slot="table-caption">Statistics</template>
        <template slot="percent" slot-scope="data">
          {{ data.item.percent }}%
        </template>
        
      </b-table>

      <div class="mt-4">
        <b-progress 
          v-for="(item, index) in getStatistics" 
          :key="index" 
          :variant="checkType(item.type)"
          show-progress 
          striped 
          animated 
          height="25px" 
          class="mb-3 progress-text"
        >
          <b-progress-bar 
            :value="item.percent" 
          >
            <strong>{{item.type}}: {{item.percent}}% / {{item.area}}</strong>
          </b-progress-bar>
        </b-progress>
      </div>

    </div>
  </div>
  
</template>

<script>
import axios from 'axios'
import Spinner from '@/components/Spinner'
import NoFiguresAlert from '@/components/NoFiguresAlert'
import { APP } from '../application-constants'

export default {
  name: 'statistics',
  components: {
    Spinner,
    NoFiguresAlert,
  },
  data() {
    return {
      figures: [],
      getFiguresIsSuccess: false,
      APP,
      fields: [
        {
          key: 'type',
          label: 'Figure type',
          sortable: true
        },
        {
          key: 'area',
          sortable: true
        },
        {
          key: 'percent',
          sortable: true,
        },
      ]
    }
  },

  mounted(){
      axios
      .get(`${APP.endpoints.baseUrl}${APP.endpoints.figures}`)
      .then(response => {
        this.figures = response.data;
        this.getFiguresIsSuccess = true; 
      });
    }, 

  computed: {
    getStatistics() {
      let totalArea = 0; 
      let circlesArea = 0; 
      let squaresArea = 0; 
      let rectanglesArea = 0; 
      let trianglesArea = 0; 

      const Circles = this.figures.filter(figure => figure.type === APP.types.circle); 
      for (let item of Circles){ 
        circlesArea += item.area; 
        totalArea += item.area; 
      } 

      const Squares = this.figures.filter(figure => figure.type === APP.types.square); 
      for (let item of Squares){ 
        squaresArea += item.area; 
        totalArea += item.area; 
      } 

      const Rectangles = this.figures.filter(figure => figure.type === APP.types.rectangle); 
      for (let item of Rectangles){ 
        rectanglesArea += item.area; 
        totalArea += item.area; 
      } 

      const Triangles = this.figures.filter(figure => figure.type === APP.types.triangle); 
      for (let item of Triangles){ 
        trianglesArea += item.area; 
        totalArea += item.area; 
      } 

      const circlePercent = 100*circlesArea/totalArea; 
      const squarePercent = 100*squaresArea/totalArea; 
      const rectanglePercent = 100*rectanglesArea/totalArea; 
      const trianglePercent = 100*trianglesArea/totalArea; 

      const statistics = [
        {
          type: APP.types.circle,
          area: circlesArea,
          percent: Math.round(circlePercent * 1000) / 1000 
        },
        {
          type: APP.types.square,
          area: squaresArea,
          percent: Math.round(squarePercent * 1000) / 1000 
        },
        {
          type: APP.types.rectangle,
          area: rectanglesArea,
          percent:  Math.round(rectanglePercent * 1000) / 1000  
        },
        {
          type: APP.types.triangle,
          area: trianglesArea,
          percent:  Math.round(trianglePercent * 1000) / 1000 
        },
      ]
      return statistics;
    }
  },
  
  methods: {
    checkType(type){
      switch (type) {
        case APP.types.circle:
          return 'warning';

        case APP.types.square:
          return 'danger';

        case APP.types.rectangle:
          return 'primary';

        case APP.types.triangle:
          return 'success'; 

        default:
          return 'secondary';
      }
    }
  },

}
</script>

<style scoped>
  .statistics-section{
    margin-top: 15px;   
  }
  .progress-text{
    font-size: 15px;
  }
</style>