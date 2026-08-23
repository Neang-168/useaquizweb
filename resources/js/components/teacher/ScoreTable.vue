<template>
  <div class="space-y-3">
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">Total Submissions</span>
        <h3 class="text-lg font-bold text-slate-800 m-0 mt-1">{{ filteredStudents.length }}</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">Average Score</span>
        <h3 class="text-lg font-bold text-indigo-600 m-0 mt-1">{{ averageScore }} / {{ totalPoints }}</h3>
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

    <!-- Insight Charts -->
    <div v-if="props.viewMode === 'chart' && students.length" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <h3 class="text-sm font-bold text-slate-800 m-0 mb-3">Score Distribution</h3>
        <div class="h-56">
          <Chart type="bar" :data="distributionChartData" :options="distributionChartOptions" class="h-full w-full" />
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <h3 class="text-sm font-bold text-slate-800 m-0 mb-3">Pass vs Fail</h3>
        <div class="relative h-56 flex items-center justify-center">
          <Chart type="doughnut" :data="passFailChartData" :options="passFailChartOptions" class="h-full w-full max-w-56 mx-auto" />
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <span class="text-2xl font-bold text-slate-800">{{ passPercentage }}%</span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pass Rate</span>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <h3 class="text-sm font-bold text-slate-800 m-0 mb-3">Class Performance (Spider Chart)</h3>
        <div class="h-56">
          <Chart type="radar" :data="radarChartData" :options="radarChartOptions" class="h-full w-full" />
        </div>
      </div>
    </div>

    <!-- Score Table -->
    <div v-if="props.viewMode === 'table'" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <!-- Result Filter -->
        <div class="flex flex-wrap gap-2">
          <Button
            label="All"
            size="small"
            :class="resultFilter === 'all' ? '!bg-slate-800 !border-slate-800 !text-white' : '!bg-slate-100 !border-slate-100 !text-slate-600 hover:!bg-slate-200'"
            class="!rounded-lg !text-xs !font-semibold !px-3 !py-1"
            @click="resultFilter = 'all'"
          />
          <Button
            label="Passed"
            size="small"
            :class="resultFilter === 'passed' ? '!bg-emerald-600 !border-emerald-600 !text-white' : '!bg-emerald-50 !border-emerald-50 !text-emerald-600 hover:!bg-emerald-100'"
            class="!rounded-lg !text-xs !font-semibold !px-3 !py-1"
            @click="resultFilter = 'passed'"
          />
          <Button
            label="Failed"
            size="small"
            :class="resultFilter === 'failed' ? '!bg-rose-600 !border-rose-600 !text-white' : '!bg-rose-50 !border-rose-50 !text-rose-600 hover:!bg-rose-100'"
            class="!rounded-lg !text-xs !font-semibold !px-3 !py-1"
            @click="resultFilter = 'failed'"
          />
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

      <DataTable :value="filteredStudents" dataKey="id"
        paginator :rows="rows" v-model:first="first" :rowsPerPageOptions="[10, 20, 50]"
        scrollable scrollHeight="calc(100vh - 490px)"
        responsiveLayout="scroll" class="p-datatable-sm score-table">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No submissions yet.
          </div>
        </template>

        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ filteredStudents.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredStudents.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ filteredStudents.length }}</span>
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

        <Column header="MCQ SCORE">
          <template #body="{ data }">
            <span class="font-medium text-slate-700 text-sm">{{ data.mcqScore }}</span>
          </template>
        </Column>

        <Column header="TOTAL">
          <template #body="{ data }">
            <span class="font-bold text-indigo-600 text-sm">{{ data.mcqScore + (data.essayScore || 0) }} / {{ totalPoints }}</span>
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

        <Column field="submittedAt" header="SUBMITTED AT">
          <template #body="{ data }">
            <span class="text-slate-500 text-sm">{{ data.submittedAt }}</span>
          </template>
        </Column>

        <Column header="TAB SWITCHES">
          <template #body="{ data }">
            <span
              v-if="data.tabSwitchCount > 0"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block bg-amber-50 text-amber-600 border-amber-200"
              :title="`Left the quiz tab ${data.tabSwitchCount} time${data.tabSwitchCount === 1 ? '' : 's'}`"
            >
              {{ data.tabSwitchCount }}
            </span>
            <span v-else class="text-slate-300 text-sm">—</span>
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import SplitButton from 'primevue/splitbutton'
import InputText from 'primevue/inputtext'
import Chart from 'primevue/chart'
import api, { extractError } from '../../api'
import { downloadCsv, downloadXlsx } from '../../utils/exportTable'

const props = defineProps({
  quizId: { type: [Number, String], default: null },
  quizTitle: { type: String, default: '' },
  viewMode: { type: String, default: 'table' },
})

const students = ref([])
const totalPoints = ref(0)
const loading = ref(false)
const searchQuery = ref('')
const resultFilter = ref('all')
const first = ref(0)
const rows = ref(10)

const fetchScores = async () => {
  if (!props.quizId) {
    students.value = []
    totalPoints.value = 0
    return
  }
  loading.value = true
  try {
    const { data } = await api.get(`/teacher/quizzes/${props.quizId}/scores`)
    students.value = data.data
    totalPoints.value = data.totalPoints ?? 0
  } catch (error) {
    alert(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(fetchScores)
watch(() => props.quizId, fetchScores)

const filteredStudents = computed(() => {
  let list = students.value

  if (resultFilter.value === 'passed') list = list.filter(s => s.passed)
  else if (resultFilter.value === 'failed') list = list.filter(s => !s.passed)

  if (!searchQuery.value) return list
  const q = searchQuery.value.toLowerCase()
  return list.filter(s => s.name?.toLowerCase().includes(q) || s.studentId?.toLowerCase().includes(q))
})

// Jump back to page 1 whenever the underlying list changes shape, so the
// paginator never gets stranded past the end of a smaller filtered list.
watch(filteredStudents, () => {
  first.value = 0
})

const averageScore = computed(() => {
  if (students.value.length === 0) return 0
  const total = students.value.reduce((acc, s) => acc + (s.mcqScore + (s.essayScore || 0)), 0)
  return Math.round(total / students.value.length)
})

const passedCount = computed(() => students.value.filter(s => s.passed).length)
const failedCount = computed(() => students.value.filter(s => !s.passed).length)

// ======= Score Distribution (bar) =======
// Buckets total score (mcqScore + essayScore) as a % of totalPoints into 5
// bands, so the shape of the class's results is visible at a glance.
const distributionBuckets = computed(() => {
  const buckets = [
    { label: '0-20%', max: 20, count: 0 },
    { label: '21-40%', max: 40, count: 0 },
    { label: '41-60%', max: 60, count: 0 },
    { label: '61-80%', max: 80, count: 0 },
    { label: '81-100%', max: 100, count: 0 },
  ]

  if (!totalPoints.value) return buckets

  students.value.forEach(s => {
    const total = s.mcqScore + (s.essayScore || 0)
    const pct = Math.min(100, Math.max(0, (total / totalPoints.value) * 100))
    const bucket = buckets.find(b => pct <= b.max) || buckets[buckets.length - 1]
    bucket.count++
  })

  return buckets
})

// Same pass mark for every student on a given quiz — read it from the first
// row rather than threading a separate prop through.
const passMarkPercent = computed(() => {
  const passMark = students.value[0]?.passMark
  return totalPoints.value && passMark != null ? (passMark / totalPoints.value) * 100 : null
})

const distributionChartData = computed(() => ({
  labels: distributionBuckets.value.map(b => b.label),
  datasets: [{
    label: 'Students',
    data: distributionBuckets.value.map(b => b.count),
    backgroundColor: distributionBuckets.value.map(b =>
      passMarkPercent.value != null && b.max < passMarkPercent.value ? '#f43f5e' : '#10b981'
    ),
    borderRadius: 6,
    maxBarThickness: 48,
  }],
}))

const distributionChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
    x: { grid: { display: false } },
  },
}

// ======= Pass vs Fail (doughnut) =======
const passPercentage = computed(() => {
  const total = passedCount.value + failedCount.value
  return total ? Math.round((passedCount.value / total) * 100) : 0
})

const passFailChartData = computed(() => ({
  labels: ['Passed', 'Failed'],
  datasets: [{
    data: [passedCount.value, failedCount.value],
    backgroundColor: ['#10b981', '#f43f5e'],
    borderWidth: 0,
  }],
}))

const passFailChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '72%',
  plugins: { legend: { display: false } },
}

// ======= Class Performance (spider / radar) =======
// Five angles that sketch the overall shape of this quiz's results, all as
// a % of totalPoints so they share one 0-100 scale.
const scorePercents = computed(() => {
  if (!totalPoints.value) return []
  return students.value.map(s => Math.min(100, Math.max(0, ((s.mcqScore + (s.essayScore || 0)) / totalPoints.value) * 100)))
})

const medianPercent = computed(() => {
  const sorted = [...scorePercents.value].sort((a, b) => a - b)
  if (!sorted.length) return 0
  const mid = Math.floor(sorted.length / 2)
  return sorted.length % 2 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2
})

const radarChartData = computed(() => {
  const percents = scorePercents.value
  const avg = percents.length ? percents.reduce((a, b) => a + b, 0) / percents.length : 0
  const highest = percents.length ? Math.max(...percents) : 0
  const lowest = percents.length ? Math.min(...percents) : 0

  return {
    labels: ['Average', 'Highest', 'Median', 'Lowest', 'Pass Rate'],
    datasets: [{
      label: 'Class Performance (%)',
      data: [avg, highest, medianPercent.value, lowest, passPercentage.value].map(v => Math.round(v)),
      backgroundColor: 'rgba(99, 102, 241, 0.2)',
      borderColor: '#6366f1',
      pointBackgroundColor: '#6366f1',
      pointBorderColor: '#fff',
      borderWidth: 2,
    }],
  }
})

const radarChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    r: {
      beginAtZero: true,
      max: 100,
      ticks: { stepSize: 20, backdropColor: 'transparent', color: '#94a3b8', font: { size: 9 } },
      grid: { color: '#e2e8f0' },
      angleLines: { color: '#e2e8f0' },
      pointLabels: { color: '#475569', font: { size: 9, weight: 'bold' } },
    },
  },
}

const exportHeader = ['#', 'Student ID', 'Name', 'Submitted At', 'MCQ Score', 'Essay Score', 'Total', 'Result', 'Tab Switches']

const exportRows = () => filteredStudents.value.map((s, i) => [
  i + 1,
  s.studentId,
  s.name,
  s.submittedAt,
  s.mcqScore,
  s.essayScore ?? '',
  s.mcqScore + (s.essayScore || 0),
  s.passed ? 'PASSED' : 'FAILED',
  s.tabSwitchCount ?? 0,
])

function exportCsv() {
  downloadCsv(`score-report-${props.quizTitle || 'quiz'}.csv`, exportHeader, exportRows())
}

function exportXlsx() {
  downloadXlsx(`score-report-${props.quizTitle || 'quiz'}.xlsx`, exportHeader, exportRows(), 'Scores')
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
</script>

<style scoped>
/* Header two sizes smaller, body two sizes larger, than the table's base text-xs — matches FeedbackTable. */
.score-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.score-table :deep(.p-datatable-tbody > tr > td) {
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
