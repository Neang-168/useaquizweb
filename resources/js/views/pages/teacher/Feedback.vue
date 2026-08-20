<template>
  <div class="space-y-6">
    <!-- ======= PAGE HEADER ======= -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-comments text-indigo-600 text-2xl"></i>
          Student Feedback
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Send encouragement or study guidance to students, especially those with low scores.
        </p>
      </div>

      <!-- Filter Bar: Class / Subject / Quiz, each narrowing the ones after it -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Class</label>
          <Dropdown
            v-model="classFilter"
            :options="classOptions"
            option-label="label"
            option-value="value"
            placeholder="All Classes"
            showClear
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Subject</label>
          <Dropdown
            v-model="subjectFilter"
            :options="subjectOptions"
            option-label="label"
            option-value="value"
            placeholder="All Subjects"
            showClear
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Quiz / Exam</label>
          <Dropdown
            v-model="selectedQuiz"
            :options="quizOptions"
            option-label="label"
            option-value="value"
            :placeholder="quizOptions.length ? 'Select a quiz' : 'No quizzes match'"
            :disabled="!quizOptions.length"
            filter
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          >
            <template #dropdownicon>
              <i class="pi pi-book text-slate-400 text-xs"></i>
            </template>
          </Dropdown>
        </div>
      </div>
    </div>

    <div v-if="!quizOptions.length" class="text-center py-12 bg-white rounded-2xl border border-slate-200">
      <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
      <p class="text-xs text-slate-500">
        {{ myQuizzes.length ? 'No quizzes match this filter.' : "You don't have any quizzes yet — feedback becomes available once students submit one." }}
      </p>
    </div>
    <FeedbackTable v-else :quiz-id="selectedQuiz" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Dropdown from 'primevue/dropdown'
import api from '../../../api'
import FeedbackTable from '../../../components/teacher/FeedbackTable.vue'

const myQuizzes = ref([])
const selectedQuiz = ref(null)
const classFilter = ref(null)
const subjectFilter = ref(null)

// Option lists are built from whichever classes/subjects already appear
// among this teacher's own quizzes — no separate lookup endpoint needed.
const uniqueOptions = (idKey, labelKey) => {
  const seen = new Map()
  myQuizzes.value.forEach(q => {
    if (q[idKey] && !seen.has(q[idKey])) seen.set(q[idKey], q[labelKey])
  })
  return Array.from(seen, ([value, label]) => ({ label, value }))
}

const classOptions = computed(() => uniqueOptions('class_id', 'className'))
const subjectOptions = computed(() => uniqueOptions('subject_id', 'subjectName'))

const filteredQuizzes = computed(() => myQuizzes.value.filter(q => {
  if (classFilter.value && q.class_id !== classFilter.value) return false
  if (subjectFilter.value && q.subject_id !== subjectFilter.value) return false
  return true
}))

const quizOptions = computed(() => filteredQuizzes.value.map(q => ({ label: `${q.title} (${q.className})`, value: q.id })))

// Keep the selected quiz valid whenever the Class/Subject filters narrow
// (or widen) the list, falling back to the first quiz that still matches.
watch(quizOptions, (options) => {
  if (!options.some(o => o.value === selectedQuiz.value)) {
    selectedQuiz.value = options[0]?.value ?? null
  }
})

const fetchQuizzes = async () => {
  const { data } = await api.get('/teacher/quizzes')
  myQuizzes.value = data.data
  selectedQuiz.value = myQuizzes.value[0]?.id ?? null
}

onMounted(fetchQuizzes)
</script>
