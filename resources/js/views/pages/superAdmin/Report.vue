<template>
  <div class="p-6 space-y-6 bg-slate-50/50 min-h-screen">
    
    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Quiz & Academic Reports</h1>
        <p class="text-xs text-slate-500 mt-1">Detailed examination metrics, student performance, and evaluation reports.</p>
      </div>
      <div class="flex items-center gap-2">
        <Button 
          label="Export PDF" 
          icon="pi pi-file-pdf" 
          class="!bg-white !text-slate-700 hover:!bg-slate-100 !border-slate-200 !rounded-xl !text-xs !font-semibold !py-2 !px-3.5 shadow-sm cursor-pointer" 
        />
        <Button 
          label="Export Excel" 
          icon="pi pi-file-excel" 
          class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold !py-2 !px-3.5 shadow-sm cursor-pointer" 
        />
      </div>
    </div>

    <!-- KPI STATS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.title" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
          <span class="text-xs font-medium text-slate-500">{{ stat.title }}</span>
          <div class="text-xl font-bold text-slate-800">{{ stat.value }}</div>
          <div class="flex items-center gap-1 text-[11px]" :class="stat.isUp ? 'text-emerald-600' : 'text-rose-600'">
            <i :class="stat.isUp ? 'pi pi-arrow-up-right' : 'pi pi-arrow-down-right'" class="text-[10px]"></i>
            <span class="font-semibold">{{ stat.change }}</span>
            <span class="text-slate-400 font-normal">vs last month</span>
          </div>
        </div>
        <div :class="stat.bgClass" class="w-11 h-11 rounded-xl flex items-center justify-center">
          <i :class="[stat.icon, stat.iconColor]" class="text-lg"></i>
        </div>
      </div>
    </div>

    <!-- FILTER BAR SECTION -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
      <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-2 text-slate-700 font-semibold text-xs uppercase tracking-wider">
          <i class="pi pi-filter text-blue-600 text-sm"></i>
          <span>Filter Report Options</span>
        </div>
        <Button
          v-if="hasActiveFilters"
          label="Reset Filters"
          icon="pi pi-filter-slash"
          class="!bg-rose-50 !text-rose-600 hover:!bg-rose-100 !border-0 !rounded-lg !py-1.5 !px-3 !text-xs !font-medium transition-all cursor-pointer"
          @click="clearFilters"
        />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5">
        <Dropdown v-model="majorFilter" :options="majors" optionLabel="name_en" optionValue="id" placeholder="All Majors" showClear class="w-full custom-filter-dropdown" />
        <Dropdown v-model="stageFilter" :options="stages" optionLabel="name_en" optionValue="id" placeholder="All Stages" showClear class="w-full custom-filter-dropdown" />
        <Dropdown v-model="shiftFilter" :options="shifts" optionLabel="name_en" optionValue="id" placeholder="All Shifts" showClear class="w-full custom-filter-dropdown" />
        <Dropdown v-model="termFilter" :options="terms" optionLabel="name_en" optionValue="id" placeholder="All Terms" showClear class="w-full custom-filter-dropdown" />
        <Dropdown v-model="statusFilter" :options="['Passed', 'Failed', 'Pending']" placeholder="Quiz Result" showClear class="w-full custom-filter-dropdown" />
      </div>
    </div>

    <!-- MAIN REPORT TABLE SECTION -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="text-sm font-bold text-slate-800 m-0">Detailed Quiz Results</h3>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <div class="relative w-full sm:w-56">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="searchQuery"
              size="small"
              placeholder="Search student or quiz..."
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
          </div>
        </div>
      </div>

      <DataTable :value="reportData" dataKey="code"
        paginator :rows="rows" v-model:first="first" :rowsPerPageOptions="[10, 20, 50]"
        scrollable scrollHeight="calc(100vh - 428px)" scrollDirection="both"
        responsiveLayout="scroll" class="p-datatable-sm report-table" tableStyle="min-width: 1200px">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No quiz results found.
          </div>
        </template>

        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ reportData.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, reportData.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ reportData.length }}</span>
          </span>
        </template>

        <Column field="code" header="STUDENT ID" sortable style="padding-left: 1.25rem">
          <template #body="slotProps">
            <span class="font-mono font-bold text-indigo-600 text-sm">{{ slotProps.data.code }}</span>
          </template>
        </Column>

        <Column field="name" header="STUDENT NAME" sortable>
          <template #body="slotProps">
            <span class="font-semibold text-slate-800 text-sm">{{ slotProps.data.name }}</span>
          </template>
        </Column>

        <Column field="quizTitle" header="QUIZ TITLE">
          <template #body="slotProps">
            <span class="text-slate-600 text-sm">{{ slotProps.data.quizTitle }}</span>
          </template>
        </Column>

        <Column field="attempts" header="ATTEMPTS" sortable>
          <template #body="slotProps">
            <span class="font-bold text-slate-700 text-sm">{{ slotProps.data.attempts }}</span>
          </template>
        </Column>

        <Column field="score" header="SCORE (%)" sortable>
          <template #body="slotProps">
            <span class="font-bold text-sm" :class="slotProps.data.score >= 70 ? 'text-emerald-600' : 'text-rose-600'">
              {{ slotProps.data.score }}%
            </span>
          </template>
        </Column>

        <Column field="timeSpent" header="TIME SPENT">
          <template #body="slotProps">
            <span class="text-slate-600 text-sm">{{ slotProps.data.timeSpent }}</span>
          </template>
        </Column>

        <Column field="submittedAt" header="SUBMITTED DATE">
          <template #body="slotProps">
            <span class="text-slate-600 text-sm">{{ slotProps.data.submittedAt }}</span>
          </template>
        </Column>

        <Column field="status" header="RESULT" style="padding-right: 1.25rem">
          <template #body="slotProps">
            <span
              :class="{
                'bg-emerald-50 text-emerald-600 border-emerald-200': slotProps.data.status === 'Passed',
                'bg-rose-50 text-rose-600 border-rose-200': slotProps.data.status === 'Failed',
                'bg-amber-50 text-amber-600 border-amber-200': slotProps.data.status === 'Pending'
              }"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
            >
              {{ slotProps.data.status }}
            </span>
          </template>
        </Column>
      </DataTable>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'

