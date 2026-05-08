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
        path: '/journal/:id/edit',
        name: 'journal-edit',
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
        path: '/backtest',
        name: 'backtest',
        component: () => import('../views/BacktestView.vue'),
        meta: { auth: true }
    },
    {
        path: '/invalid-trades',
        name: 'invalid-trades',
        component: () => import('../views/InvalidTradesView.vue'),
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
        path: '/subscription',
        name: 'subscription',
        component: () => import('../views/SubscriptionView.vue'),
        meta: { auth: true }
    },
    {
        path: '/referral',
        name: 'referral',
        component: () => import('../views/ReferralView.vue'),
        meta: { auth: true }
    },
    {
        path: '/admin/users',
        name: 'admin-users',
        component: () => import('../views/admin/UsersView.vue'),
        meta: { auth: true, superuser: true }
    },
    {
        path: '/admin/revenue',
        name: 'admin-revenue',
        component: () => import('../views/admin/AdminRevenueView.vue'),
        meta: { auth: true, superuser: true }
    },
    { path: '*', redirect: '/' }
]

const router = new VueRouter({
    mode: 'history',
    base: '/',
    routes
})

let tokenVerified = false

router.beforeEach(async (to, from, next) => {
    const token = store.state.auth.token

    if (token && !tokenVerified) {
        tokenVerified = true
        await store.dispatch('auth/verify')
    }

    const loggedIn    = !!store.state.auth.token
    const isSuperuser = store.state.auth.user?.role === 'superuser'

    if (to.meta.auth && !loggedIn)         return next('/login')
    if (to.meta.guest && loggedIn)         return next('/')
    if (to.meta.superuser && !isSuperuser) return next('/')
    next()
})

export default router