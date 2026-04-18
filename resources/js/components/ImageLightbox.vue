<template>
  <transition name="fade">
    <div v-if="visible"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/95"
      @click.self="close">

      <button @click="close"
        class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors z-10 text-lg">
        ✕
      </button>

      <!-- Single image mode -->
      <div v-if="!images || images.length === 0" class="relative max-w-5xl w-full mx-4">
        <div class="text-center mb-2 text-gray-400 text-sm font-medium">{{ timeframe }} Chart</div>
        <img :src="src" class="max-h-screen max-w-full mx-auto rounded-lg object-contain" style="max-height:80vh" />
      </div>

      <!-- Multi image mode - H4, M15, M1 side by side -->
      <div v-else class="w-full flex flex-col px-4 py-12" style="height:100vh">
        <div class="flex gap-3" style="height: calc(100vh - 80px)">
          <div v-for="img in orderedImages" :key="img.timeframe" class="flex-1 flex flex-col min-w-0">
            <div class="text-center mb-2 flex-shrink-0">
              <span class="text-xs font-bold px-3 py-1 rounded-full"
                :class="{
                  'bg-blue-900/60 text-blue-300':     img.timeframe === 'H4',
                  'bg-purple-900/60 text-purple-300': img.timeframe === 'M15',
                  'bg-yellow-900/60 text-yellow-300': img.timeframe === 'M1',
                }">
                {{ img.timeframe }}
              </span>
            </div>
            <div class="flex-1 rounded-xl overflow-hidden bg-gray-900 flex items-center justify-center min-h-0 transition-all"
              :class="startAt === img.timeframe ? 'border-2 border-win' : 'border border-gray-800'">
              <img v-if="img.path"
                :src="`/api/images/${img.path}`"
                class="w-full h-full object-contain"
              />
              <div v-else class="text-center text-gray-600 p-4">
                <div class="text-3xl mb-2">📷</div>
                <div class="text-xs">No {{ img.timeframe }} image</div>
              </div>
            </div>
          </div>
        </div>
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
    images:    { type: Array,   default: () => [] },
    startAt:   { type: String,  default: '' },
  },
  computed: {
    orderedImages() {
      const order = ['H4', 'M15', 'M1']
      const map   = {}
      this.images.forEach(img => { map[img.timeframe] = img })
      return order.map(tf => map[tf] || { timeframe: tf, path: null })
    },
  },
  mounted()       { window.addEventListener('keydown', this.onKey) },
  beforeDestroy() { window.removeEventListener('keydown', this.onKey) },
  methods: {
    close()  { this.$emit('close') },
    onKey(e) { if (e.key === 'Escape') this.close() },
  },
}
</script>