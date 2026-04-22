<template>
  <AppLayout>
    <div class="p-6 max-w-3xl">

      <div class="page-header">
        <h1 class="page-title">📥 Import Candle Data</h1>
        <p class="page-sub">Upload Dukascopy CSV files to populate the replay database. Files are processed and deleted after import.</p>
      </div>

      <!-- Upload card -->
      <div class="card mb-6">
        <h2 class="section-title">Upload CSV File</h2>

        <div class="grid grid-cols-2 gap-4 mb-5">
          <div>
            <label class="label">Pair *</label>
            <div class="flex gap-2">
              <button v-for="p in pairs" :key="p"
                class="flex-1 py-2 rounded-lg text-sm font-semibold border transition-colors"
                :class="form.pair === p
                  ? 'bg-win/15 border-win text-win'
                  : 'bg-surface border-border text-gray-500 hover:border-win/50'"
                @click="form.pair = p">{{ p }}</button>
            </div>
          </div>
          <div>
            <label class="label">Timeframe *</label>
            <select v-model="form.timeframe" class="select">
              <option value="">Select timeframe</option>
              <option v-for="tf in timeframes" :key="tf" :value="tf">{{ tf }}</option>
            </select>
          </div>
        </div>

        <!-- Drop zone -->
        <div class="drop-zone"
          :class="{
            'drop-zone--over':    isDragOver,
            'drop-zone--ready':   file && !uploading,
            'drop-zone--loading': uploading,
          }"
          @dragover.prevent="isDragOver = true"
          @dragleave="isDragOver = false"
          @drop.prevent="onDrop"
          @click="$refs.fileInput.click()">

          <input ref="fileInput" type="file" accept=".csv,.txt" class="hidden" @change="onFileSelect" />

          <!-- Idle state -->
          <div v-if="!file && !uploading" class="drop-zone__content">
            <div class="drop-zone__icon">📄</div>
            <div class="drop-zone__title">Drop your CSV file here</div>
            <div class="drop-zone__sub">or click to browse · .csv or .txt · up to 100MB</div>
          </div>

          <!-- File selected -->
          <div v-else-if="file && !uploading" class="drop-zone__content">
            <div class="drop-zone__icon">✅</div>
            <div class="drop-zone__title">{{ file.name }}</div>
            <div class="drop-zone__sub">{{ formatSize(file.size) }} · ready to import</div>
          </div>

          <!-- Uploading -->
          <div v-else-if="uploading" class="drop-zone__content">
            <div class="drop-zone__spinner"></div>
            <div class="drop-zone__title">Importing {{ form.pair }} {{ form.timeframe }}…</div>
            <div class="drop-zone__sub">This may take a moment for large files</div>
          </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="mt-3 p-3 bg-red-900/20 border border-red-800/30 rounded-lg text-red-400 text-sm">
          {{ error }}
        </div>

        <!-- Success result -->
        <div v-if="result" class="mt-3 p-4 bg-win/10 border border-win/30 rounded-lg">
          <div class="flex items-center gap-2 mb-3">
            <span class="text-win font-bold text-sm">✓ Import complete — {{ result.pair }} {{ result.timeframe }}</span>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div class="text-center">
              <div class="text-2xl font-bold text-win">{{ result.inserted.toLocaleString() }}</div>
              <div class="text-xs text-gray-500 mt-1">Candles imported</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-gray-500">{{ result.skipped.toLocaleString() }}</div>
              <div class="text-xs text-gray-500 mt-1">Duplicates skipped</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-white">{{ result.total.toLocaleString() }}</div>
              <div class="text-xs text-gray-500 mt-1">Total in file</div>
            </div>
          </div>
        </div>

        <div class="flex gap-3 mt-5">
          <button class="btn-primary" :disabled="!canUpload" @click="upload">
            📥 Import Data
          </button>
          <button v-if="file" class="btn-ghost" @click="clearFile">Clear</button>
          <router-link to="/replay" class="btn-ghost">Go to Replay →</router-link>
        </div>
      </div>

      <!-- Existing data table -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="section-title mb-0">Imported Datasets</h2>
          <button class="btn-ghost text-xs" @click="loadStats">↻ Refresh</button>
        </div>

        <div v-if="loadingStats" class="text-gray-600 text-sm py-4 text-center">Loading…</div>

        <div v-else-if="stats.length === 0" class="text-gray-600 text-sm py-4 text-center">
          No data imported yet. Upload a CSV file above to get started.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-border">
                <th class="th">Pair</th>
                <th class="th">TF</th>
                <th class="th text-right">Candles</th>
                <th class="th">From</th>
                <th class="th">To</th>
                <th class="th text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="row in stats" :key="row.pair+row.timeframe" class="hover:bg-card-hover transition-colors">
                <td class="td font-mono font-bold text-white">{{ row.pair }}</td>
                <td class="td">
                  <span class="text-xs font-bold px-2 py-0.5 rounded bg-blue-900/30 text-blue-300">{{ row.timeframe }}</span>
                </td>
                <td class="td text-right text-white font-semibold">{{ row.count.toLocaleString() }}</td>
                <td class="td text-gray-500 text-xs font-mono">{{ row.first?.slice(0,10) }}</td>
                <td class="td text-gray-500 text-xs font-mono">{{ row.last?.slice(0,10) }}</td>
                <td class="td text-right">
                  <button class="text-xs text-loss hover:underline" @click="confirmDelete(row)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Dukascopy format guide -->
      <div class="card mt-6 border border-border/50">
        <h2 class="section-title">📖 Dukascopy CSV Format</h2>
        <p class="text-sm text-gray-500 mb-3">EdgeLedger accepts the standard Dukascopy JForex export format. When exporting from JForex Strategy Tester or the Dukascopy data feed, use these settings:</p>
        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
          <div class="bg-surface rounded-lg p-3 border border-border">
            <div class="text-xs font-bold text-gray-400 mb-2">Expected columns</div>
            <code class="text-xs text-win">Gmt time, Open, High, Low, Close, Volume</code>
          </div>
          <div class="bg-surface rounded-lg p-3 border border-border">
            <div class="text-xs font-bold text-gray-400 mb-2">Date format</div>
            <code class="text-xs text-win">01.01.2024 00:00:00.000</code>
          </div>
        </div>
        <p class="text-xs text-gray-600">The importer also accepts semicolon-delimited files, Unix timestamps, and YYYY-MM-DD date formats. Header rows are skipped automatically.</p>
      </div>

    </div>

    <!-- Delete confirm modal -->
    <div v-if="deleteTarget" class="fixed inset-0 bg-black/70 flex items-center justify-center z-40 p-4">
      <div class="card w-full max-w-sm">
        <h2 class="section-title">Delete dataset</h2>
        <p class="text-sm text-gray-400 mb-4">
          Delete all <strong class="text-white">{{ deleteTarget.count.toLocaleString() }}</strong> candles
          for <strong class="text-white">{{ deleteTarget.pair }} {{ deleteTarget.timeframe }}</strong>?
          This cannot be undone.
        </p>
        <div class="flex gap-3">
          <button class="btn-danger" :disabled="deleting" @click="deleteDataset">
            {{ deleting ? 'Deleting…' : 'Delete' }}
          </button>
          <button class="btn-ghost" @click="deleteTarget = null">Cancel</button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script>
