<template>
  <aside class="w-64 bg-slate-900 text-slate-300 min-h-screen flex flex-col justify-between border-r border-slate-800">
    <!-- Header Section (Logo / App Name) -->
    <div>
      <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
          <i class="pi pi-book"></i>
        </div>
        <div>
          <h2 class="text-white font-bold text-sm tracking-wide">Portal គ្រូបង្រៀន</h2>
          <p class="text-xs text-slate-500">Quiz Management</p>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="p-4 space-y-1.5">
        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 mb-2">
          Menu ចម្បង
        </div>

        <!-- Dashboard Link -->
        <router-link
          :to="{ name: 'teacher.dashboard' }"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors"
          :class="isRouteActive('teacher.dashboard') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200'"
        >
          <i class="pi pi-th-large text-base"></i>
          <span>ផ្ទាំងគ្រប់គ្រង (Dashboard)</span>
        </router-link>

        <!-- Profile Link -->
        <router-link
          :to="{ name: 'teacher.role-profile' }"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors"
          :class="isRouteActive('teacher.role-profile') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200'"
        >
          <i class="pi pi-user text-base"></i>
          <span>ព័ត៌មានផ្ទាល់ខ្លួន (Profile)</span>
        </router-link>
      </nav>
    </div>

    <!-- User & Logout Section (ខាងក្រោមបង្អស់) -->
    <div class="p-4 border-t border-slate-800">
      <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800/40">
        <div class="flex items-center gap-3 overflow-hidden">
          <div class="w-9 h-9 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-sm border border-indigo-500/30">
            {{ userInitial }}
          </div>
          <div class="truncate">
            <h4 class="text-xs font-semibold text-white truncate">{{ userFullName }}</h4>
            <span class="text-[10px] text-indigo-400 bg-indigo-950/60 px-1.5 py-0.5 rounded border border-indigo-800/50">Teacher</span>
          </div>
        </div>
        
        <!-- Logout Button -->
        <button
          @click="handleLogout"
          title="ចាកចេញ"
          class="text-slate-400 hover:text-red-400 p-2 rounded-lg hover:bg-slate-700/50 transition-colors"
        >
          <i class="pi pi-sign-out text-base"></i>
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// ទាញយកព័ត៌មាន User ពី localStorage
const authUser = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('auth_user')) || {}
  } catch (e) {
    return {}
  }
})

// បង្ហាញឈ្មោះពេញ
const userFullName = computed(() => {
  if (authUser.value.first_name || authUser.value.last_name) {
    return `${authUser.value.first_name || ''} ${authUser.value.last_name || ''}`.trim()
  }
  return authUser.value.username || 'Teacher'
})

// ទាញយកអក្សរកាត់ឈ្មោះ (ឧ. T)
const userInitial = computed(() => {
  return userFullName.value.charAt(0).toUpperCase()
})

// Check ថា Route ណាដែលកំពុង Active
function isRouteActive(routeName) {
  return route.name === routeName
}

// Function Logout
function handleLogout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  localStorage.removeItem('auth_role')
  localStorage.removeItem('remember_me')
  
  router.push({ name: 'login' })
}
</script>