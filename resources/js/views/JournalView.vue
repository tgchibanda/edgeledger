<template>
  <AppLayout>
    <div class="p-6 max-w-5xl">
      <div class="flex items-center justify-between mb-6">
        <div><h1 class="page-title">Journal</h1><p class="page-sub">Your trade journal entries.</p></div>
        <router-link to="/journal/new" class="btn-primary">📓 New Entry</router-link>
      </div>

      <div class="card mb-5">
        <div class="flex gap-4 flex-wrap">
          <select v-model="filters.status" class="select w-auto" @change="load">
            <option value="">All entries</option>
            <option value="pre_trade">Pre-trade only</option>
            <option value="completed">Completed only</option>
          </select>
          <select v-model="filters.result" class="select w-auto" @change="load">
            <option value="">All results</option>
            <option value="win">Win</option>
            <option value="loss">Loss</option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-500">Loading…</div>
      <div v-else-if="!journals.length" class="card text-center py-10">
        <div class="text-4xl mb-3">📓</div>
        <p class="text-gray-500">No journal entries yet.</p>
        <router-link to="/journal/new" class="btn-primary mt-4 inline-block">Create first entry</router-link>
      </div>

      <div v-else class="space-y-3">
        <div v-for="j in journals" :key="j.id" class="card hover:bg-card-hover transition-colors cursor-pointer" @click="$router.push('/journal/'+j.id)">
          <div class="flex items-start justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-2 flex-wrap">
              <span v-if="j.status==='pre_trade'" class="badge-pre">📋 Pre-Trade</span>
              <span v-else-if="j.result==='win'"  class="badge-win">WIN</span>
              <span v-else-if="j.result==='loss'" class="badge-loss">LOSS</span>
              <span v-else                         class="badge-neutral">COMPLETED</span>
              <span v-if="j.is_valid===false"      class="badge-invalid">✗ Invalid</span>
              <span v-if="j.promote_to_database"   class="badge-ref">⭐ In Database</span>
              <span class="text-sm font-semibold text-white">{{ j.entry_technique }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500">
              <span v-if="j.pair">{{ j.pair.symbol }}</span>
              <span v-if="j.session">{{ j.session.name }}</span>
              <span>{{ formatDate(j.trade_date || j.created_at) }}</span>
            </div>
          </div>
          <div class="mt-1 flex items-center gap-2 text-xs text-gray-500">
            <span class="text-blue-400">{{ j.h4 && j.h4.name }}</span>
            <span>→</span>
            <span class="text-purple-400">{{ j.m15 && j.m15.name }}</span>
            <span>→</span>
            <span class="text-yellow-400">{{ j.m1 && j.m1.name }}</span>
          </div>
          <div v-if="j.pre_trade_notes" class="mt-2 text-xs text-gray-600 line-clamp-1">{{ j.pre_trade_notes }}</div>
        </div>

        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-3 pt-4">
          <button @click="changePage(pagination.current_page-1)" :disabled="pagination.current_page===1" class="btn-secondary btn-sm">← Prev</button>
          <span class="text-sm text-gray-500">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
          <button @click="changePage(pagination.current_page+1)" :disabled="pagination.current_page===pagination.last_page" class="btn-secondary btn-sm">Next →</button>
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
    return { journals: [], loading: true, filters: { status:'', result:'' }, pagination: {}, page: 1 }
  },
  async created() { await this.load() },
  methods: {
    async load() {
      this.loading = true
      const params = { page: this.page }
      if (this.filters.status) params.status = this.filters.status
      if (this.filters.result) params.result = this.filters.result
      const { data } = await this.$http.get('/journals', { params })
      this.journals   = data.data
      this.pagination = { current_page: data.current_page, last_page: data.last_page }
      this.loading = false
    },
    changePage(p) { this.page = p; this.load() },
    formatDate(d) { return d ? new Date(d).toLocaleDateString() : '' },
  },
}
</script>
