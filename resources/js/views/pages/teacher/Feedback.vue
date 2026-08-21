<template>
  <div class="space-y-6 max-w-7xl mx-auto font-sans pb-10">
    <!-- ======= PAGE HEADER ======= -->
    <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#D8E7EC] space-y-4">
      <div>
        <h1 class="text-2xl font-bold text-[#002060] tracking-tight m-0 flex items-center gap-2.5">
          <i class="pi pi-comments text-[#63C7DF] text-2xl"></i>
          Student Feedback
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Send encouragement or study guidance to students, especially those who need extra support.
        </p>
      </div>

      <!-- Filter Bar: Class / Subject / Quiz -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Class</label>
          <Dropdown
            v-model="classFilter"
            :options="classOptions"
            option-label="label"
            option-value="value"
            placeholder="All Classes"
            showClear
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Subject</label>
          <Dropdown
            v-model="subjectFilter"
            :options="subjectOptions"
            option-label="label"
            option-value="value"
            placeholder="All Subjects"
            showClear
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Quiz / Exam</label>
          <Dropdown
            v-model="selectedQuiz"
            :options="quizOptions"
            option-label="label"
            option-value="value"
            :placeholder="quizOptions.length ? 'Select a quiz' : 'No quizzes match'"
            :disabled="!quizOptions.length"
            filter
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl text-xs"
          >
            <template #dropdownicon>
              <i class="pi pi-book text-[#63C7DF] text-xs"></i>
            </template>
          </Dropdown>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!quizOptions.length" class="text-center py-16 bg-white rounded-2xl border border-[#D8E7EC]">
      <i class="pi pi-inbox text-4xl text-slate-300 mb-3 block"></i>
      <p class="text-xs font-semibold text-slate-500 m-0">
        {{ myQuizzes.length ? 'No quizzes match this filter.' : "You don't have any quizzes yet — feedback becomes available once students submit one." }}
      </p>
    </div>

    <!-- Feedback Table Component -->
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