<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <div class="page-header">
        <h1 class="page-title">{{ isEdit ? 'Edit Trade' : 'Add Trade to Database' }}</h1>
        <p class="page-sub">{{ isEdit ? 'Update trade details below.' : 'Record a historical trade in your database.' }}</p>
      </div>

      <!-- Loading state for edit -->
      <div v-if="isEdit && loadingTrade" class="card flex items-center gap-3 text-gray-500">
        <div class="w-5 h-5 border-2 border-gray-600 border-t-win rounded-full animate-spin"></div>
        Loading trade data…
      </div>

      <form v-else @submit.prevent="submit" class="space-y-5">

        <!-- ── Classification ── -->
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
              <input v-model="form.entry_technique" class="input" placeholder="e.g. OB + FVG confluence" required />
            </div>
            <div>
              <label class="label">Trade Date</label>
              <input v-model="form.trade_date" type="datetime-local" class="input" />
            </div>
          </div>
        </div>

        <!-- ── Result & Rules ── -->
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

        <!-- ── Notes ── -->
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

        <!-- ── Chart Images ── -->
        <div class="card">
          <h2 class="section-title">Chart Images</h2>
          <p class="text-xs text-gray-500 mb-4">Your normal trade screenshots — drawings and annotations are fine here.</p>

          <!-- Show existing images when editing -->
          <div v-if="isEdit && existingImages.length" class="mb-4">
            <p class="text-xs text-gray-500 mb-2">Current images — upload new ones below to replace:</p>
            <div class="flex gap-3">
              <div v-for="img in existingImages" :key="img.id"
                class="flex-1 rounded-lg overflow-hidden border border-border bg-surface"
                style="height:120px">
                <img :src="`/api/images/${img.path}`" class="w-full h-full object-contain" />
                <div class="text-center text-xs py-1"
                  :class="img.timeframe==='H4'?'text-blue-400':img.timeframe==='M15'?'text-purple-400':'text-yellow-400'">
                  {{ img.timeframe === 'H4' ? TF.h4 : img.timeframe === 'M15' ? TF.m15 : TF.m1 }}
                </div>
              </div>
            </div>
          </div>

          <ImageUploader v-model="images" />
        </div>

        <div v-if="error" class="p-3 bg-red-900/30 border border-red-800/40 rounded-lg text-red-400 text-sm">{{ error }}</div>

        <div class="flex gap-3">
          <button type="submit" class="btn-primary" :disabled="saving">
            {{ saving ? 'Saving…' : isEdit ? '✓ Update Trade' : 'Add to Database' }}
          </button>
          <router-link to="/database" class="btn-ghost">Cancel</router-link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout           from '../components/AppLayout.vue'
import RulesToggle         from '../components/RulesToggle.vue'
import ImageUploader       from '../components/ImageUploader.vue'
import { TF } from '@/timeframes.js'

export default {
  name: 'TradeFormView',
  components: { AppLayout, RulesToggle, ImageUploader },
  data() {
    return {
      loadingTrade: false,
      TF,
      form: {
        h4_category_id: '', m15_category_id: '', m1_category_id: '',
        pair_id: '', trading_session_id: '', entry_technique: '',
        result: '', followed_rules: true,
        pips_result: '', r_multiple: '',
        confluences: '', mistakes: '', notes: '', trade_date: '',
      },
      images:         {},
      existingImages: [],   // current images on the trade (for edit display)
      saving:         false,
      error:          '',
      savedTradeId:   null,
    }
  },
  computed: {
    isEdit()   { return !!this.$route.params.id },
    sessions() { return this.$store.state.app.sessions },
    pairs()    { return this.$store.state.app.pairs },
    h4cats()   { return this.$store.state.app.categories.H4 },
    m15cats()  { return this.$store.state.app.categories.M15 },
    m1cats()   { return this.$store.state.app.categories.M1 },
    trainingDirection() {
      if (this.form.result === 'win')  return 'bullish'
      if (this.form.result === 'loss') return 'bearish'
      return 'neutral'
    },
  },

  async created() {
    // Load categories first, then populate form — this prevents the
    // select options being empty when Vue tries to set the value
    await this.$store.dispatch('app/loadAll')

    if (this.isEdit) {
      this.loadingTrade = true
      try {
        const { data } = await this.$http.get(`/trade-database/${this.$route.params.id}`)

        // Store existing images for display
        this.existingImages = data.images || []

        // Map integer IDs — Vue selects require exact type match
        this.form = {
          h4_category_id:     data.h4_category_id     ? Number(data.h4_category_id)     : '',
          m15_category_id:    data.m15_category_id    ? Number(data.m15_category_id)    : '',
          m1_category_id:     data.m1_category_id     ? Number(data.m1_category_id)     : '',
          pair_id:            data.pair_id             ? Number(data.pair_id)             : '',
          trading_session_id: data.trading_session_id ? Number(data.trading_session_id) : '',
          entry_technique:    data.entry_technique    || '',
          result:             data.result             || '',
          followed_rules:     data.followed_rules != null ? Boolean(data.followed_rules) : true,
          pips_result:        data.pips_result        != null ? data.pips_result         : '',
          r_multiple:         data.r_multiple         != null ? data.r_multiple          : '',
          confluences:        data.confluences        || '',
          mistakes:           data.mistakes           || '',
          notes:              data.notes              || '',
          trade_date:         data.trade_date         ? data.trade_date.slice(0,16)      : '',
        }
      } catch(e) {
        this.error = 'Failed to load trade data.'
      } finally {
        this.loadingTrade = false
      }
    }
  },

  methods: {
    async submit() {
      this.saving = true
      this.error  = ''
      try {
        const fd = new FormData()
        Object.entries(this.form).forEach(([k, v]) => {
          if (v !== '' && v !== null && v !== undefined) fd.append(k, v)
        })
        if (this.images.H4)  fd.append('h4_image',  this.images.H4)
        if (this.images.M15) fd.append('m15_image', this.images.M15)
        if (this.images.M1)  fd.append('m1_image',  this.images.M1)

        if (this.isEdit) {
          await this.$http.post(
            `/trade-database/${this.$route.params.id}?_method=PUT`, fd,
            { headers: { 'Content-Type': 'multipart/form-data' } }
          )
          this.$router.push('/database')
        } else {
          const { data } = await this.$http.post('/trade-database', fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
          this.savedTradeId = data.id
          window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' })
        }
      } catch(e) {
        this.error = e.response?.data?.message || 'Failed to save trade.'
      } finally { this.saving = false }
    },
  },
}
</script>