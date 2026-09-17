<template>
  <div class="space-y-6">

    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-[#002060] m-0 flex items-center gap-2">
          <i class="pi pi-chart-bar text-[#e4ac40] text-2xl"></i>
          Reports
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">Quiz performance, student, and teacher headcount reports.</p>
      </div>
      <div class="flex items-center gap-2">
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

    <!-- REPORT TABS -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
      <div class="flex flex-wrap gap-1.5">
        <button
          type="button"
          @click="activeReport = 'quiz'"
          class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeReport === 'quiz' ? '!bg-[#002060] !text-white' : '!bg-slate-100 !text-[#002060] hover:!bg-slate-200'"
        >
          <i class="pi pi-file-edit text-[#e4ac40] text-sm"></i>
          <span>Quiz Results</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeReport === 'quiz' ? 'bg-white text-[#002060]' : 'bg-[#002060] text-white'">
            {{ quizData.length }}
          </span>
        </button>

        <button
          type="button"
          @click="activeReport = 'students'"
          class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeReport === 'students' ? '!bg-[#002060] !text-white' : '!bg-blue-50 !text-[#002060] hover:!bg-blue-100'"
        >
          <i class="pi pi-users text-[#e4ac40] text-sm"></i>
          <span>Students</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeReport === 'students' ? 'bg-white text-[#002060]' : 'bg-[#002060] text-white'">
            {{ allStudents.length }}
          </span>
        </button>

        <button
          type="button"
          @click="activeReport = 'teachers'"
          class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeReport === 'teachers' ? '!bg-[#002060] !text-white' : '!bg-indigo-50 !text-[#002060] hover:!bg-indigo-100'"
        >
          <i class="pi pi-id-card text-[#e4ac40] text-sm"></i>
          <span>Teachers</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeReport === 'teachers' ? 'bg-white text-[#002060]' : 'bg-[#002060] text-white'">
            {{ allTeachers.length }}
          </span>
        </button>
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

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2.5">
        <Dropdown v-model="facultyFilter" :options="faculties" optionLabel="name_en" optionValue="id" placeholder="All Faculties" showClear class="w-full custom-filter-dropdown" />
        <Dropdown v-model="departmentFilter" :options="departmentFilterOptions" optionLabel="name_en" optionValue="id" placeholder="All Departments" showClear class="w-full custom-filter-dropdown" />
        <Dropdown v-model="majorFilter" :options="majorFilterOptions" optionLabel="name_en" optionValue="id" placeholder="All Majors" showClear class="w-full custom-filter-dropdown" />

        <template v-if="activeReport === 'quiz'">
          <Dropdown v-model="classFilter" :options="classes" optionLabel="name" optionValue="id" placeholder="All Classes" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="subjectFilter" :options="subjects" optionLabel="name_en" optionValue="id" placeholder="All Subjects" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="teacherFilter" :options="teachers" optionLabel="name_en" optionValue="id" placeholder="All Teachers" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="stageFilter" :options="stages" optionLabel="name_en" optionValue="id" placeholder="All Stages" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="shiftFilter" :options="shifts" optionLabel="name_en" optionValue="id" placeholder="All Shifts" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="termFilter" :options="terms" optionLabel="name_en" optionValue="id" placeholder="All Terms" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="resultFilter" :options="['Passed', 'Failed', 'Pending']" placeholder="Quiz Result" showClear class="w-full custom-filter-dropdown" />
        </template>

        <template v-else-if="activeReport === 'students'">
          <Dropdown v-model="stageFilter" :options="stages" optionLabel="name_en" optionValue="id" placeholder="All Stages" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="shiftFilter" :options="shifts" optionLabel="name_en" optionValue="id" placeholder="All Shifts" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="termFilter" :options="terms" optionLabel="name_en" optionValue="id" placeholder="All Terms" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="statusFilter" :options="['Active', 'Inactive']" placeholder="All Statuses" showClear class="w-full custom-filter-dropdown" />
        </template>

        <template v-else>
          <Dropdown v-model="typeFilter" :options="['Full-Time', 'Part-Time']" placeholder="All Types" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="academicYearFilter" :options="academicYears" optionLabel="name_en" optionValue="id" placeholder="All Academic Years" showClear class="w-full custom-filter-dropdown" />
          <Dropdown v-model="statusFilter" :options="['Active', 'Inactive']" placeholder="All Statuses" showClear class="w-full custom-filter-dropdown" />
        </template>
      </div>
    </div>

    <!-- KPI STATS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.title" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
          <span class="text-xs font-medium text-slate-500">{{ stat.title }}</span>
          <div class="text-xl font-bold text-slate-800">{{ stat.value }}</div>
        </div>
        <div :class="stat.bgClass" class="w-11 h-11 rounded-xl flex items-center justify-center">
          <i :class="[stat.icon, stat.iconColor]" class="text-lg"></i>
        </div>
      </div>
    </div>

    <!-- BREAKDOWN CHARTS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 m-0 mb-3">{{ barChartTitle }}</h3>
        <div class="h-64">
          <Chart type="bar" :data="barChartData" :options="barChartOptions" class="h-full w-full" />
        </div>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 m-0 mb-3">{{ pieChartTitle }}</h3>
        <div class="h-64 flex items-center justify-center">
          <Chart type="pie" :data="pieChartData" :options="pieChartOptions" class="h-full max-w-64" />
        </div>
      </div>
    </div>

    <!-- MAIN TABLE SECTION -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="text-sm font-bold text-slate-800 m-0">
          {{ activeReport === 'quiz' ? 'Detailed Quiz Results' : activeReport === 'students' ? 'Student Roster' : 'Teacher Roster' }}
        </h3>
        <div class="flex items-center gap-2">
          <div class="relative w-full sm:w-56">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="searchQuery"
              size="small"
              :placeholder="activeReport === 'quiz' ? 'Search student or quiz...' : 'Search name or ID...'"
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
          </div>
          <Button
            :label="showMore ? 'Fewer Column' : 'More Column'"
            :icon="showMore ? 'pi pi-angle-double-left' : 'pi pi-angle-double-right'"
            size="small"
            class="!bg-[#002060] !border-slate-100 !text-white hover:!bg-blue-900 !rounded-lg !text-xs !font-semibold !px-3 !py-1"
            @click="showMore = !showMore"
          />
        </div>
      </div>

      <!-- Quiz Results Table -->
      <DataTable v-if="activeReport === 'quiz'" :value="filteredQuizData" dataKey="id" :loading="loading"
        paginator :rows="rows" v-model:first="first" :rowsPerPageOptions="[10, 20, 50]"
        scrollable scrollHeight="calc(100vh - 428px)" scrollDirection="both"
        responsiveLayout="scroll" class="p-datatable-sm report-table" :tableStyle="showMore ? 'min-width: 2500px' : 'min-width: 1200px'">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">No quiz results found.</div>
        </template>
        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ filteredQuizData.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredQuizData.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ filteredQuizData.length }}</span>
          </span>
        </template>

        <Column field="username" header="USERNAME" sortable style="padding-left: 1.25rem">
          <template #body="slotProps"><span class="font-mono text-slate-600 text-sm">{{ slotProps.data.username || '—' }}</span></template>
        </Column>
        <Column field="name" header="STUDENT NAME" sortable>
          <template #body="slotProps"><span class="font-semibold text-slate-800 text-sm">{{ slotProps.data.name }}</span></template>
        </Column>
        <template v-if="showMore">
          <Column field="gender" header="GENDER">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.gender || '—' }}</span></template>
          </Column>
          <Column field="phone" header="PHONE">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.phone || '—' }}</span></template>
          </Column>
          <Column field="facultyName" header="FACULTY">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.facultyName || '—' }}</span></template>
          </Column>
          <Column field="departmentName" header="DEPARTMENT">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.departmentName || '—' }}</span></template>
          </Column>
          <Column field="majorName" header="MAJOR">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.majorName || '—' }}</span></template>
          </Column>
          <Column field="stageName" header="STAGE">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.stageName || '—' }}</span></template>
          </Column>
          <Column field="shiftName" header="SHIFT">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.shiftName || '—' }}</span></template>
          </Column>
          <Column field="termName" header="TERM">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.termName || '—' }}</span></template>
          </Column>
          <Column field="promotionName" header="PROMOTION">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.promotionName || '—' }}</span></template>
          </Column>
          <Column field="academicYearName" header="ACADEMIC YEAR">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.academicYearName || '—' }}</span></template>
          </Column>
          <Column field="semesterName" header="SEMESTER">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.semesterName || '—' }}</span></template>
          </Column>
          <Column field="enrollmentDate" header="ENROLLMENT DATE">
            <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.enrollmentDate || '—' }}</span></template>
          </Column>
        </template>
        <Column field="quizTitle" header="QUIZ TITLE">
          <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.quizTitle }}</span></template>
        </Column>
        <Column field="attempts" header="ATTEMPTS" sortable>
          <template #body="slotProps"><span class="font-bold text-slate-700 text-sm">{{ slotProps.data.attempts }}</span></template>
        </Column>
        <Column field="score" header="SCORE (%)" sortable>
          <template #body="slotProps">
            <span class="font-bold text-sm" :class="slotProps.data.status === 'Passed' ? 'text-emerald-600' : 'text-rose-600'">
              {{ slotProps.data.score }}%
            </span>
          </template>
        </Column>
        <Column field="timeSpent" header="TIME SPENT">
          <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.timeSpent }}</span></template>
        </Column>
        <Column field="submittedAt" header="SUBMITTED DATE">
          <template #body="slotProps"><span class="text-slate-600 text-sm">{{ slotProps.data.submittedAt }}</span></template>
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

      <!-- Student Roster Table -->
      <DataTable v-else-if="activeReport === 'students'" :value="filteredStudents" dataKey="id" :loading="loading"
        paginator :rows="rows" v-model:first="first" :rowsPerPageOptions="[10, 20, 50]"
        scrollable scrollHeight="calc(100vh - 490px)" scrollDirection="both"
        responsiveLayout="scroll" class="p-datatable-sm report-table" :tableStyle="showMore ? 'min-width: 2100px' : 'min-width: 1100px'">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">No students found.</div>
        </template>
        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ filteredStudents.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredStudents.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ filteredStudents.length }}</span>
          </span>
        </template>

        <Column field="username" header="USERNAME" sortable style="padding-left: 1.25rem">
          <template #body="{ data }"><span class="font-mono text-slate-600 text-sm">{{ data.username || '—' }}</span></template>
        </Column>
        <Column field="name" header="NAME" sortable>
          <template #body="{ data }"><span class="font-semibold text-slate-800 text-sm">{{ data.name }}</span></template>
        </Column>
        <template v-if="showMore">
          <Column field="gender" header="GENDER">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.gender || '—' }}</span></template>
          </Column>
          <Column field="phone" header="PHONE">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.phone || '—' }}</span></template>
          </Column>
          <Column field="dob" header="DATE OF BIRTH">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.dob || '—' }}</span></template>
          </Column>
        </template>
        <Column field="facultyName" header="FACULTY">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.facultyName || '—' }}</span></template>
        </Column>
        <Column field="departmentName" header="DEPARTMENT">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.departmentName || '—' }}</span></template>
        </Column>
        <Column field="majorName" header="MAJOR">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.majorName || '—' }}</span></template>
        </Column>
        <Column field="stageName" header="STAGE">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.stageName || '—' }}</span></template>
        </Column>
        <Column field="shiftName" header="SHIFT">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.shiftName || '—' }}</span></template>
        </Column>
        <template v-if="showMore">
          <Column field="termName" header="TERM">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.termName || '—' }}</span></template>
          </Column>
          <Column field="promotionName" header="PROMOTION">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.promotionName || '—' }}</span></template>
          </Column>
          <Column field="academicYearName" header="ACADEMIC YEAR">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.academicYearName || '—' }}</span></template>
          </Column>
          <Column field="semesterName" header="SEMESTER">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.semesterName || '—' }}</span></template>
          </Column>
          <Column field="enrollmentDate" header="ENROLLMENT DATE">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.enrollmentDate || '—' }}</span></template>
          </Column>
        </template>
        <Column field="status" header="STATUS" style="padding-right: 1.25rem">
          <template #body="{ data }">
            <span
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
            >{{ data.status }}</span>
          </template>
        </Column>
      </DataTable>

      <!-- Teacher Roster Table -->
      <DataTable v-else :value="filteredTeachers" dataKey="id" :loading="loading"
        paginator :rows="rows" v-model:first="first" :rowsPerPageOptions="[10, 20, 50]"
        scrollable scrollHeight="calc(100vh - 490px)" scrollDirection="both"
        responsiveLayout="scroll" class="p-datatable-sm report-table" :tableStyle="showMore ? 'min-width: 1900px' : 'min-width: 1150px'">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">No teachers found.</div>
        </template>
        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ filteredTeachers.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredTeachers.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ filteredTeachers.length }}</span>
          </span>
        </template>

        <Column field="code" header="EMPLOYEE ID" sortable style="padding-left: 1.25rem">
          <template #body="{ data }"><span class="font-mono font-bold text-indigo-600 text-sm">{{ data.code }}</span></template>
        </Column>
        <Column field="name" header="NAME" sortable>
          <template #body="{ data }"><span class="font-semibold text-slate-800 text-sm">{{ data.name }}</span></template>
        </Column>
        <template v-if="showMore">
          <Column field="username" header="USERNAME">
            <template #body="{ data }"><span class="font-mono text-slate-600 text-sm">{{ data.username || '—' }}</span></template>
          </Column>
          <Column field="gender" header="GENDER">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.gender || '—' }}</span></template>
          </Column>
          <Column field="phone" header="PHONE">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.phone || '—' }}</span></template>
          </Column>
        </template>
        <Column field="facultyName" header="FACULTY">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.facultyName || '—' }}</span></template>
        </Column>
        <Column field="departmentName" header="DEPARTMENT">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.departmentName || '—' }}</span></template>
        </Column>
        <Column field="majorName" header="MAJOR">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.majorName || '—' }}</span></template>
        </Column>
        <Column field="academicYearNames" header="ACADEMIC YEAR">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.academicYearNames || '—' }}</span></template>
        </Column>
        <template v-if="showMore">
          <Column field="qualification" header="QUALIFICATION">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.qualification || '—' }}</span></template>
          </Column>
          <Column field="specialization" header="SPECIALIZATION">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.specialization || '—' }}</span></template>
          </Column>
          <Column field="hireDate" header="HIRE DATE">
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.hireDate || '—' }}</span></template>
          </Column>
        </template>
        <Column field="type" header="TYPE">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.type }}</span></template>
        </Column>
        <Column field="status" header="STATUS" style="padding-right: 1.25rem">
          <template #body="{ data }">
            <span
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
            >{{ data.status }}</span>
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
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
import SplitButton from 'primevue/splitbutton'
import InputText from 'primevue/inputtext'
import Chart from 'primevue/chart'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../../api'
import { downloadCsv, downloadXlsx } from '../../../utils/exportTable'

