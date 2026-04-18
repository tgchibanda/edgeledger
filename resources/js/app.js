import Vue      from 'vue'
import App       from './App.vue'
import router    from './router'
import store     from './store'
import axios     from 'axios'

Vue.config.productionTip = false

axios.defaults.baseURL = window.location.origin + '/api'
axios.defaults.headers.common['Accept'] = 'application/json'

axios.interceptors.request.use(cfg => {
    const token = localStorage.getItem('el_token')
    if (token) cfg.headers.Authorization = `Bearer ${token}`
    return cfg
})
axios.interceptors.response.use(res => res, err => {
    if (err.response?.status === 401) {
        localStorage.removeItem('el_token')
        localStorage.removeItem('el_user')
        store.commit('auth/CLEAR')
        if (router.currentRoute.name !== 'login') router.push('/login')
    }
    return Promise.reject(err)
})

Vue.prototype.$http = axios

new Vue({ router, store, render: h => h(App) }).$mount('#app')
