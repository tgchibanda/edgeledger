<template>
  <AppLayout>
    <div class="replay-page">

      <!-- ── TOP TOOLBAR ── -->
      <div class="replay-toolbar">

        <!-- Pair selector -->
        <div class="tb-group">
          <label class="tb-label">Pair</label>
          <div class="tb-pills">
            <button v-for="p in pairs" :key="p"
              class="tb-pill" :class="{ active: selectedPair === p }"
              @click="switchPair(p)">{{ p }}</button>
          </div>
        </div>

        <!-- Timeframe selector -->
        <div class="tb-group">
          <label class="tb-label">Timeframe</label>
          <div class="tb-pills">
            <button v-for="tf in timeframes" :key="tf"
              class="tb-pill" :class="{ active: selectedTF === tf }"
              @click="switchTF(tf)">{{ tf }}</button>
          </div>
        </div>

        <!-- Date picker -->
        <div class="tb-group">
          <label class="tb-label">Start Date</label>
          <input type="date" v-model="startDate" class="tb-input" @change="jumpToDate" />
        </div>

        <!-- Playback controls -->
        <div class="tb-group">
          <label class="tb-label">Playback</label>
          <div class="tb-controls">
            <button class="tb-btn" @click="stepBack"   title="Step back">⏮</button>
            <button class="tb-btn tb-btn--play" @click="togglePlay" :class="{ playing }">
              {{ playing ? '⏸' : '▶' }}
            </button>
            <button class="tb-btn" @click="stepForward" title="Step forward">⏭</button>
            <select v-model="speed" class="tb-select">
              <option :value="1000">1×</option>
              <option :value="500">2×</option>
              <option :value="200">5×</option>
              <option :value="100">10×</option>
            </select>
          </div>
        </div>

        <!-- Speed / progress -->
        <div class="tb-group tb-group--info">
          <div class="tb-candle-info" v-if="currentCandle">
            <span class="tb-candle-date">{{ formatDate(currentCandle.time) }}</span>
            <span class="tb-ohlc">
              O:<span :class="priceColor">{{ fmt(currentCandle.open) }}</span>
              H:<span class="text-win">{{ fmt(currentCandle.high) }}</span>
              L:<span class="text-loss">{{ fmt(currentCandle.low) }}</span>
              C:<span :class="priceColor">{{ fmt(currentCandle.close) }}</span>
            </span>
          </div>
          <div class="tb-progress">
            <span class="tb-progress-text">{{ currentIndex + 1 }} / {{ allCandles.length }}</span>
            <div class="tb-progress-bar">
              <div class="tb-progress-fill" :style="{ width: progressPct + '%' }"></div>
            </div>
          </div>
        </div>

      </div>

      <!-- ── MAIN AREA ── -->
      <div class="replay-main">

        <!-- Drawing toolbar -->
        <div class="draw-toolbar">
          <button v-for="tool in drawTools" :key="tool.id"
            class="draw-btn" :class="{ active: activeTool === tool.id }"
            :title="tool.label" @click="selectTool(tool.id)">
            {{ tool.icon }}
          </button>
          <div class="draw-sep"></div>
          <button class="draw-btn draw-btn--buy"  title="Buy marker"  @click="addMarker('buy')">▲ BUY</button>
          <button class="draw-btn draw-btn--sell" title="Sell marker" @click="addMarker('sell')">▼ SELL</button>
          <div class="draw-sep"></div>
          <button class="draw-btn" title="Undo last drawing" @click="undoDrawing">↩</button>
          <button class="draw-btn draw-btn--clear" title="Clear all drawings" @click="clearDrawings">🗑</button>
        </div>

        <!-- Chart container -->
        <div class="chart-wrap">
          <div ref="chartContainer" class="chart-container"></div>
          <!-- Drawing overlay SVG -->
          <svg ref="drawingLayer" class="drawing-layer"
            :style="{ pointerEvents: activeTool !== 'cursor' ? 'all' : 'none' }"
            @mousedown="onMouseDown"
            @mousemove="onMouseMove"
            @mouseup="onMouseUp">
            <!-- Completed drawings -->
            <g v-for="(d, i) in drawings" :key="'d'+i">
              <!-- Trendline -->
              <line v-if="d.type === 'trendline'"
                :x1="d.x1" :y1="d.y1" :x2="d.x2" :y2="d.y2"
                stroke="#1D9E75" stroke-width="1.5" />
              <!-- Horizontal line -->
              <line v-if="d.type === 'hline'"
                :x1="0" :y1="d.y" :x2="chartWidth" :y2="d.y"
                stroke="#EF9F27" stroke-width="1" stroke-dasharray="4 3" />
              <!-- Rectangle -->
              <rect v-if="d.type === 'rect'"
                :x="Math.min(d.x1,d.x2)" :y="Math.min(d.y1,d.y2)"
                :width="Math.abs(d.x2-d.x1)" :height="Math.abs(d.y2-d.y1)"
                fill="rgba(29,158,117,0.08)" stroke="#1D9E75" stroke-width="1" />
            </g>
            <!-- In-progress drawing -->
            <g v-if="drawing">
              <line v-if="drawing.type === 'trendline'"
                :x1="drawing.x1" :y1="drawing.y1" :x2="drawing.x2" :y2="drawing.y2"
                stroke="#1D9E75" stroke-width="1.5" stroke-dasharray="5 3" />
              <line v-if="drawing.type === 'hline'"
                :x1="0" :y1="drawing.y" :x2="chartWidth" :y2="drawing.y"
                stroke="#EF9F27" stroke-width="1" stroke-dasharray="4 3" />
              <rect v-if="drawing.type === 'rect'"
                :x="Math.min(drawing.x1,drawing.x2)" :y="Math.min(drawing.y1,drawing.y2)"
                :width="Math.abs(drawing.x2-drawing.x1)" :height="Math.abs(drawing.y2-drawing.y1)"
                fill="rgba(29,158,117,0.08)" stroke="#1D9E75" stroke-width="1" stroke-dasharray="5 3"/>
            </g>
          </svg>
        </div>

        <!-- Volume panel -->
        <div ref="volumeContainer" class="volume-container"></div>

      </div>

      <!-- Loading overlay -->
      <div v-if="loading" class="replay-loading">
        <div class="replay-loading__spinner"></div>
        <div class="replay-loading__text">Loading {{ selectedPair }} {{ selectedTF }}…</div>
      </div>

      <!-- Empty state -->
      <div v-if="!loading && allCandles.length === 0" class="replay-empty">
        <div class="replay-empty__icon">📉</div>
        <div class="replay-empty__title">No candle data</div>
        <div class="replay-empty__sub">
          Import data using the artisan command:<br/>
          <code>php artisan candles:import file.csv {{ selectedPair }} {{ selectedTF }}</code>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../components/AppLayout.vue'

