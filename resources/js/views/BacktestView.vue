<template>
  <AppLayout>
    <div class="p-6 max-w-5xl">

      <!-- ── LIST VIEW: Sessions ── -->
      <div v-if="!activeSession">
        <div class="page-header">
          <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
              <h1 class="page-title">🧪 Backtest Journal</h1>
              <p class="page-sub">Track and analyse your backtesting sessions.</p>
            </div>
            <button class="btn-primary flex-shrink-0" @click="openSessionForm()">+ New Session</button>
          </div>
        </div>

        <div v-if="loading" class="card text-gray-500 text-sm">Loading…</div>

        <div v-else-if="!sessions.length" class="card text-center py-12">
          <div class="text-4xl mb-3">🧪</div>
          <div class="text-white font-semibold text-lg mb-2">No backtest sessions yet</div>
          <p class="text-gray-500 text-sm max-w-sm mx-auto mb-5">Create a session to start recording backtest trades. Group them by strategy, pair, or time period.</p>
          <button class="btn-primary" @click="openSessionForm()">Create First Session</button>
        </div>

        <div v-else class="space-y-4">
          <div v-for="s in sessions" :key="s.id" class="session-card" @click="openSession(s)">
            <div class="session-card__left">
              <div class="flex items-center gap-3 mb-2">
                <span class="session-status" :class="s.status === 'completed' ? 'session-status--done' : 'session-status--active'">
                  {{ s.status === 'completed' ? 'Completed' : 'Active' }}
                </span>
                <span class="text-xs font-mono text-gray-500">{{ s.pair?.symbol }} · {{ s.timeframe }}</span>
              </div>
              <div class="session-name">{{ s.name }}</div>
              <div v-if="s.description" class="session-desc">{{ s.description }}</div>
              <div v-if="s.date_from || s.date_to" class="text-xs text-gray-600 mt-1">
                {{ fmtd(s.date_from) }} → {{ fmtd(s.date_to) }}
              </div>
            </div>
            <div class="session-card__stats">
              <div class="session-stat">
                <span class="session-stat__n">{{ s.stats?.total || 0 }}</span>
                <span class="session-stat__l">Trades</span>
              </div>
              <div class="session-stat">
                <span class="session-stat__n" :class="(s.stats?.win_rate||0) >= 50 ? 'text-win' : 'text-loss'">
                  {{ s.stats?.win_rate || 0 }}%
                </span>
                <span class="session-stat__l">Win Rate</span>
              </div>
              <div class="session-stat">
                <span class="session-stat__n" :class="(s.stats?.total_r||0) >= 0 ? 'text-win' : 'text-loss'">
                  {{ (s.stats?.total_r || 0) >= 0 ? '+' : '' }}{{ s.stats?.total_r || 0 }}R
                </span>
                <span class="session-stat__l">Total R</span>
              </div>
              <div class="session-stat">
                <span class="session-stat__n" :class="(s.stats?.expectancy||0) >= 0 ? 'text-win' : 'text-loss'">
                  {{ s.stats?.expectancy || 0 }}R
                </span>
                <span class="session-stat__l">Expectancy</span>
              </div>
              <div class="session-card__arrow">→</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── SESSION DETAIL VIEW ── -->
      <div v-else>
        <div class="page-header">
          <div class="flex items-start gap-3 mb-1">
            <button class="text-gray-500 hover:text-white text-sm mt-1" @click="activeSession = null; loadSessions()">← Back</button>
          </div>
          <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="session-status" :class="activeSession.status==='completed'?'session-status--done':'session-status--active'">
                  {{ activeSession.status === 'completed' ? 'Completed' : 'Active' }}
                </span>
                <span class="text-xs font-mono text-gray-500">{{ activeSession.pair?.symbol }} · {{ activeSession.timeframe }}</span>
              </div>
              <h1 class="page-title">{{ activeSession.name }}</h1>
              <p v-if="activeSession.description" class="page-sub">{{ activeSession.description }}</p>
            </div>
            <div class="flex gap-2 flex-wrap">
              <button class="btn-ghost text-sm" @click="openSessionForm(activeSession)">Edit Session</button>
              <button class="btn-primary" @click="openTradeForm()">+ Add Trade</button>
            </div>
          </div>
        </div>

        <!-- Stats row -->
        <div class="grid grid-cols-5 gap-3 mb-6" v-if="sessionStats">
          <div class="stat-mini"><div class="stat-mini__n">{{ sessionStats.total }}</div><div class="stat-mini__l">Trades</div></div>
          <div class="stat-mini"><div class="stat-mini__n" :class="sessionStats.win_rate >= 50 ? 'text-win':'text-loss'">{{ sessionStats.win_rate }}%</div><div class="stat-mini__l">Win Rate</div></div>
          <div class="stat-mini"><div class="stat-mini__n" :class="sessionStats.total_r >= 0 ? 'text-win':'text-loss'">{{ sessionStats.total_r >= 0 ? '+':'' }}{{ sessionStats.total_r }}R</div><div class="stat-mini__l">Total R</div></div>
          <div class="stat-mini"><div class="stat-mini__n" :class="sessionStats.expectancy >= 0 ? 'text-win':'text-loss'">{{ sessionStats.expectancy }}R</div><div class="stat-mini__l">Expectancy</div></div>
          <div class="stat-mini"><div class="stat-mini__n">{{ sessionStats.profit_factor }}</div><div class="stat-mini__l">Profit Factor</div></div>
        </div>

        <!-- Trade list -->
        <div v-if="tradesLoading" class="card text-gray-500 text-sm">Loading trades…</div>
        <div v-else-if="!trades.length" class="card text-center py-10">
          <div class="text-3xl mb-2">📋</div>
          <div class="text-white font-semibold mb-1">No trades recorded yet</div>
          <p class="text-gray-500 text-sm mb-4">Add your first backtest trade to this session.</p>
          <button class="btn-primary" @click="openTradeForm()">Add First Trade</button>
        </div>
        <div v-else class="space-y-2">
          <div v-for="(t, i) in trades" :key="t.id" class="bt-trade" :class="'bt-trade--' + t.result">
            <div class="bt-trade__num">{{ i + 1 }}</div>
            <div class="bt-trade__result" :class="'bt-trade__result--' + t.result">{{ t.result.toUpperCase()[0] }}</div>
            <div class="bt-trade__info">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-semibold text-white">{{ t.entry_technique || 'No technique' }}</span>
                <span v-if="t.h4" class="text-xs px-1.5 py-0.5 rounded bg-blue-900/30 text-blue-300">{{ t.h4.name }}</span>
                <span v-if="t.m15" class="text-xs px-1.5 py-0.5 rounded bg-purple-900/30 text-purple-300">{{ t.m15.name }}</span>
                <span v-if="t.m1" class="text-xs px-1.5 py-0.5 rounded bg-yellow-900/30 text-yellow-300">{{ t.m1.name }}</span>
              </div>
              <div class="flex items-center gap-3 mt-0.5">
                <span v-if="t.trade_date" class="text-xs text-gray-500 font-mono">{{ fmt(t.trade_date) }}</span>
                <span v-if="!t.followed_rules" class="text-xs text-loss">⚠ Impulsive</span>
                <span v-if="t.notes" class="text-xs text-gray-600 truncate max-w-xs">{{ t.notes }}</span>
              </div>
            </div>
            <!-- Images thumbnails -->
            <div v-if="t.images?.length" class="bt-trade__imgs">
              <div v-for="img in orderedImages(t)" :key="img.id" class="bt-thumb" @click.stop="openLightbox(t)">
                <img :src="imgUrl(img)" />
              </div>
            </div>
            <div class="bt-trade__r" :class="(t.r_multiple||0) >= 0 ? 'text-win':'text-loss'">
              {{ t.r_multiple != null ? ((t.r_multiple >= 0 ? '+':'')+t.r_multiple+'R') : '' }}
            </div>
            <div class="bt-trade__actions">
              <button class="text-xs text-gray-500 hover:text-win" @click.stop="openTradeForm(t)">Edit</button>
              <button class="text-xs text-gray-500 hover:text-loss" @click.stop="confirmDeleteTrade(t)">Del</button>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ── SESSION FORM MODAL ── -->
    <div v-if="sessionForm.show" class="modal-overlay" @click.self="sessionForm.show=false">
      <div class="modal-panel">
        <div class="flex items-center justify-between mb-5">
          <h2 class="section-title mb-0">{{ sessionForm.id ? 'Edit Session' : 'New Backtest Session' }}</h2>
          <button class="modal-close" @click="sessionForm.show=false">✕</button>
        </div>
        <div class="space-y-4">
          <div>
            <label class="label">Session Name *</label>
            <input v-model="sessionForm.name" class="input" placeholder="e.g. EURUSD M1 Range Strategy — Q1 2025" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Pair *</label>
              <select v-model="sessionForm.pair_id" class="select" :disabled="!!sessionForm.id">
                <option value="">Select pair</option>
                <option v-for="p in pairs" :key="p.id" :value="p.id">{{ p.symbol }}</option>
              </select>
            </div>
            <div>
              <label class="label">Timeframe</label>
              <select v-model="sessionForm.timeframe" class="select" :disabled="!!sessionForm.id">
                <option v-for="tf in timeframes" :key="tf" :value="tf">{{ tf }}</option>
              </select>
            </div>
            <div>
              <label class="label">Date From</label>
              <input v-model="sessionForm.date_from" type="date" class="input" />
            </div>
            <div>
              <label class="label">Date To</label>
              <input v-model="sessionForm.date_to" type="date" class="input" />
            </div>
          </div>
          <div>
            <label class="label">Description / Strategy Notes</label>
            <textarea v-model="sessionForm.description" class="textarea" rows="3" placeholder="Describe the strategy rules you are backtesting…"></textarea>
          </div>
          <div v-if="sessionForm.id">
            <label class="label">Status</label>
            <div class="flex gap-3">
              <button v-for="s in ['active','completed']" :key="s"
                class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors capitalize"
                :class="sessionForm.status===s ? 'bg-win/15 border-win text-win' : 'bg-surface border-border text-gray-500'"
                @click="sessionForm.status=s">{{ s }}</button>
            </div>
          </div>
          <div v-if="sessionForm.error" class="text-loss text-sm">{{ sessionForm.error }}</div>
          <div class="flex gap-3">
            <button class="btn-primary" :disabled="sessionForm.saving" @click="saveSession">
              {{ sessionForm.saving ? 'Saving…' : sessionForm.id ? 'Update' : 'Create Session' }}
            </button>
            <button class="btn-ghost" @click="sessionForm.show=false">Cancel</button>
            <button v-if="sessionForm.id" class="ml-auto text-xs text-loss hover:underline" @click="deleteSession">Delete Session</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TRADE FORM MODAL ── -->
    <div v-if="tradeForm.show" class="modal-overlay" @click.self="tradeForm.show=false">
      <div class="modal-panel modal-panel--wide">
        <div class="flex items-center justify-between mb-5">
          <h2 class="section-title mb-0">{{ tradeForm.id ? 'Edit Backtest Trade' : 'Add Backtest Trade' }}</h2>
          <button class="modal-close" @click="tradeForm.show=false">✕</button>
        </div>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Result *</label>
              <div class="flex gap-2">
                <button v-for="r in ['win','loss','breakeven']" :key="r"
                  class="flex-1 py-2 rounded-lg text-xs font-bold border capitalize transition-colors"
                  :class="tradeForm.result===r
                    ? r==='win' ? 'bg-win/20 border-win text-win'
                    : r==='loss' ? 'bg-loss/20 border-loss text-loss'
                    : 'bg-gray-700/40 border-gray-600 text-gray-300'
                    : 'bg-surface border-border text-gray-500 hover:border-gray-500'"
                  @click="tradeForm.result=r">{{ r }}</button>
              </div>
            </div>
            <div>
              <label class="label">R Multiple</label>
              <input v-model="tradeForm.r_multiple" type="number" step="0.01" class="input" placeholder="e.g. 2.5 or -1.0" />
            </div>
            <div>
              <label class="label">HTF Structure</label>
              <select v-model="tradeForm.h4_category_id" class="select">
                <option value="">None</option>
                <option v-for="c in h4cats" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="label">MTF Trigger</label>
              <select v-model="tradeForm.m15_category_id" class="select">
                <option value="">None</option>
                <option v-for="c in m15cats" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="label">Entry TF Signal</label>
              <select v-model="tradeForm.m1_category_id" class="select">
                <option value="">None</option>
                <option v-for="c in m1cats" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="label">Entry Technique</label>
              <input v-model="tradeForm.entry_technique" class="input" placeholder="e.g. OB + FVG" />
            </div>
            <div>
              <label class="label">Pips Result</label>
              <input v-model="tradeForm.pips_result" type="number" step="0.1" class="input" placeholder="e.g. 25.5" />
            </div>
            <div>
              <label class="label">Trade Date</label>
              <input v-model="tradeForm.trade_date" type="datetime-local" class="input" />
            </div>
          </div>
          <div>
            <label class="label">Followed Rules?</label>
            <div class="flex gap-3">
              <button class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors"
                :class="tradeForm.followed_rules ? 'bg-win/15 border-win text-win' : 'bg-surface border-border text-gray-500'"
                @click="tradeForm.followed_rules=true">✓ Yes — Disciplined</button>
              <button class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors"
                :class="!tradeForm.followed_rules ? 'bg-loss/15 border-loss text-loss' : 'bg-surface border-border text-gray-500'"
                @click="tradeForm.followed_rules=false">✗ No — Impulsive</button>
            </div>
          </div>
          <!-- Chart images -->
          <div>
            <label class="label">Chart Screenshots</label>
            <div class="grid grid-cols-3 gap-3">
              <div v-for="slot in imageSlots" :key="slot.key" class="upload-slot">
                <div class="upload-slot__label"><span :class="'tf-badge tf-badge--'+slot.cls">{{ slot.label }}</span></div>
                <div class="upload-slot__zone" :class="{'has-img': tradeForm[slot.key+'_preview']}"
                  @click="$refs[slot.key+'Input'][0].click()" @dragover.prevent @drop.prevent="onDrop($event, slot.key)">
                  <input :ref="slot.key+'Input'" type="file" accept="image/*" class="hidden" @change="onFile($event, slot.key)" />
                  <img v-if="tradeForm[slot.key+'_preview']" :src="tradeForm[slot.key+'_preview']" class="upload-preview" />
                  <div v-else-if="tradeForm.id && existingImg(slot.tf)" class="upload-slot__existing">
                    <img :src="imgUrl(existingImg(slot.tf))" class="upload-preview" />
                    <span class="upload-current">Current</span>
                  </div>
                  <div v-else class="upload-inner"><span class="text-xl">📷</span><span class="text-xs text-gray-500">Drop or click</span></div>
                  <button v-if="tradeForm[slot.key+'_preview']" type="button" class="upload-remove" @click.stop="clearImg(slot.key)">✕</button>
                </div>
              </div>
            </div>
          </div>
          <div>
            <label class="label">Notes</label>
            <textarea v-model="tradeForm.notes" class="textarea" rows="2" placeholder="What did you observe? What would you do differently?"></textarea>
          </div>
          <div v-if="tradeForm.error" class="text-loss text-sm">{{ tradeForm.error }}</div>
          <div class="flex gap-3">
            <button class="btn-primary" :disabled="tradeForm.saving" @click="saveTrade">
              {{ tradeForm.saving ? 'Saving…' : tradeForm.id ? 'Update Trade' : 'Add Trade' }}
            </button>
            <button class="btn-ghost" @click="tradeForm.show=false">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <ImageLightbox :visible="lightbox.show" :trades="lightboxTrades" :trade-index="lightbox.index" @close="lightbox.show=false" />

    <!-- Delete trade confirm -->
    <div v-if="deleteTradeTarget" class="fixed inset-0 bg-black/70 flex items-center justify-center z-40 p-4">
      <div class="card w-full max-w-sm">
        <h2 class="section-title">Delete trade?</h2>
        <p class="text-sm text-gray-400 mb-4">This will permanently remove this backtest trade record.</p>
        <div class="flex gap-3">
          <button class="btn-danger" :disabled="deletingTrade" @click="doDeleteTrade">{{ deletingTrade ? 'Deleting…' : 'Delete' }}</button>
          <button class="btn-ghost" @click="deleteTradeTarget=null">Cancel</button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script>
