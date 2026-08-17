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
          <h1 class="ml-10 text-[14px] font-semibold text-slate-900 leading-tight">USEA Quiz Management</h1>
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

        <div class="relative" ref="profileMenuRef">
          <button type="button" @click="profileMenuOpen = !profileMenuOpen"
            class="flex items-center gap-2 border-0 bg-transparent p-0 cursor-pointer">
            <div
              class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold overflow-hidden shrink-0">
              <img v-if="authUser.avatar_url" :src="authUser.avatar_url" alt="Avatar" class="w-full h-full object-cover" />
              <span v-else>{{ userInitial }}</span>
            </div>
            <span class="text-sm font-semibold text-slate-700">{{ userFullName }}</span>
            <i class="pi pi-chevron-down text-xs text-slate-400"></i>
          </button>

          <!-- Profile Dropdown -->
          <div v-if="profileMenuOpen"
            class="absolute right-0 top-full mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-20">
            <router-link :to="{ name: 'admin.my-profile' }" @click="profileMenuOpen = false"
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-100 py-2 px-3.5 flex items-center gap-2.5 text-sm font-medium no-underline">
              <i class="pi pi-user text-sm"></i>
              <span>User Profile</span>
            </router-link>
            <div class="h-px bg-slate-200 my-1"></div>
            <button @click="handleLogout" type="button"
              class="w-full border-0 bg-transparent text-red-500 hover:bg-red-50 py-2 px-3.5 flex items-center gap-2.5 text-sm font-medium cursor-pointer">
              <i class="pi pi-sign-out text-sm"></i>
              <span>Logout</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- ======= BODY: SIDEBAR + CONTENT ======= -->
    <div class="flex flex-1 min-h-0">

      <!-- ======= SIDEBAR ======= -->
      <aside
        class="w-60 h-full m-0 rounded-none bg-white border-r border-slate-200 flex flex-col justify-between p-2 font-sans select-none shrink-0">
        <div class="flex flex-col min-h-0 flex-1">

          <!-- Navigation Links Area -->
          <div class="flex-1 overflow-y-auto pr-1 my-2 space-y-5 custom-scrollbar">

            <!-- Dashboard sits alone at the top, unlabeled — the entry
                 point doesn't need its own section header. -->
            <div class="space-y-1">
              <router-link to="/admin/dashboard"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-th-large text-base"></i>
                <span>Dashboard</span>
              </router-link>
            </div>

            <!-- Academic: the course/class catalog, in the order you'd set
                 it up (subjects first, then the classes taught from them) -->
            <div class="space-y-1">
              <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-1.5">
                Academic
              </span>

              <router-link to="/admin/subjects"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-book text-base"></i>
                <span>Subjects</span>
              </router-link>

              <router-link to="/admin/classes"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-building text-base"></i>
                <span>Classes</span>
              </router-link>
            </div>

            <!-- People: the individuals who use the system -->
            <div class="space-y-1">
              <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-1.5">
                People
              </span>

              <router-link to="/admin/teachers"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-id-card text-base"></i>
                <span>Teachers</span>
              </router-link>

              <router-link to="/admin/students"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-users text-base"></i>
                <span>Students & Enrollments</span>
              </router-link>

              <router-link to="/admin/report"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-users text-base"></i>
                <span>Report</span>
              </router-link>
            </div>

            <!-- Administration: system-wide account management first,
                 the admin's own profile last -->
            <div class="space-y-1">
              <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-1.5">
                Administration
              </span>

              <router-link to="/admin/users"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-user-plus text-base"></i>
                <span>User Management</span>
              </router-link>

              <router-link :to="{ name: 'admin.my-profile' }"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-user text-base"></i>
                <span>My Profile</span>
              </router-link>

              <!-- <router-link to="/admin/roles-permissions"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600">
                <i class="pi pi-shield text-base"></i>
                <span>Roles & Permissions</span>
              </router-link> -->
            </div>

          </div>

          <!-- Bottom Section: Logout Button -->
          <div class="pt-2 border-t border-slate-200/80 mt-auto">
            <button @click="handleLogout" type="button"
              class="w-full border-0 bg-transparent text-red-500 hover:bg-red-50 py-2 px-3 rounded-sm flex items-center gap-3 text-sm font-semibold cursor-pointer transition-all">
              <i class="pi pi-sign-out text-base"></i>
              <span>Logout</span>
            </button>
          </div>

        </div>
      </aside>

      <!-- ======= MAIN CONTENT ======= -->
      <main class="flex-1 overflow-y-auto bg-slate-100 p-6 flex flex-col">
        <router-view :key="$route.fullPath" class="flex-1" />
      </main>

    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import LogoUsea from '../images/usea_logo.png'
import { authUser, clearAuthUser } from '../store/authUser'

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
  return fullName || 'Admin'
})

// Initial letter for the avatar (e.g. "A")
const userInitial = computed(() => {
  return userFullName.value.charAt(0).toUpperCase()
})

const handleLogout = () => {
  localStorage.removeItem('auth_token')
  clearAuthUser()
  router.push('/login')
}
</script>

<style scoped>
/* Custom Scrollbar សម្រាប់ Sidebar ឱ្យមើលទៅតូចស្អាត */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
</style>