export default {
  name: 'ReplayView',
  components: { AppLayout },

  data() {
    return {
      // Pairs / TF
      pairs:        ['EURUSD','GBPUSD','AUDUSD'],
      timeframes:   ['M1','M5','M15','M30','H1','H4','D1'],
      selectedPair: 'EURUSD',
      selectedTF:   'H1',

      // Candle data
      allCandles:   [],
      currentIndex: 0,
      loading:      false,

      // Playback
      playing:  false,
      speed:    500,
      playTimer: null,

      // Date control
      startDate: '',

      // Chart instances (Lightweight Charts)
      chart:        null,
      candleSeries: null,
      volumeSeries: null,
      markers:      [],

      // Chart dimensions
      chartWidth:  0,
      chartHeight: 0,

      // Drawing
      drawTools: [
        { id: 'cursor',    label: 'Cursor',          icon: '↖' },
        { id: 'trendline', label: 'Trendline',       icon: '╱' },
        { id: 'hline',     label: 'Horizontal Line', icon: '—' },
        { id: 'rect',      label: 'Rectangle',       icon: '□' },
      ],
      activeTool: 'cursor',
      drawings:   [],
      drawing:    null,
      isDrawing:  false,

      // Per-pair drawing persistence
      drawingsByPair: {},
    }
  },

  computed: {
    currentCandle() {
      return this.allCandles[this.currentIndex] || null
    },
    priceColor() {
      if (!this.currentCandle) return ''
      return this.currentCandle.close >= this.currentCandle.open ? 'text-win' : 'text-loss'
    },
    progressPct() {
      if (!this.allCandles.length) return 0
      return ((this.currentIndex + 1) / this.allCandles.length) * 100
    },
    visibleCandles() {
      return this.allCandles.slice(0, this.currentIndex + 1)
    },
  },

  async mounted() {
    await this.loadChartLibrary()
    await this.initChart()
    await this.loadCandles()
    window.addEventListener('resize', this.onResize)
  },

  beforeDestroy() {
    this.stopPlay()
    if (this.chart) this.chart.remove()
    window.removeEventListener('resize', this.onResize)
  },

  methods: {

    // ── Library loading ─────────────────────────────────────────────────────
    loadChartLibrary() {
      return new Promise((resolve) => {
        if (window.LightweightCharts) { resolve(); return }
        const script = document.createElement('script')
        script.src = 'https://unpkg.com/lightweight-charts/dist/lightweight-charts.standalone.production.js'
        script.onload = resolve
        document.head.appendChild(script)
      })
    },

    // ── Chart init ──────────────────────────────────────────────────────────
    initChart() {
      const container = this.$refs.chartContainer
      if (!container || !window.LightweightCharts) return

      const w = container.clientWidth  || 800
      const h = container.clientHeight || 480

      this.chart = LightweightCharts.createChart(container, {
        width:  w,
        height: h,
        layout: {
          background: { color: '#0F1923' },
          textColor:  '#94A3B8',
        },
        grid: {
          vertLines:   { color: 'rgba(255,255,255,0.04)' },
          horzLines:   { color: 'rgba(255,255,255,0.04)' },
        },
        crosshair: {
          mode: LightweightCharts.CrosshairMode.Normal,
        },
        rightPriceScale: {
          borderColor: 'rgba(255,255,255,0.08)',
        },
        timeScale: {
          borderColor:     'rgba(255,255,255,0.08)',
          timeVisible:     true,
          secondsVisible:  false,
        },
      })

      this.candleSeries = this.chart.addCandlestickSeries({
        upColor:      '#1D9E75',
        downColor:    '#E24B4A',
        borderUpColor:   '#1D9E75',
        borderDownColor: '#E24B4A',
        wickUpColor:     '#1D9E75',
        wickDownColor:   '#E24B4A',
      })

      // Volume series in a separate pane
      this.volumeSeries = this.chart.addHistogramSeries({
        color:   '#1D9E75',
        priceFormat: { type: 'volume' },
        priceScaleId: 'volume',
        scaleMargins: { top: 0.8, bottom: 0 },
      })

      this.chartWidth  = w
      this.chartHeight = h
    },

    // ── Data loading ────────────────────────────────────────────────────────
    async loadCandles() {
      this.loading = true
      this.stopPlay()
      try {
        const params = {
          pair:      this.selectedPair,
          timeframe: this.selectedTF,
          limit:     5000,
        }
        if (this.startDate) params.from = this.startDate

        const { data } = await this.$http.get('/candles', { params })
        this.allCandles  = data
        this.currentIndex = Math.max(0, data.length - 1)
        this.updateChart()
      } catch(e) {
        this.allCandles = []
      } finally {
        this.loading = false
      }
    },

    // ── Chart update ────────────────────────────────────────────────────────
    updateChart() {
      if (!this.candleSeries || !this.visibleCandles.length) return

      const candles = this.visibleCandles.map(c => ({
        time:  c.time,
        open:  c.open,
        high:  c.high,
        low:   c.low,
        close: c.close,
      }))

      const volumes = this.visibleCandles.map(c => ({
        time:  c.time,
        value: c.volume,
        color: c.close >= c.open
          ? 'rgba(29,158,117,0.4)'
          : 'rgba(226,75,74,0.4)',
      }))

      this.candleSeries.setData(candles)
      this.volumeSeries.setData(volumes)

      // Apply markers (buy/sell)
      this.candleSeries.setMarkers(this.markers)

      // Scroll to last visible candle
      this.chart.timeScale().scrollToPosition(2, false)
    },

    // ── Playback ────────────────────────────────────────────────────────────
    togglePlay() {
      this.playing ? this.stopPlay() : this.startPlay()
    },

    startPlay() {
      if (this.currentIndex >= this.allCandles.length - 1) {
        this.currentIndex = 0
      }
      this.playing  = true
      this.playTimer = setInterval(() => {
        if (this.currentIndex < this.allCandles.length - 1) {
          this.currentIndex++
          this.updateChart()
        } else {
          this.stopPlay()
        }
      }, this.speed)
    },

    stopPlay() {
      this.playing = false
      if (this.playTimer) { clearInterval(this.playTimer); this.playTimer = null }
    },

    stepForward() {
      this.stopPlay()
      if (this.currentIndex < this.allCandles.length - 1) {
        this.currentIndex++
        this.updateChart()
      }
    },

    stepBack() {
      this.stopPlay()
      if (this.currentIndex > 0) {
        this.currentIndex--
        this.updateChart()
      }
    },

    // ── Pair / TF switching ─────────────────────────────────────────────────
    switchPair(pair) {
      // Save current drawings under old pair key
      this.drawingsByPair[this.selectedPair + '_' + this.selectedTF] = [...this.drawings]
      this.selectedPair = pair
      this.restoreDrawings()
      this.loadCandles()
    },

    switchTF(tf) {
      this.drawingsByPair[this.selectedPair + '_' + this.selectedTF] = [...this.drawings]
      this.selectedTF = tf
      this.restoreDrawings()
      this.loadCandles()
    },

    restoreDrawings() {
      const key = this.selectedPair + '_' + this.selectedTF
      this.drawings = this.drawingsByPair[key] ? [...this.drawingsByPair[key]] : []
    },

    // ── Date jump ───────────────────────────────────────────────────────────
    jumpToDate() {
      if (!this.startDate || !this.allCandles.length) return
      const target = new Date(this.startDate).getTime() / 1000
      const idx = this.allCandles.findIndex(c => c.time >= target)
      if (idx >= 0) {
        this.currentIndex = idx
        this.updateChart()
      } else {
        // Date not in current data — reload from that date
        this.loadCandles()
      }
    },

    // ── Markers (buy/sell) ──────────────────────────────────────────────────
    addMarker(side) {
      if (!this.currentCandle) return
      const marker = {
        time:     this.currentCandle.time,
        position: side === 'buy' ? 'belowBar' : 'aboveBar',
        color:    side === 'buy' ? '#1D9E75'  : '#E24B4A',
        shape:    side === 'buy' ? 'arrowUp'  : 'arrowDown',
        text:     side === 'buy' ? 'BUY'      : 'SELL',
        size:     1.5,
      }
      // Remove any marker at this exact time first to avoid duplicates
      this.markers = this.markers.filter(m => m.time !== marker.time)
      this.markers.push(marker)
      this.markers.sort((a, b) => a.time - b.time)
      this.candleSeries.setMarkers(this.markers)
    },

    // ── Drawing tools ───────────────────────────────────────────────────────
    selectTool(id) {
      this.activeTool = id
    },

    getRelativePos(e) {
      const rect = this.$refs.drawingLayer.getBoundingClientRect()
      return { x: e.clientX - rect.left, y: e.clientY - rect.top }
    },

    onMouseDown(e) {
      if (this.activeTool === 'cursor') return
      const pos = this.getRelativePos(e)
      this.isDrawing = true

      if (this.activeTool === 'hline') {
        this.drawing = { type: 'hline', y: pos.y }
      } else if (this.activeTool === 'trendline') {
        this.drawing = { type: 'trendline', x1: pos.x, y1: pos.y, x2: pos.x, y2: pos.y }
      } else if (this.activeTool === 'rect') {
        this.drawing = { type: 'rect', x1: pos.x, y1: pos.y, x2: pos.x, y2: pos.y }
      }
    },

    onMouseMove(e) {
      if (!this.isDrawing || !this.drawing) return
      const pos = this.getRelativePos(e)
      if (this.drawing.type === 'hline') {
        this.drawing = { ...this.drawing, y: pos.y }
      } else {
        this.drawing = { ...this.drawing, x2: pos.x, y2: pos.y }
      }
    },

    onMouseUp(e) {
      if (!this.isDrawing || !this.drawing) return
      const pos = this.getRelativePos(e)

      if (this.drawing.type === 'hline') {
        this.drawings.push({ ...this.drawing, y: pos.y })
      } else {
        this.drawing = { ...this.drawing, x2: pos.x, y2: pos.y }
        // Only save if it has some size
        const dx = Math.abs(this.drawing.x2 - this.drawing.x1)
        const dy = Math.abs(this.drawing.y2 - this.drawing.y1)
        if (dx > 3 || dy > 3) {
          this.drawings.push({ ...this.drawing })
        }
      }

      this.drawing   = null
      this.isDrawing = false
    },

    undoDrawing() {
      this.drawings.pop()
    },

    clearDrawings() {
      this.drawings = []
      this.markers  = []
      if (this.candleSeries) this.candleSeries.setMarkers([])
    },

    // ── Resize ──────────────────────────────────────────────────────────────
    onResize() {
      if (!this.chart || !this.$refs.chartContainer) return
      const w = this.$refs.chartContainer.clientWidth
      const h = this.$refs.chartContainer.clientHeight
      this.chart.resize(w, h)
      this.chartWidth  = w
      this.chartHeight = h
    },

    // ── Formatting ──────────────────────────────────────────────────────────
    fmt(v) { return v ? v.toFixed(5) : '—' },

    formatDate(ts) {
      if (!ts) return ''
      const d = new Date(ts * 1000)
      return d.toISOString().replace('T',' ').slice(0,16)
    },
  },

  watch: {
    speed() {
      // Restart interval with new speed if playing
      if (this.playing) { this.stopPlay(); this.startPlay() }
    },
  },
}
</script>

