import './bootstrap';
import Vue from 'vue'
import VueRouter from 'vue-router'
import Vuex from 'vuex'
import App from './App.vue'
import router from './router'
import store from './store'
import axios from 'axios'

Vue.use(VueRouter)
Vue.use(Vuex)

axios.defaults.baseURL = 'http://127.0.0.1:8000/api'
axios.interceptors.request.use(cfg => {
  const token = localStorage.getItem('token')
  if (token) cfg.headers.Authorization = `Bearer ${token}`
  return cfg
})

Vue.prototype.$http = axios
new Vue({ router, store, render: h => h(App) }).$mount('#app')