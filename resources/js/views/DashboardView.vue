<template>
  <AppLayout>
    <div class="p-6 max-w-5xl">

      <div class="page-header">
        <h1 class="page-title">📊 Dashboard</h1>
        <p class="page-sub">Welcome back, {{ user && user.name }}. Here's your trading overview.</p>
      </div>

      <!-- ── LIVE TRADING STATS ── -->
      <div class="section-label">Live Trading</div>
      <div class="grid grid-cols-4 gap-4 mb-6" v-if="liveStats">
        <div class="stat-card">
          <div class="stat-card__n">{{ liveStats.total_trades || 0 }}</div>
          <div class="stat-card__l">Total Trades</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__n" :class="(liveStats.win_rate||0) >= 50 ? 'text-win':'text-loss'">{{ liveStats.win_rate || 0 }}%</div>
          <div class="stat-card__l">Win Rate</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__n" :class="(liveStats.expectancy||0) >= 0 ? 'text-win':'text-loss'">{{ liveStats.expectancy || 0 }}R</div>
          <div class="stat-card__l">Expectancy</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__n">{{ liveStats.current_streak || 0 }}</div>
          <div class="stat-card__l">Current Streak</div>
        </div>
      </div>
      <div v-else class="grid grid-cols-4 gap-4 mb-6">
        <div v-for="i in 4" :key="i" class="stat-card">
          <div class="stat-card__n text-gray-700">—</div>
          <div class="stat-card__l">Loading…</div>
        </div>
      </div>

      <!-- ── BACKTEST STATS ── -->
      <div class="section-label mt-6">Backtesting</div>
      <div v-if="!backtestSummary" class="card mb-6 text-center py-8">
        <div class="text-3xl mb-2">🧪</div>
        <div class="text-white font-semibold mb-1">No backtest sessions yet</div>
        <p class="text-gray-500 text-sm mb-3">Start journaling your backtests to track your edge in historical data.</p>
        <router-link to="/backtest" class="btn-primary inline-block">Start Backtesting</router-link>
      </div>

      <div v-else>
        <div class="grid grid-cols-4 gap-4 mb-4">
          <div class="stat-card stat-card--bt">
            <div class="stat-card__n">{{ backtestSummary.total_sessions }}</div>
            <div class="stat-card__l">Sessions</div>
          </div>
          <div class="stat-card stat-card--bt">
            <div class="stat-card__n">{{ backtestSummary.total_trades }}</div>
            <div class="stat-card__l">BT Trades</div>
          </div>
          <div class="stat-card stat-card--bt">
            <div class="stat-card__n" :class="backtestSummary.win_rate >= 50 ? 'text-win':'text-loss'">{{ backtestSummary.win_rate }}%</div>
            <div class="stat-card__l">BT Win Rate</div>
          </div>
          <div class="stat-card stat-card--bt">
            <div class="stat-card__n" :class="backtestSummary.expectancy >= 0 ? 'text-win':'text-loss'">{{ backtestSummary.expectancy }}R</div>
            <div class="stat-card__l">BT Expectancy</div>
          </div>
        </div>

        <!-- Recent sessions -->
        <div class="card mb-6">
          <div class="flex items-center justify-between mb-3">
            <h2 class="section-title mb-0">Recent Backtest Sessions</h2>
            <router-link to="/backtest" class="text-xs text-win hover:underline">View all →</router-link>
          </div>
          <div class="space-y-2">
            <div v-for="s in backtestSummary.recent_sessions" :key="s.id"
              class="flex items-center gap-4 p-3 bg-surface rounded-lg border border-border hover:border-win/30 transition-colors cursor-pointer"
              @click="$router.push('/backtest')">
              <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-white truncate">{{ s.name }}</div>
                <div class="text-xs text-gray-500">{{ s.trades }} trades</div>
              </div>
              <div class="text-right">
                <div class="text-sm font-bold" :class="s.win_rate >= 50 ? 'text-win':'text-loss'">{{ s.win_rate }}%</div>
                <div class="text-xs" :class="s.total_r >= 0 ? 'text-win':'text-loss'">{{ s.total_r >= 0 ? '+':'' }}{{ s.total_r }}R</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── LIVE vs BACKTEST COMPARISON ── -->
      <div v-if="liveStats && backtestSummary && backtestSummary.total_trades > 0" class="card mb-6">
        <h2 class="section-title">Live vs Backtest Comparison</h2>
        <div class="grid grid-cols-3 gap-4 text-center">
          <div>
            <div class="text-xs text-gray-500 uppercase tracking-wider mb-3">Metric</div>
            <div class="space-y-3">
              <div class="text-sm text-gray-400 h-8 flex items-center justify-center">Win Rate</div>
              <div class="text-sm text-gray-400 h-8 flex items-center justify-center">Expectancy</div>
              <div class="text-sm text-gray-400 h-8 flex items-center justify-center">Total R</div>
            </div>
          </div>
          <div>
            <div class="text-xs text-gray-500 uppercase tracking-wider mb-3">📈 Live</div>
            <div class="space-y-3">
              <div class="h-8 flex items-center justify-center font-bold text-sm" :class="(liveStats.win_rate||0) >= 50 ? 'text-win':'text-loss'">{{ liveStats.win_rate || 0 }}%</div>
              <div class="h-8 flex items-center justify-center font-bold text-sm" :class="(liveStats.expectancy||0) >= 0 ? 'text-win':'text-loss'">{{ liveStats.expectancy || 0 }}R</div>
              <div class="h-8 flex items-center justify-center font-bold text-sm" :class="(liveStats.total_r||0) >= 0 ? 'text-win':'text-loss'">{{ (liveStats.total_r||0) >= 0 ? '+':'' }}{{ liveStats.total_r || 0 }}R</div>
            </div>
          </div>
          <div>
            <div class="text-xs text-gray-500 uppercase tracking-wider mb-3">🧪 Backtest</div>
            <div class="space-y-3">
              <div class="h-8 flex items-center justify-center font-bold text-sm" :class="backtestSummary.win_rate >= 50 ? 'text-win':'text-loss'">{{ backtestSummary.win_rate }}%</div>
              <div class="h-8 flex items-center justify-center font-bold text-sm" :class="backtestSummary.expectancy >= 0 ? 'text-win':'text-loss'">{{ backtestSummary.expectancy }}R</div>
              <div class="h-8 flex items-center justify-center font-bold text-sm" :class="backtestSummary.total_r >= 0 ? 'text-win':'text-loss'">{{ backtestSummary.total_r >= 0 ? '+':'' }}{{ backtestSummary.total_r }}R</div>
            </div>
          </div>
        </div>
        <div v-if="convergenceNote" class="mt-4 p-3 rounded-lg text-xs" :class="convergenceNote.type === 'good' ? 'bg-win/10 text-win border border-win/20' : 'bg-yellow-900/20 text-yellow-400 border border-yellow-800/30'">
          {{ convergenceNote.text }}
        </div>
      </div>

      <!-- ── QUICK LINKS ── -->
      <div class="grid grid-cols-3 gap-4">
        <router-link to="/database/new" class="quick-link">
          <span class="quick-link__icon">➕</span>
          <span class="quick-link__label">Add Trade</span>
        </router-link>
        <router-link to="/backtest" class="quick-link">
          <span class="quick-link__icon">🧪</span>
          <span class="quick-link__label">Backtest</span>
        </router-link>
        <router-link to="/filter" class="quick-link">
          <span class="quick-link__icon">🔍</span>
          <span class="quick-link__label">Pre-Trade Filter</span>
        </router-link>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../components/AppLayout.vue'
