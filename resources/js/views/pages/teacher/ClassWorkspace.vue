<template>
  <div class="space-y-6">
    <!-- Back link -->
    <router-link :to="{ name: 'teacher.classes' }" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-indigo-600 no-underline">
      <i class="pi pi-arrow-left text-[10px]"></i>
      <span>Back to My Classes</span>
    </router-link>

    <div v-if="!classInfo" class="text-center py-16 bg-white rounded-2xl border border-slate-200">
      <p class="text-xs text-slate-400">Loading...</p>
    </div>

    <template v-else>
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold text-slate-800 m-0">{{ classInfo.className }}</h1>
            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100">{{ classInfo.shift }}</span>
          </div>
          <p class="text-xs text-slate-500 mt-1">
            Subject: <span class="font-semibold text-slate-700">{{ classInfo.subject }}</span>
            • {{ classInfo.totalStudents }} students
            • Room {{ classInfo.room }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button as="router-link" size="small"
            :to="{ name: 'teacher.questionbank', query: { subject_id: classInfo.subject_id } }"
            label="Question Bank" icon="pi pi-book"
            class="!bg-slate-100 hover:!bg-slate-200 !border-slate-100 !text-slate-700 !rounded-lg !text-xs no-underline"
          />
          <Button as="router-link" size="small"
            :to="{ name: 'teacher.subjects', query: { subject_id: classInfo.subject_id } }"
            label="Materials" icon="pi pi-folder-open"
            class="!bg-slate-100 hover:!bg-slate-200 !border-slate-100 !text-slate-700 !rounded-lg !text-xs no-underline"
          />
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-slate-100 p-1 rounded-xl w-full sm:w-fit overflow-x-auto">
        <SelectButton
          v-model="activeTab"
          :options="tabs"
          option-label="label"
          option-value="value"
          :allow-empty="false"
          class="teacher-tabs"
        />
      </div>

      <!-- ============ TAB: OVERVIEW ============ -->
      <div v-if="activeTab === 'overview'" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100">
          <h3 class="text-sm font-bold text-slate-800 m-0">Student Roster</h3>
        </div>
        <DataTable :value="classInfo.students" dataKey="id" responsiveLayout="scroll" class="p-datatable-sm">
          <template #empty>
            <div class="text-center py-10 text-xs text-slate-400">No students are enrolled in this class yet.</div>
          </template>

          <Column header="#">
            <template #body="{ index }">
              <span class="text-slate-400 font-mono text-xs">{{ index + 1 }}</span>
            </template>
          </Column>
          <Column field="student_id" header="STUDENT ID">
            <template #body="{ data }">
              <span class="font-mono font-bold text-indigo-600 text-xs">{{ data.student_id }}</span>
            </template>
          </Column>
          <Column field="name" header="NAME">
            <template #body="{ data }">
              <span class="font-semibold text-slate-800 text-xs">{{ data.name }}</span>
            </template>
          </Column>
          <Column field="gender" header="GENDER">
            <template #body="{ data }">
              <span class="text-slate-500 text-xs">{{ data.gender }}</span>
            </template>
          </Column>
          <Column field="email" header="EMAIL">
            <template #body="{ data }">
              <span class="text-slate-500 text-xs">{{ data.email }}</span>
            </template>
          </Column>
          <Column header="STATUS" class="!text-center">
            <template #body>
              <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 font-semibold px-2 py-0.5 rounded-md text-[10px]">
                Enrolled
              </span>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- ============ TAB: QUIZZES & EXAMS ============ -->
      <div v-if="activeTab === 'quizzes'" class="space-y-4">
        <div class="flex justify-end">
          <Button
            label="Create Quiz"
            icon="pi pi-plus"
            size="small"
            class="!bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg !text-xs shadow-sm"
            @click="openQuizModal()"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="quiz in quizzes"
            :key="quiz.id"
            class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-200 hover:shadow-md transition-all flex flex-col justify-between"
          >
            <div>
              <div class="flex items-center justify-between gap-2 mb-3">
                <span
                  :class="statusBadgeClass(quiz.status)"
                  class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border"
                >
                  {{ statusLabel(quiz.status) }}
                </span>
                <span v-if="quiz.passMark" class="text-[10px] text-slate-400">Pass mark: {{ quiz.passMark }}%</span>
              </div>

              <h3 class="text-base font-bold text-slate-800 m-0">{{ quiz.title }}</h3>
              <p v-if="quiz.description" class="text-xs text-slate-400 mt-1 line-clamp-2">{{ quiz.description }}</p>

              <div class="mt-4 pt-3 border-t border-slate-100 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-clock text-[11px]"></i> Duration:</span>
                  <span class="font-bold text-slate-700">{{ quiz.duration }} min</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-list text-[11px]"></i> Questions:</span>
                  <span class="font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ quiz.totalQuestions }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-refresh text-[11px]"></i> Attempts allowed:</span>
                  <span class="font-medium text-slate-700">{{ quiz.maxAttempts }}</span>
                </div>
                <div v-if="quiz.startAt || quiz.endAt" class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-calendar text-[11px]"></i> Available:</span>
                  <span class="font-medium text-slate-700 text-[11px]">{{ formatWindow(quiz) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-users text-[11px]"></i> Submitted:</span>
                  <span class="font-bold text-emerald-600">{{ quiz.submittedCount }}/{{ quiz.totalStudents }} students</span>
                </div>
              </div>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
              <div class="flex items-center gap-1">
                <Button icon="pi pi-eye" text rounded size="small" severity="secondary" title="Preview" class="!w-7 !h-7 !text-slate-400 hover:!text-indigo-600" @click="openPreview(quiz)" />
                <Button icon="pi pi-pencil" text rounded size="small" severity="secondary" title="Edit" class="!w-7 !h-7 !text-slate-400 hover:!text-indigo-600" @click="openQuizModal(quiz)" />
                <Button icon="pi pi-trash" text rounded size="small" severity="secondary" title="Delete" class="!w-7 !h-7 !text-slate-400 hover:!text-rose-500" @click="deleteQuiz(quiz.id)" />
              </div>

              <Button
                v-if="quiz.status === 'Draft'"
                label="Publish"
                size="small"
                text
                class="!bg-emerald-50 hover:!bg-emerald-100 !text-emerald-600 !rounded-xl !text-xs !font-bold !px-3 !py-1.5"
                @click="publishQuiz(quiz)"
              />
              <Button
                v-else-if="quiz.status === 'Published'"
                label="Close Quiz"
                size="small"
                text
                class="!bg-amber-50 hover:!bg-amber-100 !text-amber-600 !rounded-xl !text-xs !font-bold !px-3 !py-1.5"
                @click="closeQuiz(quiz)"
              />
              <span v-else class="text-[11px] text-slate-400 font-semibold">Closed</span>
            </div>
          </div>

          <div v-if="quizzes.length === 0" class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200">
            <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
            <p class="text-xs text-slate-500">This class doesn't have any quizzes or exams yet.</p>
          </div>
        </div>
      </div>

      <!-- ============ TAB: SCORE REPORT ============ -->
      <div v-if="activeTab === 'scores'" class="space-y-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quiz / Exam</label>
          <Dropdown
            v-model="selectedScoreQuiz"
            :options="quizzes"
            option-label="title"
            option-value="id"
            placeholder="Select a quiz"
            size="small"
            class="!bg-slate-50 !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700 w-full sm:w-64"
          />
        </div>
        <ScoreTable v-if="selectedScoreQuiz" :quiz-id="selectedScoreQuiz" />
        <div v-else class="text-center py-12 bg-white rounded-2xl border border-slate-200">
          <p class="text-xs text-slate-500">Create a quiz first to see its score report here.</p>
        </div>
      </div>

      <!-- ============ TAB: FEEDBACK ============ -->
      <div v-if="activeTab === 'feedback'" class="space-y-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quiz / Exam</label>
          <Dropdown
            v-model="selectedFeedbackQuiz"
            :options="quizzes"
            option-label="title"
            option-value="id"
            placeholder="Select a quiz"
            size="small"
            class="!bg-slate-50 !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700 w-full sm:w-64"
          />
        </div>
        <FeedbackTable v-if="selectedFeedbackQuiz" :quiz-id="selectedFeedbackQuiz" />
        <div v-else class="text-center py-12 bg-white rounded-2xl border border-slate-200">
          <p class="text-xs text-slate-500">Create a quiz first to send feedback to students.</p>
        </div>
      </div>
    </template>

    <!-- ============ MODAL: QUIZ BUILDER ============ -->
    <Dialog
      :visible="showQuizModal"
      @update:visible="(val) => { if (!val) closeQuizModal() }"
      modal
      class="w-full max-w-2xl"
    >
      <template #header>
        <h3 class="text-sm font-bold text-slate-800 m-0">
          {{ editingQuizId ? 'Edit Quiz' : 'Create Quiz / Exam' }}
        </h3>
      </template>

      <div class="space-y-4 text-xs">
        <!-- Title & Description -->
        <div>
          <label class="block font-bold text-slate-700 mb-1">Quiz / Exam Title *</label>
          <InputText
            v-model="builderForm.title"
            size="small"
            placeholder="e.g. Midterm Exam - Vue.js Basics"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg"
          />
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1">Description</label>
          <Textarea
            v-model="builderForm.description"
            rows="2"
            size="small"
            placeholder="A short summary of this quiz for students..."
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg"
          />
        </div>

        <!-- Duration, Attempts, Pass mark -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Duration (minutes) *</label>
            <InputNumber v-model="builderForm.duration" :min="1" size="small" class="w-full" input-class="w-full !bg-slate-50 !border-slate-200 !rounded-lg" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Attempts allowed *</label>
            <InputNumber v-model="builderForm.maxAttempts" :min="1" size="small" class="w-full" input-class="w-full !bg-slate-50 !border-slate-200 !rounded-lg" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Pass mark (%)</label>
            <InputNumber v-model="builderForm.passMark" :min="0" :max="100" size="small" placeholder="e.g. 50" class="w-full" input-class="w-full !bg-slate-50 !border-slate-200 !rounded-lg" />
          </div>
        </div>

        <!-- Availability window -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Opens at</label>
            <DatePicker v-model="startAtDate" showTime hourFormat="24" showIcon iconDisplay="input" dateFormat="yy-mm-dd" size="small" class="w-full" input-class="w-full !bg-slate-50 !border-slate-200 !rounded-lg" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Closes at</label>
            <DatePicker v-model="endAtDate" showTime hourFormat="24" showIcon iconDisplay="input" dateFormat="yy-mm-dd" size="small" class="w-full" input-class="w-full !bg-slate-50 !border-slate-200 !rounded-lg" />
          </div>
        </div>
        <p class="text-[11px] text-slate-400 -mt-2">Leave these blank for no fixed schedule. A published quiz is only open to students inside this window.</p>

        <!-- Shuffle toggles -->
        <div class="flex flex-wrap gap-3">
          <label class="flex items-center gap-2 cursor-pointer bg-slate-50 border border-slate-200 px-2.5 py-1.5 rounded-lg">
            <Checkbox v-model="builderForm.shuffleQuestions" binary />
            <span class="font-semibold text-slate-700">Shuffle question order</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer bg-slate-50 border border-slate-200 px-2.5 py-1.5 rounded-lg">
            <Checkbox v-model="builderForm.shuffleOptions" binary />
            <span class="font-semibold text-slate-700">Shuffle answer options</span>
          </label>
        </div>

        <!-- Question picker -->
        <div class="pt-1 border-t border-slate-100 space-y-2">
          <div class="flex items-center justify-between">
            <label class="block font-bold text-slate-700">
              Select Questions from the Question Bank
              <span class="text-indigo-600">({{ builderForm.selectedQuestionIds.length }} selected)</span>
            </label>
            <Button
              label="Add New Question"
              icon="pi pi-plus"
              text
              size="small"
              class="!text-indigo-600 hover:!text-indigo-700 !font-bold !text-[11px] !p-0"
              @click="openInlineQuestionEditor"
            />
          </div>

          <div class="flex items-center gap-2">
            <InputText
              v-model="pickerSearch"
              size="small"
              placeholder="Search questions..."
              class="flex-1 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
            <Dropdown
              v-model="pickerTypeFilter"
              :options="pickerTypeOptions"
              option-label="label"
              option-value="value"
              size="small"
              class="!bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
          </div>

          <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 max-h-56 overflow-y-auto">
            <div
              v-for="q in filteredPickerQuestions"
              :key="q.id"
              class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50"
            >
              <Checkbox :value="q.id" v-model="builderForm.selectedQuestionIds" class="shrink-0" />
              <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 shrink-0">{{ formatType(q.type) }}</span>
              <span class="text-slate-700 flex-1 truncate">{{ q.title }}</span>
              <span
                :class="{
                  'bg-emerald-50 text-emerald-600 border-emerald-200': q.difficulty === 'Easy',
                  'bg-amber-50 text-amber-600 border-amber-200': q.difficulty === 'Medium',
                  'bg-rose-50 text-rose-600 border-rose-200': q.difficulty === 'Hard'
                }"
                class="text-[10px] font-bold px-2 py-0.5 rounded border shrink-0"
              >
                {{ q.difficulty }}
              </span>
            </div>
            <div v-if="filteredPickerQuestions.length === 0" class="px-3 py-6 text-center text-xs text-slate-400">
              No questions match this filter. Try "Add New Question" to create one.
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <Button label="Cancel" size="small" class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="closeQuizModal" />
        <Button label="Save" size="small" class="!bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg !text-xs" @click="saveQuiz" />
      </template>
    </Dialog>

    <!-- ============ MODAL: PREVIEW ============ -->
    <Dialog
      :visible="showPreviewModal"
      @update:visible="(val) => { if (!val) showPreviewModal = false }"
      modal
      class="w-full max-w-2xl"
    >
      <template #header>
        <div>
          <h3 class="text-sm font-bold text-slate-800 m-0">Preview: {{ previewQuiz?.title }}</h3>
          <p class="text-xs text-slate-400 mt-0.5 mb-0">{{ previewQuiz?.totalQuestions }} questions • {{ previewQuiz?.duration }} min • {{ previewQuiz?.maxAttempts }} attempt(s) allowed</p>
        </div>
      </template>

      <template v-if="showPreviewModal">
        <div class="space-y-3 text-xs">
          <p v-if="previewQuiz?.description" class="text-slate-600 bg-slate-50 border border-slate-200 rounded-xl p-3">{{ previewQuiz.description }}</p>

          <div
            v-for="(q, idx) in previewQuiz?.questions ?? []"
            :key="q.id"
            class="border border-slate-200 rounded-xl p-4 space-y-2"
          >
            <div class="flex items-start gap-2">
              <span class="text-xs font-bold text-slate-400 font-mono">Q{{ idx + 1 }}.</span>
              <h4 class="text-sm font-semibold text-slate-800 m-0">{{ q.title }}</h4>
            </div>

            <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-6">
              <div
                v-for="(opt, oIdx) in q.options"
                :key="oIdx"
                :class="opt.isCorrect ? 'bg-emerald-50/80 border-emerald-300 text-emerald-800 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-600'"
                class="p-2 rounded-lg border text-xs flex items-center justify-between"
              >
                <span>{{ String.fromCharCode(65 + oIdx) }}. {{ opt.text }}</span>
                <i v-if="opt.isCorrect" class="pi pi-check-circle text-emerald-600 text-xs"></i>
              </div>
            </div>

            <div v-else-if="q.type === 'matching'" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-6">
              <div v-for="(pair, pIdx) in q.matchingPairs" :key="pIdx" class="p-2 rounded-lg border border-slate-200 bg-slate-50 text-xs flex items-center gap-2 text-slate-600">
                <span class="font-semibold text-slate-700">{{ pair.leftText }}</span>
                <i class="pi pi-arrow-right-arrow-left text-slate-300 text-[10px]"></i>
                <span>{{ pair.rightText }}</span>
              </div>
            </div>

            <div v-else class="pl-6">
              <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-500">
                <span class="font-bold text-slate-700">Grading notes / model answer:</span> {{ q.sampleAnswer || 'None provided' }}
              </div>
            </div>
          </div>

          <div v-if="!previewQuiz?.questions?.length" class="text-center py-8 text-xs text-slate-400">
            This quiz doesn't have any questions yet.
          </div>
        </div>
      </template>

      <template #footer>
        <Button label="Close" size="small" class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="showPreviewModal = false" />
      </template>
    </Dialog>

    <QuestionEditorModal
      v-model:visible="showQuestionEditor"
      :subjects="classInfo ? [{ id: classInfo.subject_id, code: '', name: classInfo.subject }] : []"
      :locked-subject-id="classInfo?.subject_id"
      :editing-question="null"
      @saved="onNewQuestionSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Button from 'primevue/button'
import SelectButton from 'primevue/selectbutton'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import DatePicker from 'primevue/datepicker'
import Checkbox from 'primevue/checkbox'
import api, { extractError } from '../../../api'
import ScoreTable from '../../../components/teacher/ScoreTable.vue'
import FeedbackTable from '../../../components/teacher/FeedbackTable.vue'
import QuestionEditorModal from '../../../components/teacher/QuestionEditorModal.vue'

const route = useRoute()
const assignmentId = computed(() => Number(route.params.assignmentId))

const tabs = [
  { label: 'Overview', value: 'overview' },
  { label: 'Quizzes & Exams', value: 'quizzes' },
  { label: 'Score Report', value: 'scores' },
  { label: 'Feedback', value: 'feedback' },
]
const validTabs = tabs.map(t => t.value)
const activeTab = ref(validTabs.includes(route.query.tab) ? route.query.tab : 'overview')

const classInfo = ref(null)
const quizzes = ref([])
const pickerQuestions = ref([])

const fetchClassInfo = async () => {
  const { data } = await api.get('/teacher/classes')
  classInfo.value = data.data.find(c => c.id === assignmentId.value) ?? null
}

const fetchQuizzes = async () => {
  if (!classInfo.value) return
  const { data } = await api.get('/teacher/quizzes', {
    params: { class_id: classInfo.value.class_id, subject_id: classInfo.value.subject_id },
  })
  quizzes.value = data.data
  if (!selectedScoreQuiz.value) selectedScoreQuiz.value = quizzes.value[0]?.id ?? null
  if (!selectedFeedbackQuiz.value) selectedFeedbackQuiz.value = quizzes.value[0]?.id ?? null
}

const fetchPickerQuestions = async () => {
  if (!classInfo.value) return
  const { data } = await api.get('/teacher/questions', { params: { subject_id: classInfo.value.subject_id } })
  pickerQuestions.value = data.data
}

onMounted(async () => {
  await fetchClassInfo()
  await Promise.all([fetchQuizzes(), fetchPickerQuestions()])
})

function formatType(type) {
  if (type === 'multiple_choice') return 'MC'
  if (type === 'true_false') return 'T/F'
  if (type === 'matching') return 'Matching'
  if (type === 'essay') return 'Essay'
  return type
}

function statusLabel(status) {
  return status
}

function statusBadgeClass(status) {
  if (status === 'Published') return 'bg-emerald-50 text-emerald-600 border-emerald-200'
  if (status === 'Draft') return 'bg-slate-100 text-slate-500 border-slate-200'
  return 'bg-rose-50 text-rose-500 border-rose-200'
}

function formatWindow(quiz) {
  const start = quiz.startAt ? quiz.startAt.replace('T', ' ') : null
  const end = quiz.endAt ? quiz.endAt.replace('T', ' ') : null
  if (start && end) return `${start} → ${end}`
  if (start) return `From ${start}`
  if (end) return `Until ${end}`
  return 'No fixed schedule'
}

/* ===== Quiz builder modal ===== */
const showQuizModal = ref(false)
const editingQuizId = ref(null)
const pickerSearch = ref('')
const pickerTypeFilter = ref('')

const pickerTypeOptions = [
  { label: 'All Types', value: '' },
  { label: 'Multiple Choice', value: 'multiple_choice' },
  { label: 'True/False', value: 'true_false' },
  { label: 'Matching', value: 'matching' },
  { label: 'Essay', value: 'essay' },
]

function defaultBuilderForm() {
  return {
    title: '',
    description: '',
    duration: 45,
    maxAttempts: 1,
    shuffleQuestions: false,
    shuffleOptions: false,
    passMark: null,
    startAt: '',
    endAt: '',
    selectedQuestionIds: [],
  }
}

const builderForm = ref(defaultBuilderForm())

// Converts between the "YYYY-MM-DDTHH:mm" strings the backend expects and a
// plain local Date (no timezone math) for the DatePicker widget.
function localStringToDate(str) {
  if (!str) return null
  const [datePart, timePart] = str.split('T')
  const [y, m, d] = datePart.split('-').map(Number)
  const [hh, mm] = (timePart || '00:00').split(':').map(Number)
  return new Date(y, m - 1, d, hh, mm)
}

function dateToLocalString(date) {
  if (!date) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

const startAtDate = computed({
  get: () => localStringToDate(builderForm.value.startAt),
  set: (val) => { builderForm.value.startAt = dateToLocalString(val) },
})

const endAtDate = computed({
  get: () => localStringToDate(builderForm.value.endAt),
  set: (val) => { builderForm.value.endAt = dateToLocalString(val) },
})

const filteredPickerQuestions = computed(() => {
  return pickerQuestions.value.filter(q => {
    const matchType = !pickerTypeFilter.value || q.type === pickerTypeFilter.value
    const matchSearch = !pickerSearch.value || q.title.toLowerCase().includes(pickerSearch.value.toLowerCase())
    return matchType && matchSearch
  })
})

async function openQuizModal(quiz = null) {
  pickerSearch.value = ''
  pickerTypeFilter.value = ''

  if (quiz) {
    editingQuizId.value = quiz.id
    try {
      const { data } = await api.get(`/teacher/quizzes/${quiz.id}`)
      const full = data.quiz
      builderForm.value = {
        title: full.title,
        description: full.description || '',
        duration: full.duration,
        maxAttempts: full.maxAttempts,
        shuffleQuestions: full.shuffleQuestions,
        shuffleOptions: full.shuffleOptions,
        passMark: full.passMark,
        startAt: full.startAt || '',
        endAt: full.endAt || '',
        selectedQuestionIds: full.questions.map(q => q.id),
      }
    } catch (error) {
      alert(extractError(error))
      return
    }
  } else {
    editingQuizId.value = null
    builderForm.value = defaultBuilderForm()
  }

  showQuizModal.value = true
}

function closeQuizModal() {
  showQuizModal.value = false
}

async function saveQuiz() {
  if (!builderForm.value.title.trim()) {
    alert('Please enter a title for the quiz.')
    return
  }

  const payload = {
    title: builderForm.value.title,
    description: builderForm.value.description || null,
    subject_id: classInfo.value.subject_id,
    class_id: classInfo.value.class_id,
    duration_minutes: builderForm.value.duration,
    max_attempts: builderForm.value.maxAttempts,
    shuffle_questions: builderForm.value.shuffleQuestions,
    shuffle_options: builderForm.value.shuffleOptions,
    pass_mark: builderForm.value.passMark || null,
    start_at: builderForm.value.startAt || null,
    end_at: builderForm.value.endAt || null,
    question_ids: builderForm.value.selectedQuestionIds,
  }

  try {
    if (editingQuizId.value) {
      await api.put(`/teacher/quizzes/${editingQuizId.value}`, payload)
    } else {
      await api.post('/teacher/quizzes', payload)
    }
    closeQuizModal()
    await fetchQuizzes()
  } catch (error) {
    alert(extractError(error))
  }
}

async function deleteQuiz(id) {
  if (confirm('Are you sure you want to delete this quiz?')) {
    try {
      await api.delete(`/teacher/quizzes/${id}`)
      await fetchQuizzes()
    } catch (error) {
      alert(extractError(error))
    }
  }
}

async function publishQuiz(quiz) {
  try {
    await api.post(`/teacher/quizzes/${quiz.id}/publish`)
    await fetchQuizzes()
  } catch (error) {
    alert(extractError(error))
  }
}

async function closeQuiz(quiz) {
  if (!confirm('Close this quiz? Students will no longer be able to take it.')) return
  try {
    await api.post(`/teacher/quizzes/${quiz.id}/close`)
    await fetchQuizzes()
  } catch (error) {
    alert(extractError(error))
  }
}

/* ===== Inline "create new question" from the builder ===== */
const showQuestionEditor = ref(false)

function openInlineQuestionEditor() {
  showQuestionEditor.value = true
}

function onNewQuestionSaved(question) {
  pickerQuestions.value.unshift(question)
  builderForm.value.selectedQuestionIds.push(question.id)
}

/* ===== Preview modal ===== */
const showPreviewModal = ref(false)
const previewQuiz = ref(null)

async function openPreview(quiz) {
  try {
    const { data } = await api.get(`/teacher/quizzes/${quiz.id}`)
    previewQuiz.value = data.quiz
    showPreviewModal.value = true
  } catch (error) {
    alert(extractError(error))
  }
}

/* ===== Score / Feedback tabs ===== */
const selectedScoreQuiz = ref(null)
const selectedFeedbackQuiz = ref(null)
</script>

<style scoped>
.teacher-tabs :deep(.p-togglebutton) {
  border: 0;
  background: transparent;
  color: #64748b;
  font-weight: 500;
  font-size: 0.75rem;
  padding: 0.3rem 0.75rem;
  border-radius: 0.5rem;
  white-space: nowrap;
}

.teacher-tabs :deep(.p-togglebutton:hover) {
  color: #1e293b;
}

.teacher-tabs :deep(.p-togglebutton-checked) {
  background: #ffffff;
  color: #4f46e5;
  font-weight: 700;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
</style>
