<template>
  <transition name="fade">
    <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90" @click.self="close">
      <div class="relative max-w-5xl max-h-screen w-full mx-4">
        <button @click="close" class="absolute -top-10 right-0 text-white text-2xl hover:text-gray-300 z-10">✕</button>
        <div class="text-center mb-2 text-gray-400 text-sm">{{ timeframe }} Chart</div>
        <img :src="src" class="max-h-screen max-w-full mx-auto rounded-lg object-contain" style="max-height:80vh" />
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'ImageLightbox',
  props: {
    visible:   { type: Boolean, default: false },
    src:       { type: String,  default: '' },
    timeframe: { type: String,  default: '' },
  },
  mounted() {
    window.addEventListener('keydown', this.onKey)
  },
  beforeDestroy() {
    window.removeEventListener('keydown', this.onKey)
  },
  methods: {
    close() { this.$emit('close') },
    onKey(e) { if (e.key === 'Escape') this.close() },
  },
}
</script>
