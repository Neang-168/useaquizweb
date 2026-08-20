<template>
  <div class="h-screen w-full flex flex-col bg-slate-50 font-sans overflow-hidden">

    <!-- ======= HEADER NAVIGATION BAR ======= -->
    <header class="w-full bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          
          <!-- Left: Logo & Brand -->
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
              <img :src="LogoUsea" alt="Logo" class="w-full h-full object-contain" />
            </div>
            <div>
              <h1 class="m-0 text-sm font-bold text-slate-900 leading-tight">USEA</h1>
              <p class="m-0 text-[11px] text-slate-400 font-medium leading-tight">Student Portal</p>
            </div>
          </div>

          <!-- Center: Navigation Menus (Header Menu) -->
          <nav
            v-if="!quizInProgress"
            class="hidden md:flex items-center space-x-1 lg:space-x-2"
          >
            <router-link to="/student/dashboard"
              class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition-all flex items-center gap-2 no-underline"
              active-class="!bg-blue-50 !text-blue-600">
              <!-- <i class="pi pi-th-large text-base"></i> -->
              <span>Dashboard</span>
            </router-link>

            <router-link to="/student/mycourses"
              class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition-all flex items-center gap-2 no-underline"
              active-class="!bg-blue-50 !text-blue-600">
              <!-- <i class="pi pi-book text-base"></i> -->
              <span>My Courses</span>
            </router-link>

            <router-link to="/student/myexam"
              class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition-all flex items-center gap-2 no-underline"
              active-class="!bg-blue-50 !text-blue-600">
              <!-- <i class="pi pi-file-edit text-base"></i> -->
              <span>Quizzes & Exams</span>
            </router-link>

            <router-link to="/student/gradeHistory"
              class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition-all flex items-center gap-2 no-underline"
              active-class="!bg-blue-50 !text-blue-600">
              <!-- <i class="pi pi-chart-line text-base"></i> -->
              <span>Grades & History</span>
            </router-link>
          </nav>

          <!-- While a quiz is in progress, navigation is locked so nothing pulls the student away mid-attempt -->
          <div v-else class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200">
            <i class="pi pi-lock text-amber-500 text-xs"></i>
            <span class="text-xs font-bold text-amber-700">Quiz in progress — navigation locked</span>
          </div>

          <!-- Right: Notifications & Profile Menu -->
          <div v-if="!quizInProgress" class="flex items-center gap-4">
            <!-- Notification Button -->
            <router-link  to="/student/notification"
                class="relative text-slate-400 hover:text-blue-600 transition-colors p-1 cursor-pointer border-0 bg-transparent">
              <i class="pi pi-bell text-lg"></i>
              <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-blue-600"></span>
            </router-link>

            <div class="h-6 w-px bg-slate-200"></div>

            <!-- Profile Dropdown Button -->
            <div class="relative flex items-center gap-3">
              <router-link to="/student/role-profile" class="flex items-center gap-2.5 no-underline">
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-xs">
                  {{ userInitial }}
                </div>
                <div class="hidden sm:block text-left">
                  <p class="text-xs font-bold text-slate-800 leading-tight m-0">{{ userFullName }}</p>
                  <p class="text-[10px] text-slate-400 leading-tight m-0">Student</p>
                </div>
              </router-link>

              <!-- Logout Button -->
              <button @click="handleLogout" type="button" title="Logout"
                class="ml-1 p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors border-0 bg-transparent cursor-pointer">
                <i class="pi pi-sign-out text-base"></i>
              </button>
            </div>

          </div>
          <div v-else class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-xs">
            {{ userInitial }}
          </div>

        </div>
      </div>
    </header>

    <!-- ======= MAIN CONTENT AREA ======= -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 max-w-7xl w-full mx-auto">
      <router-view :key="$route.fullPath" />
    </main>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import LogoUsea from '../images/usea_logo.png'
import { authUser, clearAuthUser } from '../store/authUser'
import { quizInProgress } from '../utils/quizLock'

const router = useRouter()

// បង្ហាញឈ្មោះពេញ
const userFullName = computed(() => {
  if (authUser.first_name || authUser.last_name) {
    return `${authUser.first_name || ''} ${authUser.last_name || ''}`.trim()
  }
  return authUser.username || 'Student'
})

// ទាញយកអក្សរកាត់ឈ្មោះ
const userInitial = computed(() => {
  return userFullName.value.charAt(0).toUpperCase()
})

// Function Logout
function handleLogout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_role')
  localStorage.removeItem('remember_me')
  clearAuthUser()

  router.push({ name: 'login' })
}
</script>