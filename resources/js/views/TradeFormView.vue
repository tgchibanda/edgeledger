<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <div class="page-header">
        <h1 class="page-title">{{ isEdit ? 'Edit Trade' : 'Add Trade to Database' }}</h1>
        <p class="page-sub">{{ isEdit ? 'Update trade details.' : 'Record a historical trade in your database.' }}</p>
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <div class="card space-y-4">
          <h2 class="section-title">Trade Classification</h2>
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
              <label class="label">H4 Structure *</label>
              <select v-model="form.h4_category_id" class="select" required>
                <option value="">Select H4</option>
                <option v-for="c in h4cats" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="label">M15 Structure *</label>
              <select v-model="form.m15_category_id" class="select" required>
                <option value="">Select M15</option>
                <option v-for="c in m15cats" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="label">M1 Entry *</label>
              <select v-model="form.m1_category_id" class="select" required>
                <option value="">Select M1</option>
                <option v-for="c in m1cats" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="label">Entry Technique *</label>
              <input v-model="form.entry_technique" class="input" placeholder="e.g. OB + FVG confluence" required />
            </div>
            <div>
              <label class="label">Trade Date</label>
              <input v-model="form.trade_date" type="datetime-local" class="input" />
            </div>
          </div>
        </div>

        <div class="card space-y-4">
          <h2 class="section-title">Result & Rules</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Result *</label>
              <select v-model="form.result" class="select" required>
                <option value="">Select result</option>
                <option value="win">Win</option>
                <option value="loss">Loss</option>
                <option value="breakeven">Breakeven</option>
              </select>
            </div>
            <div>
              <label class="label">Pips Result</label>
              <input v-model="form.pips_result" type="number" step="0.1" class="input" placeholder="e.g. 25.5 or -12.0" />
            </div>
            <div>
              <label class="label">R Multiple</label>
              <input v-model="form.r_multiple" type="number" step="0.01" class="input" placeholder="e.g. 2.5 or -1.0" />
            </div>
          </div>
          <RulesToggle v-model="form.followed_rules" />
        </div>

        <div class="card space-y-4">
          <h2 class="section-title">Notes & Confluences</h2>
          <div>
            <label class="label">Confluences</label>
            <input v-model="form.confluences" class="input" placeholder="e.g. HTF OB, FVG, Session open" />
          </div>
          <div v-if="form.result === 'loss'">
            <label class="label">Mistakes</label>
            <input v-model="form.mistakes" class="input" placeholder="What went wrong?" />
          </div>
          <div>
            <label class="label">Notes</label>
            <textarea v-model="form.notes" class="textarea" rows="3" placeholder="Any additional observations…"></textarea>
          </div>
        </div>

        <div class="card">
          <h2 class="section-title">Chart Images</h2>
          <ImageUploader v-model="images" />
        </div>

        <div v-if="error" class="p-3 bg-red-900/30 border border-red-800/40 rounded-lg text-red-400 text-sm">{{ error }}</div>

        <div class="flex gap-3">
          <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'Saving…' : isEdit ? 'Update Trade' : 'Add to Database' }}</button>
          <router-link to="/database" class="btn-ghost">Cancel</router-link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout     from '../components/AppLayout.vue'
import RulesToggle   from '../components/RulesToggle.vue'
import ImageUploader from '../components/ImageUploader.vue'
export default {
  name: 'TradeFormView',
  components: { AppLayout, RulesToggle, ImageUploader },
  data() {
    return {
      form: { h4_category_id:'', m15_category_id:'', m1_category_id:'', pair_id:'', trading_session_id:'',
              entry_technique:'', result:'', followed_rules: true, pips_result:'', r_multiple:'',
              confluences:'', mistakes:'', notes:'', trade_date:'' },
      images: {},
      saving: false,
      error: '',
    }
  },
  computed: {
    isEdit()   { return !!this.$route.params.id },
    sessions() { return this.$store.state.app.sessions },
    pairs()    { return this.$store.state.app.pairs },
    h4cats()   { return this.$store.state.app.categories.H4 },
    m15cats()  { return this.$store.state.app.categories.M15 },
    m1cats()   { return this.$store.state.app.categories.M1 },
  },
  async created() {
    await this.$store.dispatch('app/loadAll')
    if (this.isEdit) {
      const { data } = await this.$http.get(`/trade-database/${this.$route.params.id}`)
      Object.assign(this.form, {
        h4_category_id: data.h4_category_id, m15_category_id: data.m15_category_id,
        m1_category_id: data.m1_category_id, pair_id: data.pair_id,
        trading_session_id: data.trading_session_id, entry_technique: data.entry_technique,
        result: data.result, followed_rules: data.followed_rules,
        pips_result: data.pips_result, r_multiple: data.r_multiple,
        confluences: data.confluences, mistakes: data.mistakes,
        notes: data.notes, trade_date: data.trade_date ? data.trade_date.slice(0,16) : '',
      })
    }
  },
  methods: {
    async submit() {
      if (this.form.followed_rules === null) { this.error = 'Please indicate if you followed your rules.'; return }
      this.saving = true; this.error = ''
      try {
        const fd = new FormData()
        Object.entries(this.form).forEach(([k,v]) => { if (v !== '' && v !== null) fd.append(k, v) })
        if (this.images.H4)  fd.append('h4_image',  this.images.H4)
        if (this.images.M15) fd.append('m15_image', this.images.M15)
        if (this.images.M1)  fd.append('m1_image',  this.images.M1)
        if (this.isEdit) {
          await this.$http.post(`/trade-database/${this.$route.params.id}?_method=PUT`, fd, { headers:{'Content-Type':'multipart/form-data'} })
        } else {
          await this.$http.post('/trade-database', fd, { headers:{'Content-Type':'multipart/form-data'} })
        }
        this.$router.push('/database')
      } catch(e) {
        this.error = e.response?.data?.message || 'Failed to save trade.'
      } finally { this.saving = false }
    },
  },
}
</script>
