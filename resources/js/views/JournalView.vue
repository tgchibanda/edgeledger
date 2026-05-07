<template>
  <AppLayout>
    <div class="p-6 max-w-4xl">

      <div class="page-header">
        <div class="flex items-start justify-between gap-4 flex-wrap">
          <div>
            <h1 class="page-title">📓 Journal</h1>
            <p class="page-sub">Pre-trade plans and post-trade reviews.</p>
          </div>
          <router-link to="/journal/new" class="btn-primary flex-shrink-0">+ New Entry</router-link>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 mb-5">
        <button v-for="tab in tabs" :key="tab.key"
          class="px-4 py-2 rounded-lg text-sm font-semibold border transition-colors"
          :class="activeTab === tab.key
            ? 'bg-win/15 border-win text-win'
            : 'bg-surface border-border text-gray-500 hover:text-gray-300'"
          @click="activeTab = tab.key">
          {{ tab.label }}
          <span v-if="counts[tab.key]" class="ml-1 text-xs opacity-70">({{ counts[tab.key] }})</span>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="card flex items-center gap-3 text-gray-500 text-sm">
        <div class="w-4 h-4 border-2 border-gray-600 border-t-win rounded-full animate-spin"></div>
        Loading journals…
      </div>

      <!-- Error -->
      <div v-else-if="error" class="card border border-red-800/40 text-red-400 text-sm p-4">
        {{ error }}
      </div>

      <!-- Empty -->
      <div v-else-if="!filtered.length" class="card text-center py-10">
        <div class="text-3xl mb-2">📓</div>
        <div class="text-white font-semibold mb-1">
          {{ activeTab === 'all' ? 'No journal entries yet' : 'No ' + activeTab + ' entries' }}
        </div>
        <p class="text-gray-500 text-sm mb-4">Plan your trades before taking them.</p>
        <router-link to="/journal/new" class="btn-primary inline-block">New Entry</router-link>
      </div>

      <!-- Journal list -->
      <div v-else class="space-y-3">
        <div v-for="j in filtered" :key="j.id"
          class="journal-card"
          :class="'journal-card--' + (j.result || j.status)"
          @click="$router.push('/journal/' + j.id)">

          <div class="journal-card__left">
            <!-- Status badge -->
            <div class="flex items-center gap-2 mb-2">
              <span class="jbadge" :class="j.status === 'completed' ? 'jbadge--done' : 'jbadge--pre'">
                {{ j.status === 'completed' ? 'Completed' : 'Pre-Trade' }}
              </span>
              <span v-if="j.result" class="jbadge" :class="'jbadge--' + j.result">
                {{ j.result.toUpperCase() }}
              </span>
            </div>

            <!-- Entry technique + pair -->
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-white text-sm">{{ j.entry_technique }}</span>
              <span class="text-xs font-mono text-gray-500">{{ j.pair?.symbol }}</span>
              <span v-if="j.session" class="text-xs text-gray-600">{{ j.session.name }}</span>
            </div>

            <!-- Categories -->
            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
              <span v-if="j.h4" class="cat-badge cat-badge--h4">{{ j.h4.name }}</span>
              <span v-if="j.m15" class="cat-badge cat-badge--m15">{{ j.m15.name }}</span>
              <span v-if="j.m1" class="cat-badge cat-badge--m1">{{ j.m1.name }}</span>
            </div>

            <!-- Notes preview -->
            <div v-if="j.pre_trade_notes" class="text-xs text-gray-500 mt-1.5 truncate max-w-lg">
              {{ j.pre_trade_notes }}
            </div>

            <!-- Indicators -->
            <div class="flex items-center gap-3 mt-2">
              <span v-if="j.trade_date" class="text-xs text-gray-600 font-mono">{{ fmt(j.trade_date) }}</span>
              <span v-if="j.followed_rules === false" class="text-xs text-loss">⚠ Impulsive</span>
              <span v-if="j.images && j.images.length" class="text-xs text-gray-600">📷 {{ j.images.length }}</span>
            </div>
          </div>

          <div class="journal-card__right">
            <div v-if="j.r_multiple != null" class="journal-r" :class="j.r_multiple >= 0 ? 'text-win' : 'text-loss'">
              {{ j.r_multiple >= 0 ? '+' : '' }}{{ j.r_multiple }}R
            </div>
            <div v-if="j.pips_result != null" class="text-xs text-gray-500">
              {{ j.pips_result > 0 ? '+' : '' }}{{ j.pips_result }} pips
            </div>
            <svg class="mt-2 text-gray-700" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
          </div>

        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../components/AppLayout.vue'