export default {
  name: 'DashboardView',
  components: { AppLayout },
  data() {
    return {
      liveStats:        null,
      backtestSummary:  null,
    }
  },
  computed: {
    user() { return this.$store.state.auth.user },
    convergenceNote() {
      if (!this.liveStats || !this.backtestSummary) return null
      const liveWR = this.liveStats.win_rate || 0
      const btWR   = this.backtestSummary.win_rate || 0
      const diff   = Math.abs(liveWR - btWR)
      if (diff <= 5) return { type:'good', text:`✓ Your live and backtest win rates are closely aligned (${diff.toFixed(1)}% difference). Your strategy is consistent.` }
      if (liveWR < btWR) return { type:'warn', text:`⚠ Your live win rate (${liveWR}%) is ${diff.toFixed(1)}% below your backtest (${btWR}%). Review execution discipline or market condition changes.` }
      return { type:'good', text:`Your live win rate (${liveWR}%) is running ahead of your backtest (${btWR}%). Keep monitoring for sample size validity.` }
    },
  },
  async mounted() {
    await Promise.all([this.loadLiveStats(), this.loadBacktestSummary()])
  },
  methods: {
    async loadLiveStats() {
      try {
        const [summary, streaks] = await Promise.all([
          this.$http.get('/analytics/summary'),
          this.$http.get('/analytics/streaks'),
        ])
        const s = summary.data
        const k = streaks.data
        this.liveStats = {
          total_trades:    s.total || 0,
          win_rate:        s.win_rate || 0,
          expectancy:      s.expectancy || 0,
          total_r:         s.total_r || 0,
          current_streak:  k.current?.count || 0,
        }
      } catch(e) {}
    },
    async loadBacktestSummary() {
      try {
        const { data } = await this.$http.get('/backtest/summary')
        this.backtestSummary = data.total_trades > 0 ? data : null
      } catch(e) {}
    },
  },
}
</script>

<style scoped>
.section-label { font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#4A5568; margin-bottom:12px; }
.stat-card { background:#1A2633; border:1px solid rgba(255,255,255,.07); border-radius:12px; padding:20px; text-align:center; }
.stat-card--bt { border-color:rgba(29,158,117,.12); background:rgba(29,158,117,.03); }
.stat-card__n { font-family:'Syne',sans-serif; font-size:28px; font-weight:800; color:#fff; }
.stat-card__l { font-size:11px; color:#4A5568; margin-top:4px; }
.text-win  { color:#1D9E75 !important; }
.text-loss { color:#E24B4A !important; }

.quick-link { display:flex; flex-direction:column; align-items:center; gap:8px; padding:20px; background:#1A2633; border:1px solid rgba(255,255,255,.07); border-radius:12px; text-decoration:none; transition:all .2s; cursor:pointer; }
.quick-link:hover { border-color:rgba(29,158,117,.3); transform:translateY(-1px); }
.quick-link__icon  { font-size:24px; }
.quick-link__label { font-size:13px; font-weight:600; color:#94A3B8; }

@media(max-width:640px) {
  .grid { grid-template-columns:1fr 1fr !important; }
}
</style>