const toast = useToast()

// 'quiz' | 'students' | 'teachers'
const activeReport = ref('quiz')

const first = ref(0)
const rows = ref(10)
const loading = ref(false)
const showMore = ref(false)

// Filter States — shared (faculty/department/major) plus per-report ones.
// resultFilter is the quiz Passed/Failed/Pending filter; statusFilter is the
// Active/Inactive filter shared by the Students and Teachers reports.
const facultyFilter = ref(null)
const departmentFilter = ref(null)
const majorFilter = ref(null)
const classFilter = ref(null)
const subjectFilter = ref(null)
const teacherFilter = ref(null)
const stageFilter = ref(null)
const shiftFilter = ref(null)
const termFilter = ref(null)
const typeFilter = ref(null)
const academicYearFilter = ref(null)
const resultFilter = ref(null)
const statusFilter = ref(null)
const searchQuery = ref('')

const hasActiveFilters = computed(() => !!(
  facultyFilter.value || departmentFilter.value || majorFilter.value ||
  classFilter.value || subjectFilter.value || teacherFilter.value ||
  stageFilter.value || shiftFilter.value || termFilter.value ||
  typeFilter.value || academicYearFilter.value || resultFilter.value || statusFilter.value
))

const clearFilters = () => {
  facultyFilter.value = null
  departmentFilter.value = null
  majorFilter.value = null
  classFilter.value = null
  subjectFilter.value = null
  teacherFilter.value = null
  stageFilter.value = null
  shiftFilter.value = null
  termFilter.value = null
  typeFilter.value = null
  academicYearFilter.value = null
  resultFilter.value = null
  statusFilter.value = null
}