<style scoped>
.replay-page {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 64px);
  background: #080D13;
  position: relative;
  overflow: hidden;
}

/* ── TOOLBAR ── */
.replay-toolbar {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 10px 16px;
  background: #0F1923;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  flex-shrink: 0;
  flex-wrap: wrap;
}
.tb-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.tb-group--info {
  margin-left: auto;
  align-items: flex-end;
}
.tb-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #4A5568;
  font-weight: 600;
}
.tb-pills {
  display: flex;
  gap: 3px;
}
.tb-pill {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  background: #1A2633;
  border: 1px solid rgba(255,255,255,0.07);
  color: #64748B;
  cursor: pointer;
  transition: all 0.15s;
  font-family: 'DM Mono', monospace;
}
.tb-pill:hover { border-color: rgba(29,158,117,0.4); color: #94A3B8; }
.tb-pill.active { background: rgba(29,158,117,0.15); border-color: #1D9E75; color: #1D9E75; }
.tb-input {
  background: #1A2633;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 6px;
  padding: 5px 8px;
  color: #E2E8F0;
  font-size: 12px;
  outline: none;
}
.tb-input:focus { border-color: #1D9E75; }
.tb-controls { display: flex; gap: 4px; align-items: center; }
.tb-btn {
  width: 32px; height: 32px;
  border-radius: 6px;
  background: #1A2633;
  border: 1px solid rgba(255,255,255,0.08);
  color: #94A3B8;
  cursor: pointer;
  font-size: 14px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.15s;
}
.tb-btn:hover { border-color: rgba(29,158,117,0.4); color: #fff; }
.tb-btn--play { width: 36px; height: 36px; font-size: 16px; }
.tb-btn--play.playing { background: rgba(226,75,74,0.15); border-color: #E24B4A; color: #E24B4A; }
.tb-select {
  background: #1A2633;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 6px;
  padding: 4px 6px;
  color: #94A3B8;
  font-size: 12px;
  outline: none;
}
.tb-candle-info {
  display: flex; gap: 12px; align-items: center; margin-bottom: 4px;
}
.tb-candle-date { font-size: 12px; color: #64748B; font-family: monospace; }
.tb-ohlc { font-size: 12px; font-family: monospace; display: flex; gap: 8px; }
.tb-ohlc span { color: #94A3B8; }
.tb-progress { display: flex; gap: 8px; align-items: center; }
.tb-progress-text { font-size: 11px; color: #4A5568; white-space: nowrap; }
.tb-progress-bar {
  width: 120px; height: 3px;
  background: rgba(255,255,255,0.08);
  border-radius: 2px;
  overflow: hidden;
}
.tb-progress-fill { height: 100%; background: #1D9E75; border-radius: 2px; transition: width 0.1s; }

/* ── MAIN ── */
.replay-main {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  position: relative;
}

/* ── DRAWING TOOLBAR ── */
.draw-toolbar {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  background: #0F1923;
  border-bottom: 1px solid rgba(255,255,255,0.04);
  flex-shrink: 0;
}
.draw-btn {
  padding: 5px 10px;
  border-radius: 6px;
  background: #1A2633;
  border: 1px solid rgba(255,255,255,0.07);
  color: #64748B;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.draw-btn:hover { border-color: rgba(255,255,255,0.15); color: #94A3B8; }
.draw-btn.active { background: rgba(29,158,117,0.15); border-color: #1D9E75; color: #1D9E75; }
.draw-btn--buy  { color: #1D9E75; border-color: rgba(29,158,117,0.3); }
.draw-btn--buy:hover  { background: rgba(29,158,117,0.15); }
.draw-btn--sell { color: #E24B4A; border-color: rgba(226,75,74,0.3); }
.draw-btn--sell:hover { background: rgba(226,75,74,0.15); }
.draw-btn--clear { color: #E24B4A; }
.draw-sep { width: 1px; height: 20px; background: rgba(255,255,255,0.06); margin: 0 4px; }

/* ── CHART ── */
.chart-wrap {
  flex: 1;
  min-height: 0;
  position: relative;
}
.chart-container {
  width: 100%;
  height: 100%;
}
.drawing-layer {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 10;
}
/* Enable pointer events only when a draw tool is active */
.draw-toolbar .draw-btn.active ~ .chart-wrap .drawing-layer {
  pointer-events: all;
}
.volume-container {
  height: 80px;
  flex-shrink: 0;
  background: #080D13;
}

/* ── STATES ── */
.replay-loading {
  position: absolute;
  inset: 0;
  background: rgba(8,13,19,0.85);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  z-index: 50;
}
.replay-loading__spinner {
  width: 36px; height: 36px;
  border: 3px solid rgba(29,158,117,0.2);
  border-top-color: #1D9E75;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.replay-loading__text { font-size: 14px; color: #64748B; }

.replay-empty {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  z-index: 40;
}
.replay-empty__icon { font-size: 40px; }
.replay-empty__title { font-size: 18px; font-weight: 700; color: #fff; }
.replay-empty__sub { font-size: 13px; color: #4A5568; text-align: center; line-height: 1.7; }
.replay-empty__sub code {
  display: block;
  margin-top: 8px;
  padding: 8px 16px;
  background: #0F1923;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 6px;
  color: #1D9E75;
  font-family: monospace;
  font-size: 12px;
}

/* ── Utility ── */
.text-win  { color: #1D9E75; }
.text-loss { color: #E24B4A; }
</style>
