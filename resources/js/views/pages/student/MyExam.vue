<template>
  <div class="space-y-6 font-sans">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-800 m-0">My Exams & Quizzes</h2>
        <p class="text-xs text-slate-400 m-0 mt-1">Manage and take exams for your subjects</p>
      </div>

      <!-- Filter Subject -->
      <select v-model="selectedSubject" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 transition-all cursor-pointer">
        <option value="all">All Subjects</option>
        <option v-for="subject in subjectOptions" :key="subject" :value="subject">{{ subject }}</option>
      </select>
    </div>

    <!-- ======= NAVIGATION TABS ======= -->
    <div class="flex border-b border-slate-200 space-x-6">
      <button
        @click="activeTab = 'active'"
        :class="['pb-3 text-sm font-bold border-b-2 transition-all bg-transparent cursor-pointer border-0',
          activeTab === 'active' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600']">
        <i class="pi pi-clock text-xs mr-1"></i> Open ({{ activeExams.length }})
      </button>

      <button
        @click="activeTab = 'upcoming'"
        :class="['pb-3 text-sm font-bold border-b-2 transition-all bg-transparent cursor-pointer border-0',
          activeTab === 'upcoming' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600']">
        <i class="pi pi-calendar text-xs mr-1"></i> Upcoming ({{ upcomingExams.length }})
      </button>

      <button
        @click="activeTab = 'completed'"
        :class="['pb-3 text-sm font-bold border-b-2 transition-all bg-transparent cursor-pointer border-0',
          activeTab === 'completed' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600']">
        <i class="pi pi-check-circle text-xs mr-1"></i> Completed ({{ completedExams.length }})
      </button>
    </div>

    <div v-if="loading" class="text-center text-sm text-slate-400 py-10">Loading data...</div>

    <template v-else>
    <!-- ======= TAB 1: ACTIVE EXAMS ======= -->
    <div v-if="activeTab === 'active'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <p v-if="!activeExams.length" class="text-sm text-slate-400 col-span-2">No open exams right now</p>
      <div v-for="exam in activeExams" :key="exam.id" class="bg-white rounded-2xl border border-blue-200 p-5 shadow-xs flex flex-col justify-between space-y-4">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-700">
              {{ exam.subject }}
            </span>
            <span v-if="exam.endAt" class="text-xs font-semibold text-amber-600 flex items-center gap-1 bg-amber-50 px-2 py-0.5 rounded-md">
              <i class="pi pi-exclamation-circle text-xs"></i> Due: {{ exam.endAt }}
            </span>
          </div>
          <h3 class="text-base font-bold text-slate-800 m-0">{{ exam.title }}</h3>

          <div class="flex items-center gap-4 text-xs text-slate-500 mt-3">
            <span class="flex items-center gap-1"><i class="pi pi-clock text-slate-400"></i> {{ exam.duration }} min</span>
            <span class="flex items-center gap-1"><i class="pi pi-list text-slate-400"></i> {{ exam.totalQuestions }} questions</span>
            <span class="flex items-center gap-1"><i class="pi pi-percentage text-slate-400"></i> Total points: {{ exam.totalPoints }}</span>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
          <span class="text-xs text-slate-400">Attempts used: {{ exam.attemptsUsed }} / {{ exam.maxAttempts }}</span>
          <router-link :to="`/student/take-quiz?id=${exam.id}`" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition-all no-underline shadow-xs">
            Start Exam
          </router-link>
        </div>
      </div>
    </div>

    <!-- ======= TAB 2: UPCOMING EXAMS ======= -->
    <div v-if="activeTab === 'upcoming'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <p v-if="!upcomingExams.length" class="text-sm text-slate-400 col-span-2">No upcoming exams</p>
      <div v-for="exam in upcomingExams" :key="exam.id" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between space-y-4 opacity-80">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600">
              {{ exam.subject }}
            </span>
            <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
              <i class="pi pi-calendar text-xs"></i> Opens: {{ exam.startAt }}
            </span>
          </div>
          <h3 class="text-base font-bold text-slate-700 m-0">{{ exam.title }}</h3>

          <div class="flex items-center gap-4 text-xs text-slate-500 mt-3">
            <span><i class="pi pi-clock"></i> {{ exam.duration }} min</span>
            <span><i class="pi pi-list"></i> {{ exam.totalQuestions }} questions</span>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
          <span class="text-xs text-amber-600 font-medium">Not open yet</span>
          <button disabled class="px-4 py-2 bg-slate-200 text-slate-400 font-bold text-xs rounded-xl cursor-not-allowed border-0">
            <i class="pi pi-lock text-xs mr-1"></i> Locked
          </button>
        </div>
      </div>
    </div>

    <!-- ======= TAB 3: COMPLETED EXAMS ======= -->
    <div v-if="activeTab === 'completed'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
      <p v-if="!completedExams.length" class="text-sm text-slate-400 p-4">You haven't taken any exams yet</p>
      <table v-else class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
            <th class="p-4">Exam & Subject</th>
            <th class="p-4">Submitted On</th>
            <th class="p-4">Score</th>
            <th class="p-4">Result</th>
            <th class="p-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
          <tr v-for="exam in completedExams" :key="exam.id" class="hover:bg-slate-50/50">
            <td class="p-4">
              <p class="font-bold text-slate-800 m-0">{{ exam.title }}</p>
              <p class="text-[11px] text-slate-400 m-0">{{ exam.subject }}</p>
            </td>
            <td class="p-4 text-slate-500">{{ exam.submittedAt }}</td>
            <td class="p-4 font-bold text-base text-slate-800">{{ exam.score }} / {{ exam.totalPoints }}</td>
            <td class="p-4">
              <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold', exam.passed ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                {{ exam.passed ? 'Passed' : 'Failed' }}
              </span>
            </td>
            <td class="p-4 text-right">
              <router-link to="/student/gradeHistory" class="text-blue-600 hover:underline font-bold no-underline">
                View Result
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { extractError } from '../../../api'

const activeTab = ref('active')
const selectedSubject = ref('all')
const loading = ref(true)
const quizzes = ref([])
const submissions = ref([])

const subjectOptions = computed(() => [...new Set(quizzes.value.map((q) => q.subject).filter(Boolean))])

const bySubject = (list) => selectedSubject.value === 'all'
  ? list
  : list.filter((item) => item.subject === selectedSubject.value)

const activeExams = computed(() => bySubject(quizzes.value.filter((q) => q.isOpen)))
const upcomingExams = computed(() => bySubject(quizzes.value.filter((q) => q.isUpcoming)))
const completedExams = computed(() => bySubject(submissions.value.map((s) => ({
  id: s.id,
  title: s.quizTitle,
  subject: s.subject,
  submittedAt: s.submittedAt,
  score: s.score,
  totalPoints: s.totalPoints,
  passed: s.passed,
}))))

async function fetchData() {
  loading.value = true
  try {
    const [quizzesRes, resultsRes] = await Promise.all([
      api.get('/student/quizzes'),
      api.get('/student/results'),
    ])
    quizzes.value = quizzesRes.data.data
    submissions.value = resultsRes.data.data
  } catch (error) {
    console.error(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>