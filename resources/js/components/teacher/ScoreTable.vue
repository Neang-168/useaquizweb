<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">Total Submissions</span>
        <h3 class="text-lg font-bold text-slate-800 m-0 mt-1">{{ filteredStudents.length }}</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">Average Score</span>
        <h3 class="text-lg font-bold text-indigo-600 m-0 mt-1">{{ averageScore }} / 100</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">Passed</span>
        <h3 class="text-lg font-bold text-emerald-600 m-0 mt-1">{{ passedCount }}</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">Failed</span>
        <h3 class="text-lg font-bold text-rose-600 m-0 mt-1">{{ failedCount }}</h3>
      </div>
    </div>

    <!-- Score Table -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="text-sm font-bold text-slate-800 m-0">
            Student Scores<span v-if="quizTitle"> - {{ quizTitle }}</span>
          </h3>
          <span v-if="className" class="text-xs text-slate-400">Class: {{ className }}</span>
        </div>
        <div class="flex items-center gap-3">
          <div class="relative w-full sm:w-56">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="searchQuery"
              size="small"
              placeholder="Search by name or student ID..."
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
          </div>
          <Button
            label="Export"
            icon="pi pi-file-excel"
            size="small"
            class="!bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600 !text-white !rounded-lg !text-xs shadow-sm whitespace-nowrap"
            @click="exportReport"
          />
        </div>
      </div>

      <DataTable :value="filteredStudents" dataKey="id" responsiveLayout="scroll" class="p-datatable-sm">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No submissions yet.
          </div>
        </template>

        <Column header="#">
          <template #body="{ index }">
            <span class="font-mono text-slate-400 text-xs">{{ index + 1 }}</span>
          </template>
        </Column>

        <Column field="studentId" header="STUDENT ID">
          <template #body="{ data }">
            <span class="font-mono font-bold text-indigo-600 text-xs">{{ data.studentId }}</span>
          </template>
        </Column>

        <Column field="name" header="NAME">
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-xs">{{ data.name }}</span>
          </template>
        </Column>

        <Column field="submittedAt" header="SUBMITTED AT">
          <template #body="{ data }">
            <span class="text-slate-500 text-xs">{{ data.submittedAt }}</span>
          </template>
        </Column>

        <Column header="MCQ SCORE" class="!text-center">
          <template #body="{ data }">
            <span class="font-medium text-slate-700 text-xs">{{ data.mcqScore }}/50</span>
          </template>
        </Column>

        <Column header="ESSAY SCORE" class="!text-center">
          <template #body="{ data }">
            <span v-if="data.essayNeedsGrade" class="bg-amber-50 text-amber-600 font-bold px-2 py-0.5 rounded text-[10px] border border-amber-200">
              Needs Grading
            </span>
            <span v-else class="font-medium text-slate-700 text-xs">{{ data.essayScore }}/50</span>
          </template>
        </Column>

        <Column header="TOTAL" class="!text-center">
          <template #body="{ data }">
            <span class="font-bold text-indigo-600 text-sm">{{ data.mcqScore + (data.essayScore || 0) }}</span>
          </template>
        </Column>

        <Column header="RESULT" class="!text-center">
          <template #body="{ data }">
            <span
              :class="(data.mcqScore + (data.essayScore || 0)) >= 50 ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
              class="font-bold px-2.5 py-0.5 rounded-full text-[10px] border"
            >
              {{ (data.mcqScore + (data.essayScore || 0)) >= 50 ? 'PASSED' : 'FAILED' }}
            </span>
          </template>
        </Column>

        <Column header="ACTION" class="!text-center">
          <template #body="{ data }">
            <Button
              label="Grade Essay"
              size="small"
              text
              class="!bg-indigo-50 hover:!bg-indigo-600 !text-indigo-600 hover:!text-white !rounded-lg !text-[11px] !px-2.5 !py-1"
              @click="openGradingModal(data)"
            />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Modal: Grade Essay Question -->
    <Dialog
      :visible="!!selectedStudentForGrading"
      @update:visible="(val) => { if (!val) selectedStudentForGrading = null }"
      modal
      class="w-full max-w-md"
    >
      <template #header>
        <div>
          <h3 class="text-sm font-bold text-slate-800 m-0">Grade Essay - {{ selectedStudentForGrading?.name }}</h3>
          <p class="text-xs text-slate-400 mt-0.5 mb-0">Student ID: {{ selectedStudentForGrading?.studentId }}</p>
        </div>
      </template>

      <div class="text-xs">
        <label class="block font-bold text-slate-700 mb-1">Enter Essay Score (out of 50) *</label>
        <InputNumber
          v-model="inputEssayScore"
          :min="0"
          :max="50"
          size="small"
          class="w-full"
          input-class="w-full !bg-slate-50 !border-slate-200 !rounded-lg !font-bold !text-indigo-600"
        />
      </div>

      <template #footer>
        <Button label="Cancel" size="small" class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="selectedStudentForGrading = null" />
        <Button label="Save Score" size="small" class="!bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg !text-xs" @click="saveEssayScore" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import api, { extractError } from '../../api'

