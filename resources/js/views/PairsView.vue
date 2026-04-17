<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <div class="page-header">
        <h1 class="page-title">Currency Pairs</h1>
        <p class="page-sub">Manage the pairs you trade.</p>
      </div>

      <!-- Add pair -->
      <div class="card mb-6">
        <h2 class="section-title">Add Pair</h2>
        <div class="flex gap-3">
          <input v-model="newSymbol" class="input" placeholder="e.g. EURUSD, GBPJPY" @keyup.enter="addPair" />
          <button class="btn-primary flex-shrink-0" :disabled="!newSymbol || saving" @click="addPair">
            {{ saving ? 'Adding…' : 'Add' }}
          </button>
        </div>
        <div v-if="error" class="text-red-400 text-sm mt-2">{{ error }}</div>
        <!-- Quick add common pairs -->
        <div class="mt-3 flex flex-wrap gap-2">
          <span class="text-xs text-gray-500">Quick add:</span>
          <button v-for="sym in quickPairs" :key="sym"
            class="text-xs px-2 py-1 bg-surface border border-border rounded hover:border-win text-gray-400 hover:text-white transition-colors"
            @click="quickAdd(sym)">{{ sym }}</button>
        </div>
      </div>

      <!-- Pairs list -->
      <div class="card">
        <h2 class="section-title">Your Pairs ({{ pairs.length }})</h2>
        <div v-if="!pairs.length" class="text-gray-600 text-sm text-center py-6">No pairs added yet.</div>
        <div v-else class="space-y-2">
          <div v-for="p in pairs" :key="p.id"
            class="flex items-center justify-between px-4 py-3 bg-surface rounded-lg border border-border">
            <div class="flex items-center gap-3">
              <span class="font-mono font-semibold text-white text-sm">{{ p.symbol }}</span>
              <span :class="p.is_active ? 'badge-win' : 'badge-invalid'">
                {{ p.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <div class="flex gap-2">
              <button @click="toggleActive(p)"
                class="btn-secondary btn-sm">
                {{ p.is_active ? 'Disable' : 'Enable' }}
              </button>
              <button @click="deletePair(p)" class="btn-danger btn-sm">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../components/AppLayout.vue'
export default {
  name: 'PairsView',
  components: { AppLayout },
  data() {
    return {
      pairs: [],
      newSymbol: '',
      saving: false,
      error: '',
      quickPairs: ['EURUSD','GBPUSD','USDJPY','GBPJPY','AUDUSD','USDCAD','XAUUSD','NAS100','US30','GBPNZD'],
    }
  },
  async created() { await this.load() },
  methods: {
    async load() {
      const { data } = await this.$http.get('/pairs')
      this.pairs = data
    },
    async addPair() {
      if (!this.newSymbol.trim()) return
      this.saving = true; this.error = ''
      try {
        const { data } = await this.$http.post('/pairs', { symbol: this.newSymbol.trim() })
        this.pairs.push(data)
        this.newSymbol = ''
        await this.$store.dispatch('app/loadPairs')
      } catch(e) {
        this.error = e.response?.data?.message || 'Failed to add pair.'
      } finally { this.saving = false }
    },
    async quickAdd(sym) {
      if (this.pairs.find(p => p.symbol === sym)) return
      this.newSymbol = sym
      await this.addPair()
    },
    async toggleActive(pair) {
      const { data } = await this.$http.put(`/pairs/${pair.id}`, { is_active: !pair.is_active })
      const i = this.pairs.findIndex(p => p.id === pair.id)
      if (i > -1) this.pairs.splice(i, 1, data)
      await this.$store.dispatch('app/loadPairs')
    },
    async deletePair(pair) {
      if (!confirm(`Delete ${pair.symbol}? This cannot be undone.`)) return
      await this.$http.delete(`/pairs/${pair.id}`)
      this.pairs = this.pairs.filter(p => p.id !== pair.id)
      await this.$store.dispatch('app/loadPairs')
    },
  },
}
</script>
