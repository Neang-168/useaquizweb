<template>
  <div class="h-[calc(100vh-2rem)] flex flex-col gap-4 overflow-hidden font-sans text-slate-800">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0 bg-white/60 backdrop-blur-md p-4 rounded-xl border border-slate-200/60 shadow-xs">
      <div>
        <h1 class="text-xl font-bold text-[#002060] tracking-tight flex items-center gap-2.5">
          <i class="pi pi-sitemap text-[#e4ac40] text-2xl"></i>
          Academic Structure
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 m-0 mt-1 pl-0.5">
          Manage faculties, departments, majors, promotions, academic years, semesters, terms, stages and shifts.
        </p>
      </div>

      <Button
        :label="`Add ${activeTabDef.singular}`"
        icon="pi pi-plus"
        class="!bg-[#002060] hover:!bg-blue-900 active:!bg-blue-800 !text-white !border-0 !rounded-lg !py-2.5 !px-4 !text-xs !font-semibold shadow-sm hover:shadow transition-all duration-200 gap-2 shrink-0 cursor-pointer"
        @click="openNewForActiveTab"
      />
    </div>

    <!-- ======= DATA TABLE ======= -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs flex-1 flex flex-col min-h-0">

      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
        <h3 class="text-sm font-bold text-[#002060] m-0">{{ activeTabDef.title }}</h3>
        <div class="relative w-full sm:w-64">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
          <InputText
            v-model="search"
            size="small"
            :placeholder="activeTabDef.searchPlaceholder"
            class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
          />
        </div>
      </div>

      <!-- Tab Header Buttons -->
      <div class="flex flex-wrap gap-1.5 px-4 pt-3 shrink-0">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          @click="activeTab = tab.key"
          class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeTab === tab.key ? '!bg-[#002060] !text-white' : '!bg-slate-100 !text-[#002060] hover:!bg-slate-200'"
        >
          <i :class="[tab.icon, 'text-[#e4ac40] text-sm']"></i>
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <LookupCrudTab
        v-for="tab in tabs"
        v-show="activeTab === tab.key"
        :key="tab.key"
        :ref="(el) => registerTabRef(tab.key, el)"
        :singular="tab.singular"
        :endpoint="tab.endpoint"
        :fields="tab.fields"
        :lookups="lookups"
        :search="search"
        :empty-message="tab.emptyMessage"
        :supports-set-current="!!tab.supportsSetCurrent"
        class="flex-1 min-h-0 flex flex-col"
        @changed="fetchLookups"
      />
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../../api'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import LookupCrudTab from './academicStructure/LookupCrudTab.vue'

// ======= Shared lookups (fetched once, reused by tabs whose forms need a
// related dropdown — Department needs Faculty, Major needs Department,
// Semester/Term need Academic Year). Refetched whenever any tab reports a
// change, so dependent tabs' dropdowns never go stale. =======
const lookups = reactive({
  faculties: [],
  departments: [],
  academicYears: [],
})

const fetchLookups = async () => {
  const [facultiesRes, departmentsRes, academicYearsRes] = await Promise.all([
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/departments', { params: { per_page: 200 } }),
    api.get('/academic-years', { params: { per_page: 100 } }),
  ])
  lookups.faculties = facultiesRes.data.data
  lookups.departments = departmentsRes.data.data
  lookups.academicYears = academicYearsRes.data.data
}

onMounted(fetchLookups)

// ======= Field configs — one per tab. `key` drives the form's v-model and
// the API response field it reads; `requestKey` overrides the field name
// sent in the create/update payload when it differs (only Promotion's
// `name` needs this — every other endpoint accepts `name_en`). =======

const facultyFields = [
  { key: 'code', label: 'Code', type: 'text', placeholder: 'e.g. SCT', showInTable: false, showInForm: false },
  { key: 'name_en', label: 'Name (English)', type: 'text', required: true, placeholder: 'e.g. Science And Technology' },
  { key: 'name_kh', label: 'Name (Khmer)', type: 'text', khmer: true },
  { key: 'description', label: 'Description', type: 'textarea', span: 3, showInTable: false },
  { key: 'status', label: 'Status', type: 'status', required: true },
]

