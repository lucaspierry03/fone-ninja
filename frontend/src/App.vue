<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { CubeIcon, TruckIcon, BanknotesIcon, ChartBarIcon } from '@heroicons/vue/24/outline'

const links = [
  { to: '/produtos', label: 'Produtos', icon: CubeIcon },
  { to: '/compras', label: 'Compras', icon: TruckIcon },
  { to: '/vendas', label: 'Vendas', icon: BanknotesIcon },
]
</script>

<template>
  <div class="flex h-screen bg-gray-900 text-gray-100">
    <aside class="w-56 bg-gray-800 flex flex-col border-r border-gray-700">
      <div class="flex items-center gap-2 p-5">
        <ChartBarIcon class="w-6 h-6 text-indigo-400" />
        <span class="text-xl font-bold text-indigo-400 tracking-wide">ERP Estoque</span>
      </div>
      <nav class="flex-1 px-3 space-y-1">
        <RouterLink
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-gray-700 hover:text-white"
          active-class="!bg-indigo-600 !text-white"
        >
          <component :is="link.icon" class="w-5 h-5" />
          <span>{{ link.label }}</span>
        </RouterLink>
      </nav>
    </aside>
    <main class="flex-1 overflow-y-auto p-6">
      <RouterView />
    </main>
  </div>
</template>
