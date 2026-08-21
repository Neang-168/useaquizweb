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

    <!-- ======= QUIZ TAKING VIEW: one question at a time ======= -->
    <template v-else-if="quiz">
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between gap-4 sticky top-0 z-10">
        <div>
          <h2 class="text-lg font-bold text-slate-800 m-0">{{ quiz.title }}</h2>
          <p class="text-xs text-slate-400 m-0 mt-1">{{ quiz.subject }} &middot; Attempt {{ quiz.attemptNumber }}</p>
        </div>
        <div :class="['px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2', timeLeft <= 60 ? 'bg-red-50 text-red-600 animate-pulse' : 'bg-blue-50 text-blue-600']">
          <i class="pi pi-clock"></i> {{ formattedTime }}
        </div>
      </div>

      <!-- Progress + question navigator -->
      <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs space-y-3">
        <div class="flex items-center justify-between text-xs">
          <span class="font-bold text-slate-700">Question {{ currentIndex + 1 }} of {{ quiz.questions.length }}</span>
          <span class="text-slate-400">{{ answeredCount }} answered</span>
        </div>
        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
          <div class="h-full bg-blue-600 rounded-full transition-all" :style="{ width: progressPct + '%' }"></div>
        </div>
        <div class="flex flex-wrap gap-1.5">
          <button
            v-for="(q, idx) in quiz.questions"
            :key="q.id"
            type="button"
            @click="currentIndex = idx"
            :class="[
              'w-7 h-7 rounded-lg text-[11px] font-bold border transition-all',
              idx === currentIndex
                ? 'bg-blue-600 border-blue-600 text-white'
                : isAnswered(q) ? 'bg-blue-50 border-blue-200 text-blue-600' : 'bg-white border-slate-200 text-slate-400 hover:border-slate-300'
            ]"
          >
            {{ idx + 1 }}
          </button>
        </div>
      </div>

      <!-- Current question -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
        <div class="flex items-start justify-between gap-3">
          <h3 class="text-sm font-bold text-slate-800 m-0">{{ currentIndex + 1 }}. {{ currentQuestion.title }}</h3>
          <span class="text-[11px] font-semibold text-slate-400 shrink-0">{{ currentQuestion.points }} pts</span>
        </div>

        <div v-if="currentQuestion.imageUrl" class="inline-block rounded-xl border border-slate-100 bg-slate-50 overflow-hidden">
          <Image :src="currentQuestion.imageUrl" :alt="currentQuestion.imageAlt || ''" preview image-class="max-h-56 object-contain block" />
        </div>

        <!-- multiple_choice: radio when only one option is correct, checkboxes when several are -->
        <div v-if="currentQuestion.type === 'multiple_choice'" class="space-y-2">
          <p v-if="currentQuestion.multiSelect" class="text-[11px] font-semibold text-indigo-500 -mt-1">Select all that apply</p>
          <label v-for="option in currentQuestion.options" :key="option.id"
            class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer text-sm text-slate-700">
            <input
              v-if="currentQuestion.multiSelect"
              type="checkbox"
              :value="option.id"
              v-model="answers[currentQuestion.id].selectedOptionIds"
              class="w-4 h-4"
            />
            <input
              v-else
              type="radio"
              :name="`q-${currentQuestion.id}`"
              :value="option.id"
              :checked="answers[currentQuestion.id].selectedOptionIds[0] === option.id"
              @change="answers[currentQuestion.id].selectedOptionIds = [option.id]"
              class="w-4 h-4"
            />
            <span v-if="option.imageUrl" @click.stop.prevent>
              <Image :src="option.imageUrl" alt="" preview image-class="h-10 rounded-lg object-cover cursor-zoom-in" />
            </span>
            <span>{{ option.text }}</span>
          </label>
        </div>

        <!-- true_false: radio -->
        <div v-else-if="currentQuestion.type === 'true_false'" class="space-y-2">
          <label v-for="option in currentQuestion.options" :key="option.id"
            class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer text-sm text-slate-700">
            <input type="radio" :name="`q-${currentQuestion.id}`" :value="option.id" v-model="answers[currentQuestion.id].selectedOptionId" class="w-4 h-4" />
            <span>{{ option.text }}</span>
          </label>
        </div>

        <!-- matching: left list paired against a shuffled right list -->
        <div v-else-if="currentQuestion.type === 'matching'" class="space-y-2">
          <div v-for="left in currentQuestion.leftItems" :key="left.pairId"
            class="flex items-center gap-3 p-3 rounded-xl border border-slate-100">
            <div class="flex-1 flex items-center gap-2 text-sm font-medium text-slate-700">
              <Image v-if="left.imageUrl" :src="left.imageUrl" alt="" preview image-class="h-10 rounded-lg object-cover cursor-zoom-in" />
              <span>{{ left.text }}</span>
            </div>
            <i class="pi pi-arrow-right text-slate-300 text-xs"></i>
            <select v-model="answers[currentQuestion.id].matches[left.pairId]"
              class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-2 py-2 text-xs font-medium text-slate-700 outline-none focus:border-blue-500">
              <option :value="null">-- Select an answer --</option>
              <option v-for="right in currentQuestion.rightItems" :key="right.pairId" :value="right.pairId">
                {{ right.text }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Prev / Next / Submit -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between gap-3">
        <button
          type="button"
          :disabled="currentIndex === 0"
          @click="currentIndex--"
          class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold text-sm rounded-xl transition-all border-0 cursor-pointer flex items-center gap-2"
        >
          <i class="pi pi-arrow-left text-xs"></i> Previous
        </button>

        <button
          v-if="currentIndex < quiz.questions.length - 1"
          type="button"
          @click="currentIndex++"
          class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-all border-0 cursor-pointer flex items-center gap-2"
        >
          Next <i class="pi pi-arrow-right text-xs"></i>
        </button>
        <button
          v-else
          type="button"
          @click="openSubmitConfirm"
          :disabled="submitting"
          class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-sm rounded-xl transition-all border-0 cursor-pointer"
        >
          {{ submitting ? 'Submitting...' : 'Submit Answers' }}
        </button>
      </div>
    </template>

    <!-- ======= SUBMIT CONFIRMATION ======= -->
    <Dialog :visible="showConfirm" @update:visible="(v) => (showConfirm = v)" modal dismissable-mask class="w-full max-w-md">
      <template #header>
        <h3 class="text-sm font-bold text-slate-800 m-0 flex items-center gap-2">
          <i :class="['pi', unansweredQuestions.length ? 'pi-exclamation-triangle text-amber-500' : 'pi-check-circle text-emerald-500']"></i>
          Submit this quiz?
        </h3>
      </template>

      <div class="space-y-3 text-sm">
        <p v-if="!unansweredQuestions.length" class="text-slate-600 m-0">
          You've answered all {{ quiz?.questions.length }} questions. Once submitted, you can't change your answers.
        </p>
        <template v-else>
          <p class="text-slate-600 m-0">
            You have <b class="text-red-600">{{ unansweredQuestions.length }}</b> unanswered question{{ unansweredQuestions.length === 1 ? '' : 's' }} out of {{ quiz?.questions.length }}. Review them below before submitting, or submit anyway.
          </p>
          <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto p-0.5">
            <button
              v-for="q in unansweredQuestions"
              :key="q.index"
              type="button"
              class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 cursor-pointer"
              :title="q.title"
              @click="goToQuestion(q.index)"
            >
              Q{{ q.index + 1 }}
            </button>
          </div>
        </template>
      </div>

      <template #footer>
        <Button label="Continue Editing" size="small" class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="showConfirm = false" />
        <Button label="Submit Anyway" size="small" :disabled="submitting" class="!bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600 !text-white !rounded-lg !text-xs" @click="confirmSubmit" />
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import Image from 'primevue/image'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import api, { extractError } from '../../../api'
import { quizInProgress } from '../../../utils/quizLock'

const route = useRoute()
const quizId = route.query.id

const quiz = ref(null)
const loading = ref(true)
const loadError = ref('')
const submitting = ref(false)
const result = ref(null)
const answers = reactive({})
const currentIndex = ref(0)
const timeLeft = ref(0)
const showConfirm = ref(false)
let timer = null

// The server hands us an absolute deadline (started_at + duration), not a
// countdown — clockOffsetMs corrects for the local clock being off from the
// server's so the displayed countdown is accurate; it does not affect the
// actual deadline, which the server enforces independently.
let deadlineMs = 0
let clockOffsetMs = 0

const currentQuestion = computed(() => quiz.value.questions[currentIndex.value])

const answeredCount = computed(() => quiz.value.questions.filter(isAnswered).length)
const progressPct = computed(() => Math.round(((currentIndex.value + 1) / quiz.value.questions.length) * 100))

const unansweredQuestions = computed(() => {
  if (!quiz.value) return []
  return quiz.value.questions
    .map((question, index) => ({ index, title: question.title }))
    .filter(({ index }) => !isAnswered(quiz.value.questions[index]))
})

function openSubmitConfirm() {
  showConfirm.value = true
}

function goToQuestion(index) {
  currentIndex.value = index
  showConfirm.value = false
}

async function confirmSubmit() {
  showConfirm.value = false
  await submitQuiz()
}

function isAnswered(question) {
  const a = answers[question.id]
  if (!a) return false
  if (question.type === 'multiple_choice') return (a.selectedOptionIds || []).length > 0
  if (question.type === 'true_false') return a.selectedOptionId != null
  if (question.type === 'matching') return Object.values(a.matches || {}).some(v => v != null)
  return false
}

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

function tick() {
  const now = Date.now() + clockOffsetMs
  timeLeft.value = Math.max(0, Math.round((deadlineMs - now) / 1000))

  if (timeLeft.value <= 0) {
    clearInterval(timer)
    submitQuiz()
  }
}

function startTimer(deadlineAt, serverNow) {
  deadlineMs = new Date(deadlineAt).getTime()
  clockOffsetMs = new Date(serverNow).getTime() - Date.now()
  tick()
  timer = setInterval(tick, 1000)
}

function beforeUnloadWarning(e) {
  e.preventDefault()
  e.returnValue = ''
}

async function fetchQuiz() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await api.get(`/student/quizzes/${quizId}`)
    quiz.value = data.quiz
    currentIndex.value = 0
    initAnswers()
    startTimer(data.quiz.deadlineAt, data.quiz.serverNow)
    quizInProgress.value = true
    window.addEventListener('beforeunload', beforeUnloadWarning)
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
    quizInProgress.value = false
    window.removeEventListener('beforeunload', beforeUnloadWarning)
  }
}

onMounted(fetchQuiz)
onUnmounted(() => {
  clearInterval(timer)
  quizInProgress.value = false
  window.removeEventListener('beforeunload', beforeUnloadWarning)
})
</script>