const departmentFields = [
  { key: 'faculty_id', label: 'Faculty', type: 'select', optionsKey: 'faculties', optionLabel: 'name_en', optionValue: 'id', displayKey: 'faculty_name', required: true },
  { key: 'code', label: 'Code', type: 'text', placeholder: 'e.g. ICT', showInTable: false, showInForm: false },
  { key: 'name_en', label: 'Name (English)', type: 'text', required: true },
  { key: 'name_kh', label: 'Name (Khmer)', type: 'text', khmer: true },
  { key: 'description', label: 'Description', type: 'textarea', span: 3, showInTable: false },
  { key: 'status', label: 'Status', type: 'status', required: true },
]

const majorFields = [
  {
    key: 'department_id',
    label: 'Department',
    type: 'select',
    optionsKey: 'departments',
    optionLabel: (d) => `${d.name_en} — ${d.faculty_name || ''}`,
    optionValue: 'id',
    required: true,
    formatTable: (data) => (data.faculty_name ? `${data.department_name} · ${data.faculty_name}` : data.department_name || '—'),
  },
  { key: 'code', label: 'Code', type: 'text', placeholder: 'e.g. IT', showInTable: false, showInForm: false },
  { key: 'name_en', label: 'Name (English)', type: 'text', required: true },
  { key: 'name_kh', label: 'Name (Khmer)', type: 'text', khmer: true },
  { key: 'description', label: 'Description', type: 'textarea', span: 3, showInTable: false },
  { key: 'status', label: 'Status', type: 'status', required: true },
]

const stageFields = [
  { key: 'code', label: 'Code', type: 'text', placeholder: 'e.g. Y1', showInTable: false, showInForm: false },
  { key: 'name_en', label: 'Name (English)', type: 'text', required: true, placeholder: 'e.g. Year 1' },
  { key: 'name_kh', label: 'Name (Khmer)', type: 'text', khmer: true },
  { key: 'level', label: 'Level / Order', type: 'number', required: true, placeholder: '1' },
  { key: 'status', label: 'Status', type: 'status', required: true },
]

const shiftFields = [
  { key: 'code', label: 'Code', type: 'text', placeholder: 'e.g. M', showInTable: false, showInForm: false },
  { key: 'name_en', label: 'Name (English)', type: 'text', required: true, placeholder: 'e.g. Morning' },
  { key: 'name_kh', label: 'Name (Khmer)', type: 'text', khmer: true },
  { key: 'start_time', label: 'Start Time', type: 'time', required: true },
  { key: 'end_time', label: 'End Time', type: 'time', required: true },
  { key: 'status', label: 'Status', type: 'status', required: true },
]

const academicYearFields = [
  { key: 'code', label: 'Code', type: 'text', placeholder: 'e.g. AY2026-2027', showInTable: false, showInForm: false },
  { key: 'name_en', label: 'Name (English)', type: 'text', required: true, placeholder: 'e.g. 2026-2027' },
  { key: 'name_kh', label: 'Name (Khmer)', type: 'text', khmer: true },
  { key: 'start_date', label: 'Start Date', type: 'date', required: true },
  { key: 'end_date', label: 'End Date', type: 'date', required: true },
  { key: 'is_current', label: 'Current', type: 'boolean', checkboxLabel: 'Set as the current academic year', trueLabel: 'Current', falseLabel: '—', span: 3 },
  { key: 'status', label: 'Status', type: 'status', required: true },
]

