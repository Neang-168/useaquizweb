<template>
  <div class="space-y-6 pb-10">
    <!-- Header Page -->
    <div>
      <h1 class="text-2xl font-bold text-slate-800 m-0">Welcome to Dashboard</h1>
      <p class="text-slate-500 text-sm mt-1 m-0">Manage your classes, quizzes, and student performance from here.</p>
    </div>
    <!-- 2. Detailed Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">My Classes</span>
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <i class="pi pi-building text-lg"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-extrabold text-slate-800">{{ classesCount }}</h2>
          <p class="text-xs text-slate-500 mt-1">Total students <span class="font-semibold text-slate-700">{{ studentsCount }}</span></p>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Published Quizzes</span>
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <i class="pi pi-check-circle text-lg"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-extrabold text-slate-800">{{ activeQuizzesCount }}</h2>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Grading</span>
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <i class="pi pi-exclamation-circle text-lg"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-extrabold text-amber-600">{{ pendingEssaysCount }}</h2>
          <p class="text-xs text-slate-500 mt-1">Essay / short-answer submissions</p>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Average Score</span>
          <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <i class="pi pi-chart-line text-lg"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-extrabold text-slate-800">{{ averageScore }}%</h2>
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
              <h3 class="text-base font-bold text-slate-800">Recent Quizzes</h3>
              <p class="text-xs text-slate-400">Quizzes you've created and their current status</p>
            </div>
          </div>

          <DataTable :value="recentQuizzes" dataKey="id" responsiveLayout="scroll" class="p-datatable-sm">
            <template #empty>
              <div class="text-center py-8 text-xs text-slate-400">You haven't created any quizzes yet.</div>
            </template>

            <Column header="QUIZ">
              <template #body="{ data }">
                <div class="font-semibold text-slate-800 text-sm">{{ data.title }}</div>
                <div class="text-[11px] text-slate-400">{{ data.questionsCount }} questions • {{ data.duration }} min</div>
              </template>
            </Column>
            <Column header="SUBJECT & CLASS">
              <template #body="{ data }">
                <div class="text-slate-700 font-medium text-sm">{{ data.subject }}</div>
                <div class="text-xs text-slate-400">{{ data.className }}</div>
              </template>
            </Column>
            <Column header="SUBMITTED" class="!text-center">
              <template #body="{ data }">
                <span class="font-medium text-slate-700 text-sm">{{ data.submittedCount }}/{{ data.totalStudents }}</span>
              </template>
            </Column>
            <Column header="STATUS" class="!text-center">
              <template #body="{ data }">
                <span :class="data.statusClass" class="text-xs font-bold px-2.5 py-1 rounded-full border">
                  {{ data.statusText }}
                </span>
              </template>
            </Column>
          </DataTable>
        </div>

        <!-- Student Performance Alert -->
        <div class="bg-amber-50/50 rounded-2xl border border-amber-200/60 p-5">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center">
              <i class="pi pi-exclamation-triangle"></i>
            </div>
            <div>
              <h4 class="text-sm font-bold text-amber-900">Students Needing Attention</h4>
              <p class="text-xs text-amber-700">Students who scored below 50 on their most recent quiz</p>
            </div>
          </div>
          <div v-if="needsAttention.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
            <div v-for="(s, idx) in needsAttention" :key="idx" class="p-3 bg-white rounded-xl border border-amber-100 flex items-center justify-between shadow-xs">
              <div>
                <div class="text-xs font-bold text-slate-800">{{ s.name }}</div>
                <div class="text-[11px] text-slate-400">{{ s.className }} • {{ s.subject }}</div>
              </div>
              <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100">{{ s.score }}</span>
            </div>
          </div>
          <p v-else class="text-xs text-amber-700 mt-3">No students need extra support right now, or there's no submission data yet.</p>
        </div>

      </div>

      <!-- Right Column (1 Col): Schedule & Quick Links -->
      <div class="space-y-6">

        <!-- Today's Schedule -->
        <!-- <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800">Today's Schedule</h3>
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
                <span class="text-xs text-slate-400">Room: {{ schedule.room }}</span>
              </div>
              <h4 class="text-sm font-bold text-slate-800 mt-2">{{ schedule.subject }}</h4>
              <p class="text-xs text-slate-500 mt-0.5">Class: {{ schedule.className }}</p>
            </div>
            <p v-if="todaySchedules.length === 0" class="text-xs text-slate-400 text-center py-6">
              Timetable scheduling isn't available in the system yet.
            </p>
          </div>
        </div> -->

        <!-- Upcoming Quizzes -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800">Upcoming Quizzes</h3>
            <router-link :to="{ name: 'teacher.calendar' }" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 no-underline">
              View Calendar
            </router-link>
          </div>

          <div class="space-y-3">
            <router-link
              v-for="quiz in upcomingQuizzes"
              :key="quiz.id"
              :to="{ name: 'teacher.calendar' }"
              class="block p-3 rounded-xl border border-slate-100 hover:border-indigo-100 bg-slate-50/50 hover:bg-indigo-50/30 transition-all no-underline"
            >
              <div class="flex justify-between items-start gap-2">
                <h4 class="text-sm font-bold text-slate-800 m-0">{{ quiz.title }}</h4>
                <span :class="statusClassMap[quiz.status] || statusClassMap.Closed" class="text-[10px] font-bold px-2 py-0.5 rounded-full border shrink-0">
                  {{ quiz.status }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-0.5">{{ quiz.subject }} • {{ quiz.className }}</p>
              <p class="text-[11px] text-indigo-600 font-semibold mt-1">{{ formatQuizDate(quiz) }}</p>
            </router-link>
            <p v-if="upcomingQuizzes.length === 0" class="text-xs text-slate-400 text-center py-6">
              No upcoming quizzes scheduled.
            </p>
          </div>
        </div>

        <!-- Question Bank Quick Shortcut -->
        <div class="bg-slate-900 rounded-2xl p-6 text-white relative overflow-hidden">
          <div class="relative z-10">
            <h3 class="text-base font-bold">Question Bank</h3>
            <p class="text-xs text-slate-400 mt-1">Manage and add questions for each of your lessons.</p>
            <Button
              label="Open Question Bank"
              size="small"
              class="!mt-4 w-full !bg-indigo-600 hover:!bg-indigo-500 !border-indigo-600 !text-white !text-xs !font-bold !rounded-lg"
              @click="router.push({ name: 'teacher.questionbank' })"
            />
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import api, { extractError } from '../../../api'

const router = useRouter()

// Read the logged-in teacher's info from localStorage
const authUser = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('auth_user')) || {}
  } catch (e) { return {} }
})

