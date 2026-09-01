<template>
  <div class="space-y-3">
    <!-- Student List Tree Table -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="text-sm font-bold text-slate-800 m-0">Students & Results</h3>
          <p class="text-[11px] text-slate-400 m-0 mt-0.5">Expand a student to see how they scored on each question.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <div class="relative w-full sm:w-56">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText v-model="searchQuery" size="small" placeholder="Search by name or username..."
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs" />
          </div>
          <div class="flex gap-2">
            <Button label="All" size="small"
              :class="filterStatus === 'all' ? '!bg-slate-800 !border-slate-800 !text-white' : '!bg-slate-100 !border-slate-100 !text-slate-600 hover:!bg-slate-200'"
              class="!rounded-lg !text-xs !font-semibold !px-3 !py-1" @click="filterStatus = 'all'" />
            <Button label="Failed Only" size="small"
              :class="filterStatus === 'failed' ? '!bg-rose-600 !border-rose-600 !text-white' : '!bg-rose-50 !border-rose-50 !text-rose-600 hover:!bg-rose-100'"
              class="!rounded-lg !text-xs !font-semibold !px-3 !py-1" @click="filterStatus = 'failed'" />
          </div>
          <SplitButton label="Export CSV" icon="pi pi-file-excel" size="small" :model="exportMenuItems"
            class="!text-xs whitespace-nowrap export-split-button" @click="exportCsv" />
        </div>
      </div>

      <DataTable :value="displayedStudents" dataKey="submissionId" :loading="loading" paginator :rows="rows"
        v-model:first="first" :rowsPerPageOptions="[10, 20, 50]" scrollable scrollHeight="calc(100vh - 460px)"
        class="p-datatable-sm feedback-table">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No submissions yet.
          </div>
        </template>

        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ displayedStudents.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, displayedStudents.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ displayedStudents.length }}</span> students
          </span>
        </template>
        <Column header="USERNAME" style="width: 130px; padding-left: 1.25rem">
          <template #body="{ data }">
            <span class="font-mono font-bold text-indigo-600 text-sm">{{ data.username }}</span>
          </template>
        </Column>

        <Column field="name" header="STUDENT" style="min-width: 200px">
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm">{{ data.name }}</span>
          </template>
        </Column>

        <Column field="nameKh" header="NAME (KH)" style="min-width: 160px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm font-khmer">{{ data.nameKh || '—' }}</span>
          </template>
        </Column>

        <Column header="POINTS" style="width: 110px">
          <template #body="{ data }">
            <span class="font-bold text-slate-700 text-sm">{{ data.score }} / {{ data.totalPoints }}</span>
          </template>
        </Column>

        <Column header="%" style="width: 80px">
          <template #body="{ data }">
            <span class="font-bold text-sm" :class="data.passed ? 'text-emerald-600' : 'text-rose-600'">{{ data.scorePercentage }}%</span>
          </template>
        </Column>

        <Column header="RESULT" style="width: 140px">
          <template #body="{ data }">
            <span
              :class="data.passed ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block"
              :title="`Pass mark: ${data.passMark}%`">
              {{ data.passed ? 'PASSED' : 'FAILED' }}
            </span>
          </template>
        </Column>

        <Column header="FEEDBACK STATUS" style="width: 160px">
          <template #body="{ data }">
            <span v-if="data.hasFeedbackSent"
              class="text-emerald-600 font-semibold inline-flex items-center gap-1 text-sm">
              <i class="pi pi-check-circle text-sm"></i> Sent
            </span>
            <span v-else-if="!data.passed" class="text-rose-500 font-semibold inline-flex items-center gap-1 text-sm">
              <i class="pi pi-exclamation-circle text-sm"></i> Needs Feedback
            </span>
            <span v-else class="text-slate-400 text-sm">-</span>
          </template>
        </Column>

        <Column header="ACTION" style="width: 300px">
          <template #body="{ data }">
            <div class="flex items-center gap-2 flex-wrap">
              <Button label="View Answer" icon="pi pi-eye" size="small" outlined
                class="!border-indigo-200 !text-indigo-600 hover:!bg-indigo-50 !rounded-xl !text-xs !px-3 !py-1.5"
                @click="openViewAnswer(data)" />
              <Button v-if="!data.passed" label="Send" icon="pi pi-send" size="small"
                class="!bg-rose-500 hover:!bg-rose-600 !border-rose-500 !text-white !rounded-xl !text-xs !px-3 !py-1.5 shadow-xs"
                @click="openFeedbackModal(data)" />
              <Button v-else label="Send" icon="pi pi-send" size="small"
                class="!bg-slate-100 hover:!bg-slate-200 !border-slate-100 !text-slate-600 !rounded-xl !text-xs !px-3 !py-1.5"
                @click="openFeedbackModal(data)" />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Modal: Send Feedback to Student -->
    <Dialog :visible="!!selectedStudent" @update:visible="(val) => { if (!val) selectedStudent = null }" modal
      class="w-full max-w-lg">
      <template #header>
        <div>
          <h3 class="text-sm font-bold text-slate-800 m-0">
            Send Feedback to: <span class="text-indigo-600">{{ selectedStudent?.name }}</span>
          </h3>
          <p class="text-xs text-slate-400 mt-0.5 mb-0">
            Score: <span class="font-bold text-rose-600">{{ selectedStudent?.score }}/{{ selectedStudent?.totalPoints
              }}</span>
          </p>
        </div>
      </template>

      <div class="space-y-3 text-xs">
        <div>
          <label class="block font-bold text-slate-700 mb-1">Quick Message Templates:</label>
          <div class="flex flex-wrap gap-2">
            <Button label="+ Suggest reviewing the material" size="small" text
              class="!bg-indigo-50 hover:!bg-indigo-100 !text-indigo-700 !border !border-indigo-200 !rounded-lg !text-[11px] !px-2.5 !py-1"
              @click="applyTemplate('restudy')" />
            <Button label="+ Invite for a 1-on-1 consultation" size="small" text
              class="!bg-amber-50 hover:!bg-amber-100 !text-amber-700 !border !border-amber-200 !rounded-lg !text-[11px] !px-2.5 !py-1"
              @click="applyTemplate('consult')" />
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1">Feedback Message *</label>
          <Textarea v-model="feedbackMessage" rows="5" size="small"
            placeholder="Write study guidance or encouragement here..."
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg !text-slate-700 leading-relaxed" />
        </div>
      </div>

      <template #footer>
        <Button label="Cancel" size="small"
          class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs"
          @click="selectedStudent = null" />
        <Button label="Send to Student" icon="pi pi-send" size="small"
          class="!bg-rose-600 hover:!bg-rose-700 !border-rose-600 !text-white !rounded-lg !text-xs"
          @click="sendFeedback" />
      </template>
    </Dialog>

    <!-- Modal: View Student's Answers -->
    <Dialog :visible="!!viewingStudent" @update:visible="(val) => { if (!val) viewingStudent = null }" modal
      class="w-full max-w-3xl">
      <template #header>
        <div>
          <h3 class="text-sm font-bold text-slate-800 m-0">
            Answers: <span class="text-indigo-600">{{ viewingStudent?.name }}</span>
          </h3>
          <p class="text-xs text-slate-400 mt-0.5 mb-0">
            Score:
            <span class="font-bold" :class="viewingStudent?.passed ? 'text-emerald-600' : 'text-rose-600'">
              {{ viewingStudent?.score }}/{{ viewingStudent?.totalPoints }}
            </span>
          </p>
        </div>
      </template>

      <div class="space-y-4 max-h-[65vh] overflow-y-auto pr-1">
        <div v-for="(q, idx) in viewingStudent?.questions || []" :key="q.questionId"
          class="border border-[#D8E7EC] rounded-2xl p-4 space-y-3">
          <div class="flex items-start justify-between gap-2 border-b border-[#D8E7EC] pb-3">
            <div class="flex items-start gap-2">
              <span class="text-xs font-bold text-slate-400 font-mono">Q{{ idx + 1 }}.</span>
              <h4 class="text-sm font-semibold text-[#002060] m-0 leading-relaxed">{{ q.title }}</h4>
            </div>
            <span class="shrink-0 font-bold px-2.5 py-0.5 rounded-full text-[11px] border whitespace-nowrap"
              :class="q.isCorrect ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'">
              {{ q.isCorrect ? 'Correct' : (q.answered ? 'Incorrect' : 'Not answered') }} · {{ q.pointsAwarded }}/{{
                q.pointsPossible }} pts
            </span>
          </div>

          <div v-if="q.imageUrl"
            class="ml-6 inline-block rounded-xl border border-[#D8E7EC] bg-[#F8F8F8] overflow-hidden">
            <Image :src="q.imageUrl" :alt="q.imageAlt || ''" preview image-class="max-h-40 object-contain block" />
          </div>

          <!-- Multiple Choice / True False -->
          <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="grid grid-cols-1 gap-2 pl-6">
            <div v-for="(opt, oIdx) in q.options" :key="opt.id" :class="optionClass(opt)"
              class="p-2.5 rounded-xl border text-xs flex items-center gap-2">
              <Image v-if="opt.imageUrl" :src="opt.imageUrl" alt="" preview
                image-class="w-8 h-8 rounded-lg object-cover border border-[#D8E7EC] shrink-0 cursor-pointer" />
              <span class="flex-1">{{ String.fromCharCode(65 + oIdx) }}. {{ opt.text }}</span>
              <i v-if="opt.isCorrect" class="pi pi-check-circle text-[#002060] text-xs shrink-0"></i>
              <i v-else-if="opt.isSelected" class="pi pi-times-circle text-rose-500 text-xs shrink-0"></i>
            </div>
          </div>

          <!-- Matching -->
          <div v-else-if="q.type === 'matching'" class="space-y-2 pl-6">
            <div v-for="pair in q.matchingPairs" :key="pair.id" class="space-y-1.5">
              <div
                class="p-2.5 rounded-xl border text-xs flex items-center gap-2 bg-[#63C7DF]/15 border-[#63C7DF]/50 text-[#002060] font-bold">
                <Image v-if="pair.leftImageUrl" :src="pair.leftImageUrl" alt="" preview
                  image-class="w-8 h-8 rounded-lg object-cover border border-[#D8E7EC] shrink-0 cursor-pointer" />
                <span>{{ pair.leftText }}</span>
                <i class="pi pi-arrow-right-arrow-left text-[#63C7DF] text-[10px] shrink-0"></i>
                <Image v-if="pair.rightImageUrl" :src="pair.rightImageUrl" alt="" preview
                  image-class="w-8 h-8 rounded-lg object-cover border border-[#D8E7EC] shrink-0 cursor-pointer" />
                <span class="flex-1">{{ pair.rightText }}</span>
                <i class="pi pi-check-circle text-[#002060] text-xs shrink-0"></i>
              </div>
              <div v-if="pair.answered && !pair.isCorrect"
                class="p-2.5 rounded-xl border text-xs flex items-center gap-2 bg-rose-50 border-rose-300 text-rose-700">
                <Image v-if="pair.selectedRightImageUrl" :src="pair.selectedRightImageUrl" alt="" preview
                  image-class="w-8 h-8 rounded-lg object-cover border border-rose-200 shrink-0 cursor-pointer" />
                <span class="text-[10px] font-bold uppercase tracking-wide shrink-0">Their answer:</span>
                <span class="flex-1">{{ pair.selectedRightText }}</span>
                <i class="pi pi-times-circle text-rose-500 text-xs shrink-0"></i>
              </div>
              <p v-else-if="!pair.answered" class="text-[11px] text-slate-400 italic m-0 pl-1">Not answered</p>
            </div>
          </div>
        </div>

        <div v-if="!(viewingStudent?.questions || []).length" class="text-center py-8 text-xs text-slate-400">
          No question detail available.
        </div>
      </div>

      <template #footer>
        <Button label="Close" size="small"
          class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs"
          @click="viewingStudent = null" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import SplitButton from 'primevue/splitbutton'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'