const semesterFields = [
  { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', optionLabel: 'name_en', optionValue: 'id', displayKey: 'academic_year', required: true },
  { key: 'name_en', label: 'Name', type: 'text', required: true, placeholder: 'e.g. Semester 1' },
  { key: 'name_kh', label: 'Name (Khmer)', type: 'text', khmer: true },
  { key: 'order_no', label: 'Order', type: 'number', required: true, placeholder: '1' },
  { key: 'start_date', label: 'Start Date', type: 'date', required: true },
  { key: 'end_date', label: 'End Date', type: 'date', required: true },
  { key: 'status', label: 'Status', type: 'status', required: true },
]

const termFields = [
  { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', optionLabel: 'name_en', optionValue: 'id', displayKey: 'academic_year', required: true },
  { key: 'code', label: 'Code', type: 'text', placeholder: 'optional', showInTable: false, showInForm: false },
  { key: 'name_en', label: 'Name', type: 'text', required: true, placeholder: 'e.g. Term 1' },
  { key: 'name_kh', label: 'Name (Khmer)', type: 'text', khmer: true },
  { key: 'order_no', label: 'Order', type: 'number', required: true, placeholder: '1' },
  { key: 'start_date', label: 'Start Date', type: 'date', required: true },
  { key: 'end_date', label: 'End Date', type: 'date', required: true },
  { key: 'status', label: 'Status', type: 'status', required: true },
]

// Promotion's API is the one outlier: the wire field is `name`, not
// `name_en` — the response only ever exposes the computed `name_en`
// (falls back to "{year_start}-{year_end}" when blank), so the form still
// reads/writes `name_en` for consistency with every other tab and maps it
// back to `name` only when building the request payload.
const promotionFields = [
  { key: 'name_en', label: 'Name', type: 'text', placeholder: 'e.g. Promotion 22', requestKey: 'name' },
  { key: 'name_kh', label: 'Name (Khmer)', type: 'text', khmer: true },
  { key: 'year_start', label: 'Year Start', type: 'number', required: true, placeholder: '2026' },
  { key: 'year_end', label: 'Year End', type: 'number', required: true, placeholder: '2030' },
  { key: 'status', label: 'Status', type: 'status', required: true },
]

// ======= Tabs — same pill-button pattern as the User Management page,
// each pointing at its own endpoint/field-config instead of filtering one
// shared list. =======
const tabs = [
  { key: 'faculties', label: 'Faculties', title: 'Faculties', singular: 'Faculty', icon: 'pi pi-building', endpoint: '/faculties', fields: facultyFields, searchPlaceholder: 'Search code or name...', emptyMessage: 'No faculties found.' },
  { key: 'departments', label: 'Departments', title: 'Departments', singular: 'Department', icon: 'pi pi-sitemap', endpoint: '/departments', fields: departmentFields, searchPlaceholder: 'Search code or name...', emptyMessage: 'No departments found.' },
  { key: 'majors', label: 'Majors', title: 'Majors', singular: 'Major', icon: 'pi pi-bookmark', endpoint: '/majors', fields: majorFields, searchPlaceholder: 'Search code or name...', emptyMessage: 'No majors found.' },
  { key: 'promotions', label: 'Promotions', title: 'Promotions', singular: 'Promotion', icon: 'pi pi-users', endpoint: '/promotions', fields: promotionFields, searchPlaceholder: 'Search by year...', emptyMessage: 'No promotions found.' },
  { key: 'academicYears', label: 'Academic Years', title: 'Academic Years', singular: 'Academic Year', icon: 'pi pi-calendar', endpoint: '/academic-years', fields: academicYearFields, searchPlaceholder: 'Search code or name...', emptyMessage: 'No academic years found.', supportsSetCurrent: true },
  { key: 'stages', label: 'Stages', title: 'Stages', singular: 'Stage', icon: 'pi pi-flag', endpoint: '/stages', fields: stageFields, searchPlaceholder: 'Search code or name...', emptyMessage: 'No stages found.' },
  { key: 'semesters', label: 'Semesters', title: 'Semesters', singular: 'Semester', icon: 'pi pi-calendar-plus', endpoint: '/semesters', fields: semesterFields, searchPlaceholder: 'Search name...', emptyMessage: 'No semesters found.' },
  { key: 'terms', label: 'Terms', title: 'Terms', singular: 'Term', icon: 'pi pi-calendar-minus', endpoint: '/terms', fields: termFields, searchPlaceholder: 'Search code or name...', emptyMessage: 'No terms found.' },
  { key: 'shifts', label: 'Shifts', title: 'Shifts', singular: 'Shift', icon: 'pi pi-clock', endpoint: '/shifts', fields: shiftFields, searchPlaceholder: 'Search code or name...', emptyMessage: 'No shifts found.' },
]

const activeTab = ref('faculties')
const activeTabDef = computed(() => tabs.find((t) => t.key === activeTab.value))
const search = ref('')

const tabRefs = {}
const registerTabRef = (key, el) => {
  if (el) tabRefs[key] = el
}

const openNewForActiveTab = () => {
  tabRefs[activeTab.value]?.openNew()
}
</script>
