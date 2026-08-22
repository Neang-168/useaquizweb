<template>
  <div class="space-y-6 w-full pb-10">

    <!-- Error state -->
    <div v-if="error" class="p-4 bg-rose-50/80 backdrop-blur-md border border-rose-200/80 rounded-2xl text-sm text-rose-700 flex items-center gap-3 shadow-xs">
      <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
        <i class="pi pi-exclamation-triangle text-rose-600"></i>
      </div>
      <span class="font-medium">{{ error }}</span>
    </div>

    <!-- ======= KPI ROW (UPGRADED WITH NUMBER ANIMATION) ======= -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 w-full ">
      <div 
        v-for="card in statCards" 
        :key="card.label"
        class="group relative bg-white p-4 rounded-2xl border border-slate-100/80 shadow-xs hover:border-[#63c7df] hover:shadow-md hover:-translate-y-1 transition-all duration-300 ease-out cursor-pointer overflow-hidden flex flex-col justify-between"
      >
        <!-- Ambient Background Glow -->
        <div class="absolute -right-3 -top-3 w-16 h-16 bg-slate-900/[0.03] rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>

        <!-- Top Row: Icon Box & Micro Trend -->
        <div class="flex items-center justify-between mb-3 relative ">
          <div 
            class="w-10 h-10 rounded-xl flex items-center  justify-center transition-transform duration-300 group-hover:scale-105 shadow-2xs"
            :class="[card.bg, card.text]"
          >
            <i :class="card.icon" class="text-base"></i>
          </div>

          <!-- Micro Trend Badge -->
          <span 
            v-if="card.trend"
            class="inline-flex items-center gap-0.5 text-[10px] font-bold px-2 py-0.5 rounded-full border shadow-2xs"
            :class="card.isPositive !== false ? 'text-emerald-600 bg-emerald-50/80 border-emerald-100/60' : 'text-rose-600 bg-rose-50/80 border-rose-100/60'"
          >
            <i :class="card.isPositive !== false ? 'pi pi-arrow-up-right' : 'pi pi-arrow-down-right'" class="text-[9px]"></i>
            {{ card.trend }}
          </span>
          <span v-else class="w-2 h-2 rounded-full bg-slate-200/60 group-hover:bg-blue-400 transition-colors"></span>
        </div>

        <!-- Metric Value & Label -->
        <div class="relative z-10">
          <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight m-0 min-h-[32px] flex items-center">
            <span v-if="loading" class="inline-block w-12 h-7 bg-slate-100 rounded-lg animate-pulse"></span>
            <span 
              v-else 
              class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 bg-clip-text text-transparent font-mono transition-all duration-300"
            >
              {{ card.value }}
            </span>
          </h2>
          <p class="text-[11px] font-bold text-[#002060] uppercase tracking-wider mt-1.5 mb-0 truncate">
            {{ card.label }}
          </p>
        </div>

        <!-- Bottom Accent Line -->
        <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-slate-200/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      </div>
    </div>

    <!-- ======= CHARTS ROW ======= -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 w-full">

      <!-- Users by Role (horizontal bars) -->
      <div class="lg:col-span-2 p-6 bg-white rounded-2xl border border-slate-100/80 shadow-xs flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-base font-bold text-[#002060] m-0">Users by Role</h3>
              <p class="text-xs text-slate-400 m-0 mt-0.5">Distribution across system roles</p>
            </div>
            <span class="px-2.5 py-1 bg-[#002060] border-slate-100 rounded-lg text-xs text-white font-semibold shadow-2xs">
              {{ animatedTotals.users.toLocaleString() }} total
            </span>
          </div>

          <div v-if="loading" class="space-y-4">
            <div v-for="n in 4" :key="n" class="h-8 bg-slate-100/80 rounded-xl animate-pulse"></div>
          </div>

          <div v-else-if="usersByRole.length === 0" class="text-center text-xs text-slate-400 py-12">
            No users available.
          </div>

          <div v-else class="space-y-4">
            <div v-for="role in usersByRole" :key="role.role" class="group">
              <div class="flex items-center justify-between mb-1.5 text-xs font-semibold">
                <span class="text-slate-700 group-hover:text-slate-900 transition-colors">{{ role.role }}</span>
                <span class="text-slate-500 font-mono">{{ role.count }} <span class="text-slate-300">|</span> {{ role.pct }}%</span>
              </div>
              <div class="h-2.5 w-full bg-slate-100/80 rounded-full overflow-hidden p-0.5">
                <div 
                  class="h-full rounded-full transition-all duration-700 ease-out"
                  :style="{ width: role.pct + '%', backgroundColor: role.color }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- New Signups, last 7 days (Bar Chart) -->
      <div class="lg:col-span-3 p-6 bg-white rounded-2xl border border-slate-100/80 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-base font-bold text-[#002060] m-0">New Accounts</h3>
            <p class="text-xs text-slate-400 m-0 mt-0.5">User registrations in the last 7 days</p>
          </div>
          <span class="px-2.5 py-1 bg-[#002060] border border-blue-100/80 text-white rounded-lg text-xs font-semibold">
            Last 7 days
          </span>
        </div>

        <div v-if="loading" class="h-52 flex items-end gap-3 px-2">
          <div v-for="n in 7" :key="n" class="flex-1 bg-slate-100 rounded-t-xl animate-pulse" :style="{ height: (25 + n * 8) + '%' }"></div>
        </div>

        <div v-else class="flex items-end justify-between gap-3 h-52 px-2 pt-4">
          <div 
            v-for="day in signupsLast7Days" 
            :key="day.date"
            class="flex-1 flex flex-col items-center justify-end h-full gap-2 group cursor-pointer"
            :title="`${day.date}: ${day.count} new account${day.count === 1 ? '' : 's'}`"
          >
            <!-- Badge Count -->
            <span class="text-[11px] font-bold text-slate-600 group-hover:scale-110 group-hover:text-blue-600 transition-all">
              {{ day.count }}
            </span>
            
            <!-- Bar Container -->
            <div class="w-full max-w-[32px] h-full flex items-end bg-slate-50 rounded-t-xl overflow-hidden p-0.5">
              <div 
                class="w-full rounded-t-lg bg-gradient-to-t from-blue-600 to-indigo-500 group-hover:from-blue-500 group-hover:to-indigo-400 transition-all duration-300 shadow-2xs"
                :style="{ height: barHeight(day.count) + '%' }"
              ></div>
            </div>

            <!-- Day Label -->
            <span class="text-[11px] text-slate-400 font-medium group-hover:text-slate-700 transition-colors">
              {{ day.label }}
            </span>
          </div>
        </div>
      </div>

    </div>

    <!-- ======= STATUS + RECENT USERS ======= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full">

      <!-- Account Status meter -->
      <div class="p-6 bg-white rounded-2xl border border-slate-100/80 shadow-xs flex flex-col justify-between">
        <div>
          <h3 class="text-base font-bold text-[#002060] m-0 mb-1">Account Status</h3>
          <p class="text-xs text-slate-400 m-0 mb-6">Overall system activity ratio</p>

          <div v-if="loading" class="h-4 w-full bg-slate-100 rounded-full animate-pulse"></div>
          <template v-else>
            <!-- Custom Progress Bar -->
            <div class="h-3.5 w-full bg-rose-100/70 rounded-full overflow-hidden p-0.5" :title="`${activePct}% active`">
              <div 
                class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-700 ease-out shadow-2xs" 
                :style="{ width: activePct + '%' }"
              ></div>
            </div>

            <!-- Legend Status -->
            <div class="grid grid-cols-2 gap-3 mt-5">
              <div class="p-3 bg-slate-50/80 border border-slate-100 rounded-xl">
                <div class="flex items-center gap-2 mb-1">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  <span class="text-xs font-medium text-slate-500">Active</span>
                </div>
                <p class="text-lg font-bold text-slate-800 m-0">{{ statusBreakdown.active }}</p>
              </div>

              <div class="p-3 bg-slate-50/80 border border-slate-100 rounded-xl">
                <div class="flex items-center gap-2 mb-1">
                  <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                  <span class="text-xs font-medium text-slate-500">Inactive</span>
                </div>
                <p class="text-lg font-bold text-slate-800 m-0">{{ statusBreakdown.inactive }}</p>
              </div>
            </div>
          </template>
        </div>

        <p class="text-xs text-slate-400 mt-6 mb-0 leading-relaxed border-t border-slate-100 pt-4">
          <i class="pi pi-info-circle text-[11px] mr-1 text-slate-400"></i>
          <span class="font-semibold text-slate-600">{{ activePct }}%</span> of accounts are currently operational.
        </p>
      </div>

      <!-- Recent Users -->
      <div class="lg:col-span-2 p-6 bg-white rounded-2xl border border-slate-100/80 shadow-xs flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-base font-bold text-[#002060] m-0">Recently Added</h3>
              <p class="text-xs text-slate-400 m-0 mt-0.5">Latest registrations across all roles</p>
            </div>
            <router-link 
              :to="{ name: 'admin.users' }" 
              class="text-xs text-blue-600 font-semibold hover:text-blue-700 hover:underline inline-flex items-center gap-1 no-underline transition-colors"
            >
              View all <i class="pi pi-arrow-right text-[10px]"></i>
            </router-link>
          </div>

          <div v-if="loading" class="space-y-3">
            <div v-for="n in 5" :key="n" class="h-12 bg-slate-100/80 rounded-xl animate-pulse"></div>
          </div>

          <div v-else-if="recentUsers.length === 0" class="text-center text-xs text-slate-400 py-12">
            No users registered yet.
          </div>

          <ul v-else class="m-0 p-0 list-none divide-y divide-slate-100">
            <li 
              v-for="user in recentUsers" 
              :key="user.id" 
              class="py-3.5 first:pt-1 last:pb-0 flex items-center gap-3.5 group hover:bg-slate-50/60 -mx-2 px-2 rounded-xl transition-colors"
            >
              <!-- Avatar -->
              <Avatar
                :image="user.avatar_url || undefined"
                :label="!user.avatar_url ? (user.name || '?').charAt(0).toUpperCase() : undefined"
                shape="circle"
                class="!bg-blue-100/80 !text-blue-700 font-bold shrink-0 ring-2 ring-white shadow-2xs"
              />

              <!-- User Info -->
              <div class="min-w-0 flex-1">
                <p class="m-0 text-sm font-bold text-slate-700 truncate group-hover:text-blue-600 transition-colors">
                  {{ user.name }}
                </p>
                <p class="m-0 text-xs text-slate-400 truncate mt-0.5">{{ user.email }}</p>
              </div>

              <!-- Role Badge -->
              <span 
                class="text-[11px] font-semibold px-2.5 py-1 rounded-lg border inline-block tracking-tight shrink-0 shadow-2xs" 
                :class="roleBadgeClass(user.role)"
              >
                {{ user.role || 'No role' }}
              </span>

              <!-- Relative Time -->
              <span class="text-[11px] text-slate-400 font-medium shrink-0 w-16 text-right font-mono">
                {{ relativeTime(user.created_at) }}
              </span>
            </li>
          </ul>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Avatar from 'primevue/avatar'
