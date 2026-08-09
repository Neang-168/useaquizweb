<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">Student Feedback</h1>
        <p class="text-xs text-slate-500 mt-1">Send encouragement or study guidance to students, especially those with low scores.</p>
      </div>

      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quiz / Exam</label>
        <Dropdown
          v-model="selectedQuiz"
          :options="quizOptions"
          option-label="label"
          option-value="value"
          placeholder="Select a quiz"
          size="small"
          class="!bg-slate-50 !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700"
        />
      </div>
    </div>

    <FeedbackTable :quiz-id="selectedQuiz" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Dropdown from 'primevue/dropdown'
import api from '../../../api'
import FeedbackTable from '../../../components/teacher/FeedbackTable.vue'

const myQuizzes = ref([])
const selectedQuiz = ref(null)

const quizOptions = computed(() => myQuizzes.value.map(q => ({ label: `${q.title} (${q.className})`, value: q.id })))

const fetchQuizzes = async () => {
  const { data } = await api.get('/teacher/quizzes')
  myQuizzes.value = data.data
  selectedQuiz.value = myQuizzes.value[0]?.id ?? null
}

onMounted(fetchQuizzes)
</script>