// Switching report tabs clears filters that don't apply to the new tab, so
// stale picks (e.g. a Subject) can't silently keep filtering a table that
// has no Subject column.
watch(activeReport, () => {
  classFilter.value = null
  subjectFilter.value = null
  teacherFilter.value = null
  resultFilter.value = null
  if (activeReport.value !== 'teachers') { typeFilter.value = null; academicYearFilter.value = null }
  if (activeReport.value === 'quiz') statusFilter.value = null
  if (activeReport.value !== 'students') { stageFilter.value = null; shiftFilter.value = null; termFilter.value = null }
})

// Lookup lists for the filter dropdowns
const faculties = ref([])
const departments = ref([])
const majors = ref([])
const classes = ref([])
const subjects = ref([])
const teachers = ref([])
const stages = ref([])
const shifts = ref([])
const terms = ref([])
const academicYears = ref([])

// Department/Major narrow to whatever's picked upstream, same cascade as
// the Classes/Students admin pages.
const departmentFilterOptions = computed(() =>
  facultyFilter.value ? departments.value.filter(d => d.faculty_id === facultyFilter.value) : departments.value
)

const majorFilterOptions = computed(() => {
  if (departmentFilter.value) {
    return majors.value.filter(m => m.department_id === departmentFilter.value)
  }
  if (facultyFilter.value) {
    const departmentIds = departments.value.filter(d => d.faculty_id === facultyFilter.value).map(d => d.id)
    return majors.value.filter(m => departmentIds.includes(m.department_id))
  }
  return majors.value
})

