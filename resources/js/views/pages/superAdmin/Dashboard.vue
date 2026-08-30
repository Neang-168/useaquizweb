<template>
  <div class="space-y-6 w-full pb-10">

    <!-- Error state -->
    <div v-if="error" class="p-4 bg-rose-50/80 backdrop-blur-md border border-rose-200/80 rounded-2xl text-sm text-rose-700 flex items-center gap-3 shadow-xs">
      <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
        <i class="pi pi-exclamation-triangle text-rose-600"></i>
      </div>
      <span class="font-medium">{{ error }}</span>
    </div>

    <!-- ======= KPI CONTAINER (TOP 4 BOXES + BOTTOM 2 WIDE BOXES) ======= -->
    <div class="space-y-4 w-full">
      
      <!-- Top Row: 4 Boxes (Total Users, Teachers, Students, Classes) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
        <div 
          v-for="card in topStatCards" 
          :key="card.label"
          class="group relative bg-white p-4.5 rounded-2xl border border-slate-100/80 shadow-xs hover:border-[#63c7df] hover:shadow-md hover:-translate-y-1 transition-all duration-300 ease-out cursor-pointer overflow-hidden flex flex-col justify-between"
        >
          <div class="absolute -right-3 -top-3 w-16 h-16 bg-slate-900/[0.03] rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>

          <div class="flex items-center justify-between mb-3 relative">
            <div 
              class="w-10 h-10 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-105 shadow-2xs"
              :class="[card.bg, card.text]"
            >
              <i :class="card.icon" class="text-base"></i>
            </div>

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

          <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-slate-200/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </div>
      </div>

      <!-- Bottom Row: 2 Wide Boxes (Subjects, Faculties) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
        <div 
          v-for="card in bottomStatCards" 
          :key="card.label"
          class="group relative bg-white p-4.5 rounded-2xl border border-slate-100/80 shadow-xs hover:border-[#63c7df] hover:shadow-md hover:-translate-y-1 transition-all duration-300 ease-out cursor-pointer overflow-hidden flex items-center justify-between"
        >
          <div class="absolute -right-3 -top-3 w-20 h-20 bg-slate-900/[0.03] rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>

          <div class="flex items-center gap-4 relative z-10">
            <div 
              class="w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-105 shadow-2xs shrink-0"
              :class="[card.bg, card.text]"
            >
              <i :class="card.icon" class="text-xl"></i>
            </div>
            <div>
              <p class="text-[11px] font-bold text-[#002060] uppercase tracking-wider mb-0.5">
                {{ card.label }}
              </p>
              <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight m-0 flex items-center">
                <span v-if="loading" class="inline-block w-12 h-7 bg-slate-100 rounded-lg animate-pulse"></span>
                <span 
                  v-else 
                  class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 bg-clip-text text-transparent font-mono"
                >
                  {{ card.value }}
                </span>
              </h2>
            </div>
          </div>

          <div class="hidden sm:block text-right relative z-10 pr-2">
            <span class="text-xs text-slate-400 font-medium">System Module</span>
            <div class="text-[10px] text-slate-300 font-mono mt-0.5">Active & Configured</div>
          </div>

          <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-slate-200/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </div>
      </div>

    </div>

    <!-- ======= CHARTS ROW ======= -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 w-full">

      <!-- Users by Role (Horizontal Bar List) -->
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

      <!-- ======= MAIN GRAPH (LINE/AREA VS CYCLE GRAPH WITH ANIMATION) ======= -->
      <div class="lg:col-span-3 p-6 bg-white rounded-2xl border border-slate-100/80 shadow-xs flex flex-col justify-between">
        
        <!-- Header & Chart Type Toggle Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
          <div>
            <h3 class="text-base font-bold text-[#002060] m-0">User Statistics</h3>
            <p class="text-xs text-slate-400 m-0 mt-0.5">Visual representation of user accounts & roles</p>
          </div>
          
          <!-- Filter / View Switcher Buttons -->
          <div class="inline-flex p-1 bg-slate-100/80 rounded-xl gap-1 border border-slate-200/50 self-start sm:self-auto">
            <button 
              @click="chartViewMode = 'line'"
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-300 flex items-center gap-1.5"
              :class="chartViewMode === 'line' ? 'bg-[#002060] text-white shadow-xs' : 'text-slate-500 hover:text-[#002060]'"
            >
              <i class="pi pi-chart-line text-[11px]"></i>
              <span>Trend Line</span>
            </button>
            <button 
              @click="chartViewMode = 'cycle'"
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-300 flex items-center gap-1.5"
              :class="chartViewMode === 'cycle' ? 'bg-[#002060] text-white shadow-xs' : 'text-slate-500 hover:text-[#002060]'"
            >
              <i class="pi pi-chart-pie text-[11px]"></i>
              <span>Cycle Graph</span>
            </button>
          </div>
        </div>

        <div v-if="loading" class="h-64 flex items-center justify-center">
          <div class="w-full h-56 bg-slate-100/80 rounded-xl animate-pulse"></div>
        </div>

        <!-- GRAPH VIEW CONTAINER WITH SMOOTH FADE ANIMATION -->
        <div v-else class="relative min-h-[250px] w-full flex items-center justify-center">

          <!-- VIEW 1: LINE / AREA GRAPH (UI MATCHING YOUR ATTACHED DESIGN) -->
          <transition name="fade-slide" mode="out-in">
            <div v-if="chartViewMode === 'line'" key="line-chart" class="w-full">
              <div class="relative w-full h-56">
                <!-- Grid Lines (Horizontal Dotted Lines like image) -->
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pl-9 pb-6">
                  <div v-for="i in 4" :key="i" class="w-full border-b border-dashed border-slate-200/80"></div>
                </div>

                <!-- Y-Axis Labels -->
                <div class="absolute left-0 top-0 bottom-6 flex flex-col justify-between text-[10px] font-mono font-semibold text-slate-400 select-none">
                  <span>{{ chartYMax }}</span>
                  <span>{{ Math.round(chartYMax * 0.66) }}</span>
                  <span>{{ Math.round(chartYMax * 0.33) }}</span>
                  <span>0</span>
                </div>

                <!-- SVG Area & Line Rendering -->
                <div class="ml-9 h-48 relative">
                  <svg class="w-full h-full overflow-visible" viewBox="0 0 500 150" preserveAspectRatio="none">
                    <defs>
                      <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25" />
                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0" />
                      </linearGradient>
                    </defs>

                    <!-- Background Soft Shadow Path (Lower secondary line like image) -->
                    <path :d="secondaryLinePath" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-dasharray="4,4" opacity="0.7" />

                    <!-- Area Fill -->
                    <path :d="areaPath" fill="url(#areaGrad)" />

                    <!-- Main Trend Line with Smooth Animation -->
                    <path 
                      :d="linePath" 
                      fill="none" 
                      stroke="#2563eb" 
                      stroke-width="3" 
                      stroke-linecap="round" 
                      stroke-linejoin="round"
                      class="transition-all duration-500 ease-in-out"
                    />

                    <!-- Data Points & Interactive Tooltip -->
                    <g v-for="(pt, idx) in chartPoints" :key="idx" class="group cursor-pointer">
                      <circle 
                        :cx="pt.x" 
                        :cy="pt.y" 
                        r="5" 
                        fill="#ffffff" 
                        stroke="#2563eb" 
                        stroke-width="2.5" 
                        class="transition-all duration-200 group-hover:r-7 group-hover:stroke-blue-700" 
                      />
                      <circle :cx="pt.x" :cy="pt.y" r="2" fill="#2563eb" />
                    </g>
                  </svg>

                  <!-- Active Floating Tooltip (Matching Image Dark Card Popup) -->
                  <div 
                    v-if="hoveredPoint" 
                    class="absolute pointer-events-none transition-all duration-200 ease-out z-20"
                    :style="{ left: `calc(${(hoveredPoint.x / 500) * 100}% - 40px)`, top: `${(hoveredPoint.y / 150) * 100 - 25}%` }"
                  >
                    <div class="bg-slate-900 text-white text-[11px] font-mono px-2.5 py-1 rounded-lg shadow-xl border border-slate-700 flex items-center gap-1.5 whitespace-nowrap">
                      <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
                      <span>{{ hoveredPoint.count }} users</span>
                    </div>
                  </div>
                </div>

                <!-- X-Axis Labels -->
                <div class="ml-9 flex justify-between items-center text-[11px] text-slate-400 font-medium font-mono pt-2">
                  <span 
                    v-for="(day, idx) in signupsLast7Days" 
                    :key="day.date"
                    @mouseenter="hoveredPoint = chartPoints[idx]"
                    @mouseleave="hoveredPoint = null"
                    class="cursor-pointer hover:text-blue-600 transition-colors"
                  >
                    {{ day.label }}
                  </span>
                </div>
              </div>
            </div>

            <!-- VIEW 2: CYCLE GRAPH (DOUGHNUT / PIE GRAPH WITH INNER STATS) -->
            <div v-else key="cycle-chart" class="w-full flex flex-col md:flex-row items-center justify-around gap-6 py-2">
              
              <!-- SVG Donut Cycle Chart -->
              <div class="relative w-48 h-48 flex items-center justify-center shrink-0">
                <svg class="w-full h-full transform -rotate-90 overflow-visible" viewBox="0 0 100 100">
                  <circle 
                    v-for="(slice, index) in cycleGraphSlices" 
                    :key="index"
                    cx="50" 
                    cy="50" 
                    r="38" 
                    fill="transparent" 
                    :stroke="slice.color" 
                    stroke-width="14" 
                    :stroke-dasharray="`${slice.strokeLength} ${100 - slice.strokeLength}`"
                    :stroke-dashoffset="-slice.strokeOffset"
                    class="transition-all duration-1000 ease-out hover:opacity-85 cursor-pointer stroke-round"
                  />
                </svg>

                <!-- Center Total Counter inside Donut -->
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                  <span class="text-2xl font-black text-slate-800 font-mono tracking-tight">
                    {{ animatedTotals.users }}
                  </span>
                  <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Total Users</span>
                </div>
              </div>

              <!-- Cycle Graph Legend (Total User, Teacher, Student, Admin) -->
              <div class="grid grid-cols-2 gap-3 w-full max-w-xs">
                <div 
                  v-for="item in cycleRolesSummary" 
                  :key="item.label"
                  class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:shadow-2xs transition-all duration-200"
                >
                  <div class="flex items-center gap-1.5 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: item.color }"></span>
                    <span class="text-xs font-semibold text-slate-600">{{ item.label }}</span>
                  </div>
                  <div class="text-base font-extrabold text-slate-800 font-mono pl-4">
                    {{ item.value }} <span class="text-[10px] text-slate-400 font-normal">({{ item.pct }}%)</span>
                  </div>
                </div>
              </div>

            </div>
          </transition>

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

