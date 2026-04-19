<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <div class="page-header">
        <h1 class="page-title">New Journal Entry</h1>
        <p class="page-sub">Step {{ step }} of 2 — {{ stepLabel }}</p>
      </div>

      <!-- Step indicators -->
      <div class="flex gap-2 mb-6">
        <div v-for="n in 2" :key="n" class="flex-1 h-1.5 rounded-full" :class="n <= step ? 'bg-win' : 'bg-border'"></div>
      </div>

      <!-- Step 1: Conditions -->
      <div v-if="step === 1" class="card space-y-5">
        <h2 class="section-title">Market Conditions</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Session *</label>
            <select v-model="form.trading_session_id" class="select" required>
              <option value="">Select session</option>
              <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Pair *</label>
            <select v-model="form.pair_id" class="select" required>
              <option value="">Select pair</option>
              <option v-for="p in pairs" :key="p.id" :value="p.id">{{ p.symbol }}</option>
            </select>
          </div>
          <div>
            <label class="label">HTF Structure *</label>
            <select v-model="form.h4_category_id" class="select" required>
              <option value="">Select HTF</option>
              <option v-for="c in h4cats" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">MTF Structure *</label>
            <select v-model="form.m15_category_id" class="select" required>
              <option value="">Select MTF</option>
              <option v-for="c in m15cats" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Entry TF *</label>
            <select v-model="form.m1_category_id" class="select" required>
              <option value="">Select Entry TF</option>
              <option v-for="c in m1cats" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Entry Technique *</label>
            <input v-model="form.entry_technique" class="input" placeholder="e.g. OB + FVG" required />
          </div>
          <div>
            <label class="label">Trade Date/Time</label>
            <input v-model="form.trade_date" type="datetime-local" class="input" />
          </div>
        </div>
        <div>
          <label class="label">Confluences</label>
          <input v-model="form.confluences" class="input" placeholder="e.g. HTF OB, FVG, session open" />
        </div>
        <div>
          <label class="label">Pre-Trade Notes</label>
          <textarea v-model="form.pre_trade_notes" class="textarea" rows="3" placeholder="What is your thesis? What are you expecting?"></textarea>
        </div>
        <div>
          <label class="label text-yellow-400">⚠️ Reason NOT to take this trade</label>
          <textarea v-model="form.reason_not_to_take" class="textarea" rows="2" placeholder="Devil's advocate — why should you NOT take this trade?"></textarea>
        </div>
        <div>
          <label class="label">Pre-Trade Charts</label>
          <ImageUploader v-model="images" />
        </div>
        <div class="flex gap-3 pt-2">
          <button type="button" class="btn-primary" :disabled="!step1Valid || saving" @click="savePreTrade">
            {{ saving ? 'Saving…' : 'Save Pre-Trade Entry →' }}
          </button>
          <router-link to="/journal" class="btn-ghost">Cancel</router-link>
        </div>
        <div v-if="error" class="text-red-400 text-sm">{{ error }}</div>
      </div>

      <!-- Step 2: Post-trade -->
      <div v-if="step === 2" class="space-y-5">
        <div class="card">
          <div class="text-sm text-gray-500 mb-1">Entry saved. Come back after your trade to complete this entry.</div>
          <div class="flex gap-3">
            <router-link to="/journal" class="btn-secondary">← Back to Journal</router-link>
            <router-link :to="'/journal/'+journalId" class="btn-primary">Complete Trade Result</router-link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout     from '../components/AppLayout.vue'
import ImageUploader from '../components/ImageUploader.vue'
export default {
  name: 'JournalFormView',
  components: { AppLayout, ImageUploader },
  data() {
    return {
      step: 1,
      journalId: null,
      form: {
        h4_category_id:'', m15_category_id:'', m1_category_id:'',
        pair_id:'', trading_session_id:'', entry_technique:'',
        pre_trade_notes:'', reason_not_to_take:'', confluences:'', trade_date:'',
      },
      images: {},
      saving: false,
      error: '',
    }
  },
  computed: {
    sessions()  { return this.$store.state.app.sessions },
    pairs()     { return this.$store.state.app.pairs },
    h4cats()    { return this.$store.state.app.categories.H4 },
    m15cats()   { return this.$store.state.app.categories.M15 },
    m1cats()    { return this.$store.state.app.categories.M1 },
    stepLabel() { return this.step === 1 ? 'Pre-Trade Setup' : 'Entry Saved' },
    step1Valid() {
      return this.form.h4_category_id && this.form.m15_category_id &&
             this.form.m1_category_id && this.form.pair_id &&
             this.form.trading_session_id && this.form.entry_technique
    },
  },
  async created() {
    await this.$store.dispatch('app/loadAll')
    // Pre-fill from filter query params
    const q = this.$route.query
    if (q.h4_category_id)     this.form.h4_category_id     = q.h4_category_id
    if (q.m15_category_id)    this.form.m15_category_id    = q.m15_category_id
    if (q.m1_category_id)     this.form.m1_category_id     = q.m1_category_id
    if (q.pair_id)            this.form.pair_id            = q.pair_id
    if (q.trading_session_id) this.form.trading_session_id = q.trading_session_id
    if (q.entry_technique)    this.form.entry_technique    = q.entry_technique
  },
  methods: {
    async savePreTrade() {
      this.saving = true; this.error = ''
      try {
        const fd = new FormData()
        Object.entries(this.form).forEach(([k,v]) => { if (v) fd.append(k, v) })
        if (this.images.H4)  fd.append('h4_image',  this.images.H4)
        if (this.images.M15) fd.append('m15_image', this.images.M15)
        if (this.images.M1)  fd.append('m1_image',  this.images.M1)
        const { data } = await this.$http.post('/journals', fd, { headers:{'Content-Type':'multipart/form-data'} })
        this.journalId = data.id
        this.step = 2
      } catch(e) {
        this.error = e.response?.data?.message || 'Failed to save.'
      } finally { this.saving = false }
    },
  },
}
</script>