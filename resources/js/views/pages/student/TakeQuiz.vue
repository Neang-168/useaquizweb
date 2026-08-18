<template>
  <div class="space-y-6 font-sans max-w-4xl mx-auto">

    <!-- ======= LOADING / ERROR ======= -->
    <div v-if="loading" class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-sm text-slate-400">
      Loading questions...
    </div>

    <div v-else-if="loadError" class="bg-white rounded-2xl border border-red-200 p-10 text-center space-y-3">
      <p class="text-sm text-red-600 font-semibold m-0">{{ loadError }}</p>
      <router-link to="/student/myexam" class="text-xs font-bold text-blue-600 hover:underline no-underline">
        Back to My Exams
      </router-link>
    </div>

    <!-- ======= RESULT VIEW (after submit) ======= -->
    <div v-else-if="result" class="bg-white rounded-2xl border border-slate-200 p-8 text-center space-y-4 shadow-xs">
      <div :class="['w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-2xl', result.passed ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600']">
        <i :class="result.passed ? 'pi pi-check-circle' : 'pi pi-times-circle'"></i>
      </div>
      <h2 class="text-xl font-bold text-slate-800 m-0">{{ result.passed ? 'Congratulations! You passed' : 'You did not pass' }}</h2>
      <p class="text-sm text-slate-500 m-0">
        Score: <b class="text-slate-800">{{ result.score }} / {{ result.totalPoints }}</b>
        ({{ result.percentage }}%) — Pass mark: {{ result.passMark }}%
      </p>
      <div class="flex items-center justify-center gap-3 pt-2">
        <router-link to="/student/gradeHistory" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition-all no-underline">
          View Exam History
        </router-link>
        <router-link to="/student/myexam" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all no-underline">
          Back to Exams
        </router-link>
      </div>
    </div>

    <!-- ======= QUIZ TAKING VIEW ======= -->
    <template v-else-if="quiz">
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between gap-4 sticky top-0 z-10">
        <div>
          <h2 class="text-lg font-bold text-slate-800 m-0">{{ quiz.title }}</h2>
          <p class="text-xs text-slate-400 m-0 mt-1">{{ quiz.subject }} &middot; Attempt {{ quiz.attemptNumber }}</p>
        </div>
        <div :class="['px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2', timeLeft <= 60 ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600']">
          <i class="pi pi-clock"></i> {{ formattedTime }}
        </div>
      </div>

      <div v-for="(question, index) in quiz.questions" :key="question.id" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
        <div class="flex items-start justify-between gap-3">
          <h3 class="text-sm font-bold text-slate-800 m-0">{{ index + 1 }}. {{ question.title }}</h3>
          <span class="text-[11px] font-semibold text-slate-400 shrink-0">{{ question.points }} pts</span>
        </div>

        <img v-if="question.imageUrl" :src="question.imageUrl" :alt="question.imageAlt || ''" class="max-h-56 rounded-xl border border-slate-100" />

        <!-- multiple_choice: checkboxes -->
        <div v-if="question.type === 'multiple_choice'" class="space-y-2">
          <label v-for="option in question.options" :key="option.id"
            class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer text-sm text-slate-700">
            <input type="checkbox" :value="option.id" v-model="answers[question.id].selectedOptionIds" class="w-4 h-4" />
            <img v-if="option.imageUrl" :src="option.imageUrl" class="h-10 rounded-lg" />
            <span>{{ option.text }}</span>
          </label>
        </div>

        <!-- true_false: radio -->
        <div v-else-if="question.type === 'true_false'" class="space-y-2">
          <label v-for="option in question.options" :key="option.id"
            class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer text-sm text-slate-700">
            <input type="radio" :name="`q-${question.id}`" :value="option.id" v-model="answers[question.id].selectedOptionId" class="w-4 h-4" />
            <span>{{ option.text }}</span>
          </label>
        </div>

        <!-- matching: left list paired against a shuffled right list -->
        <div v-else-if="question.type === 'matching'" class="space-y-2">
          <div v-for="left in question.leftItems" :key="left.pairId"
            class="flex items-center gap-3 p-3 rounded-xl border border-slate-100">
            <div class="flex-1 flex items-center gap-2 text-sm font-medium text-slate-700">
              <img v-if="left.imageUrl" :src="left.imageUrl" class="h-10 rounded-lg" />
              <span>{{ left.text }}</span>
            </div>
            <i class="pi pi-arrow-right text-slate-300 text-xs"></i>
            <select v-model="answers[question.id].matches[left.pairId]"
              class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-2 py-2 text-xs font-medium text-slate-700 outline-none focus:border-blue-500">
              <option :value="null">-- Select an answer --</option>
              <option v-for="right in question.rightItems" :key="right.pairId" :value="right.pairId">
                {{ right.text }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
        <p class="text-xs text-slate-400 m-0">Please review your answers before submitting</p>
        <button @click="submitQuiz" :disabled="submitting"
          class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-sm rounded-xl transition-all border-0 cursor-pointer">
          {{ submitting ? 'Submitting...' : 'Submit Answers' }}
        </button>
      </div>
    </template>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import api, { extractError } from '../../../api'

const route = useRoute()
const quizId = route.query.id

const quiz = ref(null)
const loading = ref(true)
const loadError = ref('')
const submitting = ref(false)
const result = ref(null)
const answers = reactive({})
const timeLeft = ref(0)
let timer = null

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60).toString().padStart(2, '0')
  const s = (timeLeft.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

function initAnswers() {
  for (const question of quiz.value.questions) {
    if (question.type === 'multiple_choice') {
      answers[question.id] = { selectedOptionIds: [] }
    } else if (question.type === 'true_false') {
      answers[question.id] = { selectedOptionId: null }
    } else if (question.type === 'matching') {
      const matches = {}
      for (const left of question.leftItems) matches[left.pairId] = null
      answers[question.id] = { matches }
    }
  }
}

function startTimer() {
  timeLeft.value = quiz.value.duration * 60
  timer = setInterval(() => {
    if (timeLeft.value <= 1) {
      clearInterval(timer)
      timeLeft.value = 0
      submitQuiz()
      return
    }
    timeLeft.value -= 1
  }, 1000)
}

async function fetchQuiz() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await api.get(`/student/quizzes/${quizId}`)
    quiz.value = data.quiz
    initAnswers()
    startTimer()
  } catch (error) {
    loadError.value = extractError(error)
  } finally {
    loading.value = false
  }
}

async function submitQuiz() {
  if (submitting.value || !quiz.value) return
  submitting.value = true
  clearInterval(timer)

  const payload = {
    answers: quiz.value.questions.map((question) => {
      const answer = answers[question.id] || {}
      if (question.type === 'multiple_choice') {
        return { questionId: question.id, selectedOptionIds: answer.selectedOptionIds || [] }
      }
      if (question.type === 'true_false') {
        return { questionId: question.id, selectedOptionId: answer.selectedOptionId ?? null }
      }
      return {
        questionId: question.id,
        matches: question.leftItems.map((left) => ({
          leftPairId: left.pairId,
          selectedRightPairId: (answer.matches || {})[left.pairId] ?? null,
        })),
      }
    }),
  }

  try {
    const { data } = await api.post(`/student/quizzes/${quizId}/submit`, payload)
    result.value = data.result
  } catch (error) {
    loadError.value = extractError(error)
    quiz.value = null
  } finally {
    submitting.value = false
  }
}

onMounted(fetchQuiz)
onUnmounted(() => clearInterval(timer))
</script>
