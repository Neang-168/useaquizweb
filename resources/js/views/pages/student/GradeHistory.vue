<template>
  <div class="space-y-6 font-sans">

    <!-- ======= PAGE TITLE ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-800 m-0">Grades & History</h2>
        <p class="text-xs text-slate-400 m-0 mt-1">Review your results and quiz history</p>
      </div>

    </div>

    <!-- ======= OVERVIEW PERFORMANCE CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <!-- GPA / Average -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Overall Average</p>
          <h3 class="text-2xl font-extrabold text-blue-600 m-0 mt-1">{{ averagePercentage }}%</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-chart-line"></i>
        </div>
      </div>

      <!-- Total Passed -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Quizzes Passed</p>
          <h3 class="text-2xl font-extrabold text-emerald-600 m-0 mt-1">{{ passedCount }} / {{ historyList.length }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-check-circle"></i>
        </div>
      </div>
    </div>

    <!-- ======= HISTORY TABLE ======= -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-base font-bold text-slate-800 m-0">Detailed Exam History</h3>
        <span class="text-xs text-slate-400">Attempts: {{ historyList.length }}</span>
      </div>

      <div v-if="loading" class="p-8 text-center text-sm text-slate-400">Loading data...</div>
      <p v-else-if="!historyList.length" class="p-4 text-sm text-slate-400">You haven't taken any exams yet</p>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[600px]">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
              <th class="p-4">Quiz & Subject</th>
              <th class="p-4">Submitted On</th>
              <th class="p-4">Attempt</th>
              <th class="p-4">Score</th>
              <th class="p-4">Result</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
            <tr v-for="item in historyList" :key="item.id" class="hover:bg-slate-50/60 transition-colors">
              <td class="p-4">
                <p class="font-bold text-slate-800 m-0">{{ item.quizTitle }}</p>
                <p class="text-[11px] text-slate-400 m-0 mt-0.5">{{ item.subject }}</p>
              </td>
              <td class="p-4 text-slate-500">{{ item.submittedAt }}</td>
              <td class="p-4 text-slate-500">{{ item.attemptNumber }}</td>
              <td class="p-4 font-bold text-sm text-slate-800">
                {{ item.score }} / {{ item.totalPoints }} ({{ item.percentage }}%)
              </td>
              <td class="p-4">
                <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold inline-block',
                  item.passed ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                  {{ item.passed ? 'Passed' : 'Failed' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { extractError } from '../../../api'

const loading = ref(true)
const historyList = ref([])

const passedCount = computed(() => historyList.value.filter((item) => item.passed).length)
const averagePercentage = computed(() => {
  if (!historyList.value.length) return 0
  const total = historyList.value.reduce((sum, item) => sum + item.percentage, 0)
  return Math.round((total / historyList.value.length) * 10) / 10
})

async function fetchHistory() {
  loading.value = true
  try {
    const { data } = await api.get('/student/results')
    historyList.value = data.data
  } catch (error) {
    console.error(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(fetchHistory)
</script>