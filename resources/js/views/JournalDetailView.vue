<template>
  <AppLayout>
    <div class="p-6 max-w-2xl" v-if="journal">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="page-title">Journal Entry</h1>
          <p class="page-sub">{{ formatDate(journal.trade_date || journal.created_at) }}</p>
        </div>
        <router-link to="/journal" class="btn-ghost">← Back</router-link>
      </div>

      <!-- Summary card -->
      <div class="card mb-5">
        <div class="flex items-center gap-3 flex-wrap mb-3">
          <span v-if="journal.status==='pre_trade'" class="badge-pre">📋 Pre-Trade</span>
          <span v-else-if="journal.result==='win'"  class="badge-win">WIN</span>
          <span v-else-if="journal.result==='loss'" class="badge-loss">LOSS</span>
          <span v-else-if="journal.result"          class="badge-neutral">BREAKEVEN</span>
          <span v-if="journal.is_valid===false"     class="badge-invalid">✗ Invalid</span>
          <span v-if="journal.promote_to_database"  class="badge-ref">⭐ In Database</span>
        </div>
        <div class="grid grid-cols-2 gap-2 text-sm">
          <div><span class="text-gray-500">Pair:</span> <span class="text-white">{{ journal.pair && journal.pair.symbol }}</span></div>
          <div><span class="text-gray-500">Session:</span> <span class="text-white">{{ journal.session && journal.session.name }}</span></div>
          <div><span class="text-gray-500">Entry:</span> <span class="text-white">{{ journal.entry_technique }}</span></div>
          <div><span class="text-gray-500">H4:</span> <span class="text-blue-400">{{ journal.h4 && journal.h4.name }}</span></div>
          <div><span class="text-gray-500">M15:</span> <span class="text-purple-400">{{ journal.m15 && journal.m15.name }}</span></div>
          <div><span class="text-gray-500">M1:</span> <span class="text-yellow-400">{{ journal.m1 && journal.m1.name }}</span></div>
          <div v-if="journal.pips_result"><span class="text-gray-500">Pips:</span> <span :class="journal.pips_result>0?'text-win':'text-loss'">{{ journal.pips_result > 0 ? '+' : '' }}{{ journal.pips_result }}</span></div>
          <div v-if="journal.r_multiple"><span class="text-gray-500">R:</span> <span :class="journal.r_multiple>0?'text-win':'text-loss'">{{ journal.r_multiple > 0 ? '+' : '' }}{{ journal.r_multiple }}R</span></div>
        </div>
      </div>

      <!-- Images -->
      <div v-if="journal.images && journal.images.length" class="card mb-5">
        <h2 class="section-title">Chart Images</h2>
        <div class="flex gap-3 flex-wrap">
          <div v-for="img in journal.images" :key="img.id"
            class="flex items-center gap-2 px-3 py-2 bg-surface rounded-lg border border-border cursor-pointer hover:border-win transition-colors"
            @click="openLightbox(img)">
            <span class="text-lg">📷</span>
            <span class="text-sm font-medium" :class="img.timeframe==='H4'?'text-blue-400':img.timeframe==='M15'?'text-purple-400':'text-yellow-400'">{{ img.timeframe }}</span>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="card mb-5 space-y-4">
        <div v-if="journal.pre_trade_notes">
          <div class="label">Pre-Trade Notes</div>
          <div class="text-sm text-gray-300 whitespace-pre-wrap">{{ journal.pre_trade_notes }}</div>
        </div>
        <div v-if="journal.reason_not_to_take" class="p-3 bg-yellow-900/20 border border-yellow-700/30 rounded-lg">
          <div class="label text-yellow-400">⚠️ Reason not to take</div>
          <div class="text-sm text-gray-300">{{ journal.reason_not_to_take }}</div>
        </div>
        <div v-if="journal.confluences">
          <div class="label">Confluences</div>
          <div class="text-sm text-gray-300">{{ journal.confluences }}</div>
        </div>
        <div v-if="journal.post_trade_notes">
          <div class="label">Post-Trade Notes</div>
          <div class="text-sm text-gray-300 whitespace-pre-wrap">{{ journal.post_trade_notes }}</div>
        </div>
        <div v-if="journal.mistakes" class="p-3 bg-red-900/20 border border-red-800/30 rounded-lg">
          <div class="label text-red-400">Mistakes</div>
          <div class="text-sm text-gray-300">{{ journal.mistakes }}</div>
        </div>
      </div>

      <!-- Complete trade form -->
      <div v-if="journal.status === 'pre_trade'" class="card space-y-4">
        <h2 class="section-title">Complete Trade Result</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Result *</label>
            <select v-model="completeForm.result" class="select" required>
              <option value="">Select result</option>
              <option value="win">Win</option>
              <option value="loss">Loss</option>
              <option value="breakeven">Breakeven</option>
            </select>
          </div>
          <div>
            <label class="label">Pips Result</label>
            <input v-model="completeForm.pips_result" type="number" step="0.1" class="input" placeholder="e.g. 25.5" />
          </div>
          <div>
            <label class="label">R Multiple</label>
            <input v-model="completeForm.r_multiple" type="number" step="0.01" class="input" placeholder="e.g. 2.5" />
          </div>
          <div v-if="completeForm.result==='loss'">
            <label class="label">Mistakes</label>
            <input v-model="completeForm.mistakes" class="input" placeholder="What went wrong?" />
          </div>
        </div>
        <RulesToggle v-model="completeForm.followed_rules" />
        <div>
          <label class="label">Post-Trade Notes</label>
          <textarea v-model="completeForm.post_trade_notes" class="textarea" rows="3" placeholder="What happened? What did you learn?"></textarea>
        </div>
        <div>
          <label class="label">Post-Trade Images</label>
          <ImageUploader v-model="postImages" />
        </div>
        <div v-if="completeForm.result === 'win' && completeForm.followed_rules" class="p-3 bg-win/10 border border-win/30 rounded-lg">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="completeForm.promote" class="accent-win" />
            <span class="text-sm text-win font-medium">⭐ Promote this trade to the Reference Database</span>
          </label>
          <div class="text-xs text-gray-500 mt-1">This will add it as a reference setup other journal entries can be filtered against.</div>
        </div>
        <div v-if="error" class="text-red-400 text-sm">{{ error }}</div>
        <div class="flex gap-3">
          <button class="btn-primary" :disabled="!completeForm.result || completeForm.followed_rules===null || completing" @click="completeTrade">
            {{ completing ? 'Saving…' : 'Complete Trade' }}
          </button>
        </div>
      </div>

      <!-- Promote button for completed wins not yet in DB -->
      <div v-if="journal.status==='completed' && journal.result==='win' && !journal.promote_to_database" class="card">
        <h2 class="section-title">Add to Database</h2>
        <p class="text-sm text-gray-400 mb-4">This winning trade is not yet in your reference database.</p>
        <button class="btn-primary" :disabled="promoting" @click="promote">
          {{ promoting ? 'Promoting…' : '⭐ Promote to Reference Database' }}
        </button>
      </div>

      <ImageLightbox :visible="lightbox.visible" :src="lightbox.src" :timeframe="lightbox.tf" @close="lightbox.visible=false" />
    </div>
    <div v-else class="p-6 text-gray-500">Loading…</div>
  </AppLayout>
