import "core-js/stable";
import "regenerator-runtime/runtime";
import Vue from 'vue'
import App from './App.vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
import * as Sentry from '@sentry/browser';
import * as Integrations from '@sentry/integrations';
import vuetify from './plugins/vuetify'
if (process.env.NODE_ENV === 'production') {
  Sentry.init({
    dsn: 'https://f39725cb0be1412c814d0f0b03a2d519@sentry.io/1515120',
    integrations: [new Integrations.Vue({Vue, attachProps: true})],
  });
}
Vue.use(VueAxios, axios)
Vue.config.productionTip = false
new Vue({
  el: '#app',
  components: {App},
  template: '<App/>',
  vuetify
});