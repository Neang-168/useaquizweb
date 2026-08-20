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
      <div class="bg-white border border-[#D8E7EC] rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
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
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="exam in openQuizzes" :key="exam.id" class="bg-white rounded-2xl border border-[#D8E7EC] hover:border-[#63C7DF] p-5 shadow-xs flex flex-col justify-between space-y-4 transition-all">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span v-if="exam.status === 'in_progress'" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">Continue</span>
                  <span v-else class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#002060] text-white">Open</span>
                  <span v-if="exam.endAt" class="text-xs font-bold text-[#D71818] flex items-center gap-1 bg-red-50 border border-red-100 px-2.5 py-0.5 rounded-full">
                    <i class="pi pi-clock text-xs"></i> Due: {{ exam.endAt }}
                  </span>
                </div>
                <h3 class="text-base font-bold text-[#002060] m-0 mt-2">{{ exam.title }}</h3>
                <div class="flex items-center gap-4 text-xs text-slate-500 mt-3 bg-[#F8F8F8] p-2.5 rounded-xl border border-slate-100">
                  <span class="flex items-center gap-1 font-medium"><i class="pi pi-clock text-[#E4AC40]"></i> {{ exam.duration }} min</span>
                  <span class="flex items-center gap-1 font-medium"><i class="pi pi-list text-[#63C7DF]"></i> {{ exam.totalQuestions }} q's</span>
                  <span class="flex items-center gap-1 font-medium"><i class="pi pi-percentage text-[#002060]"></i> {{ exam.totalPoints }} pts</span>
                </div>
              </div>
              <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Attempts: <b class="text-[#002060]">{{ exam.attemptsUsed }}</b> / {{ exam.maxAttempts }}</span>
                <router-link :to="`/student/take-quiz?id=${exam.id}`" class="px-4 py-2 bg-[#002060] hover:bg-[#001848] text-white font-bold text-xs rounded-xl transition-all no-underline shadow-md shadow-[#002060]/20 flex items-center gap-1.5">
                  <span>{{ exam.status === 'in_progress' ? 'Continue' : 'Start Exam' }}</span>
                  <i class="pi pi-arrow-right text-xs text-[#E4AC40]"></i>
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <div v-if="upcomingQuizzes.length">
          <h3 class="text-xs font-bold text-slate-500 uppercase mb-2">Upcoming</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="exam in upcomingQuizzes" :key="exam.id" class="bg-white/80 rounded-2xl border border-[#D8E7EC] p-5 shadow-xs flex flex-col justify-between space-y-4">
              <div>
                <span class="text-xs font-semibold text-slate-600 flex items-center gap-1 bg-[#F8F8F8] px-2 py-0.5 rounded-md border border-slate-200 w-fit">
                  <i class="pi pi-calendar text-xs text-[#E4AC40]"></i> Opens: {{ exam.startAt }}
                </span>
                <h3 class="text-base font-bold text-slate-700 m-0 mt-2">{{ exam.title }}</h3>
                <div class="flex items-center gap-4 text-xs text-slate-500 mt-3">
                  <span class="flex items-center gap-1"><i class="pi pi-clock text-slate-400"></i> {{ exam.duration }} min</span>
                  <span class="flex items-center gap-1"><i class="pi pi-list text-slate-400"></i> {{ exam.totalQuestions }} questions</span>
                </div>
              </div>
              <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-[#E4AC40] font-bold flex items-center gap-1"><i class="pi pi-clock text-xs"></i> Not open yet</span>
                <button disabled class="px-4 py-2 bg-slate-100 text-slate-400 font-bold text-xs rounded-xl cursor-not-allowed border border-slate-200 flex items-center gap-1">
                  <i class="pi pi-lock text-xs"></i> Locked
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ============ TAB: RESULTS ============ -->
      <div v-if="activeTab === 'results'" class="bg-white rounded-2xl border border-[#D8E7EC] overflow-hidden shadow-xs">
        <div v-if="results.length === 0" class="p-12 text-center">
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
              <th class="p-4">Submitted On</th>
              <th class="p-4">Score</th>
              <th class="p-4">Result</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
            <tr v-for="r in results" :key="r.id" class="hover:bg-[#D8E7EC]/20 transition-colors">
              <td class="p-4">
                <p class="font-bold text-[#002060] m-0">{{ r.quizTitle }}</p>
                <p class="text-[11px] text-slate-400 m-0 mt-0.5">Attempt {{ r.attemptNumber }}</p>
              </td>
              <td class="p-4 text-slate-500">{{ r.submittedAt }}</td>
              <td class="p-4 font-bold text-sm text-[#002060]">{{ r.score }} / {{ r.totalPoints }} ({{ r.percentage }}%)</td>
              <td class="p-4">
                <span :class="['px-3 py-1 rounded-full text-[10px] font-bold border',
                  r.passed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-[#D71818] border-red-200']">
                  {{ r.passed ? 'Passed' : 'Failed' }}
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api, { extractError } from '../../../api'

const route = useRoute()
const classId = computed(() => Number(route.params.classId))
const subjectId = computed(() => Number(route.params.subjectId))

const loading = ref(true)
const course = ref(null)
const quizzes = ref([])
const results = ref([])
const activeTab = ref('quizzes')

const openQuizzes = computed(() => quizzes.value.filter(q => q.isOpen))
const upcomingQuizzes = computed(() => quizzes.value.filter(q => q.isUpcoming))

const tabs = computed(() => [
  { label: 'Quizzes', value: 'quizzes', count: openQuizzes.value.length + upcomingQuizzes.value.length },
  { label: 'Results', value: 'results', count: results.value.length },
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
    alert(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(loadWorkspace)
watch([classId, subjectId], loadWorkspace)
</script>