import AppLayout     from '../components/AppLayout.vue'
import ImageLightbox from '../components/ImageLightbox.vue'
import { TF }        from '@/timeframes.js'

export default {
  name: 'BacktestView',
  components: { AppLayout, ImageLightbox },

  data() {
    return {
      loading:       true,
      sessions:      [],
      activeSession: null,
      sessionStats:  null,
      trades:        [],
      tradesLoading: false,
      pairs:         [],
      timeframes:    ['M1','M5','M15','M30','H1','H4','D1'],

      imageSlots: [
        { key:'h4',  label: TF.h4,  tf:'H4',  cls:'h4' },
        { key:'m15', label: TF.m15, tf:'M15', cls:'m15' },
        { key:'m1',  label: TF.m1,  tf:'M1',  cls:'m1' },
      ],

      sessionForm: { show:false, saving:false, error:'', id:null, name:'', pair_id:'', timeframe:'M1', description:'', date_from:'', date_to:'', status:'active' },
      tradeForm:   { show:false, saving:false, error:'', id:null, result:'win', r_multiple:'', pips_result:'', h4_category_id:'', m15_category_id:'', m1_category_id:'', entry_technique:'', followed_rules:true, notes:'', trade_date:'', h4_preview:null, m15_preview:null, m1_preview:null, h4_file:null, m15_file:null, m1_file:null, images:[] },

      lightbox: { show:false, index:0 },
      deleteTradeTarget: null,
      deletingTrade:     false,
    }
  },

  computed: {
    h4cats()  { return this.$store.state.app.categories.H4  || [] },
    m15cats() { return this.$store.state.app.categories.M15 || [] },
    m1cats()  { return this.$store.state.app.categories.M1  || [] },

    lightboxTrades() {
      return this.trades.map(t => ({
        ...t,
        entry_technique: t.entry_technique || 'Backtest Trade',
        pair:   t.pair || {},
        session:null, result: t.result,
      }))
    },
  },

  async mounted() {
    await Promise.all([this.loadSessions(), this.loadPairs(), this.$store.dispatch('app/loadAll')])
  },

  methods: {
    async loadSessions() {
      this.loading = true
      try { const { data } = await this.$http.get('/backtest/sessions'); this.sessions = data } catch(e) {}
      finally { this.loading = false }
    },

    async openSession(s) {
      this.activeSession = s
      this.tradesLoading = true
      try {
        const [td, st] = await Promise.all([
          this.$http.get(`/backtest/sessions/${s.id}/trades`),
          this.$http.get(`/backtest/sessions/${s.id}/stats`),
        ])
        this.trades      = td.data
        this.sessionStats= st.data.stats
      } catch(e) {}
      finally { this.tradesLoading = false }
    },

    async loadPairs() {
      try { const { data } = await this.$http.get('/pairs'); this.pairs = data } catch(e) {}
    },

    openSessionForm(s = null) {
      this.sessionForm = {
        show:true, saving:false, error:'',
        id:          s?.id || null,
        name:        s?.name || '',
        pair_id:     s?.pair_id || '',
        timeframe:   s?.timeframe || 'M1',
        description: s?.description || '',
        date_from:   s?.date_from?.slice(0,10) || '',
        date_to:     s?.date_to?.slice(0,10) || '',
        status:      s?.status || 'active',
      }
    },

    async saveSession() {
      if (!this.sessionForm.name || !this.sessionForm.pair_id) { this.sessionForm.error = 'Name and pair are required.'; return }
      this.sessionForm.saving = true; this.sessionForm.error = ''
      try {
        if (this.sessionForm.id) {
          await this.$http.put(`/backtest/sessions/${this.sessionForm.id}`, this.sessionForm)
          if (this.activeSession) this.activeSession = { ...this.activeSession, ...this.sessionForm }
        } else {
          const { data } = await this.$http.post('/backtest/sessions', this.sessionForm)
          await this.loadSessions()
          this.openSession(data)
        }
        this.sessionForm.show = false
        if (!this.activeSession) await this.loadSessions()
      } catch(e) { this.sessionForm.error = e.response?.data?.message || 'Save failed.' }
      finally { this.sessionForm.saving = false }
    },

    async deleteSession() {
      if (!confirm('Delete this entire session and all its trades?')) return
      await this.$http.delete(`/backtest/sessions/${this.sessionForm.id}`)
      this.sessionForm.show = false
      this.activeSession = null
      await this.loadSessions()
    },

    openTradeForm(t = null) {
      this.tradeForm = {
        show:true, saving:false, error:'', id: t?.id || null,
        result:          t?.result || 'win',
        r_multiple:      t?.r_multiple ?? '',
        pips_result:     t?.pips_result ?? '',
        h4_category_id:  t?.h4_category_id ? Number(t.h4_category_id) : '',
        m15_category_id: t?.m15_category_id ? Number(t.m15_category_id) : '',
        m1_category_id:  t?.m1_category_id ? Number(t.m1_category_id) : '',
        entry_technique: t?.entry_technique || '',
        followed_rules:  t?.followed_rules != null ? Boolean(t.followed_rules) : true,
        notes:           t?.notes || '',
        trade_date:      t?.trade_date?.slice(0,16) || '',
        h4_preview:null, m15_preview:null, m1_preview:null,
        h4_file:null,    m15_file:null,    m1_file:null,
        images: t?.images || [],
      }
    },

    async saveTrade() {
      if (!this.tradeForm.result) { this.tradeForm.error = 'Please select a result.'; return }
      this.tradeForm.saving = true; this.tradeForm.error = ''
      try {
        const fd = new FormData()
        const fields = ['result','r_multiple','pips_result','h4_category_id','m15_category_id','m1_category_id','entry_technique','notes','trade_date']
        fields.forEach(k => { if (this.tradeForm[k] !== '' && this.tradeForm[k] != null) fd.append(k, this.tradeForm[k]) })
        fd.append('followed_rules', this.tradeForm.followed_rules ? '1' : '0')
        if (this.tradeForm.h4_file)  fd.append('h4_image',  this.tradeForm.h4_file)
        if (this.tradeForm.m15_file) fd.append('m15_image', this.tradeForm.m15_file)
        if (this.tradeForm.m1_file)  fd.append('m1_image',  this.tradeForm.m1_file)

        const opts = { headers: { 'Content-Type': 'multipart/form-data' } }
        const sid  = this.activeSession.id
        if (this.tradeForm.id) {
          fd.append('_method','PUT')
          await this.$http.post(`/backtest/sessions/${sid}/trades/${this.tradeForm.id}`, fd, opts)
        } else {
          await this.$http.post(`/backtest/sessions/${sid}/trades`, fd, opts)
        }
        this.tradeForm.show = false
        await this.openSession(this.activeSession)
      } catch(e) { this.tradeForm.error = e.response?.data?.message || 'Save failed.' }
      finally { this.tradeForm.saving = false }
    },

    confirmDeleteTrade(t) { this.deleteTradeTarget = t },
    async doDeleteTrade() {
      this.deletingTrade = true
      try {
        await this.$http.delete(`/backtest/sessions/${this.activeSession.id}/trades/${this.deleteTradeTarget.id}`)
        this.deleteTradeTarget = null
        await this.openSession(this.activeSession)
      } finally { this.deletingTrade = false }
    },

    orderedImages(t) {
      const order = ['H4','M15','M1']
      return order.map(tf => t.images?.find(i => i.timeframe === tf)).filter(Boolean)
    },
    existingImg(tf) { return this.tradeForm.images?.find(i => i.timeframe === tf) || null },
    imgUrl(img) { return `/api/images/${img.path}` },
    openLightbox(t) {
      const idx = this.trades.findIndex(x => x.id === t.id)
      this.lightbox = { show:true, index: Math.max(0,idx) }
    },

    onFile(e, slot) { const f = e.target.files[0]; if (f) this.setImg(slot, f) },
    onDrop(e, slot) { const f = e.dataTransfer.files[0]; if (f && f.type.startsWith('image/')) this.setImg(slot, f) },
    setImg(slot, file) { this.tradeForm[`${slot}_file`] = file; this.tradeForm[`${slot}_preview`] = URL.createObjectURL(file) },
    clearImg(slot) { this.tradeForm[`${slot}_file`] = null; this.tradeForm[`${slot}_preview`] = null },

    fmt(d)  { if (!d) return ''; return new Date(d).toLocaleString('en-GB',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) },
    fmtd(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) },
  },
}
</script>

