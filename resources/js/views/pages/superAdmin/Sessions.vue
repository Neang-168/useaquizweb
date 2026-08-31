<template>
  <div class="space-y-6">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-calendar text-blue-600 text-2xl"></i>
          Study Sessions Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Manage study sessions (courses) that can be assigned to classes.
        </p>
      </div>

      <Button
        label="Add New Session"
        icon="pi pi-plus"
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Sessions</span>
          <span class="text-2xl font-bold text-slate-800">{{ studySessions.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-calendar"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Credits</span>
          <span class="text-2xl font-bold text-indigo-600">{{ totalCredits }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
          <i class="pi pi-star"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Sessions</span>
          <span class="text-2xl font-bold text-emerald-600">{{ activeSessionsCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
          <i class="pi pi-check-circle"></i>
        </div>
      </div>
    </div>

    <!-- ======= SESSIONS TABLE ======= -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="text-sm font-bold text-slate-800 m-0">Study Sessions</h3>
        <div class="relative w-full sm:w-56">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
          <InputText
            v-model="filters['global'].value"
            size="small"
            placeholder="Search session code or name..."
            class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
          />
        </div>
      </div>

      <!-- ======= FILTER BAR ======= -->
      <div class="flex flex-wrap items-center gap-2.5 px-4 py-3 border-b border-slate-100">
        <i class="pi pi-filter text-slate-400 text-xs"></i>
        <Dropdown v-model="facultyFilter" :options="faculties" optionLabel="name_en" optionValue="id"
          placeholder="All Faculties" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
          @change="majorFilter = null" />
        <Dropdown v-model="majorFilter" :options="majorFilterOptions" optionLabel="name_en" optionValue="id"
          placeholder="All Majors" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs" />
        <Dropdown v-model="statusFilter" :options="['Active', 'Inactive']"
          placeholder="All Statuses" showClear class="w-40 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs" />
        <Button v-if="hasActiveFilters" label="Clear Filters" icon="pi pi-filter-slash" size="small"
          class="!bg-slate-100 !border-slate-100 !text-slate-600 hover:!bg-slate-200 !rounded-lg !text-xs !font-semibold !px-3 !py-1"
          @click="clearFilters" />
      </div>

      <DataTable
        :value="filteredSessions"
        v-model:filters="filters"
        dataKey="id"
        paginator
        :rows="rows"
        v-model:first="first"
        :rowsPerPageOptions="[10, 20, 50]"
        responsiveLayout="scroll"
        :loading="loading"
        class="p-datatable-sm sessions-table"
        scrollable
        scrollDirection="both"
        tableStyle="min-width: 1200px"
      >
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No study sessions found.
          </div>
        </template>

        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ filteredSessions.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredSessions.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ filteredSessions.length }}</span>
          </span>
        </template>

        <!-- Code -->
        <Column field="code" header="CODE" sortable style="padding-left: 1.25rem">
          <template #body="{ data }">
            <span class="font-mono font-bold text-indigo-600 text-sm">{{ data.code }}</span>
          </template>
        </Column>

        <!-- Name (English) -->
        <Column field="name_en" header="SESSION NAME" sortable>
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</span>
          </template>
        </Column>

        <!-- Name (Khmer) -->
        <Column field="name_kh" header="SESSION NAME (KH)">
          <template #body="{ data }">
            <span v-if="data.name_kh" class="text-slate-600 text-sm font-khmer">{{ data.name_kh }}</span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Faculty -->
        <Column field="faculty_name" header="FACULTY" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ data.faculty_name }}</span>
          </template>
        </Column>

        <!-- Credits -->
        <Column field="credits" header="CREDITS" sortable>
          <template #body="{ data }">
            <span class="font-bold text-slate-700 text-sm">{{ data.credits }} Credits</span>
          </template>
        </Column>

        <!-- Status -->
        <Column field="status" header="STATUS" sortable>
          <template #body="{ data }">
            <span
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block"
            >
              {{ data.status }}
            </span>
          </template>
        </Column>

        <!-- Actions -->
        <Column header="ACTIONS" class="!text-right" style="padding-right: 1.25rem">
          <template #body="{ data }">
            <div class="flex items-center justify-end gap-1.5">
              <Button
                icon="pi pi-pencil"
                size="small"
                class="!w-8 !h-8 !p-2 !bg-slate-100 hover:!bg-slate-200 !border-slate-100 !text-slate-600 !rounded-xl shadow-xs"
                title="Edit Session"
                @click="editSession(data)"
              />
              <Button
                icon="pi pi-trash"
                size="small"
                class="!w-8 !h-8 !p-2 !bg-rose-50 hover:!bg-rose-100 !border-rose-100 !text-rose-600 !rounded-xl shadow-xs"
                title="Delete Session"
                @click="confirmDeleteSession(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog
      v-model:visible="sessionDialog"
      :header="isEdit ? 'Edit Session' : 'Create New Session'"
      :modal="true"
      class="w-full max-w-lg"
    >
      <div class="space-y-4 pt-2">
        <!-- Faculty / Department / Major Selection -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty *</label>
          <Dropdown
            v-model="sessionForm.faculty_id"
            :options="faculties"
            optionLabel="name_en"
            optionValue="id"
            placeholder="Select Faculty"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            @change="sessionForm.department_id = null; sessionForm.major_id = null"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
            <Dropdown
              v-model="sessionForm.department_id"
              :options="departmentsForSelectedFaculty"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Any department"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              :disabled="!sessionForm.faculty_id"
              @change="sessionForm.major_id = null"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major</label>
            <Dropdown
              v-model="sessionForm.major_id"
              :options="majorsForSelectedDepartment"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Any major"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              :disabled="!sessionForm.department_id"
            />
          </div>
        </div>

        <!-- Code & Credits -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Session Code *</label>
            <InputText
              v-model="sessionForm.code"
              placeholder="e.g. SS101"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Credits *</label>
            <InputText
              v-model="sessionForm.credits"
              type="number"
              placeholder="3"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Name EN -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Session Name (English) *</label>
          <InputText
            v-model="sessionForm.name_en"
            placeholder="e.g. Morning Study Session"
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Name KH -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Session Name (Khmer)</label>
          <InputText
            v-model="sessionForm.name_kh"
            placeholder="ឧ. សម័យសិក្សាព្រឹក"
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Status & Description -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
          <Dropdown
            v-model="sessionForm.status"
            :options="['Active', 'Inactive']"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Description</label>
          <Textarea
            v-model="sessionForm.description"
            rows="3"
            placeholder="Brief session overview..."
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="sessionDialog = false" />
          <Button label="Save Session" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveSession" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api, { toastFromError } from '../../../api'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

// PrimeVue Components Import
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Textarea from 'primevue/textarea'

const toast = useToast()
const confirm = useConfirm()

const studySessions = ref([])
const faculties = ref([])
const departments = ref([])
const majors = ref([])
const loading = ref(false)

const fetchSessions = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/study-sessions', { params: { per_page: 100 } })
    studySessions.value = data.data
  } finally {
    loading.value = false
  }
}