// Fixed categorical order & color themes
const ROLE_COLORS = {
  'Admin': '#4f46e5',    // Indigo
  'Teacher': '#f59e0b',  // Amber
  'Student': '#10b981',  // Emerald
}

const loading = ref(true)
const error = ref('')

// Interactive Chart Controls
const chartViewMode = ref('line') // 'line' | 'cycle'
const hoveredPoint = ref(null)

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
    const easeOutProgress = 1 - Math.pow(1 - progress, 3)
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

// ======= KPI STAT CARDS SPLIT (4 TOP + 2 BOTTOM WIDE) =======
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

const topStatCards = computed(() => statCards.value.slice(0, 4))
const bottomStatCards = computed(() => statCards.value.slice(4, 6))

// ======= TREND LINE / AREA SVG COMPUTATIONS =======
const chartYMax = computed(() => {
  const max = Math.max(...signupsLast7Days.value.map(d => d.count), 5)
  return Math.ceil(max / 5) * 5
})

const chartPoints = computed(() => {
  const data = signupsLast7Days.value
  if (!data || data.length === 0) return []

  const width = 500
  const height = 130
  const maxVal = chartYMax.value
  const paddingY = 10
  const stepX = width / (data.length - 1 || 1)

  return data.map((d, index) => {
    const x = index * stepX
    const y = height - ((d.count / maxVal) * (height - paddingY * 2)) - paddingY
    return { x, y, count: d.count, label: d.label }
  })
})