<style scoped>
/* Session cards */
.session-card { background:#1A2633; border:1px solid rgba(255,255,255,.07); border-radius:14px; padding:20px 24px; cursor:pointer; transition:all .2s; display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
.session-card:hover { border-color:rgba(29,158,117,.3); transform:translateY(-1px); }
.session-card__left { flex:1; min-width:200px; }
.session-name { font-size:16px; font-weight:700; color:#fff; }
.session-desc { font-size:13px; color:#4A5568; margin-top:4px; }
.session-status { font-size:10px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; padding:2px 8px; border-radius:100px; }
.session-status--active { background:rgba(29,158,117,.15); color:#1D9E75; }
.session-status--done   { background:rgba(255,255,255,.08); color:#64748B; }
.session-card__stats { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
.session-stat { display:flex; flex-direction:column; align-items:center; gap:2px; min-width:56px; }
.session-stat__n { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; color:#fff; }
.session-stat__l { font-size:10px; color:#4A5568; }
.session-card__arrow { font-size:18px; color:#334155; }

/* Session detail stats */
.stat-mini { background:#1A2633; border:1px solid rgba(255,255,255,.06); border-radius:10px; padding:14px; text-align:center; }
.stat-mini__n { font-family:'Syne',sans-serif; font-size:24px; font-weight:800; color:#fff; }
.stat-mini__l { font-size:10px; color:#4A5568; margin-top:2px; }

/* Trade rows */
.bt-trade { display:flex; align-items:center; gap:10px; padding:10px 14px; background:#1A2633; border-radius:10px; border-left:3px solid; transition:background .15s; }
.bt-trade:hover { background:#1E2D40; }
.bt-trade--win  { border-color:#1D9E75; }
.bt-trade--loss { border-color:#E24B4A; }
.bt-trade--breakeven { border-color:#64748B; }
.bt-trade__num  { font-size:11px; color:#4A5568; font-weight:700; width:20px; text-align:center; flex-shrink:0; }
.bt-trade__result { width:22px; height:22px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:800; }
.bt-trade__result--win  { background:rgba(29,158,117,.2); color:#1D9E75; }
.bt-trade__result--loss { background:rgba(226,75,74,.2);  color:#E24B4A; }
.bt-trade__result--breakeven { background:rgba(255,255,255,.08); color:#64748B; }
.bt-trade__info  { flex:1; min-width:0; }
.bt-trade__imgs  { display:flex; gap:4px; }
.bt-thumb { width:40px; height:30px; border-radius:4px; overflow:hidden; cursor:pointer; border:1px solid rgba(255,255,255,.06); flex-shrink:0; }
.bt-thumb img { width:100%; height:100%; object-fit:cover; }
.bt-trade__r { font-family:'Syne',sans-serif; font-size:14px; font-weight:700; min-width:50px; text-align:right; flex-shrink:0; }
.bt-trade__actions { display:flex; flex-direction:column; gap:2px; flex-shrink:0; }

/* TF badges */
.tf-badge { font-size:10px; font-weight:700; padding:2px 7px; border-radius:4px; }
.tf-badge--h4  { background:rgba(55,138,221,.2); color:#85B7EB; }
.tf-badge--m15 { background:rgba(127,119,221,.2); color:#AFA9EC; }
.tf-badge--m1  { background:rgba(212,160,23,.15); color:#D4A017; }

/* Upload slots */
.upload-slot { display:flex; flex-direction:column; gap:4px; }
.upload-slot__label { font-size:11px; color:#64748B; display:flex; align-items:center; gap:4px; }
.upload-slot__zone { border:2px dashed rgba(255,255,255,.1); border-radius:10px; height:100px; cursor:pointer; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative; transition:border-color .2s; }
.upload-slot__zone:hover, .upload-slot__zone.has-img { border-color:rgba(29,158,117,.4); }
.upload-slot__existing { position:relative; width:100%; height:100%; }
.upload-current { position:absolute; bottom:3px; right:3px; font-size:9px; font-weight:700; background:rgba(0,0,0,.7); color:#64748B; padding:1px 5px; border-radius:3px; }
.upload-inner { display:flex; flex-direction:column; align-items:center; gap:3px; }
.upload-preview { width:100%; height:100%; object-fit:contain; }
.upload-remove { position:absolute; top:4px; right:4px; width:20px; height:20px; background:rgba(226,75,74,.8); border:none; border-radius:50%; color:#fff; font-size:9px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:2; }

/* Modals */
.modal-overlay { position:fixed; inset:0; z-index:50; background:rgba(0,0,0,.75); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; padding:16px; }
.modal-panel { background:#1A2633; border:1px solid rgba(255,255,255,.1); border-radius:16px; padding:28px; width:100%; max-width:560px; max-height:92vh; overflow-y:auto; }
.modal-panel--wide { max-width:700px; }
.modal-close { width:30px; height:30px; border-radius:50%; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08); color:#64748B; cursor:pointer; font-size:13px; }

.text-win  { color:#1D9E75; }
.text-loss { color:#E24B4A; }

@media(max-width:640px) {
  .session-card { flex-direction:column; align-items:flex-start; }
  .session-card__stats { width:100%; justify-content:space-between; }
  .session-card__arrow { display:none; }
  .grid { grid-template-columns:1fr !important; }
  .stat-mini__n { font-size:18px; }
}
</style>
