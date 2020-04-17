<template>
  <b-navbar toggleable="lg" type="dark" variant="primary">
  <div class="container">
    <router-link class="navbar-brand" to="/">Figures</router-link>
    <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

    <b-collapse id="nav-collapse" is-nav>
      <b-navbar-nav>
        <li class="nav-item">
          <router-link class="nav-link" to="/" exact>List</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" :to='APP.routes.pathAddFigures'>Add</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" :to='APP.routes.pathStatistics'>Statistics</router-link>
        </li>
      </b-navbar-nav>

    </b-collapse>
  </div>
  </b-navbar>
</template>

<script>
import { APP } from '@/application-constants'

export default {
  name: 'Header',
  data(){
    return{
      APP
    }
  }
}
</script>

<style>

</style>
