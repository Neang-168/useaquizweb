<template>
  <div class="h-screen w-full flex flex-col overflow-hidden font-sans">

    <!-- ======= HEADER ======= -->
    <header class="w-full h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 z-10">
      <!-- Left: Logo -->
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl text-white flex items-center justify-center shrink-0 shadow-sm">
          <img :src="LogoUsea" alt="Logo" class="w-full h-full object-contain" />
        </div>
        <div>
          <h1 class="m-0 text-[16px] font-semibold text-slate-900 leading-tight">University Of South-East Asia</h1>
        </div>
        <div>
          <h1 class="ml-10 text-[14px] font-semibold text-slate-900 leading-tight">Teacher Portal</h1>
        </div>
      </div>

      <!-- Right: Search, Notifications, Profile -->
      <div class="flex items-center gap-5">
        <!-- <Button text rounded severity="secondary" aria-label="Search"
          class="!text-slate-400 hover:!text-blue-600 !p-0 !w-auto !h-auto">
          <i class="pi pi-search text-lg"></i>
        </Button> -->

        <Button text rounded severity="secondary" aria-label="Notifications"
          class="relative !text-slate-400 hover:!text-blue-600 !p-0 !w-auto !h-auto">
          <i class="pi pi-bell text-lg"></i>
          <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-blue-600"></span>
        </Button>

        <div class="w-px h-6 bg-slate-200"></div>

        <div class="relative" ref="profileMenuRef">
          <Button text severity="secondary" class="!p-0 !w-auto !h-auto" @click="profileMenuOpen = !profileMenuOpen">
            <div class="flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold overflow-hidden shrink-0">
                <img v-if="authUser.avatar_url" :src="authUser.avatar_url" alt="Avatar"
                  class="w-full h-full object-cover" />
                <span v-else>{{ userInitial }}</span>
              </div>
              <span class="text-sm font-semibold text-slate-700">{{ userFullName }}</span>
              <i class="pi pi-chevron-down text-xs text-slate-400"></i>
            </div>
          </Button>

          <!-- Profile Dropdown -->
          <div v-if="profileMenuOpen"
            class="absolute right-0 top-full mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-20">
            <Button as="router-link" :to="{ name: 'teacher.my-profile' }" @click="profileMenuOpen = false"
              label="User Profile" icon="pi pi-user"
              class="w-full !justify-start !border-0 !bg-transparent !text-slate-700 hover:!bg-slate-100 !py-2 !px-3.5 !text-sm !font-medium no-underline" />
            <div class="h-px bg-slate-200 my-1"></div>
            <Button @click="handleLogout" label="Logout" icon="pi pi-sign-out"
              class="w-full !justify-start !border-0 !bg-transparent !text-red-500 hover:!bg-red-50 !py-2 !px-3.5 !text-sm !font-medium" />
          </div>
        </div>
      </div>
    </header>

    <!-- ======= BODY: SIDEBAR + CONTENT ======= -->
    <div class="flex flex-1 min-h-0">

      <!-- ======= SIDEBAR ======= -->
      <aside
        class="w-60 h-full m-0 rounded-2xl bg-white border-r ml-1.5 mt-1 border-slate-200 flex flex-col justify-between p-2 font-sans select-none shrink-0">
        <div class="flex flex-col min-h-0 flex-1">

          <!-- Navigation Links Area -->
          <div class="flex-1 overflow-y-auto pr-1 my-2 space-y-5 custom-scrollbar">

            <!-- 1. OVERVIEW -->
            <div class="space-y-1">
              <router-link to="/teacher/dashboard"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-th-large text-base"></i>
                <span>Dashboard</span>
              </router-link>
              <router-link to="/teacher/classes"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-building text-base"></i>
                <span>Classes</span>
              </router-link>
              <router-link to="/teacher/questionbank"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-book text-base"></i>
                <span>Question Bank</span>
              </router-link>
              <router-link to="/teacher/scoreReport"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-chart-bar text-base"></i>
                <span>Score & Reports</span>
              </router-link>

              <router-link to="/teacher/feedback"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-comments text-base"></i>
                <span>Feedback</span>
              </router-link>

              <!-- <router-link to="/teacher/calendar"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-calendar text-base"></i>
                <span>Calendar</span>
              </router-link> -->
            </div>

            <!-- 2. ACADEMIC STRUCTURE -->
            <!-- <div class="space-y-1"> -->
            <!-- <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-1.5">
                Academic Setup
              </span> -->



            <!-- <router-link to="/teacher/subjects"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-graduation-cap text-base"></i>
                <span>Subjects</span>
              </router-link> -->
            <!-- </div> -->

            <!--3. QUIZ & EXAM MANAGEMENT-->
            <!-- <div class="space-y-1">
              <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-1.5">
                QUIZ & EXAM MANAGEMENT
              </span>

              <router-link to="/teacher/questionbank"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-graduation-cap text-base"></i>
                <span>Question Bank</span>
              </router-link>

              <router-link to="/teacher/scoreReport"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-graduation-cap text-base"></i>
                <span>Score & Reports</span>
              </router-link>

              <router-link to="/teacher/feedback"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-graduation-cap text-base"></i>
                <span>Feedback</span>
              </router-link>
            </div> -->
          </div>

          <!--Account-->
          <!-- <div class="space-y-1">
              <router-link :to="{ name: 'teacher.my-profile' }"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-user text-base"></i>
                <span>User Profile</span>
              </router-link>
          </div> -->

          <!-- Bottom Section: Logout Button -->
          <div class="pt-2 border-t border-slate-200/80 mt-auto">
            <Button label="Logout" icon="pi pi-sign-out"
              class="w-full !justify-start !border-0 !bg-transparent !text-red-500 hover:!bg-red-50 !py-2 !px-3 !rounded-sm !text-sm !font-semibold"
              @click="handleLogout" />
          </div>

        </div>
      </aside>

      <!-- ======= MAIN CONTENT ======= -->
      <main class="flex-1 overflow-y-auto bg-slate-100 p-6">
        <!-- 💡 :key="$route.fullPath" forces a re-render when navigating between pages -->
        <router-view :key="$route.fullPath" />
      </main>

    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import LogoUsea from '../images/usea_logo.png'
import { authUser, clearAuthUser } from '../store/authUser'

const route = useRoute()
const router = useRouter()

// Profile dropdown (User Profile / Logout)
const profileMenuOpen = ref(false)
const profileMenuRef = ref(null)

function handleClickOutside(event) {
  if (profileMenuRef.value && !profileMenuRef.value.contains(event.target)) {
    profileMenuOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

// Full display name (reactive — updates live when the profile page saves changes)
const userFullName = computed(() => {
  const fullName = `${authUser.first_name || ''} ${authUser.last_name || ''}`.trim()
  return fullName || 'Teacher'
})

// Initial letter for the avatar (e.g. "T")
const userInitial = computed(() => {
  return userFullName.value.charAt(0).toUpperCase()
})

// Check whether a given route is the active one
function isRouteActive(routeName) {
  return route.name === routeName
}

// Logout handler
function handleLogout() {
  localStorage.removeItem('auth_token')
  clearAuthUser()
  localStorage.removeItem('auth_role')
  localStorage.removeItem('remember_me')

  router.push({ name: 'login' })
}
</script>