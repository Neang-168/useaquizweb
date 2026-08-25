<template>
  <div class="min-h-screen -m-6 p-6 font-sans space-y-6">

    <!-- Back link -->
    <router-link :to="backTo" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#002060] no-underline">
      <i class="pi pi-arrow-left text-[10px]"></i>
      <span>Back</span>
    </router-link>

    <div v-if="loading" class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-[#D8E7EC]">
      <i class="pi pi-spin pi-spinner text-3xl text-[#002060] mb-3"></i>
      <span class="text-xs font-semibold text-slate-500">Loading history...</span>
    </div>

    <template v-else>
      <!-- Header -->
      <div class="bg-white border border-[#D8E7EC] rounded-2xl p-5 space-y-3 shadow-sm">
        <div class="flex items-center gap-2 flex-wrap">
          <i class="pi pi-file-edit text-[#002060] text-sm"></i>
          <h1 class="text-xl font-bold text-[#002060] m-0">{{ quizTitle || 'Quiz History' }}</h1>
        </div>
        <p v-if="subjectLabel" class="text-xs text-slate-500 m-0">{{ subjectLabel }}</p>

        <div v-if="attempts.length" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-3 border-t border-[#D8E7EC]/60">
          <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
            <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Attempts</p>
            <p class="text-sm font-bold text-[#002060] m-0 mt-0.5">{{ attempts.length }}</p>
          </div>
          <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
            <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Best Score</p>
            <p class="text-sm font-bold text-[#002060] m-0 mt-0.5">{{ bestAttempt?.score }} / {{ bestAttempt?.totalPoints }}</p>
          </div>
          <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
            <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Best %</p>
            <p class="text-sm font-bold text-[#002060] m-0 mt-0.5">{{ bestAttempt?.percentage }}%</p>
          </div>
          <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
            <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Result</p>
            <p :class="['text-sm font-bold m-0 mt-0.5', bestAttempt?.passed ? 'text-emerald-600' : 'text-[#D71818]']">
              {{ bestAttempt?.passed ? 'Passed' : 'Failed' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Attempts table -->
      <div class="bg-white rounded-2xl border border-[#D8E7EC] overflow-hidden shadow-xs">
        <div v-if="!attempts.length" class="p-12 text-center">
          <div class="w-16 h-16 mx-auto rounded-full bg-[#D8E7EC]/40 flex items-center justify-center mb-3">
            <i class="pi pi-check-square text-3xl text-[#002060]"></i>
          </div>
          <h3 class="text-sm font-bold text-[#002060] m-0">No Attempts Yet</h3>
          <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1">You haven't submitted this quiz yet.</p>
        </div>
        <table v-else class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#F8F8F8] border-b border-[#D8E7EC] text-xs font-bold text-[#002060] uppercase">
              <th class="p-4">Attempt</th>
              <th class="p-4">Submitted On</th>
              <th class="p-4">Score</th>
              <th class="p-4">Result</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
            <tr v-for="a in attempts" :key="a.id" class="hover:bg-[#D8E7EC]/20 transition-colors">
              <td class="p-4 font-bold text-[#002060]">#{{ a.attemptNumber }}</td>
              <td class="p-4 text-slate-500">{{ formatDateTime(a.submittedAt) }}</td>
              <td class="p-4 font-bold text-sm text-[#002060]">{{ a.score }} / {{ a.totalPoints }} ({{ a.percentage }}%)</td>
              <td class="p-4">
                <span :class="['px-3 py-1 rounded-full text-[10px] font-bold border',
                  a.passed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-[#D71818] border-red-200']">
                  {{ a.passed ? 'Passed' : 'Failed' }}
                </span>
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
import { useRoute } from 'vue-router'
import api, { extractError } from '../../../api'
import { formatDateTime } from '../../../utils/formatDateTime'

const route = useRoute()
const quizId = computed(() => Number(route.params.quizId))
const backTo = computed(() => {
  const classId = route.query.classId
  const subjectId = route.query.subjectId
  if (classId && subjectId) {
    return { name: 'student.courseWorkspace', params: { classId, subjectId } }
  }
  return { name: 'student.myexam' }
})

const loading = ref(true)
const attempts = ref([])

const quizTitle = computed(() => attempts.value[0]?.quizTitle ?? '')
const subjectLabel = computed(() => {
  const first = attempts.value[0]
  return first ? [first.subject, first.className].filter(Boolean).join(' • ') : ''
})
const bestAttempt = computed(() => {
  if (!attempts.value.length) return null
  return [...attempts.value].sort((a, b) => b.score - a.score)[0]
})

async function loadHistory() {
  loading.value = true
  try {
    const { data } = await api.get('/student/results', { params: { quiz_id: quizId.value } })
    attempts.value = [...data.data].sort((a, b) => a.attemptNumber - b.attemptNumber)
  } catch (error) {
    console.error(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(loadHistory)
</script>
