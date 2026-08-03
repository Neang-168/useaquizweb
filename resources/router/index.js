import { createRouter, createWebHistory } from 'vue-router'

// 1. Import Layout និង Pages ឱ្យត្រូវតាម Directory របស់អ្នក
import AdminContentView from '../views/pages/superAdmin/AdminContentView.vue'
import Dashboard from '../views/pages/superAdmin/Dashboard.vue'
import RoleProfileView from '../views/pages/superAdmin/RoleProfileView.vue'
import LoginPageView from '../views/pages/superAdmin/LoginPageView.vue'

const routes = [
  {
    path: '/',
    redirect: '/admin/dashboard',
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPageView,
  },
  {
    path: '/admin',
    component: AdminContentView, // Main Admin Layout
    children: [
      {
        path: 'dashboard', // URL: /admin/dashboard
        name: 'admin.dashboard',
        component: Dashboard,
      },
      {
        path: 'profile', // URL: /admin/profile
        name: 'admin.profile',
        component: RoleProfileView,
      },
    ],
  },
  // Catch-all route សម្រាប់ករណីចូល Path ខុស (Optional)
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