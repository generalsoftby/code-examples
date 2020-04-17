<template>
  
  <Spinner v-if='!getFiguresIsSuccess'></Spinner>

  <div v-else class="container list-section">

    <NoFiguresAlert v-if="figures.length === 0"></NoFiguresAlert>

    <div v-else class="text-center">
      <b-table 
        bordered
        striped
        hover
        fixed
        caption-top
        :per-page="perPage"
        :current-page="currentPage"
        :fields="fields"
        :items='figures'
      >
        <template slot="table-caption">List added figures</template>

        <template slot="actions" slot-scope="data">

          <button class="btn btn-sm btn-outline-danger" @click="openDelModal(data.item.id)" :disabled="showDeleteSpinner">
            Delete
            <ButtonSpinner v-if="showDeleteSpinner"></ButtonSpinner>
          </button>

          <button class="btn edit-btn btn-sm btn-outline-warning" @click="data.toggleDetails">
            Edit
          </button>

        </template>

        <template slot="row-details" slot-scope="data">
        <b-card>
          <b-row class="mb-2" v-for="(value, key, index) in data.item.data" :key="index">
            <b-col sm="3" class="text-sm-right"><b>{{key}}:</b></b-col>
            <b-col>{{ value }}</b-col>
          </b-row>
          <button class="btn btn-sm btn-outline-dark" @click="data.toggleDetails">Close</button>
        </b-card>
      </template>

      </b-table>

      <b-pagination v-if='figures.length>perPage'
        class="list-pagination"
        align="center"
        v-model="currentPage"
        :per-page="perPage"
        :total-rows="rows"
        aria-controls="figures-table"
      ></b-pagination>

    </div>
  </div>
  
  
  
</template>

<script>
import axios from 'axios'
import Spinner from '@/components/Spinner'
import NoFiguresAlert from '@/components/NoFiguresAlert'
import ButtonSpinner from '@/components/ButtonSpinner'
import { APP } from '../application-constants'
import swal from 'sweetalert'

export default {
  name: 'list',
  components: {
    Spinner,
    NoFiguresAlert,
    ButtonSpinner
  },
  data() {
    return {
      APP,
      perPage: 5,
      currentPage: 1,
      figures: [],
      getFiguresIsSuccess: false,
      showDeleteSpinner: false,
      fields: [
        {
          key: 'id',
          sortable: true
        },
        {
          key: 'type',
          label: 'Figure type',
          sortable: true
        },
        {
          key: 'area',
          sortable: true,
        },
        {
          key: 'actions',
          sortable: false
        }
      ]
    }
  },
  mounted(){
    axios
    .get(`${APP.endpoints.baseUrl}${APP.endpoints.figures}`)
    .then(response => {
      this.figures = response.data.sort((first,second) => first.area-second.area);
      this.getFiguresIsSuccess = true;
    });
  },
  computed: {
    rows() {
      return this.figures.length;
    }
  },
  methods: {
    openDelModal: function(id){
      swal({
        title: 'Are you sure ?',
        text: 'This operation can not be undone!',
        icon: 'error',
        buttons: true,
        dangerMode: true,
      }).then((willDelete) => {
        if(willDelete){
          this.deleteFigure(id);
        }
      });
    },

    deleteFigure: function (id) {
      this.showDeleteSpinner = true;
      axios
      .delete(`${APP.endpoints.baseUrl}${APP.endpoints.figures}?id=${id}`)
      .then(response => {
        if(response.data.success){
          const index = this.figures.findIndex(figure => figure.id === id);
          this.figures.splice(index,1);
          this.showDeleteSpinner = false;
          this.showAlertDeleteFigure = true;
          swal(`Figure #${id} deleted`, '', 'success');

          if(this.figures.length <= (this.currentPage-1)*this.perPage){
            --this.currentPage;
          }
        }
      });
    },
  }
}
</script>

<style scoped>
  .list-section{
    margin-top: 15px;   
  }
  .deleteAlert{
    margin: 25px 50px;
  }
  .list-pagination{
    margin-top: 25px;
  }
  .deleteAlert p{
    margin-bottom: 0px;
  }
  .edit-btn{
    margin-left: 10px;
  }
</style>