const props = defineProps({
  quizId: { type: [Number, String], default: null },
  quizTitle: { type: String, default: '' },
  className: { type: String, default: '' },
})

const students = ref([])
const loading = ref(false)
const searchQuery = ref('')

const selectedStudentForGrading = ref(null)
const inputEssayScore = ref(0)

const fetchScores = async () => {
  if (!props.quizId) {
    students.value = []
    return
  }
  loading.value = true
  try {
    const { data } = await api.get(`/teacher/quizzes/${props.quizId}/scores`)
    students.value = data.data
  } catch (error) {
    alert(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(fetchScores)
watch(() => props.quizId, fetchScores)

const filteredStudents = computed(() => {
  if (!searchQuery.value) return students.value
  const q = searchQuery.value.toLowerCase()
  return students.value.filter(s => s.name?.toLowerCase().includes(q) || s.studentId?.toLowerCase().includes(q))
})

const averageScore = computed(() => {
  if (students.value.length === 0) return 0
  const total = students.value.reduce((acc, s) => acc + (s.mcqScore + (s.essayScore || 0)), 0)
  return Math.round(total / students.value.length)
})

const passedCount = computed(() => students.value.filter(s => (s.mcqScore + (s.essayScore || 0)) >= 50).length)
const failedCount = computed(() => students.value.filter(s => (s.mcqScore + (s.essayScore || 0)) < 50).length)

function openGradingModal(student) {
  selectedStudentForGrading.value = student
  inputEssayScore.value = student.essayScore || 0
}

async function saveEssayScore() {
  if (!selectedStudentForGrading.value) return

  try {
    await api.put(`/teacher/submissions/${selectedStudentForGrading.value.id}/essay-score`, {
      essay_score: inputEssayScore.value,
    })
    selectedStudentForGrading.value = null
    await fetchScores()
  } catch (error) {
    alert(extractError(error))
  }
}

function exportReport() {
  const header = ['#', 'Student ID', 'Name', 'Submitted At', 'MCQ Score', 'Essay Score', 'Total', 'Result']
  const rows = filteredStudents.value.map((s, i) => [
    i + 1,
    s.studentId,
    s.name,
    s.submittedAt,
    s.mcqScore,
    s.essayScore ?? '',
    s.mcqScore + (s.essayScore || 0),
    (s.mcqScore + (s.essayScore || 0)) >= 50 ? 'PASSED' : 'FAILED',
  ])

  const csv = [header, ...rows].map(row => row.map(cell => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(',')).join('\n')
  const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `score-report-${props.quizTitle || 'quiz'}.csv`
  link.click()
  URL.revokeObjectURL(url)
}
</script>
