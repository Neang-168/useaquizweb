<template>
  <div class="flex w-screen h-screen m-0 p-0 overflow-hidden bg-slate-100 font-sans">
    <!-- ១. Sidebar ខ្មៅ -->
    <div class="h-full shrink-0">
      <AdminSidebar active-view="dashboard" @logout="logout" />
    </div>

    <!-- ២. Content Dashboard -->
    <main class="flex-1 h-full overflow-y-auto p-8">
      <h1 class="text-2xl font-bold text-slate-800 mb-2">Welcome to Dashboard</h1>
      <p class="text-slate-600 mb-6">ទំព័រគ្រប់គ្រងព័ត៌មានទូទៅ (Dashboard Panel)</p>

      <!-- Cards គំរូ -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200">
          <p class="text-xs font-mono uppercase text-slate-500 mb-1">Total Users</p>
          <h2 class="text-3xl font-bold text-slate-800">120</h2>
        </div>
        <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200">
          <p class="text-xs font-mono uppercase text-slate-500 mb-1">Active Courses</p>
          <h2 class="text-3xl font-bold text-slate-800">15</h2>
        </div>
        <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200">
          <p class="text-xs font-mono uppercase text-slate-500 mb-1">System Roles</p>
          <h2 class="text-3xl font-bold text-slate-800">4</h2>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import AdminSidebar from '../../../components/AdminSidebar.vue'

const router = useRouter()

function logout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  router.replace({ name: 'login' })
}
</script>