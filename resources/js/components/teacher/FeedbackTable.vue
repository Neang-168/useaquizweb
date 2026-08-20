<template>
  <div class="space-y-3">
    <!-- Stats Summary -->
    <!-- <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400 uppercase">Total Submissions</span>
        <h3 class="text-lg font-bold text-slate-800 m-0 mt-1">{{ students.length }}</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400 uppercase">Passed</span>
        <h3 class="text-lg font-bold text-emerald-600 m-0 mt-1">{{ passedStudents.length }}</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-rose-200 bg-rose-50/20">
        <span class="text-[11px] font-bold text-rose-500 uppercase">Failed (needs support)</span>
        <h3 class="text-lg font-bold text-rose-600 m-0 mt-1">{{ failedStudents.length }}</h3>
      </div>
    </div> -->

    <!-- Student List Table -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="text-sm font-bold text-slate-800 m-0">Students & Results</h3>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <div class="relative w-full sm:w-56">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="searchQuery"
              size="small"
              placeholder="Search by name or student ID..."
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
          </div>
          <div class="flex gap-2">
            <Button
              label="All"
              size="small"
              :class="filterStatus === 'all' ? '!bg-slate-800 !border-slate-800 !text-white' : '!bg-slate-100 !border-slate-100 !text-slate-600 hover:!bg-slate-200'"
              class="!rounded-lg !text-xs !font-semibold !px-3 !py-1"
              @click="filterStatus = 'all'"
            />
            <Button
              label="Failed Only"
              size="small"
              :class="filterStatus === 'failed' ? '!bg-rose-600 !border-rose-600 !text-white' : '!bg-rose-50 !border-rose-50 !text-rose-600 hover:!bg-rose-100'"
              class="!rounded-lg !text-xs !font-semibold !px-3 !py-1"
              @click="filterStatus = 'failed'"
            />
          </div>
          <SplitButton
            label="Export CSV"
            icon="pi pi-file-excel"
            size="small"
            :model="exportMenuItems"
            class="!text-xs whitespace-nowrap export-split-button"
            @click="exportCsv"
          />
        </div>
      </div>

      <DataTable :value="displayedStudents" dataKey="submissionId" :loading="loading"
        paginator :rows="rows" v-model:first="first" :rowsPerPageOptions="[10, 20, 50]"
        scrollable scrollHeight="calc(100vh - 428px)"
        responsiveLayout="scroll" class="p-datatable-sm feedback-table">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No submissions yet.
          </div>
        </template>

        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ displayedStudents.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, displayedStudents.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ displayedStudents.length }}</span>
          </span>
        </template>

        <Column header="NO" style="width: 60px; padding-left: 1.25rem">
          <template #body="{ index }">
            <span class="text-slate-400 font-semibold text-sm">{{ index + 1 }}</span>
          </template>
        </Column>

        <Column field="studentId" header="STUDENT ID">
          <template #body="{ data }">
            <span class="font-mono font-bold text-indigo-600 text-sm">{{ data.studentId }}</span>
          </template>
        </Column>

        <Column field="name" header="NAME">
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm">{{ data.name }}</span>
          </template>
        </Column>

        <Column field="score" header="SCORE">
          <template #body="{ data }">
            <span class="font-bold text-slate-700 text-sm">{{ data.score }} / 100</span>
          </template>
        </Column>

        <Column header="RESULT">
          <template #body="{ data }">
            <span
              :class="data.passed ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block"
              :title="`Pass mark: ${data.passMark}`"
            >
              {{ data.passed ? 'PASSED' : 'FAILED' }}
            </span>
          </template>
        </Column>

        <Column header="FEEDBACK STATUS">
          <template #body="{ data }">
            <span v-if="data.hasFeedbackSent" class="text-emerald-600 font-semibold inline-flex items-center gap-1 text-sm">
              <i class="pi pi-check-circle text-sm"></i> Sent
            </span>
            <span v-else-if="!data.passed" class="text-rose-500 font-semibold inline-flex items-center gap-1 text-sm">
              <i class="pi pi-exclamation-circle text-sm"></i> Needs Feedback
            </span>
            <span v-else class="text-slate-400 text-sm">-</span>
          </template>
        </Column>

        <Column header="ACTION">
          <template #body="{ data }">
            <Button
              v-if="!data.passed"
              label="Send Study Guidance"
              icon="pi pi-send"
              size="small"
              class="!bg-rose-500 hover:!bg-rose-600 !border-rose-500 !text-white !rounded-xl !text-xs !px-3 !py-1.5 shadow-xs"
              @click="openFeedbackModal(data)"
            />
            <Button
              v-else
              label="Send Feedback"
              size="small"
              class="!bg-slate-100 hover:!bg-slate-200 !border-slate-100 !text-slate-600 !rounded-xl !text-xs !px-3 !py-1.5"
              @click="openFeedbackModal(data)"
            />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Modal: Send Feedback to Student -->
    <Dialog
      :visible="!!selectedStudent"
      @update:visible="(val) => { if (!val) selectedStudent = null }"
      modal
      class="w-full max-w-lg"
    >
      <template #header>
        <div>
          <h3 class="text-sm font-bold text-slate-800 m-0">
            Send Feedback to: <span class="text-indigo-600">{{ selectedStudent?.name }}</span>
          </h3>
          <p class="text-xs text-slate-400 mt-0.5 mb-0">
            Score: <span class="font-bold text-rose-600">{{ selectedStudent?.score }}/100</span>
          </p>
        </div>
      </template>

      <div class="space-y-3 text-xs">
        <div>
          <label class="block font-bold text-slate-700 mb-1">Quick Message Templates:</label>
          <div class="flex flex-wrap gap-2">
            <Button
              label="+ Suggest reviewing the material"
              size="small"
              text
              class="!bg-indigo-50 hover:!bg-indigo-100 !text-indigo-700 !border !border-indigo-200 !rounded-lg !text-[11px] !px-2.5 !py-1"
              @click="applyTemplate('restudy')"
            />
            <Button
              label="+ Invite for a 1-on-1 consultation"
              size="small"
              text
              class="!bg-amber-50 hover:!bg-amber-100 !text-amber-700 !border !border-amber-200 !rounded-lg !text-[11px] !px-2.5 !py-1"
              @click="applyTemplate('consult')"
            />
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1">Feedback Message *</label>
          <Textarea
            v-model="feedbackMessage"
            rows="5"
            size="small"
            placeholder="Write study guidance or encouragement here..."
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg !text-slate-700 leading-relaxed"
          />
        </div>
      </div>

      <template #footer>
        <Button label="Cancel" size="small" class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="selectedStudent = null" />
        <Button label="Send to Student" icon="pi pi-send" size="small" class="!bg-rose-600 hover:!bg-rose-700 !border-rose-600 !text-white !rounded-lg !text-xs" @click="sendFeedback" />
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
import api, { extractError } from '../../api'
import { downloadCsv, downloadXlsx } from '../../utils/exportTable'