import InputText from 'primevue/inputtext'
import Image from 'primevue/image'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../api'
import { downloadCsv, downloadXlsx } from '../../utils/exportTable'

const toast = useToast()

const props = defineProps({
  quizId: { type: [Number, String], default: null },
})

const filterStatus = ref('all')
const searchQuery = ref('')
const selectedStudent = ref(null)
const feedbackMessage = ref('')
const viewingStudent = ref(null)
const students = ref([])
const loading = ref(false)
const first = ref(0)
const rows = ref(10)

const fetchFeedbackList = async () => {
  if (!props.quizId) {
    students.value = []
    return
  }
  loading.value = true
  try {
    const { data } = await api.get(`/teacher/quizzes/${props.quizId}/feedback`)
    students.value = data.data
  } catch (error) {
    toast.add({ summary: 'Failed to load feedback list', ...toastFromError(error) })
  } finally {
    loading.value = false
  }
}

onMounted(fetchFeedbackList)
watch(() => props.quizId, fetchFeedbackList)

const passedStudents = computed(() => students.value.filter(s => s.passed))
const failedStudents = computed(() => students.value.filter(s => !s.passed))

const displayedStudents = computed(() => {
  const byStatus = filterStatus.value === 'failed' ? failedStudents.value : students.value

  if (!searchQuery.value.trim()) return byStatus

  const q = searchQuery.value.toLowerCase()
  return byStatus.filter(s =>
    s.name?.toLowerCase().includes(q) ||
    s.nameKh?.toLowerCase().includes(q) ||
    s.username?.toLowerCase().includes(q)
  )
})

