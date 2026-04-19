<template>
  <div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <div>
        <label class="label">Session</label>
        <select v-model="form.trading_session_id" class="select" @change="onChange">
          <option value="">Any session</option>
          <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <div>
        <label class="label">Pair</label>
        <select v-model="form.pair_id" class="select" @change="onChange">
          <option value="">Any pair</option>
          <option v-for="p in pairs" :key="p.id" :value="p.id">{{ p.symbol }}</option>
        </select>
      </div>
      <div>
        <label class="label">{{ TF.h4 }} Structure</label>
        <select v-model="form.h4_category_id" class="select" @change="onH4Change">
          <option value="">Any {{ TF.h4 }}</option>
          <option v-for="c in h4cats" :key="c.id" :value="c.id">
            {{ c.name }}{{ c.trade_count ? ' ('+c.trade_count+')' : '' }}
          </option>
        </select>
      </div>
      <div>
        <label class="label">{{ TF.m15 }} Structure</label>
        <select v-model="form.m15_category_id" class="select" @change="onM15Change" :disabled="!form.h4_category_id && !allM15.length">
          <option value="">Any {{ TF.m15 }}</option>
          <option v-for="c in displayM15" :key="c.id" :value="c.id">
            {{ c.name }}{{ c.trade_count ? ' ('+c.trade_count+')' : '' }}
          </option>
        </select>
        <div v-if="suggestedM15.length" class="text-xs text-win mt-1">↑ Filtered by {{ TF.h4 }} history</div>
      </div>
      <div>
        <label class="label">{{ TF.m1 }} Entry</label>
        <select v-model="form.m1_category_id" class="select" @change="onChange" :disabled="!form.m15_category_id && !allM1.length">
          <option value="">Any {{ TF.m1 }}</option>
          <option v-for="c in displayM1" :key="c.id" :value="c.id">
            {{ c.name }}{{ c.trade_count ? ' ('+c.trade_count+')' : '' }}
          </option>
        </select>
        <div v-if="suggestedM1.length" class="text-xs text-win mt-1">↑ Filtered by {{ TF.h4 }}+{{ TF.m15 }} history</div>
      </div>
    </div>
    <div class="flex gap-3 mt-4">
      <button type="button" class="btn-primary" :disabled="loading" @click="submit">
        {{ loading ? 'Searching…' : '🔍 Find Matching Setups' }}
      </button>
      <button type="button" class="btn-ghost" @click="clear">Clear</button>
    </div>
  </div>
</template>

<script>
import { TF } from '@/timeframes.js'
export default {
  name: 'CascadeFilter',
  data() {
    return {
      TF,
      form: { h4_category_id:'', m15_category_id:'', m1_category_id:'', pair_id:'', trading_session_id:'' },
      suggestedM15: [],
      suggestedM1:  [],
      loading: false,
    }
  },
  computed: {
    sessions() { return this.$store.state.app.sessions },
    pairs()    { return this.$store.state.app.pairs },
    h4cats()   { return this.$store.state.app.categories.H4 },
    allM15()   { return this.$store.state.app.categories.M15 },
    allM1()    { return this.$store.state.app.categories.M1 },
    displayM15() { return this.suggestedM15.length ? this.suggestedM15 : this.allM15 },
    displayM1()  { return this.suggestedM1.length  ? this.suggestedM1  : this.allM1 },
  },
  async created() { await this.$store.dispatch('app/loadAll') },
  methods: {
    async onH4Change() {
      this.form.m15_category_id = ''
      this.form.m1_category_id  = ''
      this.suggestedM15 = []
      this.suggestedM1  = []
      if (this.form.h4_category_id) {
        const { data } = await this.$http.get('/categories/suggest', { params: { h4_category_id: this.form.h4_category_id } })
        this.suggestedM15 = data.m15 || []
      }
      this.onChange()
    },
    async onM15Change() {
      this.form.m1_category_id = ''
      this.suggestedM1 = []
      if (this.form.h4_category_id && this.form.m15_category_id) {
        const { data } = await this.$http.get('/categories/suggest', { params: { h4_category_id: this.form.h4_category_id, m15_category_id: this.form.m15_category_id } })
        this.suggestedM1 = data.m1 || []
      }
      this.onChange()
    },
    onChange() { this.$emit('change', { ...this.form }) },
    async submit() {
      this.loading = true
      this.$emit('loading', true)
      try {
        const params = Object.fromEntries(Object.entries(this.form).filter(([,v]) => v))
        const { data } = await this.$http.post('/trade-database/filter', params)
        this.$emit('results', data)
      } finally {
        this.loading = false
        this.$emit('loading', false)
      }
    },
    clear() {
      this.form = { h4_category_id:'', m15_category_id:'', m1_category_id:'', pair_id:'', trading_session_id:'' }
      this.suggestedM15 = []
      this.suggestedM1  = []
      this.$emit('cleared')
      this.$emit('change', { ...this.form })
    },
    getForm() { return { ...this.form } },
  },
}
</script>