watch(facultyFilter, () => {
  if (departmentFilter.value && !departmentFilterOptions.value.some(d => d.id === departmentFilter.value)) {
    departmentFilter.value = null
  }
})

watch([facultyFilter, departmentFilter], () => {
  if (majorFilter.value && !majorFilterOptions.value.some(m => m.id === majorFilter.value)) {
    majorFilter.value = null
  }
})

// Report data — every completed quiz submission, and every student/teacher,
// school-wide.
const quizData = ref([])
const allStudents = ref([])
const allTeachers = ref([])

const fetchLookups = async () => {
  const [facultiesRes, departmentsRes, majorsRes, classesRes, subjectsRes, teachersRes, stagesRes, shiftsRes, termsRes, academicYearsRes] = await Promise.all([
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/departments', { params: { per_page: 100 } }),
    api.get('/majors', { params: { per_page: 100 } }),
    api.get('/classes', { params: { per_page: 100 } }),
    api.get('/subjects', { params: { per_page: 200 } }),
    api.get('/teachers', { params: { per_page: 200 } }),
    api.get('/stages', { params: { per_page: 100 } }),
    api.get('/shifts', { params: { per_page: 100 } }),
    api.get('/terms', { params: { per_page: 100 } }),
    api.get('/academic-years', { params: { per_page: 100 } }),
  ])
  faculties.value = facultiesRes.data.data
  departments.value = departmentsRes.data.data
  majors.value = majorsRes.data.data
  classes.value = classesRes.data.data
  subjects.value = subjectsRes.data.data
  teachers.value = teachersRes.data.data
  stages.value = stagesRes.data.data
  shifts.value = shiftsRes.data.data
  terms.value = termsRes.data.data
  academicYears.value = academicYearsRes.data.data
}

