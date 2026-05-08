<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <div class="page-header">
        <h1 class="page-title">{{ isEdit ? 'Edit Journal Entry' : 'New Journal Entry' }}</h1>
        <p class="page-sub">{{ isEdit ? 'Update your pre-trade plan.' : 'Plan your trade before you take it.' }}</p>
      </div>

      <div v-if="isEdit && loadingEntry" class="card flex items-center gap-3 text-gray-500 text-sm">
        <div class="w-4 h-4 border-2 border-gray-600 border-t-win rounded-full animate-spin"></div>
        Loading entry…
      </div>

      <form v-else @submit.prevent="submit" class="space-y-5">

        <!-- Classification -->
        <div class="card space-y-4">
          <h2 class="section-title">Trade Setup</h2>
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
              <label class="label">MTF Trigger *</label>
              <select v-model="form.m15_category_id" class="select" required>
                <option value="">Select MTF</option>
                <option v-for="c in m15cats" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="label">Entry TF Signal *</label>
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

        <!-- Pre-trade plan -->
        <div class="card space-y-4">
          <h2 class="section-title">Pre-Trade Plan</h2>
          <div>
            <label class="label">Pre-Trade Notes</label>
            <textarea v-model="form.pre_trade_notes" class="textarea" rows="4"
              placeholder="What is your trade plan? What do you need to see? HTF context, MTF trigger, entry conditions…"></textarea>
          </div>
        </div>

        <!-- Post-trade results (always editable) -->
        <div class="card space-y-4">
          <h2 class="section-title">Result & Rules</h2>
          <p class="text-xs text-gray-500">Leave blank if this is a pre-trade plan only.</p>

          <div>
            <label class="label">Result</label>
            <div class="flex gap-2">
              <button v-for="r in ['win','loss','breakeven']" :key="r"
                type="button"
                class="flex-1 py-2.5 rounded-lg text-sm font-bold border capitalize transition-colors"
                :class="form.result === r
                  ? r==='win'  ? 'bg-win/20 border-win text-win'
                  : r==='loss' ? 'bg-loss/20 border-loss text-loss'
                  : 'bg-gray-700/40 border-gray-600 text-gray-300'
                  : 'bg-surface border-border text-gray-500 hover:text-gray-300'"
                @click="form.result = form.result === r ? '' : r">
                {{ r }}
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">R Multiple</label>
              <input v-model="form.r_multiple" type="number" step="0.01" class="input" placeholder="e.g. 2.5 or -1.0" />
            </div>
            <div>
              <label class="label">Pips Result</label>
              <input v-model="form.pips_result" type="number" step="0.1" class="input" placeholder="e.g. 25.5 or -12.0" />
            </div>
          </div>

          <div>
            <label class="label">Followed Rules?</label>
            <div class="flex gap-3">
              <button type="button"
                class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors"
                :class="form.followed_rules ? 'bg-win/15 border-win text-win' : 'bg-surface border-border text-gray-500'"
                @click="form.followed_rules = true">✓ Yes — Disciplined</button>
              <button type="button"
                class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors"
                :class="!form.followed_rules ? 'bg-loss/15 border-loss text-loss' : 'bg-surface border-border text-gray-500'"
                @click="form.followed_rules = false">✗ No — Impulsive</button>
            </div>
          </div>

          <div>
            <label class="label">Post-Trade Notes</label>
            <textarea v-model="form.post_trade_notes" class="textarea" rows="3"
              placeholder="What happened? What did you observe after the trade?"></textarea>
          </div>

          <div>
            <label class="label">Mistakes</label>
            <textarea v-model="form.mistakes" class="textarea" rows="2"
              placeholder="What would you do differently?"></textarea>
          </div>
        </div>

        <!-- Chart images -->
        <div class="card">
          <h2 class="section-title">Chart Screenshots</h2>
          <p class="text-xs text-gray-500 mb-4">Upload your pre-trade analysis charts.</p>

          <!-- Existing images (edit mode) -->
          <div v-if="isEdit && existingImages.length" class="mb-4">
            <p class="text-xs text-gray-500 mb-2">Current images — upload new ones to replace:</p>
            <div class="grid grid-cols-3 gap-3">
              <div v-for="img in existingImages" :key="img.id"
                class="rounded-lg overflow-hidden border border-border bg-surface">
                <img :src="`/api/images/${img.path}`" class="w-full h-24 object-contain" />
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
            {{ saving ? 'Saving…' : isEdit ? '✓ Update Entry' : 'Save Journal Entry' }}
          </button>
          <button type="button" class="btn-ghost" @click="$router.back()">Cancel</button>
        </div>

      </form>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout     from '../components/AppLayout.vue'
import ImageUploader from '../components/ImageUploader.vue'
import { TF }        from '@/timeframes.js'

