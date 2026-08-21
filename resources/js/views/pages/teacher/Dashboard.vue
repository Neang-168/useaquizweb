<template>
  <div class="space-y-6 pb-10 font-sans max-w-7xl mx-auto">
    
    <!-- 1. Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 bg-white rounded-2xl border border-[#D8E7EC] shadow-xs">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-[#002060] text-white flex items-center justify-center font-bold text-lg shadow-sm">
          {{ teacherInitial }}
        </div>
        <div>
          <h1 class="text-2xl font-bold text-[#002060] tracking-tight m-0">
            Welcome back, {{ teacherName }}!
          </h1>
          <p class="text-xs text-slate-500 mt-1 m-0">
            Manage your classes, quizzes, and student performance from here.
          </p>
        </div>
      </div>
      
      <div class="flex items-center gap-2">
        <Button 
          label="My Classes" 
          icon="pi pi-building" 
          size="small" 
          class="!bg-[#002060] hover:!bg-[#001540] !border-[#002060] !text-white !rounded-xl !text-xs !font-semibold"
          @click="goToMyClasses" 
        />
      </div>
    </div>

    <!-- 2. Detailed Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      
      <!-- Card 1: My Classes -->
      <div class="bg-white p-5 rounded-2xl border border-[#D8E7EC] shadow-xs hover:shadow-md hover:border-[#63C7DF] transition-all">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">My Classes</span>
          <div class="w-10 h-10 rounded-xl bg-[#002060]/5 text-[#002060] border border-[#002060]/10 flex items-center justify-center">
            <i class="pi pi-building text-base"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-black text-[#002060] m-0">{{ classesCount }}</h2>
          <p class="text-xs text-slate-500 mt-1 m-0">
            Total students <span class="font-bold text-[#002060]">{{ studentsCount }}</span>
          </p>
        </div>
      </div>

      <!-- Card 2: Published Quizzes -->
      <div class="bg-white p-5 rounded-2xl border border-[#D8E7EC] shadow-xs hover:shadow-md hover:border-[#63C7DF] transition-all">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Published Quizzes</span>
          <div class="w-10 h-10 rounded-xl bg-[#63C7DF]/15 text-[#002060] border border-[#63C7DF]/30 flex items-center justify-center">
            <i class="pi pi-check-circle text-base"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-black text-[#002060] m-0">{{ activeQuizzesCount }}</h2>
          <p class="text-xs text-slate-500 mt-1 m-0">Active in system</p>
        </div>
      </div>

      <!-- Card 3: Pending Grading -->
      <div class="bg-white p-5 rounded-2xl border border-[#D8E7EC] shadow-xs hover:shadow-md hover:border-[#E4AC40] transition-all">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pending Grading</span>
          <div class="w-10 h-10 rounded-xl bg-[#E4AC40]/15 text-[#E4AC40] border border-[#E4AC40]/30 flex items-center justify-center">
            <i class="pi pi-exclamation-circle text-base"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-black text-[#E4AC40] m-0">{{ pendingEssaysCount }}</h2>
          <p class="text-xs text-slate-500 mt-1 m-0">Essay & short answers</p>
        </div>
      </div>

      <!-- Card 4: Average Score -->
      <div class="bg-white p-5 rounded-2xl border border-[#D8E7EC] shadow-xs hover:shadow-md hover:border-[#63C7DF] transition-all">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Average Score</span>
          <div class="w-10 h-10 rounded-xl bg-[#002060]/5 text-[#002060] border border-[#002060]/10 flex items-center justify-center">
            <i class="pi pi-chart-line text-base"></i>
          </div>
        </div>
        <div class="mt-3">
          <h2 class="text-2xl font-black text-[#002060] m-0">{{ averageScore }}%</h2>
          <p class="text-xs text-slate-500 mt-1 m-0">Overall class performance</p>
        </div>
      </div>

    </div>

    <!-- 3. Main Operational Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left Column (2 Cols): Quizzes & Performance -->
      <div class="lg:col-span-2 space-y-6">

        <!-- Quizzes Table Section -->
        <div class="bg-white rounded-2xl border border-[#D8E7EC] shadow-xs p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
            <div>
              <h3 class="text-base font-bold text-[#002060] m-0 flex items-center gap-2">
                <i class="pi pi-[#63C7DF] pi-list text-[#63C7DF]"></i> Recent Quizzes
              </h3>
              <p class="text-xs text-slate-400 mt-0.5 m-0">Quizzes you've created and their current status</p>
            </div>
          </div>

          <DataTable :value="recentQuizzes" dataKey="id" responsiveLayout="scroll" class="p-datatable-sm">
            <template #empty>
              <div class="text-center py-8 text-xs font-medium text-slate-400">
                You haven't created any quizzes yet.
              </div>
            </template>

            <Column header="QUIZ">
              <template #body="{ data }">
                <div class="font-bold text-[#002060] text-sm">{{ data.title }}</div>
                <div class="text-[11px] text-slate-400 font-medium mt-0.5">
                  {{ data.questionsCount }} questions &middot; {{ data.duration }} min
                </div>
              </template>
            </Column>
            
            <Column header="SUBJECT & CLASS">
              <template #body="{ data }">
                <div class="text-slate-700 font-bold text-xs">{{ data.subject }}</div>
                <div class="text-[11px] text-slate-400 font-medium">{{ data.className }}</div>
              </template>
            </Column>
            
            <Column header="SUBMITTED" class="!text-center">
              <template #body="{ data }">
                <span class="font-bold text-[#002060] text-xs bg-[#F8F8F8] px-2.5 py-1 rounded-lg border border-[#D8E7EC]">
                  {{ data.submittedCount }}/{{ data.totalStudents }}
                </span>
              </template>
            </Column>
            
            <Column header="STATUS" class="!text-center">
              <template #body="{ data }">
                <span :class="data.statusClass" class="text-[10px] font-bold px-2.5 py-1 rounded-md border">
                  {{ data.statusText }}
                </span>
              </template>
            </Column>
          </DataTable>
        </div>

        <!-- Student Performance Alert -->
        <div class="bg-[#E4AC40]/10 rounded-2xl border border-[#E4AC40]/30 p-5">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-8 h-8 rounded-xl bg-[#D71818] text-white flex items-center justify-center shrink-0">
              <i class="pi pi-exclamation-triangle text-sm"></i>
            </div>
            <div>
              <h4 class="text-sm font-bold text-[#002060] m-0">Students Needing Attention</h4>
              <p class="text-xs text-slate-600 m-0">Students who scored below 50% on their most recent quiz</p>
            </div>
          </div>

          <div v-if="needsAttention.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
            <div 
              v-for="(s, idx) in needsAttention" 
              :key="idx" 
              class="p-3 bg-white rounded-xl border border-[#D8E7EC] flex items-center justify-between shadow-xs hover:border-[#D71818]/40 transition-colors"
            >
              <div>
                <div class="text-xs font-bold text-[#002060]">{{ s.name }}</div>
                <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ s.className }} &middot; {{ s.subject }}</div>
              </div>
              <span class="text-xs font-bold text-[#D71818] bg-[#D71818]/10 px-2 py-0.5 rounded-md border border-[#D71818]/20">
                {{ s.score }}%
              </span>
            </div>
          </div>
          
          <p v-else class="text-xs text-slate-500 font-medium mt-3 m-0 pl-11">
            No students need extra support right now, or there's no submission data yet.
          </p>
        </div>

      </div>

      <!-- Right Column (1 Col): Schedule & Quick Links -->
      <div class="space-y-6">

        <!-- Upcoming Quizzes -->
        <div class="bg-white rounded-2xl border border-[#D8E7EC] shadow-xs p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-[#002060] m-0 flex items-center gap-2">
              <i class="pi pi-calendar text-[#63C7DF]"></i> Upcoming Quizzes
            </h3>
            <router-link 
              :to="{ name: 'teacher.calendar' }" 
              class="text-xs font-bold text-[#63C7DF] hover:underline no-underline"
            >
              View Calendar
            </router-link>
          </div>

          <div class="space-y-3">
            <router-link
              v-for="quiz in upcomingQuizzes"
              :key="quiz.id"
              :to="{ name: 'teacher.calendar' }"
              class="block p-3.5 rounded-xl border border-[#D8E7EC] hover:border-[#63C7DF] bg-[#F8F8F8] hover:bg-white transition-all no-underline group"
            >
              <div class="flex justify-between items-start gap-2">
                <h4 class="text-xs font-bold text-[#002060] m-0 group-hover:text-[#63C7DF] transition-colors">
                  {{ quiz.title }}
                </h4>
                <span 
                  :class="statusClassMap[quiz.status] || statusClassMap.Closed" 
                  class="text-[10px] font-bold px-2 py-0.5 rounded-md border shrink-0"
                >
                  {{ quiz.status }}
                </span>
              </div>
              <p class="text-[11px] text-slate-500 font-medium mt-1 m-0">{{ quiz.subject }} &middot; {{ quiz.className }}</p>
              <p class="text-[11px] text-[#002060] font-bold mt-1.5 m-0 flex items-center gap-1">
                <i class="pi pi-clock text-[10px] text-[#E4AC40]"></i> {{ formatQuizDate(quiz) }}
              </p>
            </router-link>

            <p v-if="upcomingQuizzes.length === 0" class="text-xs text-slate-400 font-medium text-center py-6 m-0">
              No upcoming quizzes scheduled.
            </p>
          </div>
        </div>

        <!-- Question Bank Quick Shortcut -->
        <div class="bg-[#002060] rounded-2xl p-6 text-white relative overflow-hidden shadow-sm">
          <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-[#63C7DF]/10 rounded-full blur-xl pointer-events-none"></div>
          
          <div class="relative z-10">
            <div class="w-10 h-10 rounded-xl bg-white/10 text-[#E4AC40] flex items-center justify-center mb-3">
              <i class="pi pi-database text-lg"></i>
            </div>
            <h3 class="text-base font-bold m-0">Question Bank</h3>
            <p class="text-xs text-slate-300 mt-1 m-0">Manage and add questions for each of your lessons.</p>
            
            <Button
              label="Open Question Bank"
              icon="pi pi-arrow-right"
              iconPos="right"
              size="small"
              class="!mt-5 w-full !bg-[#E4AC40] hover:!bg-[#d49c30] !border-[#E4AC40] !text-white !text-xs !font-bold !rounded-xl !py-2.5 transition-colors"
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

const statusClassMap = {
  Published: 'bg-[#002060]/10 text-[#002060] border-[#002060]/20',
  Draft: 'bg-[#F8F8F8] text-slate-600 border-[#D8E7EC]',
  Closed: 'bg-[#D71818]/10 text-[#D71818] border-[#D71818]/20',
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