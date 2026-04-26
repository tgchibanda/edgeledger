<template>
  <div class="captcha-wrap">
    <div class="captcha-label">
      <span class="captcha-icon">🛡️</span>
      Human verification
    </div>

    <!-- Challenge display -->
    <div class="captcha-challenge">
      <canvas ref="canvas" class="captcha-canvas" width="200" height="60"></canvas>
      <button type="button" class="captcha-refresh" @click="generate" title="New challenge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
      </button>
    </div>

    <!-- Answer input -->
    <div class="captcha-input-row">
      <input
        v-model="answer"
        type="text"
        class="captcha-input"
        :placeholder="placeholder"
        :class="{ error: showError, success: verified }"
        maxlength="6"
        autocomplete="off"
        @input="checkAnswer"
        @keydown.enter.prevent="checkAnswer"
      />
      <div v-if="verified" class="captcha-tick">✓</div>
    </div>

    <div v-if="showError" class="captcha-error">Incorrect — try the new challenge</div>
  </div>
</template>

<script>
export default {
  name: 'CaptchaChallenge',
  props: {
    value: { type: Boolean, default: false },
  },
  data() {
    return {
      type:       'math',    // rotates: math | word | grid
      challenge:  null,      // { question, answer }
      answer:     '',
      verified:   false,
      showError:  false,
      attempts:   0,
    }
  },
  computed: {
    placeholder() {
      if (!this.challenge) return ''
      if (this.challenge.type === 'math') return 'Enter the answer'
      if (this.challenge.type === 'word') return 'Type the letters'
      return 'Enter the answer'
    },
  },
  mounted() {
    this.generate()
  },
  methods: {
    generate() {
      this.answer    = ''
      this.showError = false
      this.verified  = false
      this.attempts  = 0
      this.$emit('input', false)

      // Rotate challenge types
      const types  = ['math', 'math', 'word', 'math', 'word']
      const picked = types[Math.floor(Math.random() * types.length)]
      this.challenge = this.buildChallenge(picked)

      this.$nextTick(() => this.drawCanvas())
    },

    buildChallenge(type) {
      if (type === 'math') {
        const ops = ['+', '-', '×']
        const op  = ops[Math.floor(Math.random() * ops.length)]
        let a, b, answer

        if (op === '+') {
          a = Math.floor(Math.random() * 40) + 5
          b = Math.floor(Math.random() * 40) + 5
          answer = a + b
        } else if (op === '-') {
          a = Math.floor(Math.random() * 30) + 15
          b = Math.floor(Math.random() * 14) + 1
          answer = a - b
        } else {
          a = Math.floor(Math.random() * 9) + 2
          b = Math.floor(Math.random() * 9) + 2
          answer = a * b
        }

        return { type: 'math', question: `${a} ${op} ${b} = ?`, answer: String(answer) }
      }

      if (type === 'word') {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'
        let word = ''
        for (let i = 0; i < 5; i++) {
          word += chars[Math.floor(Math.random() * chars.length)]
        }
        return { type: 'word', question: word, answer: word }
      }

      return this.buildChallenge('math')
    },

    drawCanvas() {
      const canvas = this.$refs.canvas
      if (!canvas || !this.challenge) return
      const ctx  = canvas.getContext('2d')
      const W    = canvas.width
      const H    = canvas.height

      // Dark background
      ctx.fillStyle = '#0D1420'
      ctx.fillRect(0, 0, W, H)

      // Noise lines
      for (let i = 0; i < 8; i++) {
        ctx.strokeStyle = `rgba(${this.rand(80,150)},${this.rand(80,150)},${this.rand(80,150)},${(Math.random()*0.3+0.1).toFixed(2)})`
        ctx.lineWidth   = Math.random() * 1.5 + 0.5
        ctx.beginPath()
        ctx.moveTo(Math.random()*W, Math.random()*H)
        ctx.bezierCurveTo(Math.random()*W, Math.random()*H, Math.random()*W, Math.random()*H, Math.random()*W, Math.random()*H)
        ctx.stroke()
      }

      // Noise dots
      for (let i = 0; i < 60; i++) {
        ctx.fillStyle = `rgba(${this.rand(100,200)},${this.rand(100,200)},${this.rand(100,200)},${(Math.random()*0.25+0.05).toFixed(2)})`
        ctx.beginPath()
        ctx.arc(Math.random()*W, Math.random()*H, Math.random()*2, 0, Math.PI*2)
        ctx.fill()
      }

      // Draw text character by character with slight rotation/position variation
      const text    = this.challenge.question
      const colors  = ['#D4A017','#1D9E75','#85B7EB','#E2E8F0','#AFA9EC','#5DCAA5']
      const charW   = Math.min((W - 20) / text.length, 22)
      const startX  = (W - charW * text.length) / 2 + charW * 0.4

      ctx.textBaseline = 'middle'
      ctx.textAlign    = 'center'

      for (let i = 0; i < text.length; i++) {
        const x      = startX + i * charW
        const y      = H / 2 + this.rand(-6, 6)
        const angle  = (Math.random() - 0.5) * 0.35
        const size   = this.rand(20, 28)
        const bold   = Math.random() > 0.4 ? '700' : '400'
        const family = Math.random() > 0.5 ? 'Syne' : 'DM Sans'
        const color  = colors[Math.floor(Math.random() * colors.length)]

        ctx.save()
        ctx.translate(x, y)
        ctx.rotate(angle)
        ctx.font      = `${bold} ${size}px ${family}, sans-serif`
        ctx.fillStyle = color

        // Slight shadow
        ctx.shadowColor   = 'rgba(0,0,0,0.5)'
        ctx.shadowBlur    = 3
        ctx.shadowOffsetX = 1
        ctx.shadowOffsetY = 1

        ctx.fillText(text[i], 0, 0)
        ctx.restore()
      }

      // Border
      ctx.strokeStyle = 'rgba(255,255,255,0.08)'
      ctx.lineWidth   = 1
      ctx.strokeRect(0.5, 0.5, W-1, H-1)
    },

    rand(min, max) {
      return Math.floor(Math.random() * (max - min + 1)) + min
    },

    checkAnswer() {
      if (!this.challenge || !this.answer.trim()) return

      const given    = this.answer.trim().toUpperCase()
      const expected = this.challenge.answer.toString().toUpperCase()

      if (given === expected) {
        this.verified  = true
        this.showError = false
        this.$emit('input', true)
      } else if (this.answer.length >= this.challenge.answer.length) {
        this.attempts++
        this.showError = true
        this.verified  = false
        this.$emit('input', false)

        // Auto-regenerate after 2 failed attempts
        if (this.attempts >= 2) {
          setTimeout(() => {
            this.generate()
          }, 800)
        }
      }
    },
  },
}
</script>

