import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '../store'

Vue.use(VueRouter)

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/LoginView.vue'),
        meta: { guest: true }
    },
    {
        path: '/',
        name: 'dashboard',
        component: () => import('../views/DashboardView.vue'),
        meta: { auth: true }
    },
    {
        path: '/filter',
        name: 'filter',
        component: () => import('../views/FilterView.vue'),
        meta: { auth: true }
    },
    {
        path: '/database',
        name: 'database',
        component: () => import('../views/DatabaseView.vue'),
        meta: { auth: true }
    },
    {
        path: '/database/new',
        name: 'database-new',
        component: () => import('../views/TradeFormView.vue'),
        meta: { auth: true }
    },
    {
        path: '/database/:id/edit',
        name: 'database-edit',
        component: () => import('../views/TradeFormView.vue'),
        meta: { auth: true }
    },
    {
        path: '/journal',
        name: 'journal',
        component: () => import('../views/JournalView.vue'),
        meta: { auth: true }
    },
    {
        path: '/journal/new',
        name: 'journal-new',
        component: () => import('../views/JournalFormView.vue'),
        meta: { auth: true }
    },
    {
        path: '/journal/:id',
        name: 'journal-detail',
        component: () => import('../views/JournalDetailView.vue'),
        meta: { auth: true }
    },
    {
        path: '/analytics',
        name: 'analytics',
        component: () => import('../views/AnalyticsView.vue'),
        meta: { auth: true }
    },
    {
        path: '/categories',
        name: 'categories',
        component: () => import('../views/CategoriesView.vue'),
        meta: { auth: true }
    },
    {
        path: '/pairs',
        name: 'pairs',
        component: () => import('../views/PairsView.vue'),
        meta: { auth: true }
    },
    {
        path: '/admin/users',
        name: 'admin-users',
        component: () => import('../views/admin/UsersView.vue'),
        meta: { auth: true, superuser: true }
    },
    { path: '*', redirect: '/' }
]

const router = new VueRouter({
    mode: 'history',
    base: '/',
    routes
})

router.beforeEach((to, from, next) => {
    const loggedIn    = !!store.state.auth.token
    const isSuperuser = store.state.auth.user?.role === 'superuser'

    if (to.meta.auth && !loggedIn)         return next('/login')
    if (to.meta.guest && loggedIn)         return next('/')
    if (to.meta.superuser && !isSuperuser) return next('/')
    next()
})

export default router