const fetchReports = async () => {
  loading.value = true
  try {
    const [quizRes, enrollmentRes] = await Promise.all([
      api.get('/report'),
      api.get('/enrollment-report'),
    ])
    quizData.value = quizRes.data.data
    allStudents.value = enrollmentRes.data.students
    allTeachers.value = enrollmentRes.data.teachers
  } catch (error) {
    toast.add({ summary: 'Failed to load report', ...toastFromError(error) })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchLookups()
  fetchReports()
})

const filteredQuizData = computed(() => {
  let list = quizData.value

  if (facultyFilter.value) list = list.filter(r => r.facultyId === facultyFilter.value)
  if (departmentFilter.value) list = list.filter(r => r.departmentId === departmentFilter.value)
  if (majorFilter.value) list = list.filter(r => r.majorId === majorFilter.value)
  if (classFilter.value) list = list.filter(r => r.classId === classFilter.value)
  if (subjectFilter.value) list = list.filter(r => r.subjectId === subjectFilter.value)
  if (teacherFilter.value) list = list.filter(r => r.teacherId === teacherFilter.value)
  if (stageFilter.value) list = list.filter(r => r.stageId === stageFilter.value)
  if (shiftFilter.value) list = list.filter(r => r.shiftId === shiftFilter.value)
  if (termFilter.value) list = list.filter(r => r.termId === termFilter.value)
  if (resultFilter.value) list = list.filter(r => r.status === resultFilter.value)

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(r =>
      r.name?.toLowerCase().includes(q) ||
      r.code?.toLowerCase().includes(q) ||
      r.quizTitle?.toLowerCase().includes(q)
    )
  }

  return list
})

