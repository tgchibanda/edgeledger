<template>
  <AppLayout>
    <div class="p-6 max-w-6xl">
      <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome back, {{ user && user.name }}</p>
      </div>

      <div v-if="loading" class="text-gray-500 text-sm">Loading…</div>
      <div v-else>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="card text-center">
            <div class="text-3xl font-bold text-white">{{ s.total_db_trades || 0 }}</div>
            <div class="text-xs text-gray-500 mt-1">Total DB Trades</div>
          </div>
          <div class="card text-center">
            <div class="text-3xl font-bold" :class="s.win_rate >= 50 ? 'text-win' : 'text-loss'">{{ s.win_rate || 0 }}%</div>
            <div class="text-xs text-gray-500 mt-1">Win Rate (Valid)</div>
          </div>
          <div class="card text-center">
            <div class="text-3xl font-bold text-win">{{ s.wins || 0 }}</div>
            <div class="text-xs text-gray-500 mt-1">Wins</div>
          </div>
          <div class="card text-center">
            <div class="text-3xl font-bold text-loss">{{ s.losses || 0 }}</div>
            <div class="text-xs text-gray-500 mt-1">Losses</div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
          <div class="card">
            <div class="text-xs text-gray-500 mb-1">Valid Trades</div>
            <div class="text-2xl font-bold text-white">{{ s.valid_trades || 0 }}</div>
          </div>
          <div class="card">
            <div class="text-xs text-gray-500 mb-1">Invalid Trades</div>
            <div class="text-2xl font-bold text-red-400">{{ s.invalid_trades || 0 }}</div>
          </div>
          <div class="card">
            <div class="text-xs text-gray-500 mb-1">Journal Entries</div>
            <div class="text-2xl font-bold text-white">{{ s.completed_journals || 0 }} <span class="text-gray-600 text-sm">/ {{ s.total_journals || 0 }}</span></div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <div class="card">
            <h2 class="section-title">Win Rate by Session</h2>
            <div v-if="!s.by_session || !s.by_session.length" class="text-gray-600 text-sm">No data yet.</div>
            <div v-else class="space-y-3">
              <div v-for="row in s.by_session" :key="row.session" class="flex items-center gap-3">
                <div class="text-sm text-gray-400 w-24 flex-shrink-0">{{ row.session }}</div>
                <div class="flex-1 h-2 bg-surface rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all" :class="row.win_rate >= 50 ? 'bg-win' : 'bg-loss'" :style="{width: row.win_rate+'%'}"></div>
                </div>
                <div class="text-sm font-semibold w-12 text-right" :class="row.win_rate >= 50 ? 'text-win' : 'text-loss'">{{ row.win_rate }}%</div>
                <div class="text-xs text-gray-600 w-8 text-right">({{ row.total }})</div>
              </div>
            </div>
          </div>

          <div class="card">
            <h2 class="section-title">Win Rate by Pair</h2>
            <div v-if="!s.by_pair || !s.by_pair.length" class="text-gray-600 text-sm">No data yet.</div>
            <div v-else class="space-y-3">
              <div v-for="row in s.by_pair" :key="row.pair" class="flex items-center gap-3">
                <div class="text-sm text-gray-400 w-20 flex-shrink-0 font-mono">{{ row.pair }}</div>
                <div class="flex-1 h-2 bg-surface rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all" :class="row.win_rate >= 50 ? 'bg-win' : 'bg-loss'" :style="{width: row.win_rate+'%'}"></div>
                </div>
                <div class="text-sm font-semibold w-12 text-right" :class="row.win_rate >= 50 ? 'text-win' : 'text-loss'">{{ row.win_rate }}%</div>
                <div class="text-xs text-gray-600 w-8 text-right">({{ row.total }})</div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <h2 class="section-title">Quick Actions</h2>
          <div class="flex flex-wrap gap-3">
            <router-link to="/filter"       class="btn-primary">🔍 Pre-Trade Filter</router-link>
            <router-link to="/journal/new"  class="btn-secondary">📓 New Journal Entry</router-link>
            <router-link to="/database/new" class="btn-secondary">➕ Add to Database</router-link>
            <router-link to="/analytics"    class="btn-secondary">📈 Analytics</router-link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../components/AppLayout.vue'
export default {
  name: 'DashboardView',
  components: { AppLayout },
  data() { return { s: {}, loading: true } },
  computed: { user() { return this.$store.state.auth.user } },
  async created() {
    try { const { data } = await this.$http.get('/analytics/summary'); this.s = data }
    catch(e) {} finally { this.loading = false }
  },
}
</script>