const props = defineProps({
  quizId: { type: [Number, String], default: null },
})

const filterStatus = ref('all')
const searchQuery = ref('')
const selectedStudent = ref(null)
const feedbackMessage = ref('')
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
    alert(extractError(error))
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
    s.studentId?.toLowerCase().includes(q)
  )
})

// Jump back to page 1 whenever the underlying list changes shape, so the
// paginator never gets stranded past the end of a smaller filtered list.
watch(displayedStudents, () => {
  first.value = 0
})

const exportHeader = ['#', 'Student ID', 'Name', 'Score', 'Result', 'Feedback Status']

const exportRows = () => displayedStudents.value.map((s, i) => [
  i + 1,
  s.studentId,
  s.name,
  `${s.score}/100`,
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
    feedbackMessage.value = `Hi ${selectedStudent.value.name},\n\nYour latest result was ${selectedStudent.value.score}/100, which didn't reach the pass mark. Please review the lesson material again, and feel free to reach out if you have any questions.`
  } else if (type === 'consult') {
    feedbackMessage.value = `Hi ${selectedStudent.value.name},\n\nI noticed your recent score (${selectedStudent.value.score}/100) could use some improvement. Let's schedule a time to meet and go over your study approach together.`
  }
}

async function sendFeedback() {
  if (!feedbackMessage.value.trim()) {
    alert('Please enter a feedback message.')
    return
  }

  try {
    await api.post('/teacher/feedback', {
      quiz_submission_id: selectedStudent.value.submissionId,
      message: feedbackMessage.value,
    })
    alert(`Feedback sent to "${selectedStudent.value.name}" successfully!`)
    selectedStudent.value = null
    await fetchFeedbackList()
  } catch (error) {
    alert(extractError(error))
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
