import { createRouter, createWebHistory } from 'vue-router'

import AdminContent from '../components/AdminContent.vue'

const routes = [
    {
        path: '/',
        redirect: '/admin/profile',
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