const first = ref(0)
const rows = ref(10)

// Filter States
const majorFilter = ref(null)
const stageFilter = ref(null)
const shiftFilter = ref(null)
const termFilter = ref(null)
const statusFilter = ref(null)
const searchQuery = ref('')

const hasActiveFilters = computed(() => {
  return majorFilter.value || stageFilter.value || shiftFilter.value || termFilter.value || statusFilter.value
})

const clearFilters = () => {
  majorFilter.value = null
  stageFilter.value = null
  shiftFilter.value = null
  termFilter.value = null
  statusFilter.value = null
}

// Stats Data
const stats = ref([
  { title: 'Total Quizzes Taken', value: '3,842', change: '+14%', isUp: true, icon: 'pi pi-file-edit', iconColor: 'text-blue-600', bgClass: 'bg-blue-50' },
  { title: 'Average Score', value: '76.8%', change: '+3.2%', isUp: true, icon: 'pi pi-chart-bar', iconColor: 'text-emerald-600', bgClass: 'bg-emerald-50' },
  { title: 'Pass Rate', value: '82.4%', change: '+1.5%', isUp: true, icon: 'pi pi-check-circle', iconColor: 'text-purple-600', bgClass: 'bg-purple-50' },
  { title: 'Avg Completion Time', value: '24 mins', change: '-2 mins', isUp: true, icon: 'pi pi-clock', iconColor: 'text-amber-600', bgClass: 'bg-amber-50' },
])

// Quiz Report Data
const reportData = ref([
  { code: 'STU-1001', name: 'Sok Samnang', quizTitle: 'PHP Eloquent Basics', attempts: 1, score: 85, timeSpent: '18m 20s', submittedAt: '2026-03-28', status: 'Passed' },
  { code: 'STU-1002', name: 'Chan Voleak', quizTitle: 'Vue.js Fundamentals', attempts: 2, score: 92, timeSpent: '22m 10s', submittedAt: '2026-03-28', status: 'Passed' },
  { code: 'STU-1003', name: 'Keo Bormey', quizTitle: 'Database Schema Design', attempts: 1, score: 45, timeSpent: '30m 00s', submittedAt: '2026-03-27', status: 'Failed' },
  { code: 'STU-1004', name: 'Meas Sophea', quizTitle: 'Tailwind CSS Utility Classes', attempts: 1, score: 78, timeSpent: '15m 45s', submittedAt: '2026-03-26', status: 'Passed' },
])
</script>

<style scoped>
:deep(.custom-filter-dropdown) {
  background-color: #f8fafc !important;
  border: 1px solid #e2e8f0 !important;
  border-radius: 0.75rem !important;
  font-size: 0.75rem !important;
  transition: all 0.2s ease;
  height: 38px;
  display: flex;
  align-items: center;
}
:deep(.custom-filter-dropdown:hover) {
  border-color: #cbd5e1 !important;
  background-color: #ffffff !important;
}
:deep(.custom-filter-dropdown.p-focus) {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
  background-color: #ffffff !important;
}
:deep(.custom-filter-dropdown .p-dropdown-label) {
  font-size: 0.75rem !important;
  padding: 0.4rem 0.75rem !important;
  color: #334155 !important;
}

.report-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.report-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}
</style>