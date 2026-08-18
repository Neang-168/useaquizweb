<template>
  <div class="space-y-6">

    <!-- ======= PAGE TITLE & FILTER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-800 m-0">My Courses</h2>
        <p class="text-xs text-slate-400 m-0 mt-1">All the subjects you're taking this semester</p>
      </div>

      <!-- Filter Semester/Academic Year -->
      <div class="flex items-center gap-2">
        <label class="text-xs font-semibold text-slate-500 shrink-0">Academic Year:</label>
        <select class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 transition-all cursor-pointer">
          <option value="y3s1">Year 3 - Semester 1 (2025-2026)</option>
          <option value="y2s2">Year 2 - Semester 2 (2024-2025)</option>
        </select>
      </div>
    </div>

    <!-- ======= COURSES GRID ======= -->
    <div v-if="loading" class="text-center text-sm text-slate-400 py-10">Loading data...</div>
    <p v-else-if="!courses.length" class="text-sm text-slate-400">You're not enrolled in any subjects yet</p>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <div v-for="course in courses" :key="`${course.id}-${course.classId}`"
        class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group">
        
        <!-- Course Header Top -->
        <div class="p-5 space-y-3">
          <div class="flex items-center justify-between">
            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-600">
              {{ course.code }}
            </span>
            <span class="text-xs font-semibold text-slate-400 flex items-center gap-1">
              <i class="pi pi-users text-xs"></i> {{ course.className }}
            </span>
          </div>

          <div>
            <h3 class="text-base font-bold text-slate-800 group-hover:text-blue-600 transition-colors m-0">
              {{ course.title }}
            </h3>
            <p class="text-xs text-slate-500 m-0 mt-1 flex items-center gap-1.5">
              <i class="pi pi-user text-xs text-slate-400"></i>
              <span>Teacher: <b>{{ course.teacher }}</b></span>
            </p>
          </div>

          <!-- Course Progress / Quizzes Info -->
          <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-2 text-center">
            <div class="bg-slate-50 p-2 rounded-xl">
              <span class="block text-[10px] text-slate-400 font-semibold uppercase">Total Quizzes</span>
              <span class="text-sm font-bold text-slate-700">{{ course.totalQuizzes }} Quizzes</span>
            </div>
            <div class="bg-emerald-50 p-2 rounded-xl">
              <span class="block text-[10px] text-emerald-600 font-semibold uppercase">Completed</span>
              <span class="text-sm font-bold text-emerald-700">{{ course.completedQuizzes }} Quizzes</span>
            </div>
          </div>
        </div>

        <!-- Course Card Footer / Button -->
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
          <span class="text-xs font-medium text-slate-500">
            {{ course.totalQuizzes ? Math.round((course.completedQuizzes / course.totalQuizzes) * 100) : 0 }}% complete
          </span>
          <router-link to="/student/myexam"
            class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition-all no-underline flex items-center gap-1.5">
            <span>View Quizzes</span>
            <i class="pi pi-arrow-right text-xs"></i>
          </router-link>
        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api, { extractError } from '../../../api'

const courses = ref([])
const loading = ref(true)

async function fetchCourses() {
  loading.value = true
  try {
    const { data } = await api.get('/student/courses')
    courses.value = data.data
  } catch (error) {
    console.error(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(fetchCourses)
</script>