export default {
  name: 'JournalFormView',
  components: { AppLayout, ImageUploader },

  data() {
    return {
      TF,
      loadingEntry:   false,
      saving:         false,
      error:          '',
      existingImages: [],
      images:         {},
      form: {
        trading_session_id: '',
        pair_id:            '',
        h4_category_id:     '',
        m15_category_id:    '',
        m1_category_id:     '',
        entry_technique:    '',
        pre_trade_notes:    '',
        trade_date:         '',
        // Post-trade fields
        result:             '',
        r_multiple:         '',
        pips_result:        '',
        followed_rules:     true,
        post_trade_notes:   '',
        mistakes:           '',
      },
    }
  },

  computed: {
    isEdit()   { return !!this.$route.params.id },
    sessions() { return this.$store.state.app.sessions || [] },
    pairs()    { return this.$store.state.app.pairs    || [] },
    h4cats()   { return this.$store.state.app.categories.H4  || [] },
    m15cats()  { return this.$store.state.app.categories.M15 || [] },
    m1cats()   { return this.$store.state.app.categories.M1  || [] },
  },

  async created() {
    // Load dropdowns first so selects have options before values are set
    await this.$store.dispatch('app/loadAll')

    if (this.isEdit) {
      this.loadingEntry = true
      try {
        const { data } = await this.$http.get(`/journals/${this.$route.params.id}`)
        this.existingImages = data.images || []
        this.form = {
          trading_session_id: data.trading_session_id ? Number(data.trading_session_id) : '',
          pair_id:            data.pair_id            ? Number(data.pair_id)            : '',
          h4_category_id:     data.h4_category_id     ? Number(data.h4_category_id)     : '',
          m15_category_id:    data.m15_category_id    ? Number(data.m15_category_id)    : '',
          m1_category_id:     data.m1_category_id     ? Number(data.m1_category_id)     : '',
          entry_technique:    data.entry_technique    || '',
          pre_trade_notes:    data.pre_trade_notes    || '',
          trade_date:         data.trade_date         ? data.trade_date.slice(0, 16) : '',
          result:             data.result             || '',
          r_multiple:         data.r_multiple         != null ? data.r_multiple  : '',
          pips_result:        data.pips_result        != null ? data.pips_result : '',
          followed_rules:     data.followed_rules     != null ? Boolean(data.followed_rules) : true,
          post_trade_notes:   data.post_trade_notes   || '',
          mistakes:           data.mistakes           || '',
        }
      } catch(e) {
        this.error = 'Failed to load journal entry.'
      } finally {
        this.loadingEntry = false
      }
    }
  },

  methods: {
    async submit() {
      this.saving = true
      this.error  = ''
      try {
        const fd = new FormData()

        // Pre-trade fields
        const preFields = ['trading_session_id','pair_id','h4_category_id','m15_category_id','m1_category_id','entry_technique','pre_trade_notes','trade_date']
        preFields.forEach(k => {
          if (this.form[k] !== '' && this.form[k] != null) fd.append(k, this.form[k])
        })

        // Post-trade fields — only append if result is set
        if (this.form.result) {
          fd.append('result', this.form.result)
          fd.append('followed_rules', this.form.followed_rules ? '1' : '0')
          if (this.form.r_multiple !== '' && this.form.r_multiple != null)   fd.append('r_multiple',       this.form.r_multiple)
          if (this.form.pips_result !== '' && this.form.pips_result != null) fd.append('pips_result',      this.form.pips_result)
          if (this.form.post_trade_notes) fd.append('post_trade_notes', this.form.post_trade_notes)
          if (this.form.mistakes)         fd.append('mistakes',         this.form.mistakes)
          // Mark as completed if result is provided
          fd.append('status', 'completed')
        } else {
          // No result — keep as pre if new, preserve status if editing
          if (!this.isEdit) fd.append('status', 'pre')
          // Still save followed_rules and notes even without result
          fd.append('followed_rules', this.form.followed_rules ? '1' : '0')
          if (this.form.post_trade_notes) fd.append('post_trade_notes', this.form.post_trade_notes)
          if (this.form.mistakes)         fd.append('mistakes',         this.form.mistakes)
        }

        // Images
        if (this.images.H4)  fd.append('h4_image',  this.images.H4)
        if (this.images.M15) fd.append('m15_image', this.images.M15)
        if (this.images.M1)  fd.append('m1_image',  this.images.M1)

        const opts = { headers: { 'Content-Type': 'multipart/form-data' } }

        if (this.isEdit) {
          await this.$http.post(`/journals/${this.$route.params.id}?_method=PUT`, fd, opts)
          this.$router.push(`/journal/${this.$route.params.id}`)
        } else {
          const { data } = await this.$http.post('/journals', fd, opts)
          this.$router.push(`/journal/${data.id}`)
        }
      } catch(e) {
        const errs = e.response?.data?.errors
        this.error = errs
          ? Object.values(errs).flat().join(' ')
          : e.response?.data?.message || 'Failed to save.'
      } finally { this.saving = false }
    },
  },
}
</script>