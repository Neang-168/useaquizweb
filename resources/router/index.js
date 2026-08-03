import { createRouter, createWebHistory } from 'vue-router'

import AdminContent from '../components/AdminContent.vue'
import Dashboardview from '../views/pages/superAdmin/Dashboard.vue'

const routes = [
    {
        path: '/',
        redirect: '/admin/profile',
    },

    {
        path: '/admin/dashboard',
        name: 'admin.dashboard',
        component: Dashboardview, // <--- បន្ថែម Route សម្រាប់ Dashboard
    },

    {
        path: '/admin/profile',
        name: 'admin.profile',
        component: AdminProfile,
    },

    {
        path: '/admin/users',
        name: 'admin.users',
        component: AdminUsers,
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router