const filteredStudents = computed(() => {
  let list = allStudents.value

  if (facultyFilter.value) list = list.filter(r => r.facultyId === facultyFilter.value)
  if (departmentFilter.value) list = list.filter(r => r.departmentId === departmentFilter.value)
  if (majorFilter.value) list = list.filter(r => r.majorId === majorFilter.value)
  if (stageFilter.value) list = list.filter(r => r.stageId === stageFilter.value)
  if (shiftFilter.value) list = list.filter(r => r.shiftId === shiftFilter.value)
  if (termFilter.value) list = list.filter(r => r.termId === termFilter.value)
  if (statusFilter.value) list = list.filter(r => r.status === statusFilter.value)

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(r => r.name?.toLowerCase().includes(q) || r.code?.toLowerCase().includes(q))
  }

  return list
})

const filteredTeachers = computed(() => {
  let list = allTeachers.value

  if (facultyFilter.value) list = list.filter(r => r.facultyId === facultyFilter.value)
  if (departmentFilter.value) list = list.filter(r => r.departmentId === departmentFilter.value)
  if (majorFilter.value) list = list.filter(r => r.majorId === majorFilter.value)
  if (typeFilter.value) list = list.filter(r => r.type === typeFilter.value)
  if (academicYearFilter.value) list = list.filter(r => r.academicYearIds?.includes(academicYearFilter.value))
  if (statusFilter.value) list = list.filter(r => r.status === statusFilter.value)

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(r => r.name?.toLowerCase().includes(q) || r.code?.toLowerCase().includes(q))
  }

  return list
})

// Jump back to page 1 whenever the active list or tab changes shape, so the
// paginator never gets stranded past the end of a smaller list.
watch([filteredQuizData, filteredStudents, filteredTeachers, activeReport], () => {
  first.value = 0
})

function formatDuration(seconds) {
  const minutes = Math.floor(seconds / 60)
  const remaining = seconds % 60
  return `${minutes}m ${remaining}s`
}