const teacherName = computed(() => {
  return `${authUser.value.first_name || ''} ${authUser.value.last_name || ''}`.trim() || 'Teacher'
})

const teacherInitial = computed(() => {
  return teacherName.value.charAt(0).toUpperCase()
})

const currentDate = ref(new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' }))

const classesCount = ref(0)
const studentsCount = ref(0)
const activeQuizzesCount = ref(0)
const pendingEssaysCount = ref(0)
const averageScore = ref(0)
const recentQuizzes = ref([])
const needsAttention = ref([])
const upcomingQuizzes = ref([])

// No timetable/scheduling system exists yet - this is a future feature
const todaySchedules = ref([])

const statusClassMap = {
  Published: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  Draft: 'bg-slate-100 text-slate-600 border-slate-200',
  Closed: 'bg-rose-50 text-rose-600 border-rose-200',
}

const fetchDashboard = async () => {
  try {
    const { data } = await api.get('/teacher/dashboard')
    classesCount.value = data.classesCount
    studentsCount.value = data.studentsCount
    activeQuizzesCount.value = data.activeQuizzesCount
    pendingEssaysCount.value = data.pendingEssaysCount
    averageScore.value = data.averageScore
    recentQuizzes.value = data.recentQuizzes.map(q => ({
      ...q,
      statusClass: statusClassMap[q.statusText] || statusClassMap.Closed,
    }))
    needsAttention.value = data.needsAttention
    upcomingQuizzes.value = data.upcomingQuizzes
  } catch (error) {
    alert(extractError(error))
  }
}

onMounted(fetchDashboard)

function goToMyClasses() {
  router.push({ name: 'teacher.classes' })
}

function formatQuizDate(quiz) {
  const value = quiz.startAt || quiz.endAt
  if (!value) return 'No fixed schedule'
  const date = new Date(value.replace(' ', 'T'))
  return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })
}
</script>
