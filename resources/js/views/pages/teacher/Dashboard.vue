<template>
  <div class="space-y-6 pb-10">
    <!-- 1. Top Header Profile Banner -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-indigo-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
      <!-- Background Decorative Blur -->
      <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 relative z-10">
        <div class="flex items-center gap-5">
          <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white text-2xl font-bold shadow-inner">
            {{ teacherInitial }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-xl sm:text-3xl font-extrabold tracking-tight">លោកគ្រូ {{ teacherName }}</h1>
              <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs px-2.5 py-0.5 rounded-full font-semibold">Active</span>
            </div>
            <p class="text-indigo-200 text-xs sm:text-sm mt-1">
              អត្តលេខ: <span class="font-mono text-white">TEA-2026-089</span> • ដេប៉ាតឺម៉ង់: វិទ្យាសាស្ត្រកុំព្យូទ័រ
            </p>
          </div>
        </div>

        <!-- Quick Action Buttons Header -->
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
          <button @click="openCreateQuizModal" class="flex-1 sm:flex-initial flex items-center justify-center gap-2 bg-indigo-500 hover:bg-indigo-400 text-white font-semibold px-4 py-2.5 rounded-xl shadow-lg transition-all active:scale-95 text-sm">
            <i class="pi pi-plus-circle"></i>
            <span>បង្កើត Quiz ថ្មី</span>
          </button>
          <!-- <button class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors">
            <i class="pi pi-bell"></i>
          </button> -->
        </div>
      </div>
    </div>

    <!-- 2. Detailed Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">ថ្នាក់ទទួលខុសត្រូវ</span>
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <i class="pi pi-building text-lg"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-extrabold text-slate-800">4 ថ្នាក់</h2>
          <p class="text-xs text-slate-500 mt-1">សិស្សសរុប <span class="font-semibold text-slate-700">142 នាក់</span></p>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Quiz កំពុងដំណើរការ</span>
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <i class="pi pi-spin pi-spinner text-lg"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-extrabold text-slate-800">2 Quiz</h2>
          <p class="text-xs text-emerald-600 font-medium mt-1">បិទការប្រឡងនៅថ្ងៃស្អែក</p>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">រង់ចាំការកែពិន្ទុ (Pending)</span>
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <i class="pi pi-exclamation-circle text-lg"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-extrabold text-amber-600">18 អត្ថបទ</h2>
          <p class="text-xs text-slate-500 mt-1">សំណួរប្រភេទ Essay / Short Answer</p>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">មធ្យមភាគពិន្ទុថ្នាក់ (Avg Rate)</span>
          <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <i class="pi pi-chart-line text-lg"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-extrabold text-slate-800">78.5%</h2>
          <p class="text-xs text-emerald-600 font-medium mt-1">↑ កើនឡើង 4.2% សប្តាហ៍នេះ</p>
        </div>
      </div>
    </div>

    <!-- 3. Main Operational Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left Column (2 Cols): Quizzes & Performance -->
      <div class="lg:col-span-2 space-y-6">

        <!-- Quizzes Table Section -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
            <div>
              <h3 class="text-base font-bold text-slate-800">កម្សាន្តប្រឡង និង Quiz ចុងក្រោយ</h3>
              <p class="text-xs text-slate-400">បញ្ជី Quiz ដែលបានបង្កើត និងស្ថានភាពបច្ចុប្បន្ន</p>
            </div>
            <div class="flex items-center gap-2">
              <button class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 font-medium hover:bg-slate-50">ទាំងអស់</button>
              <button class="text-xs px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 font-semibold">Active</button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                  <th class="py-3 px-3">ឈ្មោះ Quiz</th>
                  <th class="py-3 px-3">មុខវិជ្ជា & ថ្នាក់</th>
                  <th class="py-3 px-3 text-center">សិស្សបានធ្វើ</th>
                  <th class="py-3 px-3 text-center">ស្ថានភាព</th>
                  <th class="py-3 px-3 text-right">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody class="text-sm divide-y divide-slate-100">
                <tr v-for="quiz in recentQuizzes" :key="quiz.id" class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3.5 px-3">
                    <div class="font-semibold text-slate-800">{{ quiz.title }}</div>
                    <div class="text-[11px] text-slate-400">{{ quiz.questionsCount }} សំណួរ • {{ quiz.duration }} នាទី</div>
                  </td>
                  <td class="py-3.5 px-3">
                    <div class="text-slate-700 font-medium">{{ quiz.subject }}</div>
                    <div class="text-xs text-slate-400">{{ quiz.className }}</div>
                  </td>
                  <td class="py-3.5 px-3 text-center font-medium text-slate-700">
                    {{ quiz.submittedCount }}/{{ quiz.totalStudents }}
                  </td>
                  <td class="py-3.5 px-3 text-center">
                    <span :class="quiz.statusClass" class="text-xs font-bold px-2.5 py-1 rounded-full border">
                      {{ quiz.statusText }}
                    </span>
                  </td>
                  <td class="py-3.5 px-3 text-right">
                    <button class="text-slate-400 hover:text-indigo-600 p-1.5 rounded-lg hover:bg-indigo-50 transition-colors">
                      <i class="pi pi-ellipsis-v"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Student Performance Alert (សិស្សត្រូវការជំនួយបន្ថែម) -->
        <div class="bg-amber-50/50 rounded-2xl border border-amber-200/60 p-5">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center">
              <i class="pi pi-exclamation-triangle"></i>
            </div>
            <div>
              <h4 class="text-sm font-bold text-amber-900">សិស្សទទួលបានពិន្ទុទាប (Needs Attention)</h4>
              <p class="text-xs text-amber-700">សិស្សដែលមានពិន្ទុក្រោម 50% ក្នុង Midterm Exam ចុងក្រោយ</p>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
            <div class="p-3 bg-white rounded-xl border border-amber-100 flex items-center justify-between shadow-xs">
              <div>
                <div class="text-xs font-bold text-slate-800">សុក ជាតា</div>
                <div class="text-[11px] text-slate-400">M1-Class A • Web Dev</div>
              </div>
              <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100">42%</span>
            </div>
            <div class="p-3 bg-white rounded-xl border border-amber-100 flex items-center justify-between shadow-xs">
              <div>
                <div class="text-xs font-bold text-slate-800">ចាន់ វីរៈ</div>
                <div class="text-[11px] text-slate-400">M2-Class B • Database</div>
              </div>
              <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100">48%</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column (1 Col): Schedule & Quick Links -->
      <div class="space-y-6">

        <!-- Today's Schedule -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800">កាលវិភាគថ្ងៃនេះ</h3>
            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg">
              {{ currentDate }}
            </span>
          </div>

          <div class="space-y-3">
            <div v-for="schedule in todaySchedules" :key="schedule.id" class="p-4 rounded-xl border border-slate-100 hover:border-indigo-100 bg-slate-50/50 hover:bg-indigo-50/30 transition-all">
              <div class="flex justify-between items-start">
                <span class="text-xs font-bold text-indigo-600 bg-white border border-indigo-100 px-2 py-0.5 rounded-md">
                  {{ schedule.time }}
                </span>
                <span class="text-xs text-slate-400">បន្ទប់: {{ schedule.room }}</span>
              </div>
              <h4 class="text-sm font-bold text-slate-800 mt-2">{{ schedule.subject }}</h4>
              <p class="text-xs text-slate-500 mt-0.5">ថ្នាក់: {{ schedule.className }}</p>
            </div>
          </div>
        </div>

        <!-- Question Bank Quick Shortcut -->
        <div class="bg-slate-900 rounded-2xl p-6 text-white relative overflow-hidden">
          <div class="relative z-10">
            <h3 class="text-base font-bold">ធនាគារសំណួរ (Question Bank)</h3>
            <p class="text-xs text-slate-400 mt-1">គ្រប់គ្រង និងបន្ថែមសំណួរតាមមេរៀននីមួយៗ</p>
            <button class="mt-4 w-full bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold py-2.5 rounded-xl transition-colors">
              គ្រប់គ្រងធនាគារសំណួរ
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// ទាញយកព័ត៌មាន Teacher ពី LocalStorage
const authUser = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('auth_user')) || {}
  } catch (e) { me: 'លោកគ្រូ' }
})

