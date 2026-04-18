<template>
  <AppLayout>
    <div class="p-6 max-w-5xl">
      <div class="flex items-center justify-between mb-6">
        <div><h1 class="page-title">Trade Database</h1><p class="page-sub">Your historical trade reference library.</p></div>
        <router-link to="/database/new" class="btn-primary">➕ Add Trade</router-link>
      </div>

      <!-- Filters -->
      <div class="card mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div>
            <label class="label">Pair</label>
            <select v-model="filters.pair_id" class="select" @change="load">
              <option value="">All pairs</option>
              <option v-for="p in pairs" :key="p.id" :value="p.id">{{ p.symbol }}</option>
            </select>
          </div>
          <div>
            <label class="label">Result</label>
            <select v-model="filters.result" class="select" @change="load">
              <option value="">All results</option>
              <option value="win">Win</option>
              <option value="loss">Loss</option>
              <option value="breakeven">Breakeven</option>
            </select>
          </div>
          <div class="flex items-end">
            <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer pb-2">
              <input type="checkbox" v-model="filters.reference" @change="load" class="accent-win" />
              Reference only
            </label>
          </div>
          <div class="flex items-end">
            <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer pb-2">
              <input type="checkbox" v-model="filters.valid_only" @change="load" class="accent-win" />
              Valid only
            </label>
          </div>
        </div>
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-500">Loading…</div>
      <div v-else-if="!trades.length" class="card text-center py-10">
        <div class="text-4xl mb-3">🗄️</div>
        <p class="text-gray-500">No trades in database yet.</p>
        <router-link to="/database/new" class="btn-primary mt-4 inline-block">Add your first trade</router-link>
      </div>
      <div v-else class="space-y-3">
        <TradeCard v-for="t in trades" :key="t.id" :trade="t" @view-images="openLightbox">
          <template #actions>
            <button @click="toggleReference(t)" class="btn-secondary btn-sm" :class="t.is_reference ? 'text-yellow-400' : ''">
              {{ t.is_reference ? '⭐ Reference' : '☆ Make Reference' }}
            </button>
            <router-link :to="'/database/'+t.id+'/edit'" class="btn-secondary btn-sm">Edit</router-link>
            <button @click="deleteTrade(t)" class="btn-danger btn-sm">Delete</button>
          </template>
        </TradeCard>
        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-3 pt-4">
          <button @click="changePage(pagination.current_page-1)" :disabled="pagination.current_page===1" class="btn-secondary btn-sm">← Prev</button>
          <span class="text-sm text-gray-500">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
          <button @click="changePage(pagination.current_page+1)" :disabled="pagination.current_page===pagination.last_page" class="btn-secondary btn-sm">Next →</button>
        </div>
      </div>

      <ImageLightbox :visible="lightbox.visible" :images="lightbox.images" :start-at="lightbox.startAt" @close="lightbox.visible=false" />
    </div>
  </AppLayout>
</template>

<script>
import AppLayout     from '../components/AppLayout.vue'
import TradeCard     from '../components/TradeCard.vue'
import ImageLightbox from '../components/ImageLightbox.vue'
export default {
  name: 'DatabaseView',
  components: { AppLayout, TradeCard, ImageLightbox },
  data() {
    return {
      trades: [], loading: true,
      filters: { pair_id:'', result:'', reference: false, valid_only: false },
      pagination: {},
      page: 1,
      lightbox: { visible: false, images: [], startAt: '' },
    }
  },
  computed: {
    pairs() { return this.$store.state.app.pairs },
  },
  async created() {
    await this.$store.dispatch('app/loadPairs')
    await this.load()
  },
  methods: {
    async load() {
      this.loading = true
      const params = { page: this.page }
      if (this.filters.pair_id)   params.pair_id   = this.filters.pair_id
      if (this.filters.result)    params.result     = this.filters.result
      if (this.filters.reference) params.reference  = 1
      if (this.filters.valid_only)params.valid_only = 1
      const { data } = await this.$http.get('/trade-database', { params })
      this.trades     = data.data
      this.pagination = { current_page: data.current_page, last_page: data.last_page }
      this.loading = false
    },
    async toggleReference(trade) {
      const { data } = await this.$http.post(`/trade-database/${trade.id}/promote`)
      const i = this.trades.findIndex(t => t.id === trade.id)
      if (i > -1) this.trades.splice(i, 1, data)
    },
    async deleteTrade(trade) {
      if (!confirm('Delete this trade? This cannot be undone.')) return
      await this.$http.delete(`/trade-database/${trade.id}`)
      this.trades = this.trades.filter(t => t.id !== trade.id)
    },
    changePage(p) { this.page = p; this.load() },
    openLightbox({ images, startAt }) { this.lightbox = { visible: true, images, startAt } },
  },
}
</script>