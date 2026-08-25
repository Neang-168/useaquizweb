<template>
  <div class="min-h-screen  -m-6 p-6 font-sans space-y-6">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#D8E7EC] shadow-sm">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#D8E7EC]/40 flex items-center justify-center text-[#E4AC40]">
          <i class="pi pi-file-edit text-xl text-[#002060]"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-[#002060] m-0">My Exams & Quizzes</h2>
          <p class="text-xs text-slate-500 m-0 mt-0.5">Manage and take exams for your subjects</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-center gap-2">
        <div class="flex items-center gap-2 bg-[#F8F8F8] p-1.5 px-3 rounded-xl border border-[#D8E7EC]">
          <i class="pi pi-users text-[#E4AC40] text-xs"></i>
          <select v-model="selectedClass" class="bg-transparent text-xs font-semibold text-slate-700 outline-none cursor-pointer border-0 py-1">
            <option value="all">All Classes</option>
            <option v-for="cls in classOptions" :key="cls" :value="cls">{{ cls }}</option>
          </select>
        </div>

        <div class="flex items-center gap-2 bg-[#F8F8F8] p-1.5 px-3 rounded-xl border border-[#D8E7EC]">
          <i class="pi pi-filter text-[#E4AC40] text-xs"></i>
          <select v-model="selectedSubject" class="bg-transparent text-xs font-semibold text-slate-700 outline-none cursor-pointer border-0 py-1">
            <option value="all">All Subjects</option>
            <option v-for="subject in subjectOptions" :key="subject" :value="subject">{{ subject }}</option>
          </select>
        </div>

        <div class="relative">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search quiz title..."
            class="bg-[#F8F8F8] border border-[#D8E7EC] rounded-xl text-xs font-medium text-slate-700 outline-none py-2 pl-9 pr-3 focus:border-[#63C7DF]"
          />
        </div>
      </div>
    </div>

    <!-- ======= NAVIGATION TABS ======= -->
    <div class="flex border-b border-[#D8E7EC] space-x-6 bg-white px-5 rounded-t-2xl pt-2">
      <button
        @click="activeTab = 'active'"
        :class="['pb-3 text-sm font-bold border-b-2 transition-all bg-transparent cursor-pointer border-0 flex items-center gap-2',
          activeTab === 'active' ? 'border-[#002060] text-[#002060]' : 'border-transparent text-slate-400 hover:text-slate-600']">
        <i class="pi pi-clock text-xs"></i>
        <span>Open</span>
        <span class="px-2 py-0.5 rounded-full text-[10px]" :class="activeTab === 'active' ? 'bg-[#002060] text-white' : 'bg-[#D8E7EC]/50 text-slate-600'">
          {{ activeExams.length }}
        </span>
      </button>

      <button
        @click="activeTab = 'upcoming'"
        :class="['pb-3 text-sm font-bold border-b-2 transition-all bg-transparent cursor-pointer border-0 flex items-center gap-2',
          activeTab === 'upcoming' ? 'border-[#002060] text-[#002060]' : 'border-transparent text-slate-400 hover:text-slate-600']">
        <i class="pi pi-calendar text-xs"></i>
        <span>Upcoming</span>
        <span class="px-2 py-0.5 rounded-full text-[10px]" :class="activeTab === 'upcoming' ? 'bg-[#002060] text-white' : 'bg-[#D8E7EC]/50 text-slate-600'">
          {{ upcomingExams.length }}
        </span>
      </button>

      <button
        @click="activeTab = 'completed'"
        :class="['pb-3 text-sm font-bold border-b-2 transition-all bg-transparent cursor-pointer border-0 flex items-center gap-2',
          activeTab === 'completed' ? 'border-[#002060] text-[#002060]' : 'border-transparent text-slate-400 hover:text-slate-600']">
        <i class="pi pi-check-circle text-xs"></i>
        <span>Completed</span>
        <span class="px-2 py-0.5 rounded-full text-[10px]" :class="activeTab === 'completed' ? 'bg-[#002060] text-white' : 'bg-[#D8E7EC]/50 text-slate-600'">
          {{ completedExams.length }}
        </span>
      </button>
    </div>

    <!-- ======= LOADING STATE ======= -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 bg-white rounded-b-2xl border border-[#D8E7EC] -mt-6">
      <i class="pi pi-spin pi-spinner text-3xl text-[#002060] mb-3"></i>
      <span class="text-xs font-semibold text-slate-500">Loading exam schedule...</span>
    </div>

    <template v-else>
      <!-- ======= TAB 1: ACTIVE EXAMS ======= -->
      <div v-if="activeTab === 'active'">
        
        <!-- EMPTY STATE ACTIVE -->
        <div v-if="!activeExams.length" class="bg-white rounded-2xl border border-[#D8E7EC] p-12 text-center shadow-sm max-w-xl mx-auto my-4">
          <div class="w-16 h-16 mx-auto rounded-full bg-[#D8E7EC]/40 flex items-center justify-center mb-3">
            <i class="pi pi-inbox text-3xl text-[#002060]"></i>
          </div>
          <h3 class="text-sm font-bold text-[#002060] m-0">No Active Exams Available</h3>
          <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1">There are no open quizzes or exams available for you to take right now.</p>
        </div>

        <!-- LIST ACTIVE -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <QuizCard v-for="exam in activeExams" :key="exam.id" :exam="exam" show-subject />
        </div>
      </div>

      <!-- ======= TAB 2: UPCOMING EXAMS ======= -->
      <div v-if="activeTab === 'upcoming'">

        <!-- EMPTY STATE UPCOMING -->
        <div v-if="!upcomingExams.length" class="bg-white rounded-2xl border border-[#D8E7EC] p-12 text-center shadow-sm max-w-xl mx-auto my-4">
          <div class="w-16 h-16 mx-auto rounded-full bg-[#D8E7EC]/40 flex items-center justify-center mb-3">
            <i class="pi pi-calendar-plus text-3xl text-[#002060]"></i>
          </div>
          <h3 class="text-sm font-bold text-[#002060] m-0">No Upcoming Exams</h3>
          <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1">You don't have any scheduled exams coming up in the near future.</p>
        </div>

        <!-- LIST UPCOMING -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <QuizCard v-for="exam in upcomingExams" :key="exam.id" :exam="exam" show-subject />
        </div>
      </div>

      <!-- ======= TAB 3: COMPLETED EXAMS ======= -->
      <div v-if="activeTab === 'completed'" class="bg-white rounded-2xl border border-[#D8E7EC] overflow-hidden shadow-xs">
        
        <!-- EMPTY STATE COMPLETED -->
        <div v-if="!completedExams.length" class="p-12 text-center">
          <div class="w-16 h-16 mx-auto rounded-full bg-[#D8E7EC]/40 flex items-center justify-center mb-3">
            <i class="pi pi-check-square text-3xl text-[#002060]"></i>
          </div>
          <h3 class="text-sm font-bold text-[#002060] m-0">No Completed Exams</h3>
          <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1">You haven't submitted any exams or quizzes yet.</p>
        </div>

        <!-- TABLE COMPLETED -->
        <table v-else class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#F8F8F8] border-b border-[#D8E7EC] text-xs font-bold text-[#002060] uppercase">
              <th class="p-4">Exam & Subject</th>
              <th class="p-4">Submitted On</th>
              <th class="p-4">Score</th>
              <th class="p-4">Result</th>
              <th class="p-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
            <tr v-for="exam in completedExams" :key="exam.id" class="hover:bg-[#D8E7EC]/20 transition-colors">
              <td class="p-4">
                <p class="font-bold text-[#002060] m-0">{{ exam.title }}</p>
                <p class="text-[11px] text-slate-400 m-0 mt-0.5">{{ exam.subject }}</p>
              </td>
              <td class="p-4 text-slate-500">{{ formatDateTime(exam.submittedAt) }}</td>
              <td class="p-4 font-bold text-sm text-[#002060]">{{ exam.score }} / {{ exam.totalPoints }}</td>
              <td class="p-4">
                <span :class="['px-3 py-1 rounded-full text-[10px] font-bold border', 
                  exam.passed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-[#D71818] border-red-200']">
                  {{ exam.passed ? 'Passed' : 'Failed' }}
                </span>
              </td>
              <td class="p-4 text-right">
                <router-link :to="{ name: 'student.quizHistory', params: { quizId: exam.quizId } }" class="text-[#002060] hover:text-[#001848] hover:underline font-bold no-underline inline-flex items-center gap-1">
                  <span>View Result</span>
                  <i class="pi pi-arrow-right text-[10px] text-[#E4AC40]"></i>
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
import { formatDateTime } from '../../../utils/formatDateTime'
import QuizCard from '../../../components/student/QuizCard.vue'

const activeTab = ref('active')
const selectedSubject = ref('all')
const selectedClass = ref('all')
const searchQuery = ref('')
const loading = ref(true)
const quizzes = ref([])
const submissions = ref([])

const subjectOptions = computed(() => [...new Set(quizzes.value.map((q) => q.subject).filter(Boolean))])
const classOptions = computed(() => [...new Set(quizzes.value.map((q) => q.className).filter(Boolean))])

const applyFilters = (list) => list.filter((item) => {
  if (selectedSubject.value !== 'all' && item.subject !== selectedSubject.value) return false
  if (selectedClass.value !== 'all' && item.className !== selectedClass.value) return false
  if (searchQuery.value && !item.title?.toLowerCase().includes(searchQuery.value.toLowerCase())) return false
  return true
})

const activeExams = computed(() => applyFilters(quizzes.value.filter((q) => q.isOpen)))
const upcomingExams = computed(() => applyFilters(quizzes.value.filter((q) => q.isUpcoming)))
const completedExams = computed(() => applyFilters(submissions.value.map((s) => ({
  id: s.id,
  quizId: s.quizId,
  title: s.quizTitle,
  subject: s.subject,
  className: s.className,
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