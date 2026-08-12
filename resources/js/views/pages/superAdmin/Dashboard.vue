<template>
  <div class="space-y-6 w-full pb-10">

    <!-- ======= HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/60 backdrop-blur-md p-5 rounded-2xl border border-slate-200/60 shadow-xs">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 m-0">Welcome back{{ firstName ? `, ${firstName}` : '' }} 👋</h1>
        <p class="text-slate-500 text-sm mt-1 m-0">ទំព័រគ្រប់គ្រងព័ត៌មានទូទៅ &mdash; here's what's happening across USEA Quiz today.</p>
      </div>
      <span class="text-xs font-semibold text-slate-400 shrink-0">{{ todayLabel }}</span>
    </div>

    <!-- Error state -->
    <div v-if="error" class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-sm text-rose-700 flex items-center gap-2">
      <i class="pi pi-exclamation-triangle"></i>
      {{ error }}
    </div>

    <!-- ======= KPI ROW ======= -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 w-full">
      <div v-for="card in statCards" :key="card.label"
        class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3"
          :class="[card.bg, card.text]">
          <i :class="card.icon" class="text-base"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-800 m-0 leading-none">
          <span v-if="loading" class="inline-block w-10 h-6 bg-slate-100 rounded animate-pulse"></span>
          <span v-else>{{ card.value }}</span>
        </h2>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-2 mb-0">{{ card.label }}</p>
      </div>
    </div>

    <!-- ======= CHARTS ROW ======= -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 w-full">

      <!-- Users by Role (part-to-whole, horizontal bars, direct-labeled) -->
      <div class="lg:col-span-2 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-sm font-bold text-slate-800 m-0">Users by Role</h3>
          <span class="text-xs text-slate-400 font-medium">{{ totals.users }} total</span>
        </div>

        <div v-if="loading" class="space-y-3">
          <div v-for="n in 4" :key="n" class="h-7 bg-slate-100 rounded-lg animate-pulse"></div>
        </div>

        <div v-else-if="usersByRole.length === 0" class="text-center text-xs text-slate-400 py-10">
          No users yet.
        </div>

        <div v-else class="space-y-3">
          <div v-for="role in usersByRole" :key="role.role" class="group">
            <div class="flex items-center justify-between mb-1 text-xs font-semibold">
              <span class="text-slate-700">{{ role.role }}</span>
              <span class="text-slate-500">{{ role.count }} · {{ role.pct }}%</span>
            </div>
            <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden" :title="`${role.role}: ${role.count} users (${role.pct}%)`">
              <div class="h-full rounded-full transition-all duration-500"
                :style="{ width: role.pct + '%', backgroundColor: role.color }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- New Signups, last 7 days (magnitude, sequential blue) -->
      <div class="lg:col-span-3 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-sm font-bold text-slate-800 m-0">New Accounts</h3>
          <span class="text-xs text-slate-400 font-medium">Last 7 days</span>
        </div>

        <div v-if="loading" class="h-48 flex items-end gap-3 px-1">
          <div v-for="n in 7" :key="n" class="flex-1 bg-slate-100 rounded-t-md animate-pulse" :style="{ height: (30 + n * 6) + '%' }"></div>
        </div>

        <div v-else class="flex items-end justify-between gap-3 h-48 px-1">
          <div v-for="day in signupsLast7Days" :key="day.date"
            class="flex-1 flex flex-col items-center justify-end h-full gap-2"
            :title="`${day.date}: ${day.count} new account${day.count === 1 ? '' : 's'}`">
            <span class="text-[11px] font-bold text-slate-600">{{ day.count }}</span>
            <div class="w-full max-w-[28px] rounded-t-md bg-[#2a78d6] transition-all"
              :style="{ height: barHeight(day.count) + '%' }"></div>
            <span class="text-[11px] text-slate-400 font-medium">{{ day.label }}</span>
          </div>
        </div>
      </div>

    </div>

    <!-- ======= STATUS + RECENT USERS ======= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full">

      <!-- Account Status meter -->
      <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col">
        <h3 class="text-sm font-bold text-slate-800 m-0 mb-5">Account Status</h3>

        <div v-if="loading" class="h-3 w-full bg-slate-100 rounded-full animate-pulse"></div>
        <template v-else>
          <div class="h-3 w-full bg-rose-100 rounded-full overflow-hidden" :title="`${activePct}% active`">
            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="{ width: activePct + '%' }"></div>
          </div>
          <div class="flex items-center justify-between mt-4 text-xs font-semibold">
            <span class="flex items-center gap-1.5 text-slate-600">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
              Active &middot; {{ statusBreakdown.active }}
            </span>
            <span class="flex items-center gap-1.5 text-slate-600">
              <span class="w-2.5 h-2.5 rounded-full bg-rose-300 inline-block"></span>
              Inactive &middot; {{ statusBreakdown.inactive }}
            </span>
          </div>
        </template>

        <p class="text-xs text-slate-400 mt-5 mb-0 leading-relaxed">
          {{ activePct }}% of all accounts are currently active across every role.
        </p>
      </div>

      <!-- Recent Users -->
      <div class="lg:col-span-2 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-bold text-slate-800 m-0">Recently Added</h3>
          <router-link :to="{ name: 'admin.users' }" class="text-xs text-blue-600 font-semibold hover:underline no-underline">View all</router-link>
        </div>

        <div v-if="loading" class="space-y-3">
          <div v-for="n in 5" :key="n" class="h-10 bg-slate-100 rounded-lg animate-pulse"></div>
        </div>

        <div v-else-if="recentUsers.length === 0" class="text-center text-xs text-slate-400 py-10">
          No users yet.
        </div>

        <ul v-else class="m-0 p-0 list-none divide-y divide-slate-100">
          <li v-for="user in recentUsers" :key="user.id" class="py-3 flex items-center gap-3">
            <Avatar
              :image="user.avatar_url || undefined"
              :label="!user.avatar_url ? (user.name || '?').charAt(0).toUpperCase() : undefined"
              shape="circle"
              class="!bg-blue-100 !text-blue-700 font-bold shrink-0"
            />
            <div class="min-w-0 flex-1">
              <p class="m-0 text-sm font-semibold text-slate-700 truncate">{{ user.name }}</p>
              <p class="m-0 text-xs text-slate-400 truncate">{{ user.email }}</p>
            </div>
            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-md border inline-block tracking-tight shrink-0" :class="roleBadgeClass(user.role)">
              {{ user.role || 'No role' }}
            </span>
            <span class="text-[11px] text-slate-400 font-medium shrink-0 w-16 text-right">{{ relativeTime(user.created_at) }}</span>
          </li>
        </ul>
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
// in the app (see roleBadgeClass in Users.vue) so a role's color/badge never
// changes meaning between pages. Validated for CVD-safety as a set.
const ROLE_COLORS = {
  'Super Admin': '#2a78d6',
  'Admin': '#eb6834',
  'Staff': '#1baf7a',
  'Teacher': '#eda100',
  'Student': '#e87ba4',
}