const fetchLookups = async () => {
  const [facultiesRes, departmentsRes, majorsRes] = await Promise.all([
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/departments', { params: { per_page: 200 } }),
    api.get('/majors', { params: { per_page: 100 } }),
  ])
  faculties.value = facultiesRes.data.data
  departments.value = departmentsRes.data.data
  majors.value = majorsRes.data.data
}

onMounted(() => {
  fetchSessions()
  fetchLookups()
})

// Search Filter
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// Pagination display state (purely visual — mirrors FeedbackTable's pattern)
const first = ref(0)
const rows = ref(10)

// ======= Filter Bar (Faculty / Major / Status) =======
const facultyFilter = ref(null)
const majorFilter = ref(null)
const statusFilter = ref(null)

const hasActiveFilters = computed(() =>
  !!(facultyFilter.value || majorFilter.value || statusFilter.value)
)

// No Department filter sits between Faculty and Major in this bar, so Major
// is narrowed transitively: each major's department tells us its faculty.
const majorFilterOptions = computed(() => {
  if (!facultyFilter.value) return majors.value
  return majors.value.filter((m) => {
    const dept = departments.value.find((d) => d.id === m.department_id)
    return dept && dept.faculty_id === facultyFilter.value
  })
})

const clearFilters = () => {
  facultyFilter.value = null
  majorFilter.value = null
  statusFilter.value = null
}

