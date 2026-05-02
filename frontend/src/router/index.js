import { createRouter, createWebHistory } from 'vue-router'
import ProdutosView from '../views/ProdutosView.vue'
import ComprasView from '../views/ComprasView.vue'
import VendasView from '../views/VendasView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/produtos' },
    { path: '/produtos', component: ProdutosView },
    { path: '/compras', component: ComprasView },
    { path: '/vendas', component: VendasView },
  ],
})

export default router
