import "core-js/stable";
import "regenerator-runtime/runtime";
import Vue from 'vue'
import App from './App.vue'
import axios from 'axios'
import VueAxios from 'vue-axios'

import vuetify from './plugins/vuetify'
Vue.use(VueAxios, axios)

Vue.config.productionTip = false

new Vue({
  el: '#app',
  components: {App},
  template: '<App/>',
  vuetify
});