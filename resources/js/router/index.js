import { createRouter, createWebHistory } from 'vue-router'

import LoginPageView from '../views/LoginPageView.vue'
import RoleProfileView from '../views/RoleProfileView.vue'

const routes = [
  {
    path: '/',
    redirect: { name: 'login' },
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPageView,
  },
  {
    path: '/admin/profile',
    name: 'admin.profile',
    component: RoleProfileView,
    props: { initialView: 'profile' },
  },
  {
    path: '/admin/users',
    name: 'admin.users',
    component: RoleProfileView,
    props: { initialView: 'users' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
