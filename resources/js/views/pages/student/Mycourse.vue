<template>
  <div class="space-y-6">

    <!-- ======= PAGE TITLE & FILTER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-800 m-0">មុខវិជ្ជារបស់ខ្ញុំ (My Courses)</h2>
        <p class="text-xs text-slate-400 m-0 mt-1">បញ្ជីមុខវិជ្ជាទាំងអស់ដែលអ្នកកំពុងសិក្សាក្នុងឆមាសនេះ</p>
      </div>

      <!-- Filter Semester/Academic Year -->
      <div class="flex items-center gap-2">
        <label class="text-xs font-semibold text-slate-500 shrink-0">ឆ្នាំសិក្សា៖</label>
        <select class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 transition-all cursor-pointer">
          <option value="y3s1">ឆ្នាំទី ៣ - ឆមាសទី ១ (២០២៥-២០២៦)</option>
          <option value="y2s2">ឆ្នាំទី ២ - ឆមាសទី ២ (២០២៤-២០២៥)</option>
        </select>
      </div>
    </div>

    <!-- ======= COURSES GRID ======= -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      
      <div v-for="course in courses" :key="course.id" 
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
              <span>គ្រូបង្រៀន៖ <b>{{ course.teacher }}</b></span>
            </p>
          </div>

          <!-- Course Progress / Quizzes Info -->
          <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-2 text-center">
            <div class="bg-slate-50 p-2 rounded-xl">
              <span class="block text-[10px] text-slate-400 font-semibold uppercase">Quiz ទាំងអស់</span>
              <span class="text-sm font-bold text-slate-700">{{ course.totalQuizzes }} Quizzes</span>
            </div>
            <div class="bg-emerald-50 p-2 rounded-xl">
              <span class="block text-[10px] text-emerald-600 font-semibold uppercase">ប្រឡងរួច</span>
              <span class="text-sm font-bold text-emerald-700">{{ course.completedQuizzes }} Quizzes</span>
            </div>
          </div>
        </div>

        <!-- Course Card Footer / Button -->
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
          <span class="text-xs font-medium text-slate-500">
            {{ Math.round((course.completedQuizzes / course.totalQuizzes) * 100) }}% រួចរាល់
          </span>
          <router-link :to="`/student/quizzes?subject_id=${course.id}`" 
            class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition-all no-underline flex items-center gap-1.5">
            <span>មើល Quiz</span>
            <i class="pi pi-arrow-right text-xs"></i>
          </router-link>
        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'

// ឧទាហរណ៍ Mock Data (អាចជំនួសដោយ API Call ទៅ Laravel Backend)
const courses = ref([
  {
    id: 1,
    code: 'CS301',
    title: 'Web Frontend Development',
    teacher: 'លោកគ្រូ សុខ ដារ៉ា',
    className: 'Class M1',
    totalQuizzes: 4,
    completedQuizzes: 3
  },
  {
    id: 2,
    code: 'CS302',
    title: 'Database Management Systems',
    teacher: 'អ្នកគ្រូ ចាន់ ធារី',
    className: 'Class M1',
    totalQuizzes: 3,
    completedQuizzes: 1
  },
  {
    id: 3,
    code: 'CS303',
    title: 'Object-Oriented Programming (Java)',
    teacher: 'លោកគ្រូ មាស សុភ័ក្ត្រ',
    className: 'Class M1',
    totalQuizzes: 5,
    completedQuizzes: 2
  }
])
</script>