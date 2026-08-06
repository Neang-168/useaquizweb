<template>
  <div class="h-screen w-full flex flex-col overflow-hidden font-sans">

    <!-- ======= HEADER ======= -->
    <header class="w-full h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 z-10">
      <!-- Left: Logo -->
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl  text-white flex items-center justify-center shrink-0 shadow-sm">
          <img :src="LogoUsea" alt="Logo" class="w-full h-full object-contain" />
        </div>
        <div>
          <h1 class="m-0 text-sm font-bold text-slate-900 leading-tight">USEA</h1>
          <p class="m-0 text-[11px] text-slate-400 font-medium leading-tight">Learning Suite</p>
        </div>
      </div>

      <!-- Right: Search, Notifications, Profile -->
      <div class="flex items-center gap-5">
        <button type="button"
          class="border-0 bg-transparent text-slate-400 hover:text-blue-600 transition-colors p-0 cursor-pointer"
          aria-label="Search">
          <i class="pi pi-search text-lg"></i>
        </button>

        <button type="button"
          class="relative border-0 bg-transparent text-slate-400 hover:text-blue-600 transition-colors p-0 cursor-pointer"
          aria-label="Notifications">
          <i class="pi pi-bell text-lg"></i>
          <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-blue-600"></span>
        </button>

        <div class="w-px h-6 bg-slate-200"></div>

        <button type="button" class="flex items-center gap-2 border-0 bg-transparent p-0 cursor-pointer">
          <div
            class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">
            <i class="pi pi-user text-sm"></i>
          </div>
          <i class="pi pi-chevron-down text-xs text-slate-400"></i>
        </button>
      </div>
    </header>

    <!-- ======= BODY: SIDEBAR + CONTENT ======= -->
    <div class="flex flex-1 min-h-0">

      <!-- ======= SIDEBAR ======= -->
      <aside
        class="w-64 h-full m-0 rounded-none bg-indigo-50/70 border-r border-slate-200 flex flex-col justify-between p-4 font-sans select-none shrink-0">
        <div class="flex flex-col min-h-0 flex-1">

          <!-- Navigation Links Area -->
          <div class="flex-1 overflow-y-auto pr-1 my-2 space-y-5 custom-scrollbar">

            <!-- 1. OVERVIEW -->
            <div class="space-y-1">
              <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-1.5">
                Overview
              </span>
              <router-link to="/teacher/dashboard"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-th-large text-base"></i>
                <span>Dashboard</span>
              </router-link>
            </div>

            <!-- 2. ACADEMIC STRUCTURE -->
            <div class="space-y-1">
              <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-1.5">
                Academic Setup
              </span>

              <router-link to="/teacher/classes"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-building text-base"></i>
                <span>My Classes</span>
              </router-link>

              <router-link to="/teacher/subjects"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-graduation-cap text-base"></i>
                <span>Subjects</span>
              </router-link>
            </div>

            <!--3. QUIZ & EXAM MANAGEMENT-->
            <div class="space-y-1">
              <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-1.5">
                QUIZ & EXAM MANAGEMENT
              </span>

              <router-link to="/teacher/questionbank"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-graduation-cap text-base"></i>
                <span>Question Bank</span>
              </router-link>

              <router-link to="/teacher/quizzes"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-graduation-cap text-base"></i>
                <span>Quizzes</span>
              </router-link>

              <router-link to="/teacher/scoreReport"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-graduation-cap text-base"></i>
                <span>score & Report</span>
              </router-link>

              <router-link to="/teacher/feedback"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-graduation-cap text-base"></i>
                <span>Feedback</span>
              </router-link>
            </div>
          </div>

          <!--Account-->
          <div class="space-y-1">
               <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-1.5">
                Administration
              </span>

              <router-link to="/teacher/users"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-user-plus text-base"></i>
                <span>User Accounts</span>
              </router-link>
          </div>

          <!-- Bottom Section: Logout Button -->
          <div class="pt-2 border-t border-slate-200/80 mt-auto">
            <button @click="handleLogout" type="button"
              class="w-full border-0 bg-transparent text-red-500 hover:bg-red-50 py-2 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold cursor-pointer transition-all">
              <i class="pi pi-sign-out text-base"></i>
              <span>Logout</span>
            </button>
          </div>

        </div>
      </aside>

      <!-- ======= MAIN CONTENT ======= -->
      <main class="flex-1 overflow-y-auto bg-slate-50 p-6">
        <!-- 💡 បន្ថែម :key="$route.fullPath" ដើម្បីដោះស្រាយបញ្ហា Re-render ពេលដូរទំព័រ -->
        <router-view :key="$route.fullPath" />
      </main>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import LogoUsea from '../images/usea_logo.png'

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