const stats = computed(() => {
  if (activeReport.value === 'students') {
    const list = filteredStudents.value
    const active = list.filter(r => r.status === 'Active').length
    return [
      { title: 'Total Students', value: String(list.length), icon: 'pi pi-users', iconColor: 'text-blue-600', bgClass: 'bg-blue-50' },
      { title: 'Active Students', value: String(active), icon: 'pi pi-check-circle', iconColor: 'text-emerald-600', bgClass: 'bg-emerald-50' },
      { title: 'Inactive Students', value: String(list.length - active), icon: 'pi pi-times-circle', iconColor: 'text-rose-600', bgClass: 'bg-rose-50' },
      { title: 'Faculties Covered', value: String(new Set(list.map(r => r.facultyId).filter(Boolean)).size), icon: 'pi pi-sitemap', iconColor: 'text-purple-600', bgClass: 'bg-purple-50' },
    ]
  }

  if (activeReport.value === 'teachers') {
    const list = filteredTeachers.value
    const active = list.filter(r => r.status === 'Active').length
    const fullTime = list.filter(r => r.type === 'Full-Time').length
    return [
      { title: 'Total Teachers', value: String(list.length), icon: 'pi pi-id-card', iconColor: 'text-blue-600', bgClass: 'bg-blue-50' },
      { title: 'Active Teachers', value: String(active), icon: 'pi pi-check-circle', iconColor: 'text-emerald-600', bgClass: 'bg-emerald-50' },
      { title: 'Full-Time', value: String(fullTime), icon: 'pi pi-briefcase', iconColor: 'text-purple-600', bgClass: 'bg-purple-50' },
      { title: 'Part-Time', value: String(list.length - fullTime), icon: 'pi pi-clock', iconColor: 'text-amber-600', bgClass: 'bg-amber-50' },
    ]
  }

  const list = filteredQuizData.value
  const total = list.length
  const decided = list.filter(r => r.status !== 'Pending')
  const avgScore = total ? Math.round((list.reduce((sum, r) => sum + r.score, 0) / total) * 10) / 10 : 0
  const passRate = decided.length
    ? Math.round((decided.filter(r => r.status === 'Passed').length / decided.length) * 1000) / 10
    : 0
  const timed = list.filter(r => r.timeSpentSeconds != null)
  const avgSeconds = timed.length
    ? Math.round(timed.reduce((sum, r) => sum + r.timeSpentSeconds, 0) / timed.length)
    : 0

  return [
    { title: 'Total Quizzes Taken', value: String(total), icon: 'pi pi-file-edit', iconColor: 'text-blue-600', bgClass: 'bg-blue-50' },
    { title: 'Average Score', value: `${avgScore}%`, icon: 'pi pi-chart-bar', iconColor: 'text-emerald-600', bgClass: 'bg-emerald-50' },
    { title: 'Pass Rate', value: `${passRate}%`, icon: 'pi pi-check-circle', iconColor: 'text-purple-600', bgClass: 'bg-purple-50' },
    { title: 'Avg Completion Time', value: formatDuration(avgSeconds), icon: 'pi pi-clock', iconColor: 'text-amber-600', bgClass: 'bg-amber-50' },
  ]
})

// ======= Bar chart: Score Distribution (quiz) / Headcount by Faculty (students, teachers) =======
const barChartTitle = computed(() => {
  if (activeReport.value === 'quiz') return 'Score Distribution'
  return `${activeReport.value === 'students' ? 'Students' : 'Teachers'} by Faculty`
})

const scoreDistributionBuckets = computed(() => {
  const buckets = [
    { label: '0-20%', max: 20, count: 0 },
    { label: '21-40%', max: 40, count: 0 },
    { label: '41-60%', max: 60, count: 0 },
    { label: '61-80%', max: 80, count: 0 },
    { label: '81-100%', max: 100, count: 0 },
  ]

  filteredQuizData.value.forEach(r => {
    const pct = Math.min(100, Math.max(0, r.score))
    const bucket = buckets.find(b => pct <= b.max) || buckets[buckets.length - 1]
    bucket.count++
  })

  return buckets
})

const barChartData = computed(() => {
  if (activeReport.value === 'quiz') {
    return {
      labels: scoreDistributionBuckets.value.map(b => b.label),
      datasets: [{
        label: 'Submissions',
        data: scoreDistributionBuckets.value.map(b => b.count),
        backgroundColor: '#6366f1',
        borderRadius: 6,
        maxBarThickness: 48,
      }],
    }
  }

  const list = activeReport.value === 'students' ? filteredStudents.value : filteredTeachers.value
  const labels = faculties.value.map(f => f.name_en)
  const counts = faculties.value.map(f => list.filter(r => r.facultyId === f.id).length)

  return {
    labels,
    datasets: [{
      label: activeReport.value === 'students' ? 'Students' : 'Teachers',
      data: counts,
      backgroundColor: activeReport.value === 'students' ? '#6366f1' : '#f59e0b',
      borderRadius: 6,
      maxBarThickness: 48,
    }],
  }
})

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
  },
}