import api, { extractError } from '../../../api'
import { authUser } from '../../../store/authUser'

// Fixed categorical order — mirrors the role hierarchy used everywhere else
const ROLE_COLORS = {
  'Admin': '#eb6834',
  'Teacher': '#eda100',
  'Student': '#e87ba4',
}

const loading = ref(true)
const error = ref('')

const totals = ref({ users: 0, teachers: 0, students: 0, classes: 0, subjects: 0, faculties: 0 })

// ផ្ទុកតួលេខដែលត្រូវធ្វើចលនា Count-up Animation
const animatedTotals = ref({ users: 0, teachers: 0, students: 0, classes: 0, subjects: 0, faculties: 0 })

const usersByRoleRaw = ref([])
const statusBreakdown = ref({ active: 0, inactive: 0 })
const signupsLast7Days = ref([])
const recentUsers = ref([])

const firstName = computed(() => authUser.first_name || '')
const todayLabel = computed(() => new Date().toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))

// Logic សម្រាប់ Count-up Animation តួលេខ
const animateValue = (key, start, end, duration = 1000) => {
  if (start === end) {
    animatedTotals.value[key] = end
    return
  }
  let startTimestamp = null
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp
    const progress = Math.min((timestamp - startTimestamp) / duration, 1)
    const easeOutProgress = 1 - Math.pow(1 - progress, 3) // Ease-out efecto ដើរលឿនដើមយឺតចុង
    animatedTotals.value[key] = Math.floor(easeOutProgress * (end - start) + start)
    if (progress < 1) {
      window.requestAnimationFrame(step)
    } else {
      animatedTotals.value[key] = end
    }
  }
  window.requestAnimationFrame(step)
}

