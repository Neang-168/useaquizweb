<template>
  <div class="space-y-6">
    
    <!-- ======= WELCOME BANNER ======= -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold m-0">Hi, {{ studentName }}! 👋</h2>
        <p class="text-blue-100 text-sm mt-1 m-0">Welcome to the online exam system. Check your schedule and quizzes below:</p>
      </div>
      <router-link to="/student/myexam" class="px-4 py-2.5 bg-white text-blue-600 hover:bg-blue-50 font-semibold text-sm rounded-xl transition-all shadow-sm no-underline shrink-0">
        Take an Exam
      </router-link>
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Active Quizzes -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-hourglass"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Quizzes To Do</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">{{ stats.todoCount }}</h3>
        </div>
      </div>

      <!-- Completed Quizzes -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-check-circle"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Completed</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">{{ stats.completedCount }}</h3>
        </div>
      </div>

      <!-- Average Score -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-chart-bar"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Average Score</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">{{ stats.averageScore }}%</h3>
        </div>
      </div>

      <!-- Enrolled Courses -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-book"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Enrolled Subjects</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">{{ stats.enrolledSubjectsCount }}</h3>
        </div>
      </div>
    </div>

    <!-- ======= MAIN CONTENT GRID ======= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- LEFT (2 Cols): Active & Upcoming Quizzes -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 m-0 flex items-center gap-2">
              <i class="pi pi-clock text-amber-500"></i>
              Open & Due Soon Quizzes
            </h3>
            <router-link to="/student/myexam" class="text-xs font-bold text-blue-600 hover:underline no-underline">
              View All
            </router-link>
          </div>

          <!-- Quiz Cards List -->
          <div class="space-y-3">
            <p v-if="!stats.dueSoon.length" class="text-sm text-slate-400 m-0">No quizzes to take right now</p>
            <div v-for="quiz in stats.dueSoon" :key="quiz.id" class="p-4 rounded-xl border border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <span v-if="quiz.subject" class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-700 mb-1">
                  {{ quiz.subject }}
                </span>
                <h4 class="text-sm font-bold text-slate-800 m-0">{{ quiz.title }}</h4>
                <p class="text-xs text-slate-400 m-0 mt-1 flex items-center gap-3">
                  <span><i class="pi pi-clock text-xs"></i> {{ quiz.duration }} min</span>
                  <span><i class="pi pi-list text-xs"></i> {{ quiz.totalQuestions }} questions</span>
                </p>
              </div>
              <router-link :to="`/student/take-quiz?id=${quiz.id}`" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition-all no-underline text-center shrink-0">
                Start Exam
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT (1 Col): Announcements & Recent Results -->
      <div class="space-y-6">
        <!-- Announcements -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
          <h3 class="text-base font-bold text-slate-800 m-0 mb-4 flex items-center gap-2">
            <i class="pi pi-bell text-blue-500"></i>
            Feedback From Teachers
          </h3>
          <div class="space-y-3">
            <p v-if="!stats.announcements.length" class="text-xs text-slate-400 m-0">No feedback yet</p>
            <div v-for="note in stats.announcements" :key="note.id" class="p-3 rounded-xl bg-slate-50 border border-slate-100">
              <p class="text-xs font-bold text-slate-700 m-0">{{ note.teacherName || 'Teacher' }}</p>
              <p class="text-[11px] text-slate-500 m-0 mt-1">{{ note.message }}</p>
              <span class="text-[10px] text-slate-400 mt-2 block">{{ note.sentAt }}</span>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { extractError } from '../../../api'

const authUser = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('auth_user')) || {}
  } catch (e) {
    return {}
  }
})

const studentName = computed(() => {
  return authUser.value.first_name || authUser.value.username || 'Student'
})

const stats = ref({
  todoCount: 0,
  completedCount: 0,
  averageScore: 0,
  enrolledSubjectsCount: 0,
  dueSoon: [],
  announcements: [],
})

async function fetchDashboard() {
  try {
    const { data } = await api.get('/student/dashboard')
    stats.value = data
  } catch (error) {
    console.error(extractError(error))
  }
}

onMounted(fetchDashboard)
</script>