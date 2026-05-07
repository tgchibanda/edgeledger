<template>
  <AppLayout>
    <div class="p-6 max-w-3xl" v-if="!loading && journal">

      <!-- Header -->
      <div class="page-header">
        <div class="flex items-center gap-3 mb-2">
          <button class="text-gray-500 hover:text-white text-sm" @click="$router.push('/journal')">← Back</button>
        </div>
        <div class="flex items-start justify-between gap-4 flex-wrap">
          <div>
            <div class="flex items-center gap-2 mb-1 flex-wrap">
              <span class="jbadge" :class="journal.status === 'completed' ? 'jbadge--done' : 'jbadge--pre'">
                {{ journal.status === 'completed' ? 'Completed' : 'Pre-Trade' }}
              </span>
              <span v-if="journal.result" class="jbadge" :class="'jbadge--' + journal.result">
                {{ journal.result.toUpperCase() }}
              </span>
              <span v-if="journal.followed_rules === false" class="text-xs text-loss">⚠ Impulsive</span>
            </div>
            <h1 class="page-title">{{ journal.entry_technique }}</h1>
            <p class="page-sub">
              {{ journal.pair?.symbol }}
              <span v-if="journal.session"> · {{ journal.session.name }}</span>
              <span v-if="journal.trade_date"> · {{ fmt(journal.trade_date) }}</span>
            </p>
          </div>
          <div class="flex gap-2 flex-wrap">
            <button v-if="journal.status !== 'completed'" class="btn-primary" @click="showComplete = true">
              Complete Entry
            </button>
            <button class="btn-ghost text-sm" @click="$router.push('/journal/' + journal.id + '/edit')">Edit</button>
            <button class="text-sm text-loss hover:underline" @click="showDelete = true">Delete</button>
          </div>
        </div>
      </div>

      <!-- Classification -->
      <div class="card mb-4">
        <h2 class="section-title">Setup Classification</h2>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <div class="detail-label">HTF Structure</div>
            <div class="cat-badge cat-badge--h4 inline-block mt-1">{{ journal.h4?.name || '—' }}</div>
          </div>
          <div>
            <div class="detail-label">MTF Trigger</div>
            <div class="cat-badge cat-badge--m15 inline-block mt-1">{{ journal.m15?.name || '—' }}</div>
          </div>
          <div>
            <div class="detail-label">Entry TF Signal</div>
            <div class="cat-badge cat-badge--m1 inline-block mt-1">{{ journal.m1?.name || '—' }}</div>
          </div>
        </div>
      </div>

      <!-- Results (if completed) -->
      <div v-if="journal.status === 'completed'" class="card mb-4">
        <h2 class="section-title">Result</h2>
        <div class="grid grid-cols-3 gap-4 text-center">
          <div>
            <div class="detail-label mb-1">R Multiple</div>
            <div class="text-2xl font-bold" :class="(journal.r_multiple||0) >= 0 ? 'text-win' : 'text-loss'">
              {{ journal.r_multiple != null ? ((journal.r_multiple >= 0 ? '+' : '') + journal.r_multiple + 'R') : '—' }}
            </div>
          </div>
          <div>
            <div class="detail-label mb-1">Pips</div>
            <div class="text-2xl font-bold text-white">
              {{ journal.pips_result != null ? ((journal.pips_result > 0 ? '+' : '') + journal.pips_result) : '—' }}
            </div>
          </div>
          <div>
            <div class="detail-label mb-1">Rules</div>
            <div class="text-lg font-bold" :class="journal.followed_rules ? 'text-win' : 'text-loss'">
              {{ journal.followed_rules ? '✓ Disciplined' : '✗ Impulsive' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="card mb-4" v-if="journal.pre_trade_notes || journal.post_trade_notes || journal.mistakes || journal.confluences">
        <h2 class="section-title">Notes</h2>
        <div class="space-y-3">
          <div v-if="journal.pre_trade_notes">
            <div class="detail-label mb-1">Pre-Trade Plan</div>
            <p class="text-sm text-gray-300 leading-relaxed">{{ journal.pre_trade_notes }}</p>
          </div>
          <div v-if="journal.confluences">
            <div class="detail-label mb-1">Confluences</div>
            <p class="text-sm text-gray-300 leading-relaxed">{{ journal.confluences }}</p>
          </div>
          <div v-if="journal.post_trade_notes">
            <div class="detail-label mb-1">Post-Trade Review</div>
            <p class="text-sm text-gray-300 leading-relaxed">{{ journal.post_trade_notes }}</p>
          </div>
          <div v-if="journal.mistakes">
            <div class="detail-label mb-1">Mistakes</div>
            <p class="text-sm text-loss leading-relaxed">{{ journal.mistakes }}</p>
          </div>
        </div>
      </div>

      <!-- ── CHART IMAGES — thumbnails + lightbox ── -->
      <div class="card mb-4" v-if="journal.images && journal.images.length">
        <h2 class="section-title">Chart Images</h2>
        <div class="grid grid-cols-3 gap-4">
          <div v-for="img in orderedImages" :key="img.id" class="chart-thumb-wrap" @click="openLightbox(img.timeframe)">
            <div class="chart-thumb">
              <img :src="imgUrl(img)" class="chart-thumb__img" />
              <div class="chart-thumb__overlay">
                <span class="chart-thumb__zoom">🔍</span>
              </div>
            </div>
            <div class="chart-thumb__label" :class="'chart-thumb__label--' + img.timeframe.toLowerCase()">
              {{ tfLabel(img.timeframe) }}
            </div>
          </div>
          <!-- Empty slots for missing timeframes -->
          <div v-for="tf in missingTFs" :key="'empty-'+tf" class="chart-thumb-wrap">
            <div class="chart-thumb chart-thumb--empty">
              <span class="text-gray-700 text-xs">No {{ tfLabel(tf) }} image</span>
            </div>
            <div class="chart-thumb__label" :class="'chart-thumb__label--' + tf.toLowerCase()">
              {{ tfLabel(tf) }}
            </div>
          </div>
        </div>
      </div>

      <!-- No images yet -->
      <div class="card mb-4" v-else>
        <h2 class="section-title">Chart Images</h2>
        <div class="text-center py-6 text-gray-600 text-sm">
          No chart screenshots uploaded yet.
          <button class="text-win hover:underline ml-1" @click="showImageUpload = true">Upload now</button>
        </div>
      </div>

      <!-- Image upload panel -->
      <div v-if="showImageUpload" class="card mb-4">
        <h2 class="section-title">Upload Chart Screenshots</h2>
        <ImageUploader v-model="postImages" />
        <div class="flex gap-3 mt-4">
          <button class="btn-primary" :disabled="uploadingSaving" @click="saveImages">
            {{ uploadingSaving ? 'Saving…' : 'Save Images' }}
          </button>
          <button class="btn-ghost" @click="showImageUpload = false">Cancel</button>
        </div>
      </div>

      <!-- Complete Entry panel (shown when pre-trade) -->
      <div v-if="showComplete" class="card mb-4 border border-win/30">
        <h2 class="section-title">Complete This Entry</h2>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Result *</label>
              <div class="flex gap-2">
                <button v-for="r in ['win','loss','breakeven']" :key="r"
                  class="flex-1 py-2 rounded-lg text-xs font-bold border capitalize transition-colors"
                  :class="completeForm.result === r
                    ? r==='win' ? 'bg-win/20 border-win text-win' : r==='loss' ? 'bg-loss/20 border-loss text-loss' : 'bg-gray-700/40 border-gray-600 text-gray-300'
                    : 'bg-surface border-border text-gray-500'"
                  @click="completeForm.result = r">{{ r }}</button>
              </div>
            </div>
            <div>
              <label class="label">R Multiple</label>
              <input v-model="completeForm.r_multiple" type="number" step="0.01" class="input" placeholder="e.g. 2.5 or -1.0" />
            </div>
            <div>
              <label class="label">Pips Result</label>
              <input v-model="completeForm.pips_result" type="number" step="0.1" class="input" placeholder="e.g. 25.0" />
            </div>
          </div>
          <div>
            <label class="label">Followed Rules?</label>
            <div class="flex gap-3">
              <button class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors"
                :class="completeForm.followed_rules ? 'bg-win/15 border-win text-win' : 'bg-surface border-border text-gray-500'"
                @click="completeForm.followed_rules = true">✓ Yes — Disciplined</button>
              <button class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors"
                :class="!completeForm.followed_rules ? 'bg-loss/15 border-loss text-loss' : 'bg-surface border-border text-gray-500'"
                @click="completeForm.followed_rules = false">✗ No — Impulsive</button>
            </div>
          </div>
          <div>
            <label class="label">Post-Trade Notes</label>
            <textarea v-model="completeForm.post_trade_notes" class="textarea" rows="2" placeholder="What happened? What did you learn?"></textarea>
          </div>
          <div>
            <label class="label">Mistakes (if any)</label>
            <textarea v-model="completeForm.mistakes" class="textarea" rows="2" placeholder="What would you do differently?"></textarea>
          </div>
          <div>
            <label class="label">Post-Trade Screenshots</label>
            <ImageUploader v-model="completeImages" />
          </div>
          <div class="flex gap-3">
            <button class="btn-primary" :disabled="completing" @click="submitComplete">
              {{ completing ? 'Completing…' : 'Mark as Completed' }}
            </button>
            <button class="btn-ghost" @click="showComplete = false">Cancel</button>
          </div>
        </div>
      </div>

    </div>

    <!-- Loading state -->
    <div v-else-if="loading" class="p-6 flex items-center gap-3 text-gray-500 text-sm">
      <div class="w-4 h-4 border-2 border-gray-600 border-t-win rounded-full animate-spin"></div>
      Loading…
    </div>

    <!-- ── LIGHTBOX — same ImageLightbox used by trade database ── -->
    <ImageLightbox
      :visible="lightbox.show"
      :trades="[journalAsLightboxTrade]"
      :trade-index="0"
      @close="lightbox.show = false"
    />

    <!-- Delete confirm -->
    <div v-if="showDelete" class="fixed inset-0 bg-black/70 flex items-center justify-center z-40 p-4">
      <div class="card w-full max-w-sm">
        <h2 class="section-title">Delete journal entry?</h2>
        <p class="text-sm text-gray-400 mb-4">This cannot be undone.</p>
        <div class="flex gap-3">
          <button class="btn-danger" :disabled="deleting" @click="deleteJournal">{{ deleting ? 'Deleting…' : 'Delete' }}</button>
          <button class="btn-ghost" @click="showDelete = false">Cancel</button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script>
import AppLayout     from '../components/AppLayout.vue'
import ImageUploader from '../components/ImageUploader.vue'
import ImageLightbox from '../components/ImageLightbox.vue'
import { TF }        from '@/timeframes.js'

const TF_ORDER = ['H4', 'M15', 'M1']

export default {
  name: 'JournalDetailView',
  components: { AppLayout, ImageUploader, ImageLightbox },

  data() {
    return {
      loading:  true,
      journal:  null,

      showComplete:   false,
      showImageUpload:false,
      showDelete:     false,
      completing:     false,
      uploadingSaving:false,
      deleting:       false,

      completeForm: {
        result:           '',
        r_multiple:       '',
        pips_result:      '',
        followed_rules:   true,
        post_trade_notes: '',
        mistakes:         '',
      },

      completeImages: {},
      postImages:     {},

      lightbox: { show: false },
    }
  },

  computed: {
    orderedImages() {
      if (!this.journal?.images) return []
      return TF_ORDER
        .map(tf => this.journal.images.find(i => i.timeframe === tf))
        .filter(Boolean)
    },

    missingTFs() {
      if (!this.journal?.images) return TF_ORDER
      const present = this.journal.images.map(i => i.timeframe)
      return TF_ORDER.filter(tf => !present.includes(tf))
    },

    // Shape the journal for ImageLightbox — same format as trade database
    journalAsLightboxTrade() {
      if (!this.journal) return {}
      return {
        id:              this.journal.id,
        entry_technique: this.journal.entry_technique,
        pair:            this.journal.pair,
        session:         this.journal.session,
        result:          this.journal.result,
        r_multiple:      this.journal.r_multiple,
        pips_result:     this.journal.pips_result,
        followed_rules:  this.journal.followed_rules,
        h4:              this.journal.h4,
        m15:             this.journal.m15,
        m1:              this.journal.m1,
        images:          this.journal.images || [],
      }
    },
  },

  async mounted() {
    await this.load()
  },

  methods: {
    async load() {
      this.loading = true
      try {
        const { data } = await this.$http.get(`/journals/${this.$route.params.id}`)
        this.journal = data
      } catch(e) {
        console.error('Journal load error:', e?.response?.data || e?.message)
      } finally {
        this.loading = false
      }
    },

    tfLabel(tf) {
      return tf === 'H4' ? TF.h4 : tf === 'M15' ? TF.m15 : TF.m1
    },

    imgUrl(img) {
      return `/api/images/${img.path}`
    },

    openLightbox(tf) {
      this.lightbox.show = true
    },

    async submitComplete() {
      if (!this.completeForm.result) return
      this.completing = true
      try {
        const fd = new FormData()
        fd.append('result',           this.completeForm.result)
        fd.append('followed_rules',   this.completeForm.followed_rules ? '1' : '0')
        if (this.completeForm.r_multiple  != null && this.completeForm.r_multiple !== '')  fd.append('r_multiple',  this.completeForm.r_multiple)
        if (this.completeForm.pips_result != null && this.completeForm.pips_result !== '') fd.append('pips_result', this.completeForm.pips_result)
        if (this.completeForm.post_trade_notes) fd.append('post_trade_notes', this.completeForm.post_trade_notes)
        if (this.completeForm.mistakes)         fd.append('mistakes',         this.completeForm.mistakes)
        if (this.completeImages.H4)  fd.append('h4_image',  this.completeImages.H4)
        if (this.completeImages.M15) fd.append('m15_image', this.completeImages.M15)
        if (this.completeImages.M1)  fd.append('m1_image',  this.completeImages.M1)

        await this.$http.post(`/journals/${this.journal.id}/complete`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        this.showComplete = false
        await this.load()
      } catch(e) {
        alert(e.response?.data?.message || 'Failed to complete.')
      } finally { this.completing = false }
    },

    async saveImages() {
      this.uploadingSaving = true
      try {
        const fd = new FormData()
        if (this.postImages.H4)  fd.append('h4_image',  this.postImages.H4)
        if (this.postImages.M15) fd.append('m15_image', this.postImages.M15)
        if (this.postImages.M1)  fd.append('m1_image',  this.postImages.M1)

        await this.$http.post(`/journals/${this.journal.id}`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        this.showImageUpload = false
        this.postImages = {}
        await this.load()
      } catch(e) {
        alert('Failed to save images.')
      } finally { this.uploadingSaving = false }
    },

    async deleteJournal() {
      this.deleting = true
      try {
        await this.$http.delete(`/journals/${this.journal.id}`)
        this.$router.push('/journal')
      } finally { this.deleting = false }
    },

    fmt(d) {
      if (!d) return ''
      return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    },
  },
}
</script>

<style scoped>
/* Badges */
.jbadge { font-size:10px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; padding:2px 8px; border-radius:100px; }
.jbadge--pre       { background:rgba(212,160,23,.15);  color:#D4A017; }
.jbadge--done      { background:rgba(255,255,255,.08); color:#64748B; }
.jbadge--win       { background:rgba(29,158,117,.15);  color:#1D9E75; }
.jbadge--loss      { background:rgba(226,75,74,.15);   color:#E24B4A; }
.jbadge--breakeven { background:rgba(255,255,255,.08); color:#94A3B8; }

/* Category badges */
.cat-badge { font-size:11px; font-weight:600; padding:3px 9px; border-radius:5px; display:inline-block; }
.cat-badge--h4  { background:rgba(55,138,221,.15);  color:#85B7EB; }
.cat-badge--m15 { background:rgba(127,119,221,.15); color:#AFA9EC; }
.cat-badge--m1  { background:rgba(212,160,23,.12);  color:#D4A017; }

/* Detail labels */
.detail-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.8px; color:#4A5568; }

/* Chart thumbnails */
.chart-thumb-wrap { display:flex; flex-direction:column; gap:6px; cursor:pointer; }
.chart-thumb {
  position:relative; height:130px; border-radius:10px; overflow:hidden;
  background:#131C2E; border:1px solid rgba(255,255,255,.06);
  display:flex; align-items:center; justify-content:center;
  transition:border-color .2s;
}
.chart-thumb-wrap:hover .chart-thumb { border-color:rgba(29,158,117,.4); }
.chart-thumb--empty { cursor:default; }
.chart-thumb--empty:hover { border-color:rgba(255,255,255,.06) !important; }
.chart-thumb__img { width:100%; height:100%; object-fit:contain; }
.chart-thumb__overlay {
  position:absolute; inset:0; background:rgba(0,0,0,.4);
  display:flex; align-items:center; justify-content:center;
  opacity:0; transition:opacity .2s;
}
.chart-thumb-wrap:not(.chart-thumb--empty):hover .chart-thumb__overlay { opacity:1; }
.chart-thumb__zoom { font-size:24px; }
.chart-thumb__label {
  font-size:11px; font-weight:700; text-align:center;
  padding:2px 8px; border-radius:4px; align-self:center;
}
.chart-thumb__label--h4  { background:rgba(55,138,221,.15);  color:#85B7EB; }
.chart-thumb__label--m15 { background:rgba(127,119,221,.15); color:#AFA9EC; }
.chart-thumb__label--m1  { background:rgba(212,160,23,.12);  color:#D4A017; }

.text-win  { color:#1D9E75; }
.text-loss { color:#E24B4A; }
</style>