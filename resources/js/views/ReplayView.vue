<template>
  <AppLayout>
    <div class="replay-page">

      <!-- ── TOP TOOLBAR ── -->
      <div class="replay-toolbar">
        <div class="tb-group">
          <label class="tb-label">Pair</label>
          <div class="tb-pills">
            <button v-for="p in pairs" :key="p" class="tb-pill" :class="{ active: selectedPair === p }" @click="switchPair(p)">{{ p }}</button>
          </div>
        </div>
        <div class="tb-group">
          <label class="tb-label">Timeframe</label>
          <div class="tb-pills">
            <button v-for="tf in timeframes" :key="tf" class="tb-pill" :class="{ active: selectedTF === tf }" @click="switchTF(tf)">{{ tf }}</button>
          </div>
        </div>
        <div class="tb-group">
          <label class="tb-label">Start Date</label>
          <input type="date" v-model="startDate" class="tb-input" @change="jumpToDate" />
        </div>
        <div class="tb-group">
          <label class="tb-label">Playback</label>
          <div class="tb-controls">
            <button class="tb-btn" @click="stepBack" title="Step back">⏮</button>
            <button class="tb-btn tb-btn--play" @click="togglePlay" :class="{ playing }">{{ playing ? '⏸' : '▶' }}</button>
            <button class="tb-btn" @click="stepForward" title="Step forward">⏭</button>
            <select v-model="speed" class="tb-select">
              <option :value="1000">1×</option>
              <option :value="500">2×</option>
              <option :value="200">5×</option>
              <option :value="100">10×</option>
            </select>
          </div>
        </div>
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
            <div class="tb-progress-bar"><div class="tb-progress-fill" :style="{ width: progressPct + '%' }"></div></div>
          </div>
        </div>
      </div>

      <!-- ── MAIN AREA ── -->
      <div class="replay-main">

        <!-- ── DRAWING TOOLBAR (vertical, left side, TradingView style) ── -->
        <div class="draw-sidebar">

          <!-- Cursor -->
          <button class="dt-btn" :class="{ active: activeTool==='cursor' }" title="Cursor (Esc)" @click="selectTool('cursor')">
            <svg viewBox="0 0 18 18" fill="none"><path d="M4 2l10 7-6 1-2 6z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
          </button>

          <div class="dt-sep"></div>

          <!-- Trendline -->
          <button class="dt-btn" :class="{ active: activeTool==='trendline' }" title="Trend Line" @click="selectTool('trendline')">
            <svg viewBox="0 0 18 18" fill="none"><line x1="2" y1="15" x2="16" y2="3" stroke="currentColor" stroke-width="1.5"/><circle cx="2" cy="15" r="1.5" fill="currentColor"/><circle cx="16" cy="3" r="1.5" fill="currentColor"/></svg>
          </button>

          <!-- Ray (one-directional trendline) -->
          <button class="dt-btn" :class="{ active: activeTool==='ray' }" title="Ray" @click="selectTool('ray')">
            <svg viewBox="0 0 18 18" fill="none"><line x1="2" y1="14" x2="17" y2="4" stroke="currentColor" stroke-width="1.5"/><circle cx="2" cy="14" r="1.5" fill="currentColor"/><polygon points="17,4 13,4 15,7" fill="currentColor"/></svg>
          </button>

          <!-- Extended line -->
          <button class="dt-btn" :class="{ active: activeTool==='extline' }" title="Extended Line" @click="selectTool('extline')">
            <svg viewBox="0 0 18 18" fill="none"><line x1="0" y1="14" x2="18" y2="4" stroke="currentColor" stroke-width="1.5"/><circle cx="6" cy="11" r="1.5" fill="currentColor"/><circle cx="12" cy="7" r="1.5" fill="currentColor"/></svg>
          </button>

          <div class="dt-sep"></div>

          <!-- Horizontal line -->
          <button class="dt-btn" :class="{ active: activeTool==='hline' }" title="Horizontal Line" @click="selectTool('hline')">
            <svg viewBox="0 0 18 18" fill="none"><line x1="1" y1="9" x2="17" y2="9" stroke="currentColor" stroke-width="1.5"/><line x1="4" y1="6" x2="4" y2="12" stroke="currentColor" stroke-width="1"/><line x1="14" y1="6" x2="14" y2="12" stroke="currentColor" stroke-width="1"/></svg>
          </button>

          <!-- Vertical line -->
          <button class="dt-btn" :class="{ active: activeTool==='vline' }" title="Vertical Line" @click="selectTool('vline')">
            <svg viewBox="0 0 18 18" fill="none"><line x1="9" y1="1" x2="9" y2="17" stroke="currentColor" stroke-width="1.5"/><line x1="6" y1="4" x2="12" y2="4" stroke="currentColor" stroke-width="1"/><line x1="6" y1="14" x2="12" y2="14" stroke="currentColor" stroke-width="1"/></svg>
          </button>

          <!-- Cross line -->
          <button class="dt-btn" :class="{ active: activeTool==='cross' }" title="Cross" @click="selectTool('cross')">
            <svg viewBox="0 0 18 18" fill="none"><line x1="1" y1="9" x2="17" y2="9" stroke="currentColor" stroke-width="1.3"/><line x1="9" y1="1" x2="9" y2="17" stroke="currentColor" stroke-width="1.3"/></svg>
          </button>

          <div class="dt-sep"></div>

          <!-- Parallel channel -->
          <button class="dt-btn" :class="{ active: activeTool==='channel' }" title="Parallel Channel" @click="selectTool('channel')">
            <svg viewBox="0 0 18 18" fill="none"><line x1="2" y1="13" x2="16" y2="5" stroke="currentColor" stroke-width="1.5"/><line x1="2" y1="16" x2="16" y2="8" stroke="currentColor" stroke-width="1.5" stroke-dasharray="2 1.5"/></svg>
          </button>

          <!-- Rectangle -->
          <button class="dt-btn" :class="{ active: activeTool==='rect' }" title="Rectangle" @click="selectTool('rect')">
            <svg viewBox="0 0 18 18" fill="none"><rect x="3" y="5" width="12" height="8" stroke="currentColor" stroke-width="1.5" fill="rgba(29,158,117,0.1)"/></svg>
          </button>

          <!-- Ellipse -->
          <button class="dt-btn" :class="{ active: activeTool==='ellipse' }" title="Ellipse" @click="selectTool('ellipse')">
            <svg viewBox="0 0 18 18" fill="none"><ellipse cx="9" cy="9" rx="7" ry="5" stroke="currentColor" stroke-width="1.5" fill="rgba(29,158,117,0.1)"/></svg>
          </button>

          <!-- Triangle -->
          <button class="dt-btn" :class="{ active: activeTool==='triangle' }" title="Triangle" @click="selectTool('triangle')">
            <svg viewBox="0 0 18 18" fill="none"><polygon points="9,3 16,15 2,15" stroke="currentColor" stroke-width="1.5" fill="rgba(29,158,117,0.1)"/></svg>
          </button>

          <div class="dt-sep"></div>

          <!-- Fibonacci retracement -->
          <button class="dt-btn" :class="{ active: activeTool==='fib' }" title="Fibonacci Retracement" @click="selectTool('fib')">
            <svg viewBox="0 0 18 18" fill="none">
              <line x1="2" y1="3"  x2="16" y2="3"  stroke="currentColor" stroke-width="1"/>
              <line x1="2" y1="7"  x2="16" y2="7"  stroke="#EF9F27" stroke-width="1"/>
              <line x1="2" y1="10" x2="16" y2="10" stroke="#85B7EB" stroke-width="1"/>
              <line x1="2" y1="13" x2="16" y2="13" stroke="#AFA9EC" stroke-width="1"/>
              <line x1="2" y1="15" x2="16" y2="15" stroke="currentColor" stroke-width="1"/>
              <text x="3" y="6"  font-size="3.5" fill="#EF9F27">0.618</text>
              <text x="3" y="9.5" font-size="3.5" fill="#85B7EB">0.5</text>
              <text x="3" y="12.5" font-size="3.5" fill="#AFA9EC">0.382</text>
            </svg>
          </button>

          <!-- Pitchfork -->
          <button class="dt-btn" :class="{ active: activeTool==='pitchfork' }" title="Pitchfork" @click="selectTool('pitchfork')">
            <svg viewBox="0 0 18 18" fill="none"><line x1="9" y1="3" x2="9" y2="15" stroke="currentColor" stroke-width="1.3"/><line x1="9" y1="9" x2="3" y2="15" stroke="currentColor" stroke-width="1.3"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="1.3"/><circle cx="9" cy="3" r="1.3" fill="currentColor"/></svg>
          </button>

          <div class="dt-sep"></div>

          <!-- Text label -->
          <button class="dt-btn" :class="{ active: activeTool==='text' }" title="Text" @click="selectTool('text')">
            <svg viewBox="0 0 18 18" fill="none"><text x="2" y="14" font-size="13" font-weight="bold" fill="currentColor" font-family="serif">T</text></svg>
          </button>

          <!-- Price label -->
          <button class="dt-btn" :class="{ active: activeTool==='pricelabel' }" title="Price Label" @click="selectTool('pricelabel')">
            <svg viewBox="0 0 18 18" fill="none"><rect x="2" y="6" width="14" height="7" rx="2" stroke="currentColor" stroke-width="1.3"/><text x="5" y="12" font-size="5" fill="currentColor">1.08</text></svg>
          </button>

          <div class="dt-sep"></div>

          <!-- Long position -->
          <button class="dt-btn dt-btn--buy" :class="{ active: activeTool==='long' }" title="Long Position" @click="selectTool('long')">
            <svg viewBox="0 0 18 18" fill="none"><polygon points="9,3 15,10 12,10 12,15 6,15 6,10 3,10" fill="#1D9E75" opacity="0.8"/></svg>
          </button>

          <!-- Short position -->
          <button class="dt-btn dt-btn--sell" :class="{ active: activeTool==='short' }" title="Short Position" @click="selectTool('short')">
            <svg viewBox="0 0 18 18" fill="none"><polygon points="9,15 15,8 12,8 12,3 6,3 6,8 3,8" fill="#E24B4A" opacity="0.8"/></svg>
          </button>

          <!-- Buy marker -->
          <button class="dt-btn dt-btn--buy" title="Buy Marker" @click="addMarker('buy')">
            <svg viewBox="0 0 18 18" fill="none"><polygon points="9,5 14,14 4,14" fill="#1D9E75"/><text x="6.5" y="13" font-size="5" fill="white" font-weight="bold">B</text></svg>
          </button>

          <!-- Sell marker -->
          <button class="dt-btn dt-btn--sell" title="Sell Marker" @click="addMarker('sell')">
            <svg viewBox="0 0 18 18" fill="none"><polygon points="9,13 14,4 4,4" fill="#E24B4A"/><text x="6.5" y="11" font-size="5" fill="white" font-weight="bold">S</text></svg>
          </button>

          <div class="dt-sep"></div>

          <!-- Undo -->
          <button class="dt-btn" title="Undo (Ctrl+Z)" @click="undoDrawing">
            <svg viewBox="0 0 18 18" fill="none"><path d="M3 9a6 6 0 1 1 1.5 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><polyline points="3,5 3,9 7,9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>

          <!-- Clear all -->
          <button class="dt-btn dt-btn--danger" title="Clear All Drawings" @click="clearDrawings">
            <svg viewBox="0 0 18 18" fill="none"><polyline points="3,5 15,5" stroke="currentColor" stroke-width="1.3"/><path d="M7 5V3h4v2" stroke="currentColor" stroke-width="1.3"/><rect x="4" y="5" width="10" height="10" rx="1" stroke="currentColor" stroke-width="1.3"/><line x1="7" y1="8" x2="7" y2="12" stroke="currentColor" stroke-width="1.2"/><line x1="11" y1="8" x2="11" y2="12" stroke="currentColor" stroke-width="1.2"/></svg>
          </button>

        </div>

        <!-- Chart container -->
        <div class="chart-wrap">
          <div ref="chartContainer" class="chart-container"></div>

          <!-- Drawing overlay SVG -->
          <svg ref="drawingLayer" class="drawing-layer"
            :style="{ pointerEvents: activeTool !== 'cursor' ? 'all' : 'none', cursor: svgCursor }"
            @mousedown="onMouseDown"
            @mousemove="onMouseMove"
            @mouseup="onMouseUp"
            @dblclick="onDblClick">

            <!-- Completed drawings -->
            <g v-for="(d, i) in drawings" :key="'d'+i">
              <!-- Trendline / ray / extline -->
              <line v-if="['trendline','ray','extline'].includes(d.type)"
                :x1="d.x1" :y1="d.y1" :x2="d.x2" :y2="d.y2"
                stroke="#1D9E75" stroke-width="1.5"/>
              <!-- Horizontal line (spans full width) -->
              <line v-if="d.type==='hline'" :x1="0" :y1="d.y" :x2="chartWidth" :y2="d.y" stroke="#EF9F27" stroke-width="1" stroke-dasharray="4 3"/>
              <!-- Vertical line (spans full height) -->
              <line v-if="d.type==='vline'" :x1="d.x" :y1="0" :x2="d.x" :y2="chartHeight" stroke="#AFA9EC" stroke-width="1" stroke-dasharray="4 3"/>
              <!-- Cross -->
              <g v-if="d.type==='cross'">
                <line :x1="0" :y1="d.y" :x2="chartWidth" :y2="d.y" stroke="rgba(255,255,255,0.4)" stroke-width="1" stroke-dasharray="3 3"/>
                <line :x1="d.x" :y1="0" :x2="d.x" :y2="chartHeight" stroke="rgba(255,255,255,0.4)" stroke-width="1" stroke-dasharray="3 3"/>
              </g>
              <!-- Rectangle -->
              <rect v-if="d.type==='rect'"
                :x="Math.min(d.x1,d.x2)" :y="Math.min(d.y1,d.y2)"
                :width="Math.abs(d.x2-d.x1)" :height="Math.abs(d.y2-d.y1)"
                fill="rgba(29,158,117,0.07)" stroke="#1D9E75" stroke-width="1"/>
              <!-- Ellipse -->
              <ellipse v-if="d.type==='ellipse'"
                :cx="(d.x1+d.x2)/2" :cy="(d.y1+d.y2)/2"
                :rx="Math.abs(d.x2-d.x1)/2" :ry="Math.abs(d.y2-d.y1)/2"
                fill="rgba(29,158,117,0.07)" stroke="#1D9E75" stroke-width="1"/>
              <!-- Triangle -->
              <polygon v-if="d.type==='triangle'"
                :points="`${(d.x1+d.x2)/2},${d.y1} ${d.x2},${d.y2} ${d.x1},${d.y2}`"
                fill="rgba(29,158,117,0.07)" stroke="#1D9E75" stroke-width="1"/>
              <!-- Parallel channel -->
              <g v-if="d.type==='channel'">
                <line :x1="d.x1" :y1="d.y1" :x2="d.x2" :y2="d.y2" stroke="#1D9E75" stroke-width="1.3"/>
                <line :x1="d.x1" :y1="d.y1+d.gap" :x2="d.x2" :y2="d.y2+d.gap" stroke="#1D9E75" stroke-width="1.3" stroke-dasharray="4 2"/>
              </g>
              <!-- Fibonacci retracement -->
              <g v-if="d.type==='fib'">
                <line v-for="(level, li) in fibLevels" :key="li"
                  :x1="Math.min(d.x1,d.x2)" :y1="d.y1 + (d.y2-d.y1)*level.pct"
                  :x2="Math.max(d.x1,d.x2)" :y2="d.y1 + (d.y2-d.y1)*level.pct"
                  :stroke="level.color" stroke-width="1"/>
                <text v-for="(level, li) in fibLevels" :key="'ft'+li"
                  :x="Math.min(d.x1,d.x2)+4" :y="d.y1 + (d.y2-d.y1)*level.pct - 3"
                  :fill="level.color" font-size="10" font-family="monospace">{{ level.label }}</text>
              </g>
              <!-- Text label -->
              <text v-if="d.type==='text'"
                :x="d.x" :y="d.y" fill="#E2E8F0" font-size="13" font-family="monospace">{{ d.text }}</text>
              <!-- Price label -->
              <g v-if="d.type==='pricelabel'">
                <rect :x="d.x" :y="d.y-14" width="60" height="18" rx="3" fill="#1A2633" stroke="#EF9F27" stroke-width="1"/>
                <text :x="d.x+4" :y="d.y+1" fill="#EF9F27" font-size="11" font-family="monospace">{{ d.price }}</text>
              </g>
              <!-- Long position box -->
              <g v-if="d.type==='long'">
                <rect :x="d.x" :y="d.y" width="80" height="40" rx="3" fill="rgba(29,158,117,0.15)" stroke="#1D9E75" stroke-width="1"/>
                <text :x="d.x+8" :y="d.y+16" fill="#1D9E75" font-size="11" font-weight="bold" font-family="monospace">LONG</text>
                <text :x="d.x+8" :y="d.y+32" fill="#94A3B8" font-size="10" font-family="monospace">{{ d.price }}</text>
              </g>
              <!-- Short position box -->
              <g v-if="d.type==='short'">
                <rect :x="d.x" :y="d.y" width="80" height="40" rx="3" fill="rgba(226,75,74,0.15)" stroke="#E24B4A" stroke-width="1"/>
                <text :x="d.x+8" :y="d.y+16" fill="#E24B4A" font-size="11" font-weight="bold" font-family="monospace">SHORT</text>
                <text :x="d.x+8" :y="d.y+32" fill="#94A3B8" font-size="10" font-family="monospace">{{ d.price }}</text>
              </g>
              <!-- Pitchfork -->
              <g v-if="d.type==='pitchfork'">
                <line :x1="d.x1" :y1="d.y1" :x2="(d.x2+d.x3)/2" :y2="(d.y2+d.y3)/2" stroke="#85B7EB" stroke-width="1.3"/>
                <line :x1="(d.x2+d.x3)/2" :y1="(d.y2+d.y3)/2" :x2="d.x2" :y2="d.y2" stroke="#85B7EB" stroke-width="1.3"/>
                <line :x1="(d.x2+d.x3)/2" :y1="(d.y2+d.y3)/2" :x2="d.x3" :y2="d.y3" stroke="#85B7EB" stroke-width="1.3"/>
              </g>
            </g>

            <!-- In-progress drawing preview -->
            <g v-if="drawing">
              <line v-if="['trendline','ray','extline'].includes(drawing.type)"
                :x1="drawing.x1" :y1="drawing.y1" :x2="drawing.x2" :y2="drawing.y2"
                stroke="#1D9E75" stroke-width="1.5" stroke-dasharray="5 3"/>
              <line v-if="drawing.type==='hline'" :x1="0" :y1="drawing.y" :x2="chartWidth" :y2="drawing.y" stroke="#EF9F27" stroke-width="1" stroke-dasharray="4 3"/>
              <line v-if="drawing.type==='vline'" :x1="drawing.x" :y1="0" :x2="drawing.x" :y2="chartHeight" stroke="#AFA9EC" stroke-width="1" stroke-dasharray="4 3"/>
              <rect v-if="drawing.type==='rect'"
                :x="Math.min(drawing.x1,drawing.x2)" :y="Math.min(drawing.y1,drawing.y2)"
                :width="Math.abs(drawing.x2-drawing.x1)" :height="Math.abs(drawing.y2-drawing.y1)"
                fill="rgba(29,158,117,0.07)" stroke="#1D9E75" stroke-width="1" stroke-dasharray="5 3"/>
              <ellipse v-if="drawing.type==='ellipse'"
                :cx="(drawing.x1+drawing.x2)/2" :cy="(drawing.y1+drawing.y2)/2"
                :rx="Math.abs(drawing.x2-drawing.x1)/2" :ry="Math.abs(drawing.y2-drawing.y1)/2"
                fill="rgba(29,158,117,0.07)" stroke="#1D9E75" stroke-width="1" stroke-dasharray="5 3"/>
              <polygon v-if="drawing.type==='triangle'"
                :points="`${(drawing.x1+drawing.x2)/2},${drawing.y1} ${drawing.x2},${drawing.y2} ${drawing.x1},${drawing.y2}`"
                fill="rgba(29,158,117,0.07)" stroke="#1D9E75" stroke-width="1" stroke-dasharray="5 3"/>
              <g v-if="drawing.type==='fib'">
                <line v-for="(level, li) in fibLevels" :key="li"
                  :x1="Math.min(drawing.x1,drawing.x2)" :y1="drawing.y1 + (drawing.y2-drawing.y1)*level.pct"
                  :x2="Math.max(drawing.x1,drawing.x2)" :y2="drawing.y1 + (drawing.y2-drawing.y1)*level.pct"
                  :stroke="level.color" stroke-width="1" stroke-dasharray="3 2"/>
              </g>
            </g>

            <!-- Text input overlay -->
            <foreignObject v-if="textInput.visible" :x="textInput.x" :y="textInput.y-20" width="200" height="30">
              <input xmlns="http://www.w3.org/1999/xhtml"
                ref="textInputEl"
                v-model="textInput.value"
                class="svg-text-input"
                placeholder="Type label…"
                @keydown.enter="commitText"
                @keydown.esc="textInput.visible=false"
                @blur="commitText" />
            </foreignObject>

          </svg>
        </div>

      </div>

      <!-- Loading overlay -->
      <div v-if="loading" class="replay-loading">
        <div class="replay-loading__spinner"></div>
        <div class="replay-loading__text">Loading {{ selectedPair }} {{ selectedTF }}…</div>
      </div>

      <!-- Empty state -->
      <div v-if="!loading && allCandles.length === 0" class="replay-empty">
        <div class="replay-empty__icon">📉</div>
        <div class="replay-empty__title">No candle data for {{ selectedPair }} {{ selectedTF }}</div>
        <div class="replay-empty__sub">Upload a Dukascopy CSV file to get started.</div>
        <router-link to="/replay/import" class="replay-empty__btn">📥 Import Data</router-link>
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
      pairs:        ['EURUSD','GBPUSD','AUDUSD'],
      timeframes:   ['M1','M5','M15','M30','H1','H4','D1'],
      selectedPair: 'EURUSD',
      selectedTF:   'H1',

      allCandles:   [],
      currentIndex: 0,
      loading:      false,

      playing:   false,
      speed:     500,
      playTimer: null,
      startDate: '',

      chart:        null,
      candleSeries: null,
      volumeSeries: null,
      markers:      [],

      chartWidth:  0,
      chartHeight: 0,

      // All drawing tool IDs matching the toolbar
      activeTool: 'cursor',
      drawings:   [],
      drawing:    null,
      isDrawing:  false,
      // Pitchfork needs 3 clicks
      pitchClicks: [],

      drawingsByPair: {},

      // Text input overlay
      textInput: { visible: false, x: 0, y: 0, value: '' },

      // Fibonacci levels
      fibLevels: [
        { pct: 0,    label: '1.000', color: '#E2E8F0' },
        { pct: 0.236,label: '0.764', color: '#85B7EB' },
        { pct: 0.382,label: '0.618', color: '#EF9F27' },
        { pct: 0.5,  label: '0.500', color: '#AFA9EC' },
        { pct: 0.618,label: '0.382', color: '#EF9F27' },
        { pct: 0.764,label: '0.236', color: '#85B7EB' },
        { pct: 1,    label: '0.000', color: '#E2E8F0' },
      ],
    }
  },

  computed: {
    currentCandle() { return this.allCandles[this.currentIndex] || null },
    priceColor() {
      if (!this.currentCandle) return ''
      return this.currentCandle.close >= this.currentCandle.open ? 'text-win' : 'text-loss'
    },
    progressPct() {
      if (!this.allCandles.length) return 0
      return ((this.currentIndex + 1) / this.allCandles.length) * 100
    },
    visibleCandles() { return this.allCandles.slice(0, this.currentIndex + 1) },
    svgCursor() {
      const map = {
        cursor: 'default', trendline: 'crosshair', ray: 'crosshair',
        extline: 'crosshair', hline: 'ns-resize', vline: 'ew-resize',
        cross: 'crosshair', channel: 'crosshair', rect: 'crosshair',
        ellipse: 'crosshair', triangle: 'crosshair', fib: 'crosshair',
        pitchfork: 'crosshair', text: 'text', pricelabel: 'crosshair',
        long: 'crosshair', short: 'crosshair',
      }
      return map[this.activeTool] || 'crosshair'
    },
  },

  async mounted() {
    await this.loadChartLibrary()
    await this.initChart()
    await this.loadCandles()
    window.addEventListener('resize', this.onResize)
    window.addEventListener('keydown', this.onKeyDown)
  },

  beforeDestroy() {
    this.stopPlay()
    if (this.chart) this.chart.remove()
    window.removeEventListener('resize', this.onResize)
    window.removeEventListener('keydown', this.onKeyDown)
  },

  methods: {

    loadChartLibrary() {
      return new Promise((resolve) => {
        if (window.LightweightCharts) { resolve(); return }
        const s = document.createElement('script')
        s.src = 'https://unpkg.com/lightweight-charts/dist/lightweight-charts.standalone.production.js'
        s.onload = resolve
        document.head.appendChild(s)
      })
    },

    initChart() {
      const el = this.$refs.chartContainer
      if (!el || !window.LightweightCharts) return
      const w = el.clientWidth || 900
      const h = el.clientHeight || 500

      this.chart = LightweightCharts.createChart(el, {
        width: w, height: h,
        layout: { background: { color: '#0F1923' }, textColor: '#94A3B8' },
        grid: { vertLines: { color: 'rgba(255,255,255,0.04)' }, horzLines: { color: 'rgba(255,255,255,0.04)' } },
        crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
        rightPriceScale: { borderColor: 'rgba(255,255,255,0.08)' },
        timeScale: { borderColor: 'rgba(255,255,255,0.08)', timeVisible: true, secondsVisible: false },
      })

      this.candleSeries = this.chart.addCandlestickSeries({
        upColor: '#1D9E75', downColor: '#E24B4A',
        borderUpColor: '#1D9E75', borderDownColor: '#E24B4A',
        wickUpColor: '#1D9E75', wickDownColor: '#E24B4A',
      })

      this.volumeSeries = this.chart.addHistogramSeries({
        color: '#1D9E75', priceFormat: { type: 'volume' },
        priceScaleId: 'volume', scaleMargins: { top: 0.82, bottom: 0 },
      })

      this.chartWidth  = w
      this.chartHeight = h
    },

    async loadCandles() {
      this.loading = true; this.stopPlay()
      try {
        const p = { pair: this.selectedPair, timeframe: this.selectedTF, limit: 5000 }
        if (this.startDate) p.from = this.startDate
        const { data } = await this.$http.get('/candles', { params: p })
        this.allCandles   = data
        this.currentIndex = Math.max(0, data.length - 1)
        this.updateChart()
      } catch(e) { this.allCandles = [] }
      finally { this.loading = false }
    },

    updateChart() {
      if (!this.candleSeries || !this.visibleCandles.length) return
      this.candleSeries.setData(this.visibleCandles.map(c => ({ time:c.time, open:c.open, high:c.high, low:c.low, close:c.close })))
      this.volumeSeries.setData(this.visibleCandles.map(c => ({ time:c.time, value:c.volume, color: c.close>=c.open ? 'rgba(29,158,117,0.4)' : 'rgba(226,75,74,0.4)' })))
      this.candleSeries.setMarkers(this.markers)
      this.chart.timeScale().scrollToPosition(2, false)
    },

    togglePlay() { this.playing ? this.stopPlay() : this.startPlay() },
    startPlay() {
      if (this.currentIndex >= this.allCandles.length - 1) this.currentIndex = 0
      this.playing = true
      this.playTimer = setInterval(() => {
        if (this.currentIndex < this.allCandles.length - 1) { this.currentIndex++; this.updateChart() }
        else this.stopPlay()
      }, this.speed)
    },
    stopPlay() {
      this.playing = false
      if (this.playTimer) { clearInterval(this.playTimer); this.playTimer = null }
    },
    stepForward() { this.stopPlay(); if (this.currentIndex < this.allCandles.length - 1) { this.currentIndex++; this.updateChart() } },
    stepBack()    { this.stopPlay(); if (this.currentIndex > 0) { this.currentIndex--; this.updateChart() } },

    switchPair(pair) {
      this.drawingsByPair[this.selectedPair+'_'+this.selectedTF] = [...this.drawings]
      this.selectedPair = pair; this.restoreDrawings(); this.loadCandles()
    },
    switchTF(tf) {
      this.drawingsByPair[this.selectedPair+'_'+this.selectedTF] = [...this.drawings]
      this.selectedTF = tf; this.restoreDrawings(); this.loadCandles()
    },
    restoreDrawings() {
      const key = this.selectedPair+'_'+this.selectedTF
      this.drawings = this.drawingsByPair[key] ? [...this.drawingsByPair[key]] : []
    },

    jumpToDate() {
      if (!this.startDate || !this.allCandles.length) return
      const target = new Date(this.startDate).getTime() / 1000
      const idx = this.allCandles.findIndex(c => c.time >= target)
      if (idx >= 0) { this.currentIndex = idx; this.updateChart() }
      else this.loadCandles()
    },

    addMarker(side) {
      if (!this.currentCandle) return
      const m = {
        time: this.currentCandle.time,
        position: side==='buy' ? 'belowBar' : 'aboveBar',
        color:    side==='buy' ? '#1D9E75'  : '#E24B4A',
        shape:    side==='buy' ? 'arrowUp'  : 'arrowDown',
        text:     side==='buy' ? 'BUY'      : 'SELL',
        size: 1.5,
      }
      this.markers = this.markers.filter(x => x.time !== m.time)
      this.markers.push(m)
      this.markers.sort((a,b) => a.time - b.time)
      this.candleSeries.setMarkers(this.markers)
    },

    selectTool(id) {
      this.activeTool  = id
      this.pitchClicks = []
      this.drawing     = null
      this.isDrawing   = false
    },

    getPos(e) {
      const r = this.$refs.drawingLayer.getBoundingClientRect()
      return { x: e.clientX - r.left, y: e.clientY - r.top }
    },

    onMouseDown(e) {
      if (this.activeTool === 'cursor') return
      const pos = this.getPos(e)

      // Single-click instant tools
      if (this.activeTool === 'hline') {
        this.drawings.push({ type: 'hline', y: pos.y }); return
      }
      if (this.activeTool === 'vline') {
        this.drawings.push({ type: 'vline', x: pos.x }); return
      }
      if (this.activeTool === 'cross') {
        this.drawings.push({ type: 'cross', x: pos.x, y: pos.y }); return
      }
      if (this.activeTool === 'text') {
        this.textInput = { visible: true, x: pos.x, y: pos.y, value: '' }
        this.$nextTick(() => this.$refs.textInputEl?.focus())
        return
      }
      if (this.activeTool === 'pricelabel') {
        const price = this.currentCandle ? this.fmt(this.currentCandle.close) : '0.00000'
        this.drawings.push({ type: 'pricelabel', x: pos.x, y: pos.y, price }); return
      }
      if (this.activeTool === 'long') {
        const price = this.currentCandle ? this.fmt(this.currentCandle.close) : '0.00000'
        this.drawings.push({ type: 'long', x: pos.x, y: pos.y, price }); return
      }
      if (this.activeTool === 'short') {
        const price = this.currentCandle ? this.fmt(this.currentCandle.close) : '0.00000'
        this.drawings.push({ type: 'short', x: pos.x, y: pos.y, price }); return
      }

      // Pitchfork — needs 3 clicks
      if (this.activeTool === 'pitchfork') {
        this.pitchClicks.push({ x: pos.x, y: pos.y })
        if (this.pitchClicks.length === 3) {
          const [p1,p2,p3] = this.pitchClicks
          this.drawings.push({ type:'pitchfork', x1:p1.x, y1:p1.y, x2:p2.x, y2:p2.y, x3:p3.x, y3:p3.y })
          this.pitchClicks = []
        }
        return
      }

      // Drag tools
      this.isDrawing = true
      const type = this.activeTool
      if (['trendline','ray','extline','channel','fib'].includes(type)) {
        this.drawing = { type, x1:pos.x, y1:pos.y, x2:pos.x, y2:pos.y, gap: 40 }
      } else if (['rect','ellipse','triangle'].includes(type)) {
        this.drawing = { type, x1:pos.x, y1:pos.y, x2:pos.x, y2:pos.y }
      }
    },

    onMouseMove(e) {
      if (!this.isDrawing || !this.drawing) return
      const pos = this.getPos(e)
      this.drawing = { ...this.drawing, x2: pos.x, y2: pos.y }
    },

    onMouseUp(e) {
      if (!this.isDrawing || !this.drawing) return
      const pos = this.getPos(e)
      this.drawing = { ...this.drawing, x2: pos.x, y2: pos.y }
      const dx = Math.abs(this.drawing.x2 - this.drawing.x1)
      const dy = Math.abs(this.drawing.y2 - this.drawing.y1)
      if (dx > 3 || dy > 3) this.drawings.push({ ...this.drawing })
      this.drawing = null; this.isDrawing = false
    },

    onDblClick(e) {
      // Double click clears pitchfork in-progress
      if (this.activeTool === 'pitchfork') this.pitchClicks = []
    },

    commitText() {
      if (this.textInput.value.trim()) {
        this.drawings.push({ type:'text', x:this.textInput.x, y:this.textInput.y, text:this.textInput.value.trim() })
      }
      this.textInput.visible = false
    },

    undoDrawing()  { this.drawings.pop() },
    clearDrawings(){ this.drawings = []; this.markers = []; if (this.candleSeries) this.candleSeries.setMarkers([]) },

    onKeyDown(e) {
      if (e.key === 'Escape') this.selectTool('cursor')
      if (e.key === 'ArrowRight' && !e.target.closest('input,select')) { e.preventDefault(); this.stepForward() }
      if (e.key === 'ArrowLeft'  && !e.target.closest('input,select')) { e.preventDefault(); this.stepBack() }
      if (e.key === ' ' && !e.target.closest('input,select'))           { e.preventDefault(); this.togglePlay() }
      if ((e.ctrlKey||e.metaKey) && e.key === 'z') { e.preventDefault(); this.undoDrawing() }
    },

    onResize() {
      if (!this.chart || !this.$refs.chartContainer) return
      const w = this.$refs.chartContainer.clientWidth
      const h = this.$refs.chartContainer.clientHeight
      this.chart.resize(w, h)
      this.chartWidth = w; this.chartHeight = h
    },

    fmt(v) { return v ? v.toFixed(5) : '—' },
    formatDate(ts) {
      if (!ts) return ''
      return new Date(ts*1000).toISOString().replace('T',' ').slice(0,16)
    },
  },

  watch: {
    speed() { if (this.playing) { this.stopPlay(); this.startPlay() } },
  },
}
</script>

