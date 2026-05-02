<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api.js'
import FormCard from '../components/FormCard.vue'
import DataTable from '../components/DataTable.vue'
import Alert from '../components/Alert.vue'
import Pagination from '../components/Pagination.vue'
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const produtos = ref([])
const form = ref({ nome: '', preco_venda: '' })
const imagem = ref(null)
const preview = ref(null)
const loading = ref(false)
const alert = ref(null)

const editing = ref(null)
const editForm = ref({ nome: '', preco_venda: '' })
const editImagem = ref(null)
const editPreview = ref(null)
const editLoading = ref(false)

const columns = [
  { label: '', key: 'imagem' },
  { label: 'Nome', key: 'nome' },
  { label: 'Custo Médio (R$)', key: 'custo_medio' },
  { label: 'Preço Venda (R$)', key: 'preco_venda' },
  { label: 'Estoque', key: 'estoque' },
  { label: 'Ações', key: 'actions' },
]

import { brl } from '../utils/format.js'

function onFileChange(e) {
  imagem.value = e.target.files[0]
  preview.value = imagem.value ? URL.createObjectURL(imagem.value) : null
}

function onEditFileChange(e) {
  editImagem.value = e.target.files[0]
  editPreview.value = editImagem.value ? URL.createObjectURL(editImagem.value) : null
}

const paginationMeta = ref(null)

async function load(url = '/produtos') {
  const { data } = await api.get(url)
  produtos.value = data.data
  paginationMeta.value = data.meta
}

function onPage(url) {
  const u = new URL(url)
  const path = u.pathname.replace('/api', '') + u.search
  load(path)
}

async function submit() {
  loading.value = true
  try {
    const fd = new FormData()
    fd.append('nome', form.value.nome)
    fd.append('preco_venda', Number(form.value.preco_venda))
    if (imagem.value) fd.append('imagem', imagem.value)

    await api.post('/produtos', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    form.value = { nome: '', preco_venda: '' }
    imagem.value = null
    preview.value = null
    alert.value = { type: 'success', message: 'Produto criado com sucesso!' }
    await load()
  } catch (e) {
    alert.value = { type: 'error', message: e.response?.data?.message || 'Erro ao criar produto.' }
  } finally {
    loading.value = false
  }
}

function startEdit(p) {
  editing.value = p
  editForm.value = { nome: p.nome, preco_venda: p.preco_venda }
  editImagem.value = null
  editPreview.value = p.imagem_url
}

async function submitEdit() {
  editLoading.value = true
  try {
    const fd = new FormData()
    fd.append('nome', editForm.value.nome)
    fd.append('preco_venda', Number(editForm.value.preco_venda))
    fd.append('_method', 'PUT')
    if (editImagem.value) fd.append('imagem', editImagem.value)

    await api.post(`/produtos/${editing.value.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    editing.value = null
    alert.value = { type: 'success', message: 'Produto atualizado!' }
    await load()
  } catch (e) {
    alert.value = { type: 'error', message: e.response?.data?.message || 'Erro ao atualizar.' }
  } finally {
    editLoading.value = false
  }
}

async function destroy(p) {
  if (!confirm(`Excluir "${p.nome}"?`)) return
  try {
    await api.delete(`/produtos/${p.id}`)
    alert.value = { type: 'success', message: 'Produto excluído!' }
    await load()
  } catch (e) {
    alert.value = { type: 'error', message: e.response?.data?.message || 'Erro ao excluir.' }
  }
}

onMounted(load)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Produtos</h1>
    <Alert v-if="alert" :type="alert.type" :message="alert.message" @dismiss="alert = null" />

    <FormCard title="Novo Produto">
      <form @submit.prevent="submit" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs text-gray-400 mb-1">Nome</label>
          <input v-model="form.nome" required minlength="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div class="w-44">
          <label class="block text-xs text-gray-400 mb-1">Preço Venda (R$)</label>
          <input v-model="form.preco_venda" type="number" step="0.01" min="0.01" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div class="w-52">
          <label class="block text-xs text-gray-400 mb-1">Imagem</label>
          <input type="file" accept="image/*" @change="onFileChange" class="w-full text-sm text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600" />
        </div>
        <img v-if="preview" :src="preview" class="h-10 w-10 rounded object-cover" />
        <button type="submit" :disabled="loading" class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-5 py-2 rounded-lg text-sm font-medium transition-colors">
          {{ loading ? 'Salvando...' : 'Cadastrar' }}
        </button>
      </form>
    </FormCard>

    <DataTable :columns="columns" :items="produtos">
      <tr v-for="p in produtos" :key="p.id" class="border-b border-gray-700/50 hover:bg-gray-700/30">
        <td class="px-4 py-3 w-14">
          <img v-if="p.imagem_url" :src="p.imagem_url" class="h-10 w-10 rounded object-cover" />
          <div v-else class="h-10 w-10 rounded bg-gray-700 flex items-center justify-center text-gray-500 text-xs">—</div>
        </td>
        <td class="px-4 py-3">{{ p.nome }}</td>
        <td class="px-4 py-3">R$ {{ brl(p.custo_medio) }}</td>
        <td class="px-4 py-3">R$ {{ brl(p.preco_venda) }}</td>
        <td class="px-4 py-3">{{ p.estoque }}</td>
        <td class="px-4 py-3">
          <div class="flex gap-2">
            <button @click="startEdit(p)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-400 hover:bg-indigo-500/20 transition-colors" title="Editar">
              <PencilSquareIcon class="w-4 h-4" />
            </button>
            <button @click="destroy(p)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-400 hover:bg-red-500/20 transition-colors" title="Excluir">
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </td>
      </tr>
    </DataTable>

    <Pagination :meta="paginationMeta" @page="onPage" />

    <!-- Modal de edição -->
    <div v-if="editing" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" @click.self="editing = null">
      <div class="bg-gray-800 rounded-xl p-6 w-full max-w-md shadow-xl">
        <h3 class="text-lg font-semibold mb-4">Editar Produto</h3>
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <label class="block text-xs text-gray-400 mb-1">Nome</label>
            <input v-model="editForm.nome" required minlength="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1">Preço Venda (R$)</label>
            <input v-model="editForm.preco_venda" type="number" step="0.01" min="0.01" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1">Imagem</label>
            <input type="file" accept="image/*" @change="onEditFileChange" class="w-full text-sm text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600" />
            <img v-if="editPreview" :src="editPreview" class="h-16 w-16 rounded object-cover mt-2" />
          </div>
          <div class="flex gap-3 justify-end">
            <button type="button" @click="editing = null" class="px-4 py-2 text-sm text-gray-400 hover:text-white">Cancelar</button>
            <button type="submit" :disabled="editLoading" class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-5 py-2 rounded-lg text-sm font-medium transition-colors">
              {{ editLoading ? 'Salvando...' : 'Salvar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
