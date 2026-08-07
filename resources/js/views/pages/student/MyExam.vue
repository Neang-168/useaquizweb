<template>
  <div class="space-y-6 font-sans">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-800 m-0">ការប្រឡងរបស់ខ្ញុំ (My Exams & Quizzes)</h2>
        <p class="text-xs text-slate-400 m-0 mt-1">គ្រប់គ្រង និងចូលធ្វើការប្រឡងតាមមុខវិជ្ជារបស់អ្នក</p>
      </div>

      <!-- Filter Subject -->
      <select v-model="selectedSubject" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 transition-all cursor-pointer">
        <option value="all">មុខវិជ្ជាទាំងអស់</option>
        <option value="CS301">Web Frontend Development</option>
        <option value="CS302">Database Management Systems</option>
      </select>
    </div>

    <!-- ======= NAVIGATION TABS ======= -->
    <div class="flex border-b border-slate-200 space-x-6">
      <button 
        @click="activeTab = 'active'"
        :class="['pb-3 text-sm font-bold border-b-2 transition-all bg-transparent cursor-pointer border-0', 
          activeTab === 'active' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600']">
        <i class="pi pi-clock text-xs mr-1"></i> កំពុងបើក (2)
      </button>

      <button 
        @click="activeTab = 'upcoming'"
        :class="['pb-3 text-sm font-bold border-b-2 transition-all bg-transparent cursor-pointer border-0', 
          activeTab === 'upcoming' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600']">
        <i class="pi pi-calendar text-xs mr-1"></i> ជិតមកដល់ (1)
      </button>

      <button 
        @click="activeTab = 'completed'"
        :class="['pb-3 text-sm font-bold border-b-2 transition-all bg-transparent cursor-pointer border-0', 
          activeTab === 'completed' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600']">
        <i class="pi pi-check-circle text-xs mr-1"></i> ធ្វើរួចរាល់ (5)
      </button>
    </div>

    <!-- ======= TAB 1: ACTIVE EXAMS ======= -->
    <div v-if="activeTab === 'active'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="exam in activeExams" :key="exam.id" class="bg-white rounded-2xl border border-blue-200 p-5 shadow-xs flex flex-col justify-between space-y-4">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-700">
              {{ exam.subject }}
            </span>
            <span class="text-xs font-semibold text-amber-600 flex items-center gap-1 bg-amber-50 px-2 py-0.5 rounded-md">
              <i class="pi pi-exclamation-circle text-xs"></i> ផុតកំណត់៖ {{ exam.dueDate }}
            </span>
          </div>
          <h3 class="text-base font-bold text-slate-800 m-0">{{ exam.title }}</h3>
          
          <div class="flex items-center gap-4 text-xs text-slate-500 mt-3">
            <span class="flex items-center gap-1"><i class="pi pi-clock text-slate-400"></i> {{ exam.duration }} នាទី</span>
            <span class="flex items-center gap-1"><i class="pi pi-list text-slate-400"></i> {{ exam.totalQuestions }} សំណួរ</span>
            <span class="flex items-center gap-1"><i class="pi pi-percentage text-slate-400"></i> ពិន្ទុសរុប៖ {{ exam.maxScore }}</span>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
          <span class="text-xs text-slate-400">ព្យាយាមបាន៖ {{ exam.attemptsAllowed }} ដង</span>
          <router-link :to="`/student/take-quiz?id=${exam.id}`" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition-all no-underline shadow-xs">
            ចាប់ផ្តើមប្រឡង
          </router-link>
        </div>
      </div>
    </div>

    <!-- ======= TAB 2: UPCOMING EXAMS ======= -->
    <div v-if="activeTab === 'upcoming'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="exam in upcomingExams" :key="exam.id" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between space-y-4 opacity-80">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600">
              {{ exam.subject }}
            </span>
            <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
              <i class="pi pi-calendar text-xs"></i> បើកនៅ៖ {{ exam.startDate }}
            </span>
          </div>
          <h3 class="text-base font-bold text-slate-700 m-0">{{ exam.title }}</h3>
          
          <div class="flex items-center gap-4 text-xs text-slate-500 mt-3">
            <span><i class="pi pi-clock"></i> {{ exam.duration }} នាទី</span>
            <span><i class="pi pi-list"></i> {{ exam.totalQuestions }} សំណួរ</span>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
          <span class="text-xs text-amber-600 font-medium">មិនទាន់ដល់ម៉ោងប្រឡង</span>
          <button disabled class="px-4 py-2 bg-slate-200 text-slate-400 font-bold text-xs rounded-xl cursor-not-allowed border-0">
            <i class="pi pi-lock text-xs mr-1"></i> ចាក់សោ
          </button>
        </div>
      </div>
    </div>

    <!-- ======= TAB 3: COMPLETED EXAMS ======= -->
    <div v-if="activeTab === 'completed'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
            <th class="p-4">មុខវិជ្ជា & កាលប្រឡង</th>
            <th class="p-4">ថ្ងៃធ្វើរួច</th>
            <th class="p-4">ពិន្ទុទទួលបាន</th>
            <th class="p-4">ស្ថានភាព</th>
            <th class="p-4 text-right">សកម្មភាព</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
          <tr v-for="exam in completedExams" :key="exam.id" class="hover:bg-slate-50/50">
            <td class="p-4">
              <p class="font-bold text-slate-800 m-0">{{ exam.title }}</p>
              <p class="text-[11px] text-slate-400 m-0">{{ exam.subject }}</p>
            </td>
            <td class="p-4 text-slate-500">{{ exam.submittedAt }}</td>
            <td class="p-4 font-bold text-base text-slate-800">{{ exam.score }} / {{ exam.maxScore }}</td>
            <td class="p-4">
              <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold', exam.passed ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                {{ exam.passed ? 'ជាប់ (Passed)' : 'ធ្លាក់ (Failed)' }}
              </span>
            </td>
            <td class="p-4 text-right">
              <router-link :to="`/student/history/${exam.id}`" class="text-blue-600 hover:underline font-bold no-underline">
                មើលលទ្ធផល
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('active')
const selectedSubject = ref('all')

