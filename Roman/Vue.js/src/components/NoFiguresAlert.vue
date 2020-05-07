<template>
  <b-alert variant="info" show >
    <h5 class="d-flex justify-content-center align-content-center flex-wrap waring-text">Added figures not found - <router-link class="nav-link" :to='APP.routes.pathAddFigures'>add them now</router-link></h5>
  </b-alert>
</template>

<script>
import { APP } from '@/application-constants'

export default {
  name: 'NoFiguresAlert',
  data(){
    return {
      APP
    }
  }
}
</script>

<style>
  .waring-text{
    margin-top: 8px;
  }
  .nav-link{
    padding: 0 0.5rem !important;
  }
</style>
