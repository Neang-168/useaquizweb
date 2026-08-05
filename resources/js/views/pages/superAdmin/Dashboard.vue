<template>
  <div class="space-y-6 w-full">

    <!-- Header Page -->
    <div>
      <h1 class="text-2xl font-bold text-slate-800 m-0">Welcome to Dashboard</h1>
      <p class="text-slate-500 text-sm mt-1 m-0">ទំព័រគ្រប់គ្រងព័ត៌មានទូទៅ (Dashboard Panel)</p>
    </div>

    <!-- Stat Cards (Grid 3 Columns) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">

      <div v-for="card in statCards" :key="card.label"
        class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-start justify-between">
        <div>
          <span class="text-xs font-bold text-amber-500 uppercase tracking-wider block mb-2">
            • {{ card.label }}
          </span>
          <h2 class="text-3xl font-bold text-slate-800 m-0">
            {{ loading ? '—' : card.value }}
          </h2>
          <p class="mt-2 mb-0 text-xs font-semibold flex items-center gap-1"
            :class="card.trend >= 0 ? 'text-emerald-600' : 'text-red-500'">
            <i :class="card.trend >= 0 ? 'pi pi-arrow-up' : 'pi pi-arrow-down'" class="text-[10px]"></i>
            {{ Math.abs(card.trend) }}% vs last month
          </p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
          <i :class="card.icon" class="text-lg"></i>
        </div>
      </div>

    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full">

      <!-- Course Completion Donut -->
      <div class="lg:col-span-1 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-sm font-bold text-slate-800 m-0">Course Completion</h3>
          <span class="text-xs text-slate-400 font-medium">This Month</span>
        </div>

        <div class="flex flex-col items-center">
          <div class="relative w-40 h-40 rounded-full flex items-center justify-center" :style="donutStyle">
            <div class="absolute w-28 h-28 bg-white rounded-full flex flex-col items-center justify-center">
              <span class="text-2xl font-bold text-slate-800">{{ stats.courseCompletionRate }}%</span>
              <span class="text-[11px] text-slate-400 font-medium">Completed</span>
            </div>
          </div>

          <div class="flex items-center gap-4 mt-6 text-xs font-semibold text-slate-500">
            <span class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
              Completed
            </span>
            <span class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-slate-200 inline-block"></span>
              In Progress
            </span>
          </div>
        </div>
      </div>

      <!-- Weekly Activity Bar Chart -->
      <div class="lg:col-span-2 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-sm font-bold text-slate-800 m-0">Weekly Activity</h3>
          <span class="text-xs text-slate-400 font-medium">Logins This Week</span>
        </div>

        <div class="flex items-end justify-between gap-3 h-48 px-1">
          <div v-for="day in weeklyActivity" :key="day.label"
            class="flex-1 flex flex-col items-center justify-end h-full gap-2">
            <span class="text-[11px] font-bold text-slate-600">{{ day.value }}</span>
            <div class="w-full max-w-[28px] rounded-t-md bg-blue-600 transition-all"
              :style="{ height: barHeight(day.value) + '%' }"></div>
            <span class="text-[11px] text-slate-400 font-medium">{{ day.label }}</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Recent Activity -->
    <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm w-full">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-slate-800 m-0">Recent Activity</h3>
        <span class="text-xs text-blue-600 font-semibold cursor-pointer hover:underline">View all</span>
      </div>

      <div v-if="loading" class="text-sm text-slate-400 py-6 text-center">Loading activity…</div>

      <ul v-else class="m-0 p-0 list-none divide-y divide-slate-100">
        <li v-for="item in recentActivity" :key="item.id" class="py-3 flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-indigo-50 text-blue-600 flex items-center justify-center shrink-0">
            <i :class="item.icon" class="text-sm"></i>
          </div>
          <div class="min-w-0 flex-1">
            <p class="m-0 text-sm font-semibold text-slate-700 truncate">{{ item.title }}</p>
            <p class="m-0 text-xs text-slate-400">{{ item.time }}</p>
          </div>
        </li>
      </ul>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const loading = ref(true)

const stats = ref({
  totalUsers: 0,
  activeCourses: 0,
  systemRoles: 0,
  courseCompletionRate: 0,
  usersTrend: 0,
  coursesTrend: 0,
  rolesTrend: 0
})

const weeklyActivity = ref([
  { label: 'Mon', value: 0 },
  { label: 'Tue', value: 0 },
  { label: 'Wed', value: 0 },
  { label: 'Thu', value: 0 },
  { label: 'Fri', value: 0 },
  { label: 'Sat', value: 0 },
  { label: 'Sun', value: 0 }
])

const recentActivity = ref([])

// Stat cards derived from stats.value — fixes the previous hard-coded 120/15/4
const statCards = computed(() => [
  { label: 'TOTAL USERS', value: stats.value.totalUsers, trend: stats.value.usersTrend, icon: 'pi pi-users' },
  { label: 'ACTIVE COURSES', value: stats.value.activeCourses, trend: stats.value.coursesTrend, icon: 'pi pi-book' },
  { label: 'SYSTEM ROLES', value: stats.value.systemRoles, trend: stats.value.rolesTrend, icon: 'pi pi-shield' }
])

// CSS conic-gradient donut — no chart library needed
const donutStyle = computed(() => {
  const pct = stats.value.courseCompletionRate
  return {
    background: `conic-gradient(#2563eb ${pct}%, #e2e8f0 ${pct}% 100%)`
  }
})

// Scale bar heights relative to the week's max value
const barHeight = (value) => {
  const max = Math.max(...weeklyActivity.value.map(d => d.value), 1)
  return Math.max((value / max) * 100, 4)
}

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/dashboard-stats')
    const data = await response.json()

    stats.value = {
      totalUsers: data.totalUsers ?? 0,
      activeCourses: data.activeCourses ?? 0,
      systemRoles: data.systemRoles ?? 0,
      courseCompletionRate: data.courseCompletionRate ?? 0,
      usersTrend: data.usersTrend ?? 0,
      coursesTrend: data.coursesTrend ?? 0,
      rolesTrend: data.rolesTrend ?? 0
    }
    weeklyActivity.value = data.weeklyActivity ?? weeklyActivity.value
    recentActivity.value = data.recentActivity ?? []
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>