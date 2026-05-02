<script setup>
defineProps({ title: String })
</script>

<template>
  <div class="bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-700">
    <h2 class="text-lg font-semibold text-indigo-400 mb-4">{{ title }}</h2>
    <slot />
  </div>
</template>
