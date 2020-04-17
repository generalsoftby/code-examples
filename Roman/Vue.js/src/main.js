import Vue from 'vue'
import App from './App.vue'

Vue.config.productionTip = false

import BootstrapVue from 'bootstrap-vue'
Vue.use(BootstrapVue)

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import VueRouter from 'vue-router'
Vue.use(VueRouter)

import list from '@/pages/List'
import add from '@/pages/Add'
import statistics from '@/pages/Statistics'
import NotFound from '@/components/NotFound'

import CircleComponent from '@/components/Figures/Circle/CircleComponent'
import RectangleComponent from '@/components/Figures/Rectangle/RectangleComponent'
import SquareComponent from '@/components/Figures/Square/SquareComponent'
import TriangleComponent from '@/components/Figures/Triangle/TriangleComponent'

import { APP } from '@/application-constants'

const routes = [
  { path: '/', component: list, meta: {title: 'List figures'} },
  { 
    path: `${APP.routes.pathAddFigures}`,
    component: add,
    meta: {title: 'Add figures'},
    children: [
      {
        path: '',
        redirect: `${APP.routes.pathAddFigures}/${APP.types.circle}`,
      },
      {
        path: `${APP.types.circle}`,
        component: CircleComponent,
        meta: {title: `Add figures | ${APP.types.circle}`}
      },
      {
        path: `${APP.types.rectangle}`,
        component: RectangleComponent,
        meta: {title: `Add figures | ${APP.types.rectangle}`}
      },
      {
        path: `${APP.types.square}`,
        component: SquareComponent,
        meta: {title: `Add figures | ${APP.types.square}`}
      },
      {
        path: `${APP.types.triangle}`,
        component: TriangleComponent,
        meta: {title: `Add figures | ${APP.types.triangle}`}
      },
    ]
  },
  { path: `${APP.routes.pathStatistics}`, component: statistics, meta: {title: 'Statistics figures'} },
  { path: '*', component: NotFound, meta: {title: '404 Not Found'} },
]

const router = new VueRouter({
  mode: 'history',
  routes: routes,
  linkActiveClass: "active",
  linkExactActiveClass: "exact-active",
})



new Vue({
  render: h => h(App),
  router
}).$mount('#app')

