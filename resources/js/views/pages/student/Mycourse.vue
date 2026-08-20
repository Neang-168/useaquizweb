<template>
  <div class="min-h-screen  -m-6 p-6 font-sans space-y-6">

    <!-- ======= PAGE TITLE & FILTER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#D8E7EC] shadow-sm">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#D8E7EC]/40 flex items-center justify-center text-[#E4AC40]">
          <i class="pi pi-[#002060] pi-book text-xl text-[#002060]"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-[#002060] m-0">My Courses</h2>
          <p class="text-xs text-slate-500 m-0 mt-0.5">All the subjects you're taking this semester</p>
        </div>
      </div>

      <!-- Filter Semester/Academic Year -->
      <div class="flex items-center gap-2.5 bg-[#F8F8F8] p-1.5 px-3 rounded-xl border border-[#D8E7EC]">
        <i class="pi pi-calendar text-[#E4AC40] text-xs"></i>
        <label class="text-xs font-bold text-[#002060] shrink-0">Academic Year:</label>
        <select class="bg-transparent text-xs font-semibold text-slate-700 outline-none cursor-pointer border-0 py-1">
          <option value="y3s1">Year 3 - Semester 1 (2025-2026)</option>
          <option value="y2s2">Year 2 - Semester 2 (2024-2025)</option>
        </select>
      </div>
    </div>

    <!-- ======= LOADING STATE ======= -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-[#D8E7EC]">
      <i class="pi pi-spin pi-spinner text-3xl text-[#002060] mb-3"></i>
      <span class="text-xs font-semibold text-slate-500">Loading your subjects...</span>
    </div>

    <!-- ======= EMPTY STATE (DESIGN ថ្មីស្អាតជាងមុន) ======= -->
    <div v-else-if="!courses.length" class="bg-white rounded-2xl border border-[#D8E7EC] p-12 text-center shadow-sm max-w-2xl mx-auto my-8">
      <div class="w-20 h-20 mx-auto rounded-full bg-[#D8E7EC]/40 flex items-center justify-center mb-4 relative">
        <i class="pi pi-folder-open text-4xl text-[#002060]"></i>
        <span class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-[#E4AC40] text-white flex items-center justify-center text-xs font-bold shadow-md">
          <i class="pi pi-exclamation text-xs"></i>
        </span>
      </div>
      
      <h3 class="text-base font-bold text-[#002060] m-0">No Enrolled Courses Found</h3>
      <p class="text-xs text-slate-500 max-w-sm mx-auto mt-2 leading-relaxed">
        You're not enrolled in any subjects for this semester yet. Please contact your academic advisor or administrator to get assigned to a class.
      </p>

      <div class="mt-6 flex justify-center gap-3">
        <button @click="fetchCourses" class="px-4 py-2 bg-[#D8E7EC]/50 hover:bg-[#D8E7EC] text-[#002060] font-semibold text-xs rounded-xl transition-all cursor-pointer border-0 flex items-center gap-2">
          <i class="pi pi-refresh text-xs"></i>
          <span>Refresh Data</span>
        </button>
      </div>
    </div>

    <!-- ======= COURSES GRID ======= -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <div v-for="course in courses" :key="`${course.id}-${course.classId}`"
        class="bg-white rounded-2xl border border-[#D8E7EC] shadow-xs hover:shadow-lg hover:border-[#63C7DF] transition-all flex flex-col justify-between overflow-hidden group">
        
        <!-- Course Header Top -->
        <div class="p-5 space-y-4">
          <div class="flex items-center justify-between">
            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#002060] text-white shadow-xs">
              {{ course.code }}
            </span>
            <span class="text-xs font-bold text-[#002060] bg-[#D8E7EC]/50 px-2.5 py-1 rounded-lg flex items-center gap-1.5">
              <i class="pi pi-users text-xs text-[#E4AC40]"></i> {{ course.className }}
            </span>
          </div>

          <div>
            <h3 class="text-base font-bold text-slate-800 group-hover:text-[#002060] transition-colors m-0 leading-snug">
              {{ course.title }}
            </h3>
            <p class="text-xs text-slate-500 m-0 mt-2 flex items-center gap-1.5">
              <i class="pi pi-user text-xs text-[#63C7DF]"></i>
              <span>Teacher: <b class="text-slate-700">{{ course.teacher }}</b></span>
            </p>
          </div>

          <!-- Course Progress / Quizzes Info -->
          <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-center">
            <div class="bg-[#F8F8F8] p-2.5 rounded-xl border border-slate-100">
              <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Quizzes</span>
              <span class="text-sm font-bold text-[#002060]">{{ course.totalQuizzes }} Quizzes</span>
            </div>
            <div class="bg-emerald-50/60 p-2.5 rounded-xl border border-emerald-100/60">
              <span class="block text-[10px] text-emerald-600 font-bold uppercase tracking-wider">Completed</span>
              <span class="text-sm font-bold text-emerald-700">{{ course.completedQuizzes }} Quizzes</span>
            </div>
          </div>
        </div>

        <!-- Course Card Footer / Button -->
        <div class="px-5 py-3.5 bg-[#F8F8F8] border-t border-[#D8E7EC] flex items-center justify-between">
          <div class="flex items-center gap-1.5">
            <div class="w-2 h-2 rounded-full bg-[#E4AC40]"></div>
            <span class="text-xs font-bold text-slate-600">
              {{ course.totalQuizzes ? Math.round((course.completedQuizzes / course.totalQuizzes) * 100) : 0 }}% complete
            </span>
          </div>

          <router-link :to="{ name: 'student.courseWorkspace', params: { classId: course.classId, subjectId: course.id } }"
            class="px-4 py-2 bg-[#002060] hover:bg-[#001848] text-white font-semibold text-xs rounded-xl transition-all no-underline flex items-center gap-2 shadow-md shadow-[#002060]/20">
            <span>View Quizzes</span>
            <i class="pi pi-arrow-right text-xs text-[#E4AC40]"></i>
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