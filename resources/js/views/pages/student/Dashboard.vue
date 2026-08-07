<template>
  <div class="space-y-6">
    
    <!-- ======= WELCOME BANNER ======= -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold m-0">សួស្តី, {{ studentName }}! 👋</h2>
        <p class="text-blue-100 text-sm mt-1 m-0">សូមស្វាគមន៍មកកាន់ប្រព័ន្ធប្រឡង online។ ពិនិត្យមើលកាលវិភាគ និង Quiz របស់អ្នកខាងក្រោម៖</p>
      </div>
      <router-link to="/student/quizzes" class="px-4 py-2.5 bg-white text-blue-600 hover:bg-blue-50 font-semibold text-sm rounded-xl transition-all shadow-sm no-underline shrink-0">
        ប្រឡងឥឡូវនេះ
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
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Quiz ត្រូវធ្វើ</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">2</h3>
        </div>
      </div>

      <!-- Completed Quizzes -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-check-circle"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">ធ្វើរួចរាល់</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">12</h3>
        </div>
      </div>

      <!-- Average Score -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-chart-bar"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">ពិន្ទុមធ្យម</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">85.5%</h3>
        </div>
      </div>

      <!-- Enrolled Courses -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-book"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">មុខវិជ្ជាកំពុងរៀន</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">5</h3>
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
              Quiz កំពុងបើក និងជិតផុតកំណត់
            </h3>
            <router-link to="/student/quizzes" class="text-xs font-bold text-blue-600 hover:underline no-underline">
              មើលទាំងអស់
            </router-link>
          </div>

          <!-- Quiz Cards List -->
          <div class="space-y-3">
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-700 mb-1">
                  Web Development
                </span>
                <h4 class="text-sm font-bold text-slate-800 m-0">Vue 3 & Options API Basics Quiz</h4>
                <p class="text-xs text-slate-400 m-0 mt-1 flex items-center gap-3">
                  <span><i class="pi pi-clock text-xs"></i> 30 នាទី</span>
                  <span><i class="pi pi-list text-xs"></i> 15 សំណួរ</span>
                </p>
              </div>
              <router-link to="/student/take-quiz" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition-all no-underline text-center shrink-0">
                ចាប់ផ្តើមប្រឡង
              </router-link>
            </div>

            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-700 mb-1">
                  Database Admin
                </span>
                <h4 class="text-sm font-bold text-slate-800 m-0">MySQL Indexing & Optimization</h4>
                <p class="text-xs text-slate-400 m-0 mt-1 flex items-center gap-3">
                  <span><i class="pi pi-clock text-xs"></i> 45 នាទី</span>
                  <span><i class="pi pi-list text-xs"></i> 20 សំណួរ</span>
                </p>
              </div>
              <router-link to="/student/take-quiz" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition-all no-underline text-center shrink-0">
                ចាប់ផ្តើមប្រឡង
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
            ការជូនដំណឹងពីគ្រូ
          </h3>
          <div class="space-y-3">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
              <p class="text-xs font-bold text-slate-700 m-0">ប្រឡង Midterm មុខវិជ្ជា Java</p>
              <p class="text-[11px] text-slate-500 m-0 mt-1">ការប្រឡង Midterm នឹងត្រូវធ្វើឡើងនៅថ្ងៃសុក្រ សប្តាហ៍ក្រោយ វេលាម៉ោង 9:00 ព្រឹក។</p>
              <span class="text-[10px] text-slate-400 mt-2 block">២ ម៉ោងមុន</span>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const authUser = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('auth_user')) || {}
  } catch (e) {
    return {}
  }
})

const studentName = computed(() => {
  return authUser.value.first_name || authUser.value.username || 'សិស្ស'
})
</script>