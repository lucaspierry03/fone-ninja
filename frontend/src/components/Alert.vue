<script setup>
import { onMounted } from 'vue'

const props = defineProps({ type: String, message: String })
const emit = defineEmits(['dismiss'])

onMounted(() => {
  setTimeout(() => emit('dismiss'), 3000)
})
</script>

<template>
  <div
    class="px-4 py-3 rounded-lg text-sm font-medium mb-4"
    :class="type === 'success' ? 'bg-green-900/50 text-green-300 border border-green-700' : 'bg-red-900/50 text-red-300 border border-red-700'"
  >
    {{ message }}
  </div>
</template>
