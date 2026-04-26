<template>
  <div class="grid grid-cols-3 gap-4">
    <div v-for="tf in timeframes" :key="tf" class="flex flex-col gap-2">
      <label class="label">{{ tfLabel(tf) }} Chart</label>
      <div
        class="relative border-2 border-dashed rounded-xl flex flex-col items-center justify-center cursor-pointer transition-colors h-32 overflow-hidden"
        :class="previews[tf] ? 'border-win/50' : 'border-border hover:border-win/50'"
        @click="triggerInput(tf)"
        @dragover.prevent
        @drop.prevent="onDrop($event, tf)"
      >
        <img v-if="previews[tf]" :src="previews[tf]" class="absolute inset-0 w-full h-full object-contain rounded-xl" />
        <div v-else class="text-center px-2">
          <div class="text-2xl mb-1">📷</div>
          <div class="text-xs text-gray-500">Click or drop</div>
        </div>
        <button v-if="previews[tf]" type="button"
          class="absolute top-1 right-1 w-6 h-6 bg-loss rounded-full text-white text-xs flex items-center justify-center hover:bg-loss-dark z-10"
          @click.stop="remove(tf)">✕</button>
        <input :ref="'input_'+tf" type="file" accept="image/*" class="hidden" @change="onFile($event, tf)" />
      </div>
      <div v-if="existing[tf]" class="text-xs text-gray-500 text-center">Existing image saved</div>
    </div>
  </div>
</template>

<script>
import { TF } from '@/timeframes.js'
export default {
  name: 'ImageUploader',
  props: {
    value:    { type: Object, default: () => ({}) },
    existing: { type: Object, default: () => ({}) },
  },
  data() {
    return {
      timeframes: ['H4', 'M15', 'M1'],
      previews:   { H4: null, M15: null, M1: null },
      files:      { H4: null, M15: null, M1: null },
    }
  },
  methods: {
    tfLabel(tf) {
      return tf === 'H4' ? TF.h4 : tf === 'M15' ? TF.m15 : TF.m1
    },
    triggerInput(tf) { this.$refs['input_'+tf][0].click() },
    onFile(e, tf)    { const f = e.target.files[0]; if (f) this.setFile(tf, f) },
    onDrop(e, tf)    { const f = e.dataTransfer.files[0]; if (f && f.type.startsWith('image/')) this.setFile(tf, f) },
    setFile(tf, f) {
      this.files[tf]    = f
      this.previews[tf] = URL.createObjectURL(f)
      this.$emit('input', { ...this.files })
    },
    remove(tf) {
      this.files[tf]    = null
      this.previews[tf] = null
      this.$emit('input', { ...this.files })
    },
  },
}
</script>