const linePath = computed(() => {
  const pts = chartPoints.value
  if (pts.length === 0) return ''
  return pts.reduce((acc, pt, i) => (i === 0 ? `M ${pt.x},${pt.y}` : `${acc} L ${pt.x},${pt.y}`), '')
})

const secondaryLinePath = computed(() => {
  const pts = chartPoints.value
  if (pts.length === 0) return ''
  return pts.reduce((acc, pt, i) => {
    const shiftedY = Math.min(135, pt.y + 18)
    return i === 0 ? `M ${pt.x},${shiftedY}` : `${acc} L ${pt.x},${shiftedY}`
  }, '')
})

const areaPath = computed(() => {
  const pts = chartPoints.value
  if (pts.length === 0) return ''
  const firstX = pts[0].x
  const lastX = pts[pts.length - 1].x
  const bottomY = 150
  return `${linePath.value} L ${lastX},${bottomY} L ${firstX},${bottomY} Z`
})

// ======= CYCLE GRAPH (DONUT) COMPUTATIONS =======
const cycleRolesSummary = computed(() => {
  const totalUsers = totals.value.users || 1
  
  const teacherCount = totals.value.teachers || 0
  const studentCount = totals.value.students || 0
  
  // Extract Admin or other role counts
  const adminObj = usersByRoleRaw.value.find(r => r.role === 'Admin')
  const adminCount = adminObj ? adminObj.count : 0

  return [
    { label: 'Total Users', value: totals.value.users, pct: 100, color: '#2563eb' },
    { label: 'Teachers', value: teacherCount, pct: Math.round((teacherCount / totalUsers) * 100), color: ROLE_COLORS['Teacher'] },
    { label: 'Students', value: studentCount, pct: Math.round((studentCount / totalUsers) * 100), color: ROLE_COLORS['Student'] },
    { label: 'Admins', value: adminCount, pct: Math.round((adminCount / totalUsers) * 100), color: ROLE_COLORS['Admin'] },
  ]
})

