<template>
  <AppLayout>
    <div class="p-6 max-w-5xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="page-title">User Management</h1>
          <p class="page-sub">Create and manage EdgeLedger accounts.</p>
        </div>
        <button class="btn-primary" @click="openCreate">➕ New User</button>
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-500">Loading…</div>
      <div v-else class="card">
        <table class="w-full">
          <thead class="border-b border-border">
            <tr>
              <th class="th">User</th>
              <th class="th">Role</th>
              <th class="th text-right">DB Trades</th>
              <th class="th text-right">Journals</th>
              <th class="th">Status</th>
              <th class="th">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="u in users" :key="u.id" class="hover:bg-card-hover transition-colors">
              <td class="td">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-win/20 border border-win/30 flex items-center justify-center text-win text-xs font-bold flex-shrink-0">
                    {{ initials(u.name) }}
                  </div>
                  <div>
                    <div class="text-white text-sm font-medium">{{ u.name }}</div>
                    <div class="text-gray-500 text-xs">{{ u.email }}</div>
                  </div>
                </div>
              </td>
              <td class="td">
                <span :class="u.role==='superuser' ? 'badge-ref' : 'badge-neutral'">{{ u.role }}</span>
              </td>
              <td class="td text-right">{{ u.trade_databases_count || 0 }}</td>
              <td class="td text-right">{{ u.journals_count || 0 }}</td>
              <td class="td">
                <span :class="u.is_active ? 'badge-win' : 'badge-invalid'">{{ u.is_active ? 'Active' : 'Disabled' }}</span>
              </td>
              <td class="td">
                <div class="flex gap-2">
                  <button @click="openEdit(u)" class="btn-secondary btn-sm">Edit</button>
                  <button v-if="!u.is_active || u.role !== 'superuser'" @click="toggleActive(u)"
                    class="btn-secondary btn-sm" :class="u.is_active ? 'text-yellow-400' : 'text-win'">
                    {{ u.is_active ? 'Disable' : 'Enable' }}
                  </button>
                  <button v-if="u.role !== 'superuser'" @click="deleteUser(u)" class="btn-danger btn-sm">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal -->
      <div v-if="modal.show" class="fixed inset-0 bg-black/70 flex items-center justify-center z-40 p-4">
        <div class="card w-full max-w-md">
          <h2 class="section-title">{{ modal.id ? 'Edit User' : 'Create New User' }}</h2>
          <div class="space-y-4">
            <div>
              <label class="label">Full Name</label>
              <input v-model="modal.name" class="input" placeholder="John Smith" />
            </div>
            <div>
              <label class="label">Email Address</label>
              <input v-model="modal.email" type="email" class="input" placeholder="john@example.com" />
            </div>
            <div>
              <label class="label">{{ modal.id ? 'New Password (leave blank to keep current)' : 'Password' }}</label>
              <input v-model="modal.password" type="password" class="input" placeholder="min 8 characters" />
            </div>
            <div>
              <label class="label">Role</label>
              <select v-model="modal.role" class="select">
                <option value="user">User</option>
                <option value="superuser">Superuser</option>
              </select>
            </div>
          </div>
          <div v-if="modal.error" class="text-red-400 text-sm mt-3">{{ modal.error }}</div>
          <div class="flex gap-3 mt-5">
            <button class="btn-primary" :disabled="modal.saving" @click="saveUser">
              {{ modal.saving ? 'Saving…' : modal.id ? 'Update User' : 'Create User' }}
            </button>
            <button class="btn-ghost" @click="modal.show=false">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../../components/AppLayout.vue'
export default {
  name: 'UsersView',
  components: { AppLayout },
  data() {
    return {
      users: [],
      loading: true,
      modal: { show:false, id:null, name:'', email:'', password:'', role:'user', saving:false, error:'' },
    }
  },
  async created() { await this.load() },
  methods: {
    async load() {
      this.loading = true
      const { data } = await this.$http.get('/admin/users')
      this.users = data
      this.loading = false
    },
    initials(name) { return (name||'U').split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2) },
    openCreate() {
      this.modal = { show:true, id:null, name:'', email:'', password:'', role:'user', saving:false, error:'' }
    },
    openEdit(u) {
      this.modal = { show:true, id:u.id, name:u.name, email:u.email, password:'', role:u.role, saving:false, error:'' }
    },
    async saveUser() {
      if (!this.modal.name || !this.modal.email) { this.modal.error = 'Name and email are required.'; return }
      if (!this.modal.id && !this.modal.password) { this.modal.error = 'Password is required for new users.'; return }
      this.modal.saving = true; this.modal.error = ''
      try {
        const payload = { name: this.modal.name, email: this.modal.email, role: this.modal.role }
        if (this.modal.password) payload.password = this.modal.password
        if (this.modal.id) {
          await this.$http.put(`/admin/users/${this.modal.id}`, payload)
        } else {
          await this.$http.post('/admin/users', payload)
        }
        this.modal.show = false
        await this.load()
      } catch(e) {
        this.modal.error = e.response?.data?.message || Object.values(e.response?.data?.errors||{})[0]?.[0] || 'Failed to save.'
      } finally { this.modal.saving = false }
    },
    async toggleActive(user) {
      await this.$http.post(`/admin/users/${user.id}/toggle-active`)
      await this.load()
    },
    async deleteUser(user) {
      if (!confirm(`Delete ${user.name}? This cannot be undone.`)) return
      await this.$http.delete(`/admin/users/${user.id}`)
      this.users = this.users.filter(u => u.id !== user.id)
    },
  },
}
</script>
