<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../services/api.js'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import FormCard from '../components/FormCard.vue'
import DataTable from '../components/DataTable.vue'
import Alert from '../components/Alert.vue'
import Pagination from '../components/Pagination.vue'

const produtos = ref([])
const compras = ref([])
const expanded = ref({})
const loading = ref(false)
const alert = ref(null)

const emptyLine = () => ({ produto_id: '', quantidade: 1, preco_unitario: '' })
const form = ref({ fornecedor: '', itens: [emptyLine()] })

const total = computed(() =>
  form.value.itens.reduce((s, i) => s + (Number(i.preco_unitario) || 0) * (Number(i.quantidade) || 0), 0)
)

const columns = [
  { label: 'ID', key: 'id' },
  { label: 'Fornecedor', key: 'fornecedor' },
  { label: 'Total (R$)', key: 'total' },
  { label: 'Data', key: 'created_at' },
]

import { brl } from '../utils/format.js'

function addLine() { form.value.itens.push(emptyLine()) }
function removeLine(i) { form.value.itens.splice(i, 1) }
function toggle(id) { expanded.value[id] = !expanded.value[id] }

const comprasMeta = ref(null)

async function loadAll(comprasUrl = '/compras') {
  const [p, c] = await Promise.all([api.get('/produtos/all'), api.get(comprasUrl)])
  produtos.value = p.data
  compras.value = c.data.data
  comprasMeta.value = c.data.meta
}

function onPage(url) {
  const u = new URL(url)
  loadAll(u.pathname.replace('/api', '') + u.search)
}

async function submit() {
  loading.value = true
  try {
    await api.post('/compras', {
      fornecedor: form.value.fornecedor,
      produtos: form.value.itens.map(i => ({ id: Number(i.produto_id), quantidade: Number(i.quantidade), preco_unitario: Number(i.preco_unitario) })),
    })
    form.value = { fornecedor: '', itens: [emptyLine()] }
    alert.value = { type: 'success', message: 'Compra registrada com sucesso!' }
    await loadAll()
  } catch (e) {
    alert.value = { type: 'error', message: e.response?.data?.message || 'Erro ao registrar compra.' }
  } finally {
    loading.value = false
  }
}

onMounted(loadAll)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Compras</h1>
    <Alert v-if="alert" :type="alert.type" :message="alert.message" @dismiss="alert = null" />

    <FormCard title="Nova Compra">
      <form @submit.prevent="submit" class="space-y-4">
        <div class="w-full max-w-md">
          <label class="block text-xs text-gray-400 mb-1">Fornecedor</label>
          <input v-model="form.fornecedor" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>

        <div v-for="(item, idx) in form.itens" :key="idx" class="flex flex-wrap gap-3 items-end">
          <div class="flex-1 min-w-[180px]">
            <label class="block text-xs text-gray-400 mb-1">Produto</label>
            <select v-model="item.produto_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="" disabled>Selecione</option>
              <option v-for="p in produtos" :key="p.id" :value="p.id">{{ p.nome }}</option>
            </select>
          </div>
          <div class="w-28">
            <label class="block text-xs text-gray-400 mb-1">Qtd</label>
            <input v-model.number="item.quantidade" type="number" min="1" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div class="w-36">
            <label class="block text-xs text-gray-400 mb-1">Preço Unit. (R$)</label>
            <input v-model="item.preco_unitario" type="number" step="0.01" min="0.01" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <button type="button" @click="removeLine(idx)" v-if="form.itens.length > 1" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-400 hover:bg-red-500/20 transition-colors">
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>

        <div class="flex items-center justify-between pt-2">
          <button type="button" @click="addLine" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">+ Adicionar produto</button>
          <span class="text-sm text-gray-400">Total: <strong class="text-white">R$ {{ brl(total) }}</strong></span>
        </div>

        <button type="submit" :disabled="loading" class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-5 py-2 rounded-lg text-sm font-medium transition-colors">
          {{ loading ? 'Salvando...' : 'Registrar Compra' }}
        </button>
      </form>
    </FormCard>

    <DataTable :columns="columns" :items="compras">
      <template v-for="c in compras" :key="c.id">
        <tr class="border-b border-gray-700/50 hover:bg-gray-700/30 cursor-pointer" @click="toggle(c.id)">
          <td class="px-4 py-3">{{ c.id }}</td>
          <td class="px-4 py-3">{{ c.fornecedor }}</td>
          <td class="px-4 py-3">R$ {{ brl(c.total) }}</td>
          <td class="px-4 py-3">{{ new Date(c.created_at).toLocaleDateString('pt-BR') }}</td>
        </tr>
        <tr v-if="expanded[c.id] && c.items?.length">
          <td :colspan="4" class="px-6 py-3 bg-gray-850">
            <div class="text-xs text-gray-400 space-y-1">
              <div v-for="it in c.items" :key="it.id" class="flex gap-4">
                <span>{{ it.produto?.nome || `Produto #${it.produto_id}` }}</span>
                <span>Qtd: {{ it.quantidade }}</span>
                <span>R$ {{ brl(it.preco_unitario) }}/un</span>
              </div>
            </div>
          </td>
        </tr>
      </template>
    </DataTable>

    <Pagination :meta="comprasMeta" @page="onPage" />
  </div>
</template>
