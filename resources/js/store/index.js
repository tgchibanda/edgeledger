import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        auth: {
            namespaced: true,
            state: () => ({
                // Rehydrate from localStorage immediately on page load
                token: localStorage.getItem('el_token') || null,
                user:  JSON.parse(localStorage.getItem('el_user') || 'null'),
            }),
            mutations: {
                SET_TOKEN(state, token) { state.token = token },
                SET_USER(state, user)   { state.user  = user  },
                CLEAR(state)            { state.token = null; state.user = null },
            },
            actions: {
                async login({ commit }, credentials) {
                    const { data } = await Vue.prototype.$http.post('/login', credentials)
                    commit('SET_TOKEN', data.token)
                    commit('SET_USER',  data.user)
                    localStorage.setItem('el_token', data.token)
                    localStorage.setItem('el_user',  JSON.stringify(data.user))
                    return data
                },
                async logout({ commit }) {
                    try { await Vue.prototype.$http.post('/logout') } catch(e) {}
                    commit('CLEAR')
                    localStorage.removeItem('el_token')
                    localStorage.removeItem('el_user')
                },
                // Called on app boot to verify token is still valid
                async verify({ commit, state }) {
                    if (!state.token) return false
                    try {
                        const { data } = await Vue.prototype.$http.get('/user')
                        commit('SET_USER', data)
                        return true
                    } catch(e) {
                        const status = e?.response?.status
                        // Only clear token on 401 Unauthorized — not on 500 server errors
                        if (status === 401) {
                            commit('CLEAR')
                            localStorage.removeItem('el_token')
                            localStorage.removeItem('el_user')
                            return false
                        }
                        // For other errors (network, 500) keep the user logged in
                        // They have a valid token, something else went wrong
                        return true
                    }
                },
            },
            getters: {
                isLoggedIn:  s => !!s.token,
                isSuperuser: s => s.user?.role === 'superuser',
                currentUser: s => s.user,
            },
        },

        app: {
            namespaced: true,
            state: () => ({
                sessions:   [],
                pairs:      [],
                categories: { H4: [], M15: [], M1: [] },
            }),
            mutations: {
                SET_SESSIONS(state, v)        { state.sessions = v },
                SET_PAIRS(state, v)           { state.pairs    = v },
                SET_CATS(state, { tf, list }) { state.categories[tf] = list },
            },
            actions: {
                async loadSessions({ commit, state }) {
                    if (state.sessions.length) return
                    const { data } = await Vue.prototype.$http.get('/trading-sessions')
                    commit('SET_SESSIONS', data)
                },
                async loadPairs({ commit }) {
                    const { data } = await Vue.prototype.$http.get('/pairs')
                    commit('SET_PAIRS', data)
                },
                async loadCategories({ commit }, tf) {
                    const { data } = await Vue.prototype.$http.get('/categories', { params: { timeframe: tf } })
                    commit('SET_CATS', { tf, list: data })
                },
                async loadAll({ dispatch }) {
                    await Promise.all([
                        dispatch('loadSessions'),
                        dispatch('loadPairs'),
                        dispatch('loadCategories', 'H4'),
                        dispatch('loadCategories', 'M15'),
                        dispatch('loadCategories', 'M1'),
                    ])
                },
            },
        },

        filter: {
            namespaced: true,
            state: () => ({
                h4_category_id:     null,
                m15_category_id:    null,
                m1_category_id:     null,
                pair_id:            null,
                trading_session_id: null,
                results:            null,
            }),
            mutations: {
                SET_FILTER(state, payload) { Object.assign(state, payload) },
                SET_RESULTS(state, v)      { state.results = v },
                CLEAR(state) {
                    state.h4_category_id     = null
                    state.m15_category_id    = null
                    state.m1_category_id     = null
                    state.pair_id            = null
                    state.trading_session_id = null
                    state.results            = null
                },
            },
        },
    },
    strict: process.env.NODE_ENV !== 'production',
})