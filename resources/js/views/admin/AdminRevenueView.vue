<template>
  <AppLayout>
    <div class="p-6 max-w-4xl">
      <div class="page-header">
        <h1 class="page-title">💰 Revenue & Subscriptions</h1>
        <p class="page-sub">Manage subscription pricing, activate pending payments, and process payouts.</p>
      </div>

      <!-- Overview stats -->
      <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4" v-if="overview">
        <div class="card text-center">
          <div class="text-2xl font-bold text-win">${{ fmt(overview.monthly_revenue) }}</div>
          <div class="text-xs text-gray-500 mt-1">This Month</div>
        </div>
        <div class="card text-center">
          <div class="text-2xl font-bold text-white">{{ overview.active_subscribers }}</div>
          <div class="text-xs text-gray-500 mt-1">Active Subscribers</div>
        </div>
        <div v-if="overview.pending_activations > 0" class="card text-center border border-yellow-800/40">
          <div class="text-2xl font-bold text-yellow-400">{{ overview.pending_activations }}</div>
          <div class="text-xs text-gray-500 mt-1">Pending Activation</div>
        </div>
        <div class="card text-center">
          <div class="text-2xl font-bold text-yellow-400">${{ fmt(overview.pending_redemptions) }}</div>
          <div class="text-xs text-gray-500 mt-1">Pending Payouts</div>
        </div>
      </div>

      <!-- Plan settings -->
      <div class="card mb-6">
        <h2 class="section-title">Subscription Plan Settings</h2>
        <div class="grid grid-cols-3 gap-4 mb-4">
          <div>
            <label class="label">Plan Name</label>
            <input v-model="planForm.name" class="input" />
          </div>
          <div>
            <label class="label">Monthly Price (USD)</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
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
        <div v-if="planSaved" class="text-win text-sm mb-3">✓ Saved.</div>
        <button class="btn-primary" :disabled="planSaving" @click="savePlan">
          {{ planSaving ? 'Saving…' : 'Save Settings' }}
        </button>
      </div>

      <!-- Subscriptions table -->
      <div class="card mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="section-title mb-0">All Subscriptions</h2>
          <button class="btn-ghost text-xs" @click="load">↻ Refresh</button>
        </div>
        <div v-if="!subs.length" class="text-gray-600 text-sm py-3">No subscriptions yet.</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-border">
                <th class="th">User</th>
                <th class="th">Status</th>
                <th class="th">Amount</th>
                <th class="th">Expires</th>
                <th class="th">Method / Ref</th>
                <th class="th text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="s in subs" :key="s.id"
                class="transition-colors"
                :class="s.status === 'pending' ? 'bg-yellow-900/10 hover:bg-yellow-900/15' : 'hover:bg-card-hover'">
                <td class="td">
                  <div class="text-white font-medium">{{ s.user?.name }}</div>
                  <div class="text-xs text-gray-500">{{ s.user?.email }}</div>
                </td>
                <td class="td">
                  <span class="text-xs px-2 py-0.5 rounded font-semibold"
                    :class="{
                      'bg-win/20 text-win':              s.status==='active',
                      'bg-yellow-900/30 text-yellow-400':s.status==='pending',
                      'bg-gray-800 text-gray-500':       s.status==='cancelled'||s.status==='expired',
                    }">{{ s.status }}</span>
                </td>
                <td class="td text-white">${{ fmt(s.amount) }}</td>
                <td class="td text-xs text-gray-400">{{ fmtd(s.current_period_end) }}</td>
                <td class="td">
                  <div class="text-xs capitalize text-gray-400">{{ s.payment_method }}</div>
                  <!-- Show reference and proof for pending -->
                  <template v-if="s.status === 'pending' && s.payments && s.payments.length">
                    <div v-if="s.payments[0].external_id" class="text-xs text-gray-300 mt-1 font-mono">
                      Ref: {{ s.payments[0].external_id }}
                    </div>
                    <div v-if="s.payments[0].proof_path" class="mt-2">
                      <button class="text-xs text-win underline" @click="viewProof(s.payments[0])">
                        📷 View Payment Proof
                      </button>
                    </div>
                    <div v-else class="text-xs text-gray-600 mt-1 italic">No screenshot uploaded</div>
                  </template>
                </td>
                <td class="td text-right">
                  <button
                    class="text-xs font-semibold px-3 py-1.5 rounded transition-colors"
                    :class="s.status==='pending'
                      ? 'bg-win/20 text-win hover:bg-win/30'
                      : 'bg-surface border border-border text-gray-400 hover:text-win hover:border-win/40'"
                    @click="openActivate(s)">
                    {{ s.status === 'pending' ? '✓ Activate' : 'Renew' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pending redemptions -->
      <div class="card">
        <h2 class="section-title">Payout Requests</h2>
        <div v-if="!redemptions.length" class="text-gray-600 text-sm py-3">No pending payouts.</div>
        <div v-else class="space-y-3">
          <div v-for="r in redemptions" :key="r.id"
            class="flex items-start justify-between gap-4 p-4 bg-surface rounded-lg border border-border">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1 flex-wrap">
                <span class="font-semibold text-white">{{ r.user?.name }}</span>
                <span class="text-xs text-gray-500">{{ r.user?.email }}</span>
                <span class="text-xs px-2 py-0.5 rounded font-semibold"
                  :class="{
                    'bg-yellow-900/30 text-yellow-400': r.status==='pending',
                    'bg-win/20 text-win':    r.status==='paid'||r.status==='approved',
                    'bg-loss/20 text-loss':  r.status==='rejected',
                  }">{{ r.status }}</span>
              </div>
              <div class="text-2xl font-bold text-win mb-1">${{ fmt(r.amount) }}</div>
              <div class="text-xs text-gray-500">
                {{ r.payment_method?.toUpperCase() }}: <span class="text-gray-400">{{ r.payment_details }}</span>
              </div>
              <div class="text-xs text-gray-600 mt-1">Requested {{ fmtd(r.created_at) }}</div>
              <div v-if="r.admin_notes" class="text-xs text-gray-500 mt-1 italic">{{ r.admin_notes }}</div>
            </div>
            <div v-if="r.status==='pending'||r.status==='approved'" class="flex flex-col gap-2 flex-shrink-0">
              <input v-model="r._notes" class="input text-xs" style="width:160px" placeholder="Admin notes (optional)" />
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

    <!-- ── PROOF IMAGE LIGHTBOX ── -->
    <div v-if="proofModal.show" class="fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4" @click.self="proofModal.show=false">
      <div class="relative max-w-3xl w-full">
        <button class="absolute top-2 right-2 z-10 w-8 h-8 rounded-full bg-black/60 text-white flex items-center justify-content:center text-lg" @click="proofModal.show=false">✕</button>
        <div class="card text-center">
          <h3 class="section-title mb-3">Payment Proof</h3>
          <img v-if="proofModal.url" :src="proofModal.url" class="max-w-full max-h-96 object-contain rounded-lg mx-auto" />
          <div v-else class="text-gray-500 py-8">Loading…</div>
        </div>
      </div>
    </div>

    <!-- ── ACTIVATE / RENEW MODAL ── -->
    <div v-if="activateModal.show" class="fixed inset-0 bg-black/70 flex items-center justify-center z-40 p-4">
      <div class="card w-full max-w-md">
        <h2 class="section-title">
          {{ activateModal.sub?.status === 'pending' ? '✓ Activate Subscription' : '↻ Renew Subscription' }}
        </h2>

        <div class="p-3 bg-surface rounded-lg border border-border mb-4 text-sm">
          <div class="text-white font-semibold">{{ activateModal.sub?.user?.name }}</div>
          <div class="text-gray-500 text-xs">{{ activateModal.sub?.user?.email }}</div>
          <div class="text-xs mt-1" v-if="activateModal.sub?.status === 'pending'">
            <span class="text-yellow-400">Pending payment</span> via {{ activateModal.sub?.payment_method }}
          </div>
        </div>

        <div class="space-y-3 mb-4">
          <div>
            <label class="label">Months to activate</label>
            <select v-model="activateModal.months" class="select">
              <option :value="1">1 month</option>
              <option :value="2">2 months</option>
              <option :value="3">3 months</option>
              <option :value="6">6 months</option>
              <option :value="12">12 months</option>
            </select>
          </div>
          <div>
            <label class="label">Payment Reference <span class="text-gray-600">(optional)</span></label>
            <input v-model="activateModal.payment_ref" class="input" placeholder="Transaction ID or notes" />
          </div>
        </div>

        <div v-if="activateModal.error" class="p-3 bg-red-900/20 border border-red-800/30 rounded-lg text-red-400 text-sm mb-3">
          {{ activateModal.error }}
        </div>
        <div v-if="activateModal.success" class="p-3 bg-win/10 border border-win/30 rounded-lg text-win text-sm mb-3">
          ✓ {{ activateModal.success }}
        </div>

        <div class="flex gap-3">
          <button class="btn-primary" :disabled="activateModal.saving" @click="activateSubscription">
            {{ activateModal.saving ? 'Activating…' : (activateModal.sub?.status === 'pending' ? 'Activate' : 'Renew') }}
          </button>
          <button class="btn-ghost" @click="closeActivate">Close</button>
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
      overview:    null,
      plan:        null,
      planForm:    { name: '', price: 2.00, currency: 'USD' },
      planSaving:  false,
      planSaved:   false,
      subs:        [],
      redemptions: [],
      activateModal: {
        show:        false,
        sub:         null,
        months:      1,
        payment_ref: '',
        saving:      false,
        error:       '',
        success:     '',
      },
      proofModal: {
        show: false,
        url:  '',
      },
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
        this.subs        = (sb.data.data || sb.data).map(s => ({
          ...s,
          user: s.user || { name: 'Unknown', email: '' }
        }))
        this.redemptions = (rd.data.data || rd.data).map(r => ({ ...r, _notes: '' }))
      } catch(e) { console.error('Load error:', e) }
    },

    async savePlan() {
      this.planSaving = true; this.planSaved = false
      try {
        await this.$http.put('/admin/subscription/plan', this.planForm)
        this.planSaved = true
        setTimeout(() => { this.planSaved = false }, 3000)
      } catch(e) { alert('Save failed.') }
      finally { this.planSaving = false }
    },

    viewProof(payment) {
      // Build authenticated URL — uses the token in the header via axios
      // We fetch as blob so the local file is served through the API
      this.proofModal = { show: true, url: '' }
      this.$http.get(`/admin/payment-proof/${payment.id}`, { responseType: 'blob' })
        .then(({ data }) => {
          this.proofModal.url = URL.createObjectURL(data)
        })
        .catch(() => { this.proofModal.show = false; alert('Could not load proof image.') })
    },

    openActivate(sub) {
      this.activateModal = {
        show:        true,
        sub,
        months:      1,
        payment_ref: '',
        saving:      false,
        error:       '',
        success:     '',
      }
    },
    closeActivate() {
      this.activateModal.show = false
      this.activateModal.success = ''
      this.load()
    },

    async activateSubscription() {
      const m = this.activateModal
      m.saving = true; m.error = ''; m.success = ''
      try {
        const userId = m.sub?.user?.id || m.sub?.user_id
        if (!userId) { m.error = 'Could not find user ID.'; m.saving = false; return }

        const { data } = await this.$http.post(`/admin/users/${userId}/activate-sub`, {
          months:          m.months,
          payment_ref:     m.payment_ref || undefined,
          payment_method:  m.sub?.payment_method || 'manual',
        })
        m.success = data.message || 'Subscription activated successfully.'
        m.saving  = false
        // Reload after short delay so admin can see the success message
        setTimeout(() => { this.closeActivate() }, 1500)
      } catch(e) {
        m.error  = e.response?.data?.message || 'Activation failed.'
        m.saving = false
      }
    },

    async processRedemption(r, status) {
      try {
        await this.$http.patch(`/admin/subscription/redemptions/${r.id}`, {
          status,
          admin_notes: r._notes || undefined,
        })
        await this.load()
      } catch(e) { alert('Failed.') }
    },

    fmt(v) { return v != null ? Number(v).toFixed(2) : '0.00' },
    fmtd(d) {
      if (!d) return '—'
      return new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' })
    },
  },
}
</script>