<script setup>
const props = defineProps({ meta: Object })
const emit = defineEmits(['page'])

function translateLabel(label) {
  return label
    .replace('&laquo; Previous', '« Anterior')
    .replace('Next &raquo;', 'Próximo »')
}
</script>

<template>
  <div v-if="meta && meta.last_page > 1" class="flex items-center justify-center gap-1 mt-4">
    <button
      v-for="link in meta.links"
      :key="link.label"
      @click="link.url && emit('page', link.url)"
      :disabled="!link.url"
      class="px-3 py-1 rounded text-sm transition-colors"
      :class="link.active ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600 disabled:opacity-30 disabled:cursor-not-allowed'"
      v-html="translateLabel(link.label)"
    />
  </div>
</template>