// Jump back to page 1 whenever the underlying list changes shape, so the
// paginator never gets stranded past the end of a smaller filtered list.
watch(displayedStudents, () => {
  first.value = 0
})

const exportHeader = ['#', 'Username', 'Name', 'Name (KH)', 'Score', '%', 'Result', 'Feedback Status']

const exportRows = () => displayedStudents.value.map((s, i) => [
  i + 1,
  s.username,
  s.name,
  s.nameKh,
  `${s.score}/${s.totalPoints}`,
  s.scorePercentage + '%',
  s.passed ? 'PASSED' : 'FAILED',
  s.hasFeedbackSent ? 'Sent' : (s.passed ? '-' : 'Needs Feedback'),
])

function exportCsv() {
  downloadCsv('student-feedback.csv', exportHeader, exportRows())
}

function exportXlsx() {
  downloadXlsx('student-feedback.xlsx', exportHeader, exportRows(), 'Feedback')
}

const exportMenuItems = [
  {
    label: 'Export as CSV',
    icon: 'pi pi-file',
    command: exportCsv,
  },
  {
    label: 'Export as Excel (.xlsx)',
    icon: 'pi pi-file-excel',
    command: exportXlsx,
  },
]

function openViewAnswer(student) {
  viewingStudent.value = student
}

// Correct option: same highlight Question Bank uses for its correct answer.
// The student's own wrong pick (when it isn't the correct one) gets a red
// highlight instead; anything else stays neutral.
function optionClass(opt) {
  if (opt.isCorrect) return 'bg-[#63C7DF]/15 border-[#63C7DF]/50 text-[#002060] font-bold'
  if (opt.isSelected) return 'bg-rose-50 border-rose-300 text-rose-700 font-bold'
  return 'bg-[#F8F8F8] border-[#D8E7EC] text-slate-600'
}

function openFeedbackModal(student) {
  selectedStudent.value = student
  feedbackMessage.value = ''

  if (!student.passed) {
    applyTemplate('restudy')
  }
}

function applyTemplate(type) {
  if (!selectedStudent.value) return

  if (type === 'restudy') {
    feedbackMessage.value = `Hi ${selectedStudent.value.name},\n\nYour latest result was ${selectedStudent.value.score}/${selectedStudent.value.totalPoints}, which didn't reach the pass mark. Please review the lesson material again, and feel free to reach out if you have any questions.`
  } else if (type === 'consult') {
    feedbackMessage.value = `Hi ${selectedStudent.value.name},\n\nI noticed your recent score (${selectedStudent.value.score}/${selectedStudent.value.totalPoints}) could use some improvement. Let's schedule a time to meet and go over your study approach together.`
  }
}

async function sendFeedback() {
  if (!feedbackMessage.value.trim()) {
    toast.add({ severity: 'warn', summary: 'Feedback message required', detail: 'Please enter a feedback message.', life: 4000 })
    return
  }

  try {
    await api.post('/teacher/feedback', {
      quiz_submission_id: selectedStudent.value.submissionId,
      message: feedbackMessage.value,
    })
    toast.add({ severity: 'success', summary: 'Feedback sent', detail: `Feedback sent to "${selectedStudent.value.name}" successfully!`, life: 3000 })
    selectedStudent.value = null
    await fetchFeedbackList()
  } catch (error) {
    toast.add({ summary: 'Failed to send feedback', ...toastFromError(error) })
  }
}
</script>

<style scoped>
/* Header two sizes smaller, body two sizes larger, than the table's base text-xs. */
.feedback-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.feedback-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}

.export-split-button :deep(.p-splitbutton-button),
.export-split-button :deep(.p-splitbutton-dropdown) {
  background: #059669;
  border-color: #059669;
  color: #fff;
}

.export-split-button :deep(.p-splitbutton-button:hover),
.export-split-button :deep(.p-splitbutton-dropdown:hover) {
  background: #047857;
  border-color: #047857;
}
</style>
