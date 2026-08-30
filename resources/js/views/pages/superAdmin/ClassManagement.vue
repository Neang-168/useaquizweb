<template>
  <div class="space-y-6 w-full pb-10">

    <!-- 1. Header Section (Single Class Header) -->
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-start gap-4">
        <div class="w-14 h-14 rounded-2xl bg-[#002060] text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-md">
          {{ classData.code }}
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-extrabold text-slate-800 m-0">{{ classData.name }}</h1>
            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-extrabold rounded-md border border-emerald-100">
              Active Class
            </span>
          </div>
          <p class="text-xs text-slate-400 m-0 mt-1">
            មុខវិជ្ជា៖ <span class="font-semibold text-slate-600">{{ classData.subject }}</span> | 
            គ្រូបង្រៀន៖ <span class="font-semibold text-slate-600">{{ classData.teacher }}</span>
          </p>
        </div>
      </div>

      <!-- Action Buttons for this Class -->
      <div class="flex items-center gap-2">
        <button
          @click="openSubjectsDialog"
          class="py-2 px-3.5 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200/60 hover:border-indigo-300 rounded-xl transition-all inline-flex items-center gap-1.5 shadow-2xs cursor-pointer"
        >
          <i class="fa-solid fa-gear text-[11px]"></i>
          <span>Manage Settings</span>
        </button>

        <button 
          @click="showAnalyticsModal = true"
          class="py-2 px-3.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200/60 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 cursor-pointer"
        >
          <i class="pi pi-chart-scatter text-xs"></i>
          <span>Spider Analytics</span>
        </button>

        <button 
          @click="showExportModal = true"
          class="py-2 px-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 shadow-2xs cursor-pointer"
        >
          <i class="pi pi-download text-xs"></i>
          <span>Export Results</span>
        </button>
      </div>
    </div>

    <!-- 2. KPI Summary Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider m-0">សិស្សសរុប</p>
          <h2 class="text-2xl font-black text-slate-800 font-mono m-0 mt-1">{{ classData.total_students }} នាក់</h2>
        </div>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
          <i class="pi pi-users text-base"></i>
        </div>
      </div>

      <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider m-0">Quizzes សរុប</p>
          <h2 class="text-2xl font-black text-purple-700 font-mono m-0 mt-1">{{ classData.total_quizzes }} Active</h2>
        </div>
        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
          <i class="pi pi-file text-base"></i>
        </div>
      </div>

      <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider m-0">ជាប់ / ធ្លាក់</p>
          <h2 class="text-2xl font-black text-emerald-600 font-mono m-0 mt-1">
            {{ classData.stats.passed }} <span class="text-slate-400 text-sm font-normal">/</span> <span class="text-rose-600">{{ classData.stats.failed }}</span>
          </h2>
        </div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
          <i class="pi pi-check-circle text-base"></i>
        </div>
      </div>

      <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider m-0">ពិន្ទុមធ្យម</p>
          <h2 class="text-2xl font-black text-amber-600 font-mono m-0 mt-1">{{ classData.stats.avg_score }}/100</h2>
        </div>
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
          <i class="pi pi-star text-base"></i>
        </div>
      </div>
    </div>

    <!-- 3. Student List Table inside Class -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-sm font-bold text-slate-800 m-0">បញ្ជីឈ្មោះសិស្សក្នុងថ្នាក់</h3>
          <p class="text-xs text-slate-400 m-0 mt-0.5">បញ្ជីសិស្សទាំងអស់ដែលបានចុះឈ្មោះក្នុងថ្នាក់ {{ classData.code }}</p>
        </div>
        <div class="relative w-56">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input 
            v-model="studentSearch"
            type="text" 
            placeholder="ស្វែងរកឈ្មោះសិស្ស..." 
            class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 focus:outline-none focus:border-blue-500"
          />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
              <th class="py-3 px-3">Student ID</th>
              <th class="py-3 px-3">ឈ្មោះសិស្ស</th>
              <th class="py-3 px-3 text-center">Quiz Completed</th>
              <th class="py-3 px-3 text-center">ពិន្ទុមធ្យម</th>
              <th class="py-3 px-3 text-center">ស្ថានភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr v-for="std in filteredStudents" :key="std.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3 px-3 font-mono font-bold text-slate-500">{{ std.student_id }}</td>
              <td class="py-3 px-3 font-semibold text-slate-800">{{ std.name }}</td>
              <td class="py-3 px-3 text-center font-mono">{{ std.quiz_completed }}/{{ classData.total_quizzes }}</td>
              <td class="py-3 px-3 text-center font-mono font-extrabold text-blue-600">{{ std.score }}</td>
              <td class="py-3 px-3 text-center">
                <span 
                  class="px-2.5 py-0.5 rounded-md text-[10px] font-bold"
                  :class="std.score >= 50 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'"
                >
                  {{ std.score >= 50 ? 'Passed' : 'Failed' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- SPIDER GRAPH MODAL -->
    <Dialog v-model:visible="showAnalyticsModal" modal header="Class Performance Overview (Spider Graph)" :style="{ width: '550px' }" class="rounded-2xl">
      <div class="space-y-4 pt-2">
        <div class="relative w-full h-64 flex items-center justify-center py-4 bg-slate-900/5 rounded-2xl border border-slate-100">
          <svg class="w-60 h-60 overflow-visible" viewBox="0 0 200 200">
            <polygon v-for="level in [0.2, 0.4, 0.6, 0.8, 1.0]" :key="level" :points="getSpiderWebPoints(level)" fill="none" stroke="#cbd5e1" stroke-width="1" stroke-dasharray="2,2"/>
            <line v-for="(axis, i) in spiderAxes" :key="i" x1="100" y1="100" :x2="axis.x" :y2="axis.y" stroke="#94a3b8" stroke-width="1"/>
            <polygon :points="spiderDataPoints" fill="rgba(37, 99, 235, 0.35)" stroke="#2563eb" stroke-width="2.5" />
            <circle v-for="(pt, i) in spiderDataDotCoords" :key="i" :cx="pt.x" :cy="pt.y" r="4" fill="#2563eb" stroke="#ffffff" stroke-width="2" />
            <text v-for="(axis, i) in spiderAxes" :key="'label-'+i" :x="axis.labelX" :y="axis.labelY" text-anchor="middle" dominant-baseline="middle" class="text-[9px] font-extrabold fill-slate-700 font-mono">
              {{ axis.name }} ({{ axis.value }})
            </text>
          </svg>
        </div>
      </div>
    </Dialog>

    <!-- EXPORT QUIZ MODAL -->
    <Dialog v-model:visible="showExportModal" modal header="Export Quiz Results" :style="{ width: '450px' }" class="rounded-2xl">
      <div class="space-y-4 pt-2">
        <p class="text-xs text-slate-500 m-0">ជ្រើសរើស Quiz ដើម្បីទាញយកលទ្ធផលសិស្សជា CSV សម្រាប់ថ្នាក់ {{ classData.code }}</p>
        <div class="space-y-2">
          <div 
            v-for="quiz in staticQuizzes" 
            :key="quiz.id"
            @click="selectedQuizId = quiz.id"
            class="p-3 border rounded-xl flex items-center justify-between cursor-pointer transition-all"
            :class="selectedQuizId === quiz.id ? 'border-blue-500 bg-blue-50/50' : 'border-slate-100 hover:bg-slate-50'"
          >
            <span class="text-xs font-bold text-slate-800">{{ quiz.title }}</span>
            <i class="pi" :class="selectedQuizId === quiz.id ? 'pi-check-circle text-blue-600' : 'pi-circle text-slate-300'"></i>
          </div>
        </div>
        <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
          <button @click="showExportModal = false" class="px-3.5 py-1.5 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">បោះបង់</button>
          <button @click="handleExport" :disabled="!selectedQuizId" class="px-3.5 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold disabled:opacity-50">Download</button>
        </div>
      </div>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Dialog from 'primevue/dialog'

// Data សម្រាប់ថ្នាក់តែមួយគត់ (Single Class Object)
const classData = ref({
  id: 1,
  code: 'IT-301',
  name: 'Web Architecture & Vue 3',
  subject: 'Computer Science',
  teacher: 'Dr. Chan Sok',
  total_students: 35,
  total_quizzes: 4,
  stats: {
    passed: 30,
    failed: 5,
    avg_score: 82,
    pass_rate: 86,
    completion: 92,
    attendance: 88,
    engagement: 80
  }
})

// Static Student List
const studentsList = ref([
  { id: 1, student_id: 'STD-001', name: 'Chan Keo', quiz_completed: 4, score: 95 },
  { id: 2, student_id: 'STD-002', name: 'Sok Mean', quiz_completed: 4, score: 78 },
  { id: 3, student_id: 'STD-003', name: 'Vanna Eng', quiz_completed: 3, score: 42 },
  { id: 4, student_id: 'STD-004', name: 'Rithy Leng', quiz_completed: 4, score: 88 },
  { id: 5, student_id: 'STD-005', name: 'Bopha Tep', quiz_completed: 2, score: 35 }
])

// Static Quizzes
const staticQuizzes = ref([
  { id: 101, title: 'Midterm Assessment Quiz' },
  { id: 102, title: 'HTML & Tailwind Fundamentals' },
  { id: 103, title: 'Vue 3 & Composition API Test' }
])

const studentSearch = ref('')
const showAnalyticsModal = ref(false)
const showExportModal = ref(false)
const selectedQuizId = ref(null)

const filteredStudents = computed(() => {
  if (!studentSearch.value.trim()) return studentsList.value
  return studentsList.value.filter(s => 
    s.name.toLowerCase().includes(studentSearch.value.toLowerCase()) ||
    s.student_id.toLowerCase().includes(studentSearch.value.toLowerCase())
  )
})

// Actions
const openSubjectsDialog = () => {
  alert(`បើក Manage Settings សម្រាប់ថ្នាក់ ${classData.value.code}`)
}

const handleExport = () => {
  alert(`ទាញយក CSV សម្រាប់ Quiz ID: ${selectedQuizId.value}`)
  showExportModal.value = false
}

// Spider Radar Calculations
const spiderCategories = [
  { key: 'pass_rate', name: 'Pass Rate' },
  { key: 'avg_score', name: 'Avg Score' },
  { key: 'completion', name: 'Completion' },
  { key: 'attendance', name: 'Attendance' },
  { key: 'engagement', name: 'Engagement' },
]

const spiderAxes = computed(() => {
  const stats = classData.value.stats
  const total = spiderCategories.length
  const radius = 65

  return spiderCategories.map((cat, i) => {
    const angle = (Math.PI * 2 / total) * i - (Math.PI / 2)
    const val = stats[cat.key] || 0
    const x = 100 + radius * Math.cos(angle)
    const y = 100 + radius * Math.sin(angle)
    return { name: cat.name, value: val, x, y, labelX: 100 + (radius + 20) * Math.cos(angle), labelY: 100 + (radius + 20) * Math.sin(angle) }
  })
})

const getSpiderWebPoints = (level) => {
  const total = spiderCategories.length
  const radius = 65 * level
  return Array.from({ length: total }).map((_, i) => {
    const angle = (Math.PI * 2 / total) * i - (Math.PI / 2)
    return `${100 + radius * Math.cos(angle)},${100 + radius * Math.sin(angle)}`
  }).join(' ')
}

const spiderDataPoints = computed(() => {
  const stats = classData.value.stats
  const total = spiderCategories.length
  return spiderCategories.map((cat, i) => {
    const angle = (Math.PI * 2 / total) * i - (Math.PI / 2)
    const radius = 65 * (stats[cat.key] / 100)
    return `${100 + radius * Math.cos(angle)},${100 + radius * Math.sin(angle)}`
  }).join(' ')
})

const spiderDataDotCoords = computed(() => {
  const stats = classData.value.stats
  const total = spiderCategories.length
  return spiderCategories.map((cat, i) => {
    const angle = (Math.PI * 2 / total) * i - (Math.PI / 2)
    const radius = 65 * (stats[cat.key] / 100)
    return { x: 100 + radius * Math.cos(angle), y: 100 + radius * Math.sin(angle) }
  })
})
</script>