<template>
  <AppLayout>
    <div class="p-6 max-w-6xl">
      <div class="page-header">
        <h1 class="page-title">Analytics</h1>
        <p class="page-sub">Performance statistics across all valid trades.</p>
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-500">Loading…</div>
      <div v-else>
        <!-- Expectancy + Streaks -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div class="card text-center">
            <div class="text-2xl font-bold" :class="exp.expectancy_r > 0 ? 'text-win' : 'text-loss'">{{ exp.expectancy_r != null ? exp.expectancy_r+'R' : '-' }}</div>
            <div class="text-xs text-gray-500 mt-1">Expectancy</div>
          </div>
          <div class="card text-center">
            <div class="text-2xl font-bold text-win">+{{ exp.avg_win_r || '-' }}R</div>
            <div class="text-xs text-gray-500 mt-1">Avg Win</div>
          </div>
          <div class="card text-center">
            <div class="text-2xl font-bold text-loss">{{ exp.avg_loss_r || '-' }}R</div>
            <div class="text-xs text-gray-500 mt-1">Avg Loss</div>
          </div>
          <div class="card text-center">
            <div class="text-2xl font-bold text-white">{{ exp.win_rate || 0 }}%</div>
            <div class="text-xs text-gray-500 mt-1">Win Rate</div>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div class="card text-center">
            <div class="text-2xl font-bold text-win">{{ streaks.current_win_streak }}</div>
            <div class="text-xs text-gray-500 mt-1">Current Win Streak</div>
          </div>
          <div class="card text-center">
            <div class="text-2xl font-bold text-loss">{{ streaks.current_loss_streak }}</div>
            <div class="text-xs text-gray-500 mt-1">Current Loss Streak</div>
          </div>
          <div class="card text-center">
            <div class="text-2xl font-bold text-win">{{ streaks.max_win_streak }}</div>
            <div class="text-xs text-gray-500 mt-1">Best Win Streak</div>
          </div>
          <div class="card text-center">
            <div class="text-2xl font-bold text-loss">{{ streaks.max_loss_streak }}</div>
            <div class="text-xs text-gray-500 mt-1">Worst Loss Streak</div>
          </div>
        </div>

        <!-- Win Rate Table -->
        <div class="card">
          <h2 class="section-title">Win Rate by Setup</h2>
          <WinRateTable :rows="rows" :show-r="true" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout    from '../components/AppLayout.vue'
import WinRateTable from '../components/WinRateTable.vue'
export default {
  name: 'AnalyticsView',
  components: { AppLayout, WinRateTable },
  data() { return { rows: [], exp: {}, streaks: {}, loading: true } },
  async created() {
    try {
      const [r, e, s] = await Promise.all([
        this.$http.get('/analytics/win-rates'),
        this.$http.get('/analytics/expectancy'),
        this.$http.get('/analytics/streaks'),
      ])
      this.rows    = r.data
      this.exp     = e.data
      this.streaks = s.data
    } catch(e) {} finally { this.loading = false }
  },
}
</script>