export default {
  name: 'JournalView',
  components: { AppLayout },
  data() {
    return {
      loading:   true,
      error:     '',
      journals:  [],
      activeTab: 'all',
      tabs: [
        { key: 'all',       label: 'All' },
        { key: 'pre',       label: 'Pre-Trade' },
        { key: 'completed', label: 'Completed' },
      ],
    }
  },
  computed: {
    filtered() {
      if (this.activeTab === 'all') return this.journals
      return this.journals.filter(j => j.status === this.activeTab)
    },
    counts() {
      return {
        all:       this.journals.length,
        pre:       this.journals.filter(j => j.status === 'pre').length,
        completed: this.journals.filter(j => j.status === 'completed').length,
      }
    },
  },
  async mounted() {
    await this.load()
  },
  methods: {
    async load() {
      this.loading = true
      this.error   = ''
      try {
        const { data } = await this.$http.get('/journals')
        this.journals = Array.isArray(data) ? data : []
      } catch(e) {
        this.error = e.response?.data?.message || 'Failed to load journals.'
        console.error('Journal load error:', e?.response?.data || e?.message)
      } finally {
        this.loading = false
      }
    },
    fmt(d) {
      if (!d) return ''
      return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
    },
  },
}
</script>

<style scoped>
.journal-card {
  display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
  background: #1A2633; border: 1px solid rgba(255,255,255,.07);
  border-left: 3px solid rgba(255,255,255,.1);
  border-radius: 12px; padding: 16px 18px;
  cursor: pointer; transition: all .15s;
}
.journal-card:hover { border-color: rgba(29,158,117,.3); transform: translateY(-1px); }
.journal-card--win         { border-left-color: #1D9E75; }
.journal-card--loss        { border-left-color: #E24B4A; }
.journal-card--breakeven   { border-left-color: #64748B; }
.journal-card--pre         { border-left-color: rgba(212,160,23,.5); }
.journal-card--completed   { border-left-color: rgba(255,255,255,.15); }

.journal-card__left  { flex: 1; min-width: 0; }
.journal-card__right { display: flex; flex-direction: column; align-items: flex-end; flex-shrink: 0; }
.journal-r { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; }

/* Badges */
.jbadge { font-size: 10px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; padding: 2px 8px; border-radius: 100px; }
.jbadge--pre  { background: rgba(212,160,23,.15);  color: #D4A017; }
.jbadge--done { background: rgba(255,255,255,.08); color: #64748B; }
.jbadge--win  { background: rgba(29,158,117,.15);  color: #1D9E75; }
.jbadge--loss { background: rgba(226,75,74,.15);   color: #E24B4A; }
.jbadge--breakeven { background: rgba(255,255,255,.08); color: #94A3B8; }

.cat-badge { font-size: 10px; font-weight: 600; padding: 1px 7px; border-radius: 4px; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cat-badge--h4  { background: rgba(55,138,221,.15);  color: #85B7EB; }
.cat-badge--m15 { background: rgba(127,119,221,.15); color: #AFA9EC; }
.cat-badge--m1  { background: rgba(212,160,23,.12);  color: #D4A017; }

.text-win  { color: #1D9E75; }
.text-loss { color: #E24B4A; }
</style>