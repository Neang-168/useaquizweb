<template>
  <div class="min-h-screen -m-6 p-6 font-sans space-y-6">

    <!-- Back link -->
    <router-link to="/student/mycourses" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#002060] no-underline">
      <i class="pi pi-arrow-left text-[10px]"></i>
      <span>Back to My Courses</span>
    </router-link>

    <div v-if="loading" class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-[#D8E7EC]">
      <i class="pi pi-spin pi-spinner text-3xl text-[#002060] mb-3"></i>
      <span class="text-xs font-semibold text-slate-500">Loading course...</span>
    </div>

    <template v-else>
      <!-- Header -->
      <div class="bg-white border border-[#D8E7EC] rounded-2xl p-5 space-y-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <i class="pi pi-book text-[#002060] text-sm"></i>
              <h1 class="text-xl font-bold text-[#002060] m-0">{{ course?.title || 'Course' }}</h1>
              <span v-if="course?.code" class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-[#D8E7EC]/50 text-[#002060]">{{ course.code }}</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
              <span class="font-semibold text-slate-600">{{ course?.className }}</span>
              <span v-if="course?.teacher"> • Teacher: {{ course.teacher }}</span>
            </p>
          </div>
        </div>

        <!-- Class & subject detail -->
        <div v-if="course" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-4 border-t border-[#D8E7EC]/60">
          <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
            <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Shift</p>
            <p class="text-xs font-bold text-[#002060] m-0 mt-0.5 flex items-center gap-1.5">
              <i class="pi pi-clock text-xs text-[#63C7DF]"></i> {{ course.shift || '—' }}
            </p>
          </div>
          <!-- Room field not needed in the UI
          <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
            <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Room</p>
            <p class="text-xs font-bold text-[#002060] m-0 mt-0.5 flex items-center gap-1.5">
              <i class="pi pi-building text-xs text-[#63C7DF]"></i> {{ course.room || '—' }}
            </p>
          </div>
          -->
          <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
            <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Major</p>
            <p class="text-xs font-bold text-slate-700 m-0 mt-0.5 truncate">{{ course.major || '—' }}</p>
          </div>
          <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
            <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Academic Year</p>
            <p class="text-xs font-bold text-slate-700 m-0 mt-0.5 truncate">{{ course.academicYear || '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-[#F8F8F8] p-1 rounded-xl w-full sm:w-fit flex gap-1">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          type="button"
          @click="activeTab = tab.value"
          :class="[
            'px-4 py-1.5 rounded-lg text-xs font-bold transition-all border-0 cursor-pointer',
            activeTab === tab.value ? 'bg-white text-[#002060] shadow-xs' : 'bg-transparent text-slate-500 hover:text-slate-700'
          ]"
        >
          {{ tab.label }} <span class="ml-1 text-[10px]" :class="activeTab === tab.value ? 'text-[#002060]' : 'text-slate-400'">({{ tab.count }})</span>
        </button>
      </div>

      <!-- ============ TAB: QUIZZES ============ -->
      <div v-if="activeTab === 'quizzes'" class="space-y-5">
        <div>
          <h3 class="text-xs font-bold text-slate-500 uppercase mb-2">Open Now</h3>
          <div v-if="openQuizzes.length === 0" class="bg-white rounded-2xl border border-[#D8E7EC] p-8 text-center text-xs text-slate-400">
            No quizzes are open for this course right now.
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
            <QuizCard v-for="exam in openQuizzes" :key="exam.id" :exam="exam" :class-id="classId" :subject-id="subjectId" />
          </div>
        </div>

        <div v-if="upcomingQuizzes.length">
          <h3 class="text-xs font-bold text-slate-500 uppercase mb-2">Upcoming</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <QuizCard v-for="exam in upcomingQuizzes" :key="exam.id" :exam="exam" :class-id="classId" :subject-id="subjectId" />
          </div>
        </div>
      </div>

      <!-- ============ TAB: RESULTS ============ -->
      <div v-if="activeTab === 'results'" class="bg-white rounded-2xl border border-[#D8E7EC] overflow-hidden shadow-xs">
        <div v-if="quizResults.length === 0" class="p-12 text-center">
          <div class="w-16 h-16 mx-auto rounded-full bg-[#D8E7EC]/40 flex items-center justify-center mb-3">
            <i class="pi pi-check-square text-3xl text-[#002060]"></i>
          </div>
          <h3 class="text-sm font-bold text-[#002060] m-0">No Results Yet</h3>
          <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1">You haven't submitted any quizzes for this course yet.</p>
        </div>
        <table v-else class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#F8F8F8] border-b border-[#D8E7EC] text-xs font-bold text-[#002060] uppercase">
              <th class="p-4">Quiz</th>
              <th class="p-4">Attempts</th>
              <th class="p-4">Best Score</th>
              <th class="p-4">Result</th>
              <th class="p-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
            <tr v-for="qr in quizResults" :key="qr.quizId" class="hover:bg-[#D8E7EC]/20 transition-colors">
              <td class="p-4">
                <p class="font-bold text-[#002060] m-0">{{ qr.quizTitle }}</p>
                <p class="text-[11px] text-slate-400 m-0 mt-0.5">Last submitted {{ formatDateTime(qr.best.submittedAt) }}</p>
              </td>
              <td class="p-4 text-slate-500">{{ qr.attempts.length }}</td>
              <td class="p-4 font-bold text-sm text-[#002060]">{{ qr.best.score }} / {{ qr.best.totalPoints }} ({{ qr.best.percentage }}%)</td>
              <td class="p-4">
                <span :class="['px-3 py-1 rounded-full text-[10px] font-bold border',
                  qr.best.passed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-[#D71818] border-red-200']">
                  {{ qr.best.passed ? 'Passed' : 'Failed' }}
                </span>
              </td>
              <td class="p-4 text-right">
                <router-link
                  :to="{ name: 'student.quizHistory', params: { quizId: qr.quizId }, query: { classId, subjectId } }"
                  class="text-[#002060] hover:text-[#001848] hover:underline font-bold no-underline inline-flex items-center gap-1">
                  <span>View History</span>
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../../api'
import { formatDateTime } from '../../../utils/formatDateTime'
import QuizCard from '../../../components/student/QuizCard.vue'

const route = useRoute()
const toast = useToast()
const classId = computed(() => Number(route.params.classId))
const subjectId = computed(() => Number(route.params.subjectId))

const loading = ref(true)
const course = ref(null)
const quizzes = ref([])
const results = ref([])
const activeTab = ref('quizzes')

const openQuizzes = computed(() => quizzes.value.filter(q => q.isOpen))
const upcomingQuizzes = computed(() => quizzes.value.filter(q => q.isUpcoming))

const quizResults = computed(() => {
  const byQuiz = new Map()
  for (const r of results.value) {
    if (!byQuiz.has(r.quizId)) byQuiz.set(r.quizId, [])
    byQuiz.get(r.quizId).push(r)
  }
  return Array.from(byQuiz.entries()).map(([quizId, attempts]) => ({
    quizId,
    quizTitle: attempts[0].quizTitle,
    attempts,
    best: [...attempts].sort((a, b) => b.score - a.score)[0],
  }))
})

const tabs = computed(() => [
  { label: 'Quizzes', value: 'quizzes', count: openQuizzes.value.length + upcomingQuizzes.value.length },
  { label: 'Results', value: 'results', count: quizResults.value.length },
])

async function loadWorkspace() {
  loading.value = true
  try {
    const [coursesRes, quizzesRes, resultsRes] = await Promise.all([
      api.get('/student/courses'),
      api.get('/student/quizzes', { params: { class_id: classId.value, subject_id: subjectId.value } }),
      api.get('/student/results', { params: { class_id: classId.value, subject_id: subjectId.value } }),
    ])

    course.value = coursesRes.data.data.find(c => c.classId === classId.value && c.id === subjectId.value) ?? null
    quizzes.value = quizzesRes.data.data
    results.value = resultsRes.data.data
  } catch (error) {
    toast.add({ summary: 'Failed to load course', ...toastFromError(error) })
  } finally {
    loading.value = false
  }
}

onMounted(loadWorkspace)
watch([classId, subjectId], loadWorkspace)
</script>