// ======= Pie chart: Result breakdown (quiz) / Status or Type breakdown =======
const pieChartTitle = computed(() => {
  if (activeReport.value === 'quiz') return 'Result Breakdown'
  if (activeReport.value === 'students') return 'Students by Status'
  return 'Teachers by Employment Type'
})

const pieChartData = computed(() => {
  if (activeReport.value === 'quiz') {
    const list = filteredQuizData.value
    return {
      labels: ['Passed', 'Failed', 'Pending'],
      datasets: [{
        data: [
          list.filter(r => r.status === 'Passed').length,
          list.filter(r => r.status === 'Failed').length,
          list.filter(r => r.status === 'Pending').length,
        ],
        backgroundColor: ['#10b981', '#f43f5e', '#f59e0b'],
        borderWidth: 0,
      }],
    }
  }

  if (activeReport.value === 'students') {
    const list = filteredStudents.value
    return {
      labels: ['Active', 'Inactive'],
      datasets: [{
        data: [
          list.filter(r => r.status === 'Active').length,
          list.filter(r => r.status !== 'Active').length,
        ],
        backgroundColor: ['#10b981', '#94a3b8'],
        borderWidth: 0,
      }],
    }
  }

  const list = filteredTeachers.value
  return {
    labels: ['Full-Time', 'Part-Time'],
    datasets: [{
      data: [
        list.filter(r => r.type === 'Full-Time').length,
        list.filter(r => r.type !== 'Full-Time').length,
      ],
      backgroundColor: ['#6366f1', '#f59e0b'],
      borderWidth: 0,
    }],
  }
})

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
}

const exportHeader = computed(() => {
  if (activeReport.value === 'students') {
    return ['Student ID', 'Name', 'Username', 'Gender', 'Phone', 'Date of Birth', 'Faculty', 'Department', 'Major', 'Stage', 'Shift', 'Term', 'Promotion', 'Academic Year', 'Semester', 'Enrollment Date', 'Status']
  }
  if (activeReport.value === 'teachers') {
    return ['Employee ID', 'Name', 'Username', 'Gender', 'Phone', 'Faculty', 'Department', 'Major', 'Academic Year', 'Qualification', 'Specialization', 'Hire Date', 'Type', 'Status']
  }
  return ['Student ID', 'Student Name', 'Username', 'Gender', 'Phone', 'Faculty', 'Department', 'Major', 'Stage', 'Shift', 'Term', 'Promotion', 'Academic Year', 'Semester', 'Enrollment Date', 'Quiz Title', 'Attempts', 'Score (%)', 'Time Spent', 'Submitted Date', 'Result']
})

const exportRows = () => {
  if (activeReport.value === 'students') {
    return filteredStudents.value.map(r => [
      r.code, r.name, r.username, r.gender, r.phone, r.dob,
      r.facultyName, r.departmentName, r.majorName, r.stageName, r.shiftName, r.termName,
      r.promotionName, r.academicYearName, r.semesterName, r.enrollmentDate, r.status,
    ])
  }
  if (activeReport.value === 'teachers') {
    return filteredTeachers.value.map(r => [
      r.code, r.name, r.username, r.gender, r.phone,
      r.facultyName, r.departmentName, r.majorName, r.academicYearNames, r.qualification, r.specialization, r.hireDate, r.type, r.status,
    ])
  }
  return filteredQuizData.value.map(r => [
    r.code, r.name, r.username, r.gender, r.phone,
    r.facultyName, r.departmentName, r.majorName, r.stageName, r.shiftName, r.termName,
    r.promotionName, r.academicYearName, r.semesterName, r.enrollmentDate,
    r.quizTitle, r.attempts, r.score, r.timeSpent, r.submittedAt, r.status,
  ])
}

function exportCsv() {
  downloadCsv(`${activeReport.value}-report.csv`, exportHeader.value, exportRows())
}

function exportXlsx() {
  downloadXlsx(`${activeReport.value}-report.xlsx`, exportHeader.value, exportRows(), 'Report')
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