const loading = ref(true)
const error = ref('')

const totals = ref({ users: 0, teachers: 0, students: 0, classes: 0, subjects: 0, faculties: 0 })
const usersByRoleRaw = ref([])
const statusBreakdown = ref({ active: 0, inactive: 0 })
const signupsLast7Days = ref([])
const recentUsers = ref([])

const firstName = computed(() => authUser.first_name || '')

const todayLabel = computed(() => new Date().toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))

const statCards = computed(() => [
  { label: 'Total Users', value: totals.value.users, icon: 'pi pi-users', bg: 'bg-blue-50', text: 'text-blue-600' },
  { label: 'Teachers', value: totals.value.teachers, icon: 'pi pi-id-card', bg: 'bg-amber-50', text: 'text-amber-600' },
  { label: 'Students', value: totals.value.students, icon: 'pi pi-graduation-cap', bg: 'bg-emerald-50', text: 'text-emerald-600' },
  { label: 'Classes', value: totals.value.classes, icon: 'pi pi-building', bg: 'bg-indigo-50', text: 'text-indigo-600' },
  { label: 'Subjects', value: totals.value.subjects, icon: 'pi pi-book', bg: 'bg-rose-50', text: 'text-rose-600' },
  { label: 'Faculties', value: totals.value.faculties, icon: 'pi pi-sitemap', bg: 'bg-violet-50', text: 'text-violet-600' },
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
    case 'Super Admin': return 'bg-purple-50 text-purple-700 border-purple-200'
    case 'Admin': return 'bg-indigo-50 text-indigo-700 border-indigo-200'
    case 'Staff': return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'Teacher': return 'bg-blue-50 text-blue-700 border-blue-200'
    case 'Student': return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    default: return 'bg-slate-50 text-slate-600 border-slate-200'
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