</template>

<script>
import AppLayout     from '../components/AppLayout.vue'
import RulesToggle   from '../components/RulesToggle.vue'
import ImageUploader from '../components/ImageUploader.vue'
import ImageLightbox from '../components/ImageLightbox.vue'
export default {
  name: 'JournalDetailView',
  components: { AppLayout, RulesToggle, ImageUploader, ImageLightbox },
  data() {
    return {
      journal: null,
      completeForm: { result:'', followed_rules: null, post_trade_notes:'', pips_result:'', r_multiple:'', mistakes:'', promote: false },
      postImages: {},
      completing: false, promoting: false,
      error: '',
      lightbox: { visible: false, src: '', tf: '' },
    }
  },
  async created() { await this.load() },
  methods: {
    async load() {
      const { data } = await this.$http.get(`/journals/${this.$route.params.id}`)
      this.journal = data
    },
    async completeTrade() {
      if (this.completeForm.followed_rules === null) { this.error = 'Please select if you followed your rules.'; return }
      this.completing = true; this.error = ''
      try {
        const fd = new FormData()
        Object.entries(this.completeForm).forEach(([k,v]) => { if (v !== '' && v !== null && k !== 'promote') fd.append(k, v) })
        if (this.postImages.H4)  fd.append('h4_image',  this.postImages.H4)
        if (this.postImages.M15) fd.append('m15_image', this.postImages.M15)
        if (this.postImages.M1)  fd.append('m1_image',  this.postImages.M1)
        await this.$http.post(`/journals/${this.journal.id}/complete`, fd, { headers:{'Content-Type':'multipart/form-data'} })
        if (this.completeForm.promote) {
          await this.$http.post(`/journals/${this.journal.id}/promote`)
        }
        await this.load()
      } catch(e) {
        this.error = e.response?.data?.message || 'Failed to complete.'
      } finally { this.completing = false }
    },
    async promote() {
      this.promoting = true
      await this.$http.post(`/journals/${this.journal.id}/promote`)
      await this.load()
      this.promoting = false
    },
    openLightbox(img) { this.lightbox = { visible: true, src: `/api/images/${img.path}`, tf: img.timeframe } },
    formatDate(d)     { return d ? new Date(d).toLocaleString() : '' },
  },
}
</script>