<style scoped>
.replay-page {
  display: flex; flex-direction: column;
  height: calc(100vh - 64px);
  background: #080D13;
  position: relative; overflow: hidden;
}

/* ── TOP TOOLBAR ── */
.replay-toolbar {
  display: flex; align-items: center; gap: 20px;
  padding: 8px 14px;
  background: #0F1923;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  flex-shrink: 0; flex-wrap: wrap;
}
.tb-group         { display:flex; flex-direction:column; gap:3px; }
.tb-group--info   { margin-left:auto; align-items:flex-end; }
.tb-label         { font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#4A5568; font-weight:600; }
.tb-pills         { display:flex; gap:2px; }
.tb-pill          { padding:3px 9px; border-radius:5px; font-size:11px; font-weight:700; background:#1A2633; border:1px solid rgba(255,255,255,0.07); color:#64748B; cursor:pointer; transition:all .15s; font-family:monospace; }
.tb-pill:hover    { border-color:rgba(29,158,117,.4); color:#94A3B8; }
.tb-pill.active   { background:rgba(29,158,117,.15); border-color:#1D9E75; color:#1D9E75; }
.tb-input         { background:#1A2633; border:1px solid rgba(255,255,255,.08); border-radius:5px; padding:4px 7px; color:#E2E8F0; font-size:12px; outline:none; }
.tb-input:focus   { border-color:#1D9E75; }
.tb-controls      { display:flex; gap:3px; align-items:center; }
.tb-btn           { width:30px; height:30px; border-radius:5px; background:#1A2633; border:1px solid rgba(255,255,255,.08); color:#94A3B8; cursor:pointer; font-size:13px; display:flex; align-items:center; justify-content:center; transition:all .15s; }
.tb-btn:hover     { border-color:rgba(29,158,117,.4); color:#fff; }
.tb-btn--play     { width:34px; height:34px; font-size:15px; }
.tb-btn--play.playing { background:rgba(226,75,74,.15); border-color:#E24B4A; color:#E24B4A; }
.tb-select        { background:#1A2633; border:1px solid rgba(255,255,255,.08); border-radius:5px; padding:3px 5px; color:#94A3B8; font-size:11px; outline:none; }
.tb-candle-info   { display:flex; gap:10px; align-items:center; margin-bottom:3px; }
.tb-candle-date   { font-size:11px; color:#64748B; font-family:monospace; }
.tb-ohlc          { font-size:11px; font-family:monospace; display:flex; gap:6px; }
.tb-ohlc span     { color:#94A3B8; }
.tb-progress      { display:flex; gap:6px; align-items:center; }
.tb-progress-text { font-size:10px; color:#4A5568; white-space:nowrap; }
.tb-progress-bar  { width:100px; height:3px; background:rgba(255,255,255,.08); border-radius:2px; overflow:hidden; }
.tb-progress-fill { height:100%; background:#1D9E75; border-radius:2px; transition:width .1s; }
.text-win  { color:#1D9E75; }
.text-loss { color:#E24B4A; }

/* ── MAIN ── */
.replay-main {
  display: flex; flex: 1; min-height: 0;
}

/* ── VERTICAL DRAWING SIDEBAR ── */
.draw-sidebar {
  width: 40px;
  background: #0F1923;
  border-right: 1px solid rgba(255,255,255,0.06);
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 6px 0;
  gap: 1px;
  overflow-y: auto;
  flex-shrink: 0;
}
.draw-sidebar::-webkit-scrollbar { width: 3px; }
.draw-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }

.dt-btn {
  width: 30px; height: 30px;
  border-radius: 5px;
  background: transparent;
  border: 1px solid transparent;
  color: #64748B;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all .15s;
  padding: 4px;
  flex-shrink: 0;
}
.dt-btn svg      { width: 18px; height: 18px; }
.dt-btn:hover    { background: rgba(255,255,255,.05); color: #94A3B8; border-color: rgba(255,255,255,.08); }
.dt-btn.active   { background: rgba(29,158,117,.15); color: #1D9E75; border-color: rgba(29,158,117,.4); }
.dt-btn--buy     { color: rgba(29,158,117,.7); }
.dt-btn--buy:hover  { color: #1D9E75; background: rgba(29,158,117,.1); }
.dt-btn--sell    { color: rgba(226,75,74,.7); }
.dt-btn--sell:hover { color: #E24B4A; background: rgba(226,75,74,.1); }
.dt-btn--danger:hover { color: #E24B4A; background: rgba(226,75,74,.1); }
.dt-sep {
  width: 20px; height: 1px;
  background: rgba(255,255,255,.07);
  margin: 3px 0; flex-shrink: 0;
}

/* ── CHART ── */
.chart-wrap {
  flex: 1; min-width: 0; min-height: 0;
  position: relative;
}
.chart-container {
  width: 100%; height: 100%;
}
.drawing-layer {
  position: absolute; inset: 0;
  width: 100%; height: 100%;
  pointer-events: none;
  z-index: 10;
}

/* ── Text input in SVG ── */
:global(.svg-text-input) {
  background: #1A2633 !important;
  border: 1px solid #1D9E75 !important;
  color: #E2E8F0 !important;
  font-size: 13px !important;
  padding: 2px 6px !important;
  border-radius: 4px !important;
  outline: none !important;
  width: 100% !important;
  font-family: monospace !important;
}

/* ── STATES ── */
.replay-loading {
  position:absolute; inset:0;
  background:rgba(8,13,19,.85);
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px; z-index:50;
}
.replay-loading__spinner {
  width:32px; height:32px;
  border:3px solid rgba(29,158,117,.2); border-top-color:#1D9E75;
  border-radius:50%; animation:spin .8s linear infinite;
}
@keyframes spin { to { transform:rotate(360deg); } }
.replay-loading__text { font-size:13px; color:#64748B; }

.replay-empty {
  position:absolute; inset:0;
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; z-index:40;
}
.replay-empty__icon  { font-size:38px; }
.replay-empty__title { font-size:17px; font-weight:700; color:#fff; }
.replay-empty__sub   { font-size:13px; color:#4A5568; text-align:center; line-height:1.7; }
.replay-empty__btn {
  margin-top:8px; padding:10px 24px;
  background:linear-gradient(135deg,#1D9E75,#0F6E56); color:#fff;
  border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; transition:opacity .2s;
}
.replay-empty__btn:hover { opacity:.85; }
</style>