<style scoped>
.captcha-wrap {
  background: rgba(255,255,255,.02);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 10px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.captcha-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: .8px;
}
.captcha-icon { font-size: 13px; }

.captcha-challenge {
  display: flex;
  align-items: center;
  gap: 8px;
}
.captcha-canvas {
  border-radius: 8px;
  flex: 1;
  max-width: 200px;
  height: 60px;
  user-select: none;
  -webkit-user-select: none;
}
.captcha-refresh {
  width: 32px; height: 32px;
  border-radius: 8px;
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.08);
  color: #64748B;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all .15s;
}
.captcha-refresh:hover { background: rgba(255,255,255,.1); color: #fff; }

.captcha-input-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.captcha-input {
  flex: 1;
  background: #131C2E;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 8px;
  padding: 9px 12px;
  color: #E2E8F0;
  font-size: 15px;
  font-family: 'DM Sans', sans-serif;
  outline: none;
  transition: border-color .2s;
  letter-spacing: 2px;
}
.captcha-input:focus  { border-color: rgba(212,160,23,.4); }
.captcha-input.error  { border-color: rgba(226,75,74,.5); }
.captcha-input.success{ border-color: rgba(29,158,117,.6); }

.captcha-tick {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: rgba(29,158,117,.15);
  border: 1px solid rgba(29,158,117,.4);
  color: #1D9E75;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
  flex-shrink: 0;
}

.captcha-error {
  font-size: 11px;
  color: #E24B4A;
}

@media(max-width:420px) {
  .captcha-canvas { max-width: 160px; }
}
</style>