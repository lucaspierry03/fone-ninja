<script setup>
defineProps({ columns: Array, items: Array })
</script>

<template>
  <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-700 bg-gray-800/80">
            <th v-for="col in columns" :key="col.key" class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody>
          <slot />
          <tr v-if="items && items.length === 0">
            <td :colspan="columns.length" class="px-4 py-8 text-center text-gray-500">Nenhum registro encontrado.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
