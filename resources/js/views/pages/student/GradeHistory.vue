<template>
  <div class="space-y-6 font-sans">

    <!-- ======= PAGE TITLE ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-800 m-0">ប្រវត្តិប្រឡង និងរបាយការណ៍ពិន្ទុ (Grades & History)</h2>
        <p class="text-xs text-slate-400 m-0 mt-1">ពិនិត្យមើលលទ្ធផល និងប្រវត្តិធ្វើ Quiz ទាំងអស់របស់អ្នក</p>
      </div>

      <!-- Filter Semester -->
      <select v-model="selectedSemester" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 transition-all cursor-pointer">
        <option value="y3s1">ឆ្នាំទី ៣ - ឆមាសទី ១ (២០២៥-២០២៦)</option>
        <option value="y2s2">ឆ្នាំទី ២ - ឆមាសទី ២ (២០២៤-២០២៥)</option>
      </select>
    </div>

    <!-- ======= OVERVIEW PERFORMANCE CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <!-- GPA / Average -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">ពិន្ទុមធ្យមភាគសរុប</p>
          <h3 class="text-2xl font-extrabold text-blue-600 m-0 mt-1">86.5%</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-chart-line"></i>
        </div>
      </div>

      <!-- Total Passed -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Quiz ជាប់ (Passed)</p>
          <h3 class="text-2xl font-extrabold text-emerald-600 m-0 mt-1">10 / 12</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-check-circle"></i>
        </div>
      </div>

      <!-- Average Time Spent -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">រយៈពេលមធ្យម/Quiz</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-1">22 នាទី</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-clock"></i>
        </div>
      </div>
    </div>

    <!-- ======= HISTORY TABLE ======= -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-base font-bold text-slate-800 m-0">ប្រវត្តិប្រឡងលម្អិត</h3>
        <span class="text-xs text-slate-400">ប្រឡងបាន៖ {{ historyList.length }} លើក</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[600px]">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
              <th class="p-4">ឈ្មោះ Quiz & មុខវិជ្ជា</th>
              <th class="p-4">ថ្ងៃប្រឡង</th>
              <th class="p-4">ពេលប្រើប្រាស់</th>
              <th class="p-4">ពិន្ទុ</th>
              <th class="p-4">លទ្ធផល</th>
              <th class="p-4 text-right">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
            <tr v-for="item in historyList" :key="item.id" class="hover:bg-slate-50/60 transition-colors">
              <td class="p-4">
                <p class="font-bold text-slate-800 m-0">{{ item.quizTitle }}</p>
                <p class="text-[11px] text-slate-400 m-0 mt-0.5">{{ item.subject }}</p>
              </td>
              <td class="p-4 text-slate-500">{{ item.submittedDate }}</td>
              <td class="p-4 text-slate-500">{{ item.timeSpent }} នាទី</td>
              <td class="p-4 font-bold text-sm text-slate-800">
                {{ item.score }} / {{ item.maxScore }} ({{ Math.round((item.score/item.maxScore)*100) }}%)
              </td>
              <td class="p-4">
                <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold inline-block', 
                  item.passed ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                  {{ item.passed ? 'ជាប់ (Passed)' : 'ធ្លាក់ (Failed)' }}
                </span>
              </td>
              <td class="p-4 text-right">
                <button @click="viewDetail(item.id)" 
                  class="px-3 py-1.5 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 font-semibold text-xs rounded-lg transition-all border-0 cursor-pointer">
                  <i class="pi pi-eye text-xs mr-1"></i> មើលចម្លើយ
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'

const selectedSemester = ref('y3s1')

// Mock Data សម្រាប់ប្រវត្តិប្រឡង
const historyList = ref([
  {
    id: 1,
    quizTitle: 'Vue 3 Options API & Reactivity',
    subject: 'Web Frontend Development',
    submittedDate: '05-Aug-2026 10:30 AM',
    timeSpent: 18,
    score: 90,
    maxScore: 100,
    passed: true
  },
  {
    id: 2,
    quizTitle: 'MySQL Relational Database & Joins',
    subject: 'Database Management Systems',
    submittedDate: '28-Jul-2026 02:15 PM',
    timeSpent: 35,
    score: 80,
    maxScore: 100,
    passed: true
  },
  {
    id: 3,
    quizTitle: 'Java OOP Concepts & Inheritance',
    subject: 'Object-Oriented Programming',
    submittedDate: '15-Jul-2026 09:00 AM',
    timeSpent: 40,
    score: 45,
    maxScore: 100,
    passed: false
  }
])

function viewDetail(id) {
  alert(`បើកមើលចម្លើយលម្អិតរបស់ Quiz ID: ${id}`)
}
</script>