const filteredSessions = computed(() => studySessions.value.filter((s) => {
  if (facultyFilter.value && s.faculty_id !== facultyFilter.value) return false
  if (majorFilter.value && s.major_id !== majorFilter.value) return false
  if (statusFilter.value && s.status !== statusFilter.value) return false
  return true
}))

// Jump back to page 1 whenever the filtered list changes shape, so the
// paginator never gets stranded past the end of a smaller filtered list.
watch(filteredSessions, () => {
  first.value = 0
})

// Dialog States & Form
const sessionDialog = ref(false)
const isEdit = ref(false)
const sessionForm = ref({
  id: null,
  code: '',
  name_en: '',
  name_kh: '',
  faculty_id: null,
  department_id: null,
  major_id: null,
  credits: 3,
  description: '',
  status: 'Active'
})

// Department/Major options narrow down as Faculty, then Department, is picked
const departmentsForSelectedFaculty = computed(() =>
  departments.value.filter(d => d.faculty_id === sessionForm.value.faculty_id)
)
const majorsForSelectedDepartment = computed(() =>
  majors.value.filter(m => m.department_id === sessionForm.value.department_id)
)

// Computed Properties
const activeSessionsCount = computed(() => studySessions.value.filter(s => s.status === 'Active').length)
const totalCredits = computed(() => studySessions.value.reduce((sum, s) => sum + Number(s.credits || 0), 0))

// Actions
const openNewDialog = () => {
  sessionForm.value = {
    id: null,
    code: '',
    name_en: '',
    name_kh: '',
    faculty_id: null,
    department_id: null,
    major_id: null,
    credits: 3,
    description: '',
    status: 'Active'
  }
  isEdit.value = false
  sessionDialog.value = true
}

const editSession = (data) => {
  const major = majors.value.find(m => m.id === data.major_id)
  sessionForm.value = { ...data, department_id: major?.department_id ?? null }
  isEdit.value = true
  sessionDialog.value = true
}

const saveSession = async () => {
  if (!sessionForm.value.code || !sessionForm.value.name_en || !sessionForm.value.faculty_id) {
    toast.add({ severity: 'warn', summary: 'Missing information', detail: 'Faculty, session code, and name (English) are required.', life: 4000 })
    return
  }

  const payload = {
    faculty_id: sessionForm.value.faculty_id,
    major_id: sessionForm.value.major_id,
    code: sessionForm.value.code,
    name_en: sessionForm.value.name_en,
    name_kh: sessionForm.value.name_kh,
    credits: sessionForm.value.credits,
    description: sessionForm.value.description,
    status: sessionForm.value.status,
  }

  try {
    if (isEdit.value) {
      await api.put(`/study-sessions/${sessionForm.value.id}`, payload)
      toast.add({ severity: 'success', summary: 'Session updated', detail: `${sessionForm.value.name_en} was updated successfully.`, life: 3000 })
    } else {
      await api.post('/study-sessions', payload)
      toast.add({ severity: 'success', summary: 'Session created', detail: `${sessionForm.value.name_en} was created successfully.`, life: 3000 })
    }

    sessionDialog.value = false
    await fetchSessions()
  } catch (error) {
    toast.add({ summary: isEdit.value ? 'Failed to update session' : 'Failed to create session', ...toastFromError(error) })
  }
}

const confirmDeleteSession = (data) => {
  confirm.require({
    header: 'Delete session',
    message: `Are you sure you want to delete ${data.name_en}?`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Delete',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await api.delete(`/study-sessions/${data.id}`)
        toast.add({ severity: 'success', summary: 'Session deleted', detail: `${data.name_en} was deleted.`, life: 3000 })
        await fetchSessions()
      } catch (error) {
        toast.add({ summary: 'Failed to delete session', ...toastFromError(error) })
      }
    },
  })
}
</script>

<style scoped>
/* Header two sizes smaller, body two sizes larger, than the table's base text-xs. */
.sessions-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.sessions-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}
</style>