const teacherName = computed(() => {
  return `${authUser.value.first_name || ''} ${authUser.value.last_name || ''}`.trim() || 'គ្រូបង្រៀន'
})

const teacherInitial = computed(() => {
  return teacherName.value.charAt(0).toUpperCase()
})

const currentDate = ref('ថ្ងៃនេះ, ' + new Date().toLocaleDateString('km-KH'))

// Mock Data សម្រាប់បង្ហាញលើ Dashboard
const recentQuizzes = ref([
  {
    id: 1,
    title: 'Midterm Web Development Exam',
    questionsCount: 20,
    duration: 45,
    subject: 'Web Programming',
    className: 'M1-Class A',
    submittedCount: 38,
    totalStudents: 40,
    statusText: 'Active',
    statusClass: 'bg-emerald-50 text-emerald-700 border-emerald-200'
  },
  {
    id: 2,
    title: 'Database Normalization Quiz',
    questionsCount: 10,
    duration: 15,
    subject: 'Database Systems',
    className: 'M2-Class B',
    submittedCount: 25,
    totalStudents: 35,
    statusText: 'Pending',
    statusClass: 'bg-amber-50 text-amber-700 border-amber-200'
  },
  {
    id: 3,
    title: 'HTML & CSS Basics',
    questionsCount: 15,
    duration: 30,
    subject: 'Web Programming',
    className: 'M1-Class A',
    submittedCount: 40,
    totalStudents: 40,
    statusText: 'Closed',
    statusClass: 'bg-slate-100 text-slate-600 border-slate-200'
  }
])

const todaySchedules = ref([
  { id: 1, time: '08:00 - 09:30', room: 'Lab 302', subject: 'Web Programming', className: 'M1-Class A' },
  { id: 2, time: '10:00 - 11:30', room: 'Room 405', subject: 'Database Systems', className: 'M2-Class B' }
])

function openCreateQuizModal() {
  console.log("Open Create Quiz Modal")
}
</script>