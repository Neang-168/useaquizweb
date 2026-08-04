import { createRouter, createWebHistory } from 'vue-router'

// 1. Import ដោយប្រើឈ្មោះ File ពិតប្រាកដតាមរូបភាព Structure
import AdminLayout from '../views/AdminContentView.vue'
import LoginPageView from '../views/LoginPageView.vue' // 👈 ឈ្មោះត្រូវ ១០០%
import RoleProfileView from '../views/RoleProfileView.vue'

const routes = [
  // 2. Route Login ដាច់ដោយឡែក (បង្ហាញ Form Login នៅចំកណ្តាល គ្មាន Sidebar ទេ)
  {
    path: '/login',
    name: 'login',
    component: LoginPageView,
  },

  // 3. Route Admin រុំក្នុង Layout មេ (មាន Sidebar នៅឆ្វេង និង Dashboard/Profile នៅស្តាំ)
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: 'dashboard',
        name: 'admin.dashboard',
        // ភ្ជាប់ទៅ File Dashboard ក្នុង Folder pages/superAdmin/
        component: () => import('../views/pages/superAdmin/Dashboard.vue'),
      },
      {
        path: 'profile',
        name: 'admin.profile',
        component: RoleProfileView,
      },
    ],
  },

  // បើក / ដំបូង ឱ្យរត់ទៅ /login
  {
    path: '/',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router