// Mock Data សម្រាប់ការប្រឡងកំពុងបើក
const activeExams = ref([
  {
    id: 101,
    subject: 'Web Frontend Development',
    title: 'Vue 3 & Options API Basics Quiz',
    duration: 30,
    totalQuestions: 15,
    maxScore: 100,
    dueDate: 'ថ្ងៃនេះ ម៉ោង 11:59 PM',
    attemptsAllowed: 1
  },
  {
    id: 102,
    subject: 'Database Management',
    title: 'MySQL Indexing & Optimization Exam',
    duration: 45,
    totalQuestions: 20,
    maxScore: 100,
    dueDate: 'ស្អែក ម៉ោង 5:00 PM',
    attemptsAllowed: 1
  }
])

// Mock Data សម្រាប់ការប្រឡងជិតមកដល់
const upcomingExams = ref([
  {
    id: 103,
    subject: 'Java Programming',
    title: 'Midterm Practical Exam',
    duration: 60,
    totalQuestions: 25,
    startDate: '15-Aug-2026, 09:00 AM'
  }
])

// Mock Data សម្រាប់ការប្រឡងដែលធ្វើរួច
const completedExams = ref([
  {
    id: 99,
    subject: 'Web Development',
    title: 'HTML & CSS Fundamentals',
    submittedAt: '01-Aug-2026',
    score: 85,
    maxScore: 100,
    passed: true
  },
  {
    id: 98,
    subject: 'Database Management',
    title: 'SQL Normalization Quiz',
    submittedAt: '25-Jul-2026',
    score: 90,
    maxScore: 100,
    passed: true
  }
])
</script>