const triggerNumbersAnimation = () => {
  Object.keys(totals.value).forEach(key => {
    animateValue(key, 0, totals.value[key], 1200)
  })
}

// ======= UPDATED STAT CARDS (SHOWING ANIMATED VALUES) =======
const statCards = computed(() => [
  { 
    label: 'Total Users', 
    value: animatedTotals.value.users.toLocaleString(), 
    icon: 'pi pi-users', 
    bg: 'bg-blue-50/80 border border-blue-100/50', 
    text: 'text-blue-600',
    trend: '+12%', 
    isPositive: true 
  },
  { 
    label: 'Teachers', 
    value: animatedTotals.value.teachers.toLocaleString(), 
    icon: 'pi pi-id-card', 
    bg: 'bg-amber-50/80 border border-amber-100/50', 
    text: 'text-amber-600',
    trend: '+3', 
    isPositive: true 
  },
  { 
    label: 'Students', 
    value: animatedTotals.value.students.toLocaleString(), 
    icon: 'pi pi-graduation-cap', 
    bg: 'bg-emerald-50/80 border border-emerald-100/50', 
    text: 'text-emerald-600',
    trend: '+8%', 
    isPositive: true 
  },
  { 
    label: 'Classes', 
    value: animatedTotals.value.classes.toLocaleString(), 
    icon: 'pi pi-building', 
    bg: 'bg-indigo-50/80 border border-indigo-100/50', 
    text: 'text-indigo-600',
    trend: null 
  },
  { 
    label: 'Subjects', 
    value: animatedTotals.value.subjects.toLocaleString(), 
    icon: 'pi pi-book', 
    bg: 'bg-rose-50/80 border border-rose-100/50', 
    text: 'text-rose-600',
    trend: null 
  },
  { 
    label: 'Faculties', 
    value: animatedTotals.value.faculties.toLocaleString(), 
    icon: 'pi pi-sitemap', 
    bg: 'bg-violet-50/80 border border-violet-100/50', 
    text: 'text-violet-600',
    trend: null 
  },
])