// Generate SVG stroke-dash offset for donut chart segments
const cycleGraphSlices = computed(() => {
  const totalUsers = totals.value.users || 1
  const radius = 38
  const circumference = 2 * Math.PI * radius // ~238.76

  // Take sub-roles for pie segments
  const roles = [
    { name: 'Teachers', count: totals.value.teachers || 0, color: ROLE_COLORS['Teacher'] },
    { name: 'Students', count: totals.value.students || 0, color: ROLE_COLORS['Student'] },
    { name: 'Admins', count: (usersByRoleRaw.value.find(r => r.role === 'Admin')?.count) || 0, color: ROLE_COLORS['Admin'] }
  ]

  let accumulatedOffset = 0
  return roles.map(r => {
    const pct = (r.count / totalUsers)
    const strokeLength = pct * 238.76
    const slice = {
      color: r.color,
      strokeLength: (strokeLength / 238.76) * 100,
      strokeOffset: (accumulatedOffset / 238.76) * 100
    }
    accumulatedOffset += strokeLength
    return slice
  })
})

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

const roleBadgeClass = (roleName) => {
  switch (roleName) {
    case 'Admin': return 'bg-indigo-50 text-indigo-700 border-indigo-200/80'
    case 'Teacher': return 'bg-amber-50 text-amber-700 border-amber-200/80'
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
<style scoped>
/* Smooth transition setup for switching graph view modes */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.35s ease-in-out;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(8px) scale(0.98);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.98);
}
</style>