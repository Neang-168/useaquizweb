import { createRouter, createWebHistory } from 'vue-router'

import AdminContentView from '../views/pages/superAdmin/AdminContentView.vue'
import Dashboard from '../views/pages/superAdmin/Dashboard.vue'
import RoleProfileView from '../views/pages/superAdmin/RoleProfileView.vue'
import LoginPageView from '../views/pages/superAdmin/LoginPageView.vue'

const routes = [
  {
    path: '/',
    redirect: '/admin/dashboard', // <--- ត្រូវ៖ បើចូល path ដើម វារុញទៅ dashboard
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPageView,
  },
  {
    path: '/admin',
    component: AdminContentView, 
    children: [
      {
        path: 'dashboard', 
        name: 'admin.dashboard', // <--- ត្រូវ៖ name នេះត្រូវគ្នានឹង router.push({ name: 'admin.dashboard' })
        component: Dashboard,
      },
      {
        path: 'profile', 
        name: 'admin.profile',
        component: RoleProfileView,
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/admin/dashboard',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router