<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../services/api.js'
import { XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import FormCard from '../components/FormCard.vue'
import DataTable from '../components/DataTable.vue'
import Alert from '../components/Alert.vue'
import Pagination from '../components/Pagination.vue'

const produtos = ref([])
const vendas = ref([])
const loading = ref(false)
const alert = ref(null)

const emptyLine = () => ({ produto_id: '', quantidade: 1, preco_unitario: '' })

function onProdutoSelect(item) {
  const p = produtos.value.find(p => p.id == item.produto_id)
  if (p) item.preco_unitario = p.preco_venda
}
const form = ref({ cliente: '', itens: [emptyLine()] })

const produtoMap = computed(() => Object.fromEntries(produtos.value.map(p => [p.id, p])))

const total = computed(() =>
  form.value.itens.reduce((s, i) => s + (Number(i.preco_unitario) || 0) * (Number(i.quantidade) || 0), 0)
)

const lucroEstimado = computed(() =>
  form.value.itens.reduce((s, i) => {
    const p = produtoMap.value[i.produto_id]
    if (!p) return s
    return s + ((Number(i.preco_unitario) || 0) - Number(p.custo_medio)) * (Number(i.quantidade) || 0)
  }, 0)
)

const columns = [
  { label: 'ID', key: 'id' },
  { label: 'Cliente', key: 'cliente' },
  { label: 'Total (R$)', key: 'total' },
  { label: 'Lucro (R$)', key: 'lucro' },
  { label: 'Status', key: 'status' },
  { label: 'Ações', key: 'actions' },
]

import { brl } from '../utils/format.js'

function addLine() { form.value.itens.push(emptyLine()) }
function removeLine(i) { form.value.itens.splice(i, 1) }

const vendasMeta = ref(null)

async function loadAll(vendasUrl = '/vendas') {
  const [p, v] = await Promise.all([api.get('/produtos/all'), api.get(vendasUrl)])
  produtos.value = p.data
  vendas.value = v.data.data
  vendasMeta.value = v.data.meta
}

function onPage(url) {
  const u = new URL(url)
  loadAll(u.pathname.replace('/api', '') + u.search)
}

async function submit() {
  loading.value = true
  try {
    await api.post('/vendas', {
      cliente: form.value.cliente,
      produtos: form.value.itens.map(i => ({ id: Number(i.produto_id), quantidade: Number(i.quantidade), preco_unitario: Number(i.preco_unitario) })),
    })
    form.value = { cliente: '', itens: [emptyLine()] }
    alert.value = { type: 'success', message: 'Venda registrada com sucesso!' }
    await loadAll()
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.errors?.produtos?.[0] || 'Erro ao registrar venda.'
    alert.value = { type: 'error', message: msg }
  } finally {
    loading.value = false
  }
}

async function cancelar(id) {
  if (!confirm('Tem certeza que deseja cancelar esta venda?')) return
  try {
    await api.patch(`/vendas/${id}/cancelar`)
    alert.value = { type: 'success', message: 'Venda cancelada com sucesso!' }
    await loadAll()
  } catch (e) {
    alert.value = { type: 'error', message: e.response?.data?.message || 'Erro ao cancelar venda.' }
  }
}

onMounted(loadAll)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Vendas</h1>
    <Alert v-if="alert" :type="alert.type" :message="alert.message" @dismiss="alert = null" />

    <FormCard title="Nova Venda">
      <form @submit.prevent="submit" class="space-y-4">
        <div class="w-full max-w-md">
          <label class="block text-xs text-gray-400 mb-1">Cliente</label>
          <input v-model="form.cliente" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>

        <div v-for="(item, idx) in form.itens" :key="idx" class="flex flex-wrap gap-3 items-end">
          <div class="flex-1 min-w-[180px]">
            <label class="block text-xs text-gray-400 mb-1">Produto</label>
            <select v-model="item.produto_id" @change="onProdutoSelect(item)" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="" disabled>Selecione</option>
              <option v-for="p in produtos" :key="p.id" :value="p.id">{{ p.nome }} (estoque: {{ p.estoque }})</option>
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

        <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
          <button type="button" @click="addLine" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">+ Adicionar produto</button>
          <div class="flex gap-6 text-sm text-gray-400">
            <span>Total: <strong class="text-white">R$ {{ brl(total) }}</strong></span>
            <span>Lucro estimado: <strong :class="lucroEstimado >= 0 ? 'text-green-400' : 'text-red-400'">R$ {{ brl(lucroEstimado) }}</strong></span>
          </div>
        </div>

        <button type="submit" :disabled="loading" class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-5 py-2 rounded-lg text-sm font-medium transition-colors">
          {{ loading ? 'Salvando...' : 'Registrar Venda' }}
        </button>
      </form>
    </FormCard>

    <DataTable :columns="columns" :items="vendas">
      <tr v-for="v in vendas" :key="v.id" class="border-b border-gray-700/50 hover:bg-gray-700/30">
        <td class="px-4 py-3">{{ v.id }}</td>
        <td class="px-4 py-3">{{ v.cliente }}</td>
        <td class="px-4 py-3">R$ {{ brl(v.total) }}</td>
        <td class="px-4 py-3">R$ {{ brl(v.lucro) }}</td>
        <td class="px-4 py-3">
          <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="v.status === 'ativa' ? 'bg-green-900/50 text-green-300' : 'bg-red-900/50 text-red-300'">
            {{ v.status }}
          </span>
        </td>
        <td class="px-4 py-3">
          <button v-if="v.status === 'ativa'" @click="cancelar(v.id)" class="inline-flex items-center gap-1 text-red-400 hover:text-red-300 text-xs font-medium">
            <XCircleIcon class="w-4 h-4" /> Cancelar
          </button>
        </td>
      </tr>
    </DataTable>

    <Pagination :meta="vendasMeta" @page="onPage" />
  </div>
</template>