const usersByRole = computed(() => {
  const max = totals.value.users || 1
  return usersByRoleRaw.value.map(row => ({
    role: row.role,
    count: row.count,
    pct: Math.round((row.count / max) * 100),
    color: ROLE_COLORS[row.role] || '#898781',
  }))
})

const activePct = computed(() => {
  const total = statusBreakdown.value.active + statusBreakdown.value.inactive
  return total ? Math.round((statusBreakdown.value.active / total) * 100) : 0
})

const barHeight = (value) => {
  const max = Math.max(...signupsLast7Days.value.map(d => d.count), 1)
  return Math.max((value / max) * 100, 4)
}

const roleBadgeClass = (roleName) => {
  switch (roleName) {
    case 'Admin': return 'bg-indigo-50 text-indigo-700 border-indigo-200/80'
    case 'Teacher': return 'bg-blue-50 text-blue-700 border-blue-200/80'
    case 'Student': return 'bg-emerald-50 text-emerald-700 border-emerald-200/80'
    default: return 'bg-slate-50 text-slate-600 border-slate-200/80'
  }
}

const relativeTime = (isoDate) => {
  if (!isoDate) return ''
  const diffMs = Date.now() - new Date(isoDate).getTime()
  const diffMins = Math.floor(diffMs / 60000)
  if (diffMins < 1) return 'just now'
  if (diffMins < 60) return `${diffMins}m ago`
  const diffHours = Math.floor(diffMins / 60)
  if (diffHours < 24) return `${diffHours}h ago`
  const diffDays = Math.floor(diffHours / 24)
  if (diffDays < 30) return `${diffDays}d ago`
  return new Date(isoDate).toLocaleDateString()
}

const fetchDashboardData = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/dashboard-stats')
    totals.value = data.totals
    usersByRoleRaw.value = data.users_by_role
    statusBreakdown.value = data.status_breakdown
    signupsLast7Days.value = data.signups_last_7_days
    recentUsers.value = data.recent_users

    // ចាប់ផ្តើម Run Animation ពេលទទួលបាន Data
    triggerNumbersAnimation()
  } catch (err) {
    error.value = extractError(err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>