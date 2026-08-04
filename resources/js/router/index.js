import { createRouter, createWebHistory } from 'vue-router'

import AdminContentView from '../views/AdminContentView.vue'
import Dashboard from '../views/pages/superAdmin/Dashboard.vue'
import RoleProfileView from '../views/RoleProfileView.vue'
import LoginPageView from '../views/LoginPageView.vue'

const routes = [
  { path: '/', redirect: '/admin/dashboard' },
  { path: '/login', name: 'login', component: LoginPageView },
  {
    path: '/admin',
    component: AdminContentView, // Layout មាន Sidebar + router-view
    children: [
      {
        path: 'dashboard',
        name: 'admin.dashboard',
        component: Dashboard,
      },
      {
        path: 'profile',
        name: 'admin.profile',
        component: RoleProfileView,
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router