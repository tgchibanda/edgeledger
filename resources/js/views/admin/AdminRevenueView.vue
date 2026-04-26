<template>
  <AppLayout>
    <div class="p-6 max-w-4xl">
      <div class="page-header">
        <h1 class="page-title">💰 Revenue & Subscriptions</h1>
        <p class="page-sub">Manage subscription pricing, user subscriptions, and referral payouts.</p>
      </div>

      <!-- Revenue overview -->
      <div class="grid grid-cols-3 gap-4 mb-6" v-if="overview">
        <div class="card text-center">
          <div class="text-3xl font-bold text-win">${{ fmt(overview.monthly_revenue) }}</div>
          <div class="text-xs text-gray-500 mt-1">This Month's Revenue</div>
        </div>
        <div class="card text-center">
          <div class="text-3xl font-bold text-white">{{ overview.active_subscribers }}</div>
          <div class="text-xs text-gray-500 mt-1">Active Subscribers</div>
        </div>
        <div class="card text-center">
          <div class="text-3xl font-bold text-yellow-400">${{ fmt(overview.pending_redemptions) }}</div>
          <div class="text-xs text-gray-500 mt-1">Pending Payouts</div>
        </div>
      </div>

      <!-- Plan price settings -->
      <div class="card mb-6">
        <h2 class="section-title">Subscription Plan Settings</h2>
        <div v-if="plan" class="grid grid-cols-3 gap-4 mb-4">
          <div>
            <label class="label">Plan Name</label>
            <input v-model="planForm.name" class="input" />
          </div>
          <div>
            <label class="label">Monthly Price (USD)</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
              <input v-model="planForm.price" type="number" step="0.50" min="0.50" class="input pl-7" />
            </div>
          </div>
          <div>
            <label class="label">Currency</label>
            <select v-model="planForm.currency" class="select">
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
              <option value="GBP">GBP</option>
            </select>
          </div>
        </div>
        <div v-if="planSaved" class="text-win text-sm mb-3">✓ Plan updated successfully.</div>
        <button class="btn-primary" :disabled="planSaving" @click="savePlan">
          {{ planSaving ? 'Saving…' : 'Save Plan Settings' }}
        </button>
      </div>

      <!-- Active subscriptions -->
      <div class="card mb-6">
        <h2 class="section-title">Active Subscriptions</h2>
        <div v-if="!subs.length" class="text-gray-600 text-sm py-3">No subscriptions yet.</div>
        <table v-else class="w-full text-sm">
          <thead><tr class="border-b border-border">
            <th class="th">User</th>
            <th class="th">Status</th>
            <th class="th">Amount</th>
            <th class="th">Period End</th>
            <th class="th">Method</th>
            <th class="th text-right">Action</th>
          </tr></thead>
          <tbody class="divide-y divide-border">
            <tr v-for="s in subs" :key="s.id" class="hover:bg-card-hover">
              <td class="td">
                <div class="text-white font-medium">{{ s.user?.name }}</div>
                <div class="text-xs text-gray-500">{{ s.user?.email }}</div>
              </td>
              <td class="td">
                <span class="text-xs px-2 py-0.5 rounded"
                  :class="s.status==='active'?'bg-win/20 text-win':'bg-gray-800 text-gray-500'">
                  {{ s.status }}
                </span>
              </td>
              <td class="td text-white">${{ fmt(s.amount) }}</td>
              <td class="td text-xs text-gray-400">{{ fmtd(s.current_period_end) }}</td>
              <td class="td capitalize text-gray-400">{{ s.payment_method }}</td>
              <td class="td text-right">
                <button class="text-xs text-win hover:underline" @click="renewUser(s.user)">Renew</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pending redemptions -->
      <div class="card">
        <h2 class="section-title">Payout Requests</h2>
        <div v-if="!redemptions.length" class="text-gray-600 text-sm py-3">No pending payouts.</div>
        <div v-else class="space-y-3">
          <div v-for="r in redemptions" :key="r.id"
            class="flex items-start justify-between gap-4 p-4 bg-surface rounded-lg border border-border">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="font-semibold text-white">{{ r.user?.name }}</span>
                <span class="text-xs px-2 py-0.5 rounded"
                  :class="{
                    'bg-yellow-900/30 text-yellow-400': r.status==='pending',
                    'bg-win/20 text-win':    r.status==='paid'||r.status==='approved',
                    'bg-loss/20 text-loss':  r.status==='rejected',
                  }">{{ r.status }}</span>
              </div>
              <div class="text-2xl font-bold text-win mb-1">${{ fmt(r.amount) }}</div>
              <div class="text-xs text-gray-500">
                {{ r.payment_method?.toUpperCase() }}: {{ r.payment_details }}
              </div>
              <div class="text-xs text-gray-600 mt-1">Requested {{ fmtd(r.created_at) }}</div>
              <div v-if="r.admin_notes" class="text-xs text-gray-500 mt-1 italic">{{ r.admin_notes }}</div>
            </div>
            <div class="flex flex-col gap-2" v-if="r.status==='pending'||r.status==='approved'">
              <div>
                <label class="label text-xs">Admin Notes</label>
                <input v-model="r._notes" class="input text-xs" placeholder="Optional" />
              </div>
              <div class="flex gap-2">
                <button class="btn-primary text-xs py-1 px-3" @click="processRedemption(r,'paid')">✓ Mark Paid</button>
                <button class="btn-ghost text-xs py-1 px-3" @click="processRedemption(r,'approved')">Approve</button>
                <button class="text-xs text-loss hover:underline" @click="processRedemption(r,'rejected')">Reject</button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../../components/AppLayout.vue'
export default {
  name: 'AdminRevenueView',
  components: { AppLayout },
  data() {
    return {
      overview:  null,
      plan:      null,
      planForm:  { name: '', price: 2.00, currency: 'USD' },
      planSaving:false,
      planSaved: false,
      subs:      [],
      redemptions:[],
    }
  },
  async created() { await this.load() },
  methods: {
    async load() {
      try {
        const [ov, pl, sb, rd] = await Promise.all([
          this.$http.get('/admin/subscription/overview'),
          this.$http.get('/admin/subscription/plan'),
          this.$http.get('/admin/subscription/subscriptions'),
          this.$http.get('/admin/subscription/redemptions'),
        ])
        this.overview    = ov.data
        this.plan        = pl.data
        this.planForm    = { name: pl.data.name, price: pl.data.price, currency: pl.data.currency }
        this.subs        = sb.data.data || sb.data
        this.redemptions = (rd.data.data || rd.data).map(r => ({ ...r, _notes: '' }))
      } catch(e) {}
    },
    async savePlan() {
      this.planSaving = true; this.planSaved = false
      try {
        await this.$http.put('/admin/subscription/plan', this.planForm)
        this.planSaved = true
        setTimeout(() => { this.planSaved = false }, 3000)
      } finally { this.planSaving = false }
    },
    renewUser(user) {
      // Redirect to user management to activate
      this.$router.push('/admin/users')
    },
    async processRedemption(r, status) {
      try {
        await this.$http.patch(`/admin/subscription/redemptions/${r.id}`, { status, admin_notes: r._notes })
        await this.load()
      } catch(e) { alert('Failed.') }
    },
    fmt(v) { return v != null ? Number(v).toFixed(2) : '0.00' },
    fmtd(d) { return d ? new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }) : '—' },
  },
}
</script>