import AppLayout from '../components/AppLayout.vue'
export default {
  name: 'CandleImportView',
  components: { AppLayout },
  data() {
    return {
      pairs:      ['EURUSD','GBPUSD','AUDUSD'],
      timeframes: ['M1','M5','M15','M30','H1','H4','D1'],
      form:       { pair: 'EURUSD', timeframe: 'H1' },
      file:       null,
      isDragOver: false,
      uploading:  false,
      error:      '',
      result:     null,
      stats:      [],
      loadingStats: false,
      deleteTarget: null,
      deleting: false,
    }
  },
  computed: {
    canUpload() {
      return this.file && this.form.pair && this.form.timeframe && !this.uploading
    },
  },
  mounted() { this.loadStats() },
  methods: {
    onFileSelect(e) {
      const f = e.target.files[0]
      if (f) { this.file = f; this.error = ''; this.result = null }
    },
    onDrop(e) {
      this.isDragOver = false
      const f = e.dataTransfer.files[0]
      if (f && (f.name.endsWith('.csv') || f.name.endsWith('.txt'))) {
        this.file = f; this.error = ''; this.result = null
      } else {
        this.error = 'Please drop a .csv or .txt file.'
      }
    },
    clearFile() {
      this.file = null; this.result = null; this.error = ''
      if (this.$refs.fileInput) this.$refs.fileInput.value = ''
    },
    async upload() {
      if (!this.canUpload) return
      this.uploading = true
      this.error     = ''
      this.result    = null
      try {
        const fd = new FormData()
        fd.append('file',      this.file)
        fd.append('pair',      this.form.pair)
        fd.append('timeframe', this.form.timeframe)
        const { data } = await this.$http.post('/candles/upload', fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
          timeout: 300000, // 5 min for large files
        })
        this.result = data
        this.clearFile()
        await this.loadStats()
      } catch(e) {
        this.error = e.response?.data?.message || 'Import failed. Check your CSV format.'
      } finally { this.uploading = false }
    },
    async loadStats() {
      this.loadingStats = true
      try {
        const { data } = await this.$http.get('/candles/stats')
        this.stats = data
      } catch(e) {}
      finally { this.loadingStats = false }
    },
    confirmDelete(row) { this.deleteTarget = row },
    async deleteDataset() {
      if (!this.deleteTarget) return
      this.deleting = true
      try {
        await this.$http.delete('/candles', {
          data: { pair: this.deleteTarget.pair, timeframe: this.deleteTarget.timeframe }
        })
        this.deleteTarget = null
        await this.loadStats()
      } catch(e) {
        alert('Delete failed.')
      } finally { this.deleting = false }
    },
    formatSize(bytes) {
      if (bytes < 1024)       return bytes + ' B'
      if (bytes < 1048576)    return (bytes/1024).toFixed(1) + ' KB'
      return (bytes/1048576).toFixed(1) + ' MB'
    },
  },
}
</script>

<style scoped>
.drop-zone {
  border: 2px dashed rgba(255,255,255,0.1);
  border-radius: 12px;
  padding: 48px 24px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: rgba(255,255,255,0.01);
}
.drop-zone:hover, .drop-zone--over {
  border-color: rgba(29,158,117,0.5);
  background: rgba(29,158,117,0.04);
}
.drop-zone--ready {
  border-color: rgba(29,158,117,0.4);
  background: rgba(29,158,117,0.05);
}
.drop-zone--loading {
  border-color: rgba(29,158,117,0.3);
  cursor: not-allowed;
}
.drop-zone__content { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.drop-zone__icon    { font-size: 36px; }
.drop-zone__title   { font-size: 15px; font-weight: 600; color: #E2E8F0; }
.drop-zone__sub     { font-size: 13px; color: #64748B; }
.drop-zone__spinner {
  width: 32px; height: 32px;
  border: 3px solid rgba(29,158,117,0.2);
  border-top-color: #1D9E75;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 8px;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>