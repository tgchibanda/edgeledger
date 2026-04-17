<template>
  <AppLayout>
    <div class="p-6 max-w-4xl">
      <div class="page-header"><h1 class="page-title">Categories</h1><p class="page-sub">Manage your H4, M15 and M1 market structure categories.</p></div>
      <div v-for="tf in ['H4','M15','M1']" :key="tf" class="card mb-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-semibold" :class="tf==='H4'?'text-blue-400':tf==='M15'?'text-purple-400':'text-yellow-400'">{{ tf }} Categories</h2>
          <button class="btn-primary btn-sm" @click="openAdd(tf)">+ Add</button>
        </div>
        <div class="space-y-2">
          <div v-for="c in catsByTf[tf]" :key="c.id" class="flex items-center justify-between px-3 py-2 bg-surface rounded-lg border border-border">
            <div>
              <span class="text-sm text-white font-medium">{{ c.name }}</span>
              <span v-if="c.trade_count" class="ml-2 text-xs text-gray-500">({{ c.trade_count }} trades)</span>
              <span v-if="c.last_traded_at" class="ml-2 text-xs text-gray-600">last: {{ formatDate(c.last_traded_at) }}</span>
            </div>
            <div class="flex gap-2">
              <button @click="openEdit(c)" class="btn-secondary btn-sm">Edit</button>
              <button @click="deleteCategory(c)" class="btn-danger btn-sm">Delete</button>
            </div>
          </div>
          <div v-if="!catsByTf[tf].length" class="text-sm text-gray-600 text-center py-4">No {{ tf }} categories yet.</div>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="modal.show" class="fixed inset-0 bg-black/70 flex items-center justify-center z-40 p-4">
        <div class="card w-full max-w-md">
          <h2 class="section-title">{{ modal.id ? 'Edit' : 'Add' }} Category</h2>
          <div class="form-group">
            <label class="label">Timeframe</label>
            <select v-model="modal.timeframe" class="select" :disabled="!!modal.id">
              <option value="H4">H4</option><option value="M15">M15</option><option value="M1">M1</option>
            </select>
          </div>
          <div class="form-group">
            <label class="label">Name</label>
            <input v-model="modal.name" class="input" placeholder="e.g. Bullish BOS" />
          </div>
          <div class="form-group">
            <label class="label">Description (optional)</label>
            <textarea v-model="modal.description" class="textarea" rows="2"></textarea>
          </div>
          <div v-if="modal.error" class="text-red-400 text-sm mb-3">{{ modal.error }}</div>
          <div class="flex gap-3">
            <button class="btn-primary" :disabled="modal.saving" @click="saveCategory">{{ modal.saving ? 'Saving…' : 'Save' }}</button>
            <button class="btn-ghost" @click="modal.show=false">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../components/AppLayout.vue'
export default {
  name: 'CategoriesView',
  components: { AppLayout },
  data() {
    return {
      cats: [],
      modal: { show:false, id:null, timeframe:'H4', name:'', description:'', saving:false, error:'' },
    }
  },
  computed: {
    catsByTf() {
      return { H4: this.cats.filter(c=>c.timeframe==='H4'), M15: this.cats.filter(c=>c.timeframe==='M15'), M1: this.cats.filter(c=>c.timeframe==='M1') }
    },
  },
  async created() { await this.load() },
  methods: {
    async load() {
      const { data } = await this.$http.get('/categories')
      this.cats = data
    },
    openAdd(tf)  { this.modal = { show:true, id:null, timeframe:tf, name:'', description:'', saving:false, error:'' } },
    openEdit(c)  { this.modal = { show:true, id:c.id, timeframe:c.timeframe, name:c.name, description:c.description||'', saving:false, error:'' } },
    async saveCategory() {
      if (!this.modal.name) { this.modal.error = 'Name is required.'; return }
      this.modal.saving = true; this.modal.error = ''
      try {
        if (this.modal.id) {
          await this.$http.put(`/categories/${this.modal.id}`, { name: this.modal.name, description: this.modal.description })
        } else {
          await this.$http.post('/categories', { timeframe: this.modal.timeframe, name: this.modal.name, description: this.modal.description })
        }
        this.modal.show = false
        await this.load()
        await this.$store.dispatch('app/loadCategories', this.modal.timeframe)
      } catch(e) { this.modal.error = 'Failed to save.' } finally { this.modal.saving = false }
    },
    async deleteCategory(c) {
      if (!confirm(`Delete "${c.name}"?`)) return
      await this.$http.delete(`/categories/${c.id}`)
      this.cats = this.cats.filter(x => x.id !== c.id)
    },
    formatDate(d) { return d ? new Date(d).toLocaleDateString() : '' },
  },
}
</script>
