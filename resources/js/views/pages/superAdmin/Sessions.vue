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

    <!-- ======= TABLE HEADER BAR / SEARCH & FILTER ======= -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
      <div class="relative w-full sm:w-80">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <InputText
          v-model="filters['global'].value"
          placeholder="Search session code or name..."
          class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
        />
      </div>
      <div class="text-xs text-slate-400">
        Showing <b>{{ filteredSessions.length }}</b> entries
      </div>
    </div>

    <!-- ======= FILTER BAR ======= -->
    <div class="flex flex-wrap items-center gap-2.5 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-sm">
      <i class="pi pi-filter text-slate-400 text-sm ml-1"></i>
      <Dropdown v-model="facultyFilter" :options="faculties" optionLabel="name_en" optionValue="id"
        placeholder="All Faculties" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Dropdown v-model="majorFilter" :options="majors" optionLabel="name_en" optionValue="id"
        placeholder="All Majors" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Dropdown v-model="statusFilter" :options="['Active', 'Inactive']"
        placeholder="All Statuses" showClear class="w-40 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Button v-if="hasActiveFilters" label="Clear Filters" icon="pi pi-filter-slash"
        class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !py-2 !px-3 !text-xs !font-semibold cursor-pointer"
        @click="clearFilters" />
    </div>

    <!-- ======= DATA TABLE (floating card rows) ======= -->
    <DataTable
        :value="filteredSessions"
        v-model:filters="filters"
        dataKey="id"
        paginator
        :rows="5"
        :rowsPerPageOptions="[5, 10, 20]"
        responsiveLayout="scroll"
        class="p-datatable-sm sessions-table"
      >
        <template #empty>
          <div class="text-center py-8 text-slate-400 text-sm">
            No study sessions found.
          </div>
        </template>

        <!-- Code -->
        <Column field="code" header="CODE" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
              {{ data.code }}
            </span>
          </template>
        </Column>

        <!-- Name (English) -->
        <Column field="name_en" header="SESSION NAME" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</span>
          </template>
        </Column>

        <!-- Name (Khmer) -->
        <Column field="name_kh" header="SESSION NAME (KH)" class="!py-3.5">
          <template #body="{ data }">
            <span v-if="data.name_kh" class="text-slate-600 text-sm font-khmer">{{ data.name_kh }}</span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Faculty -->
        <Column field="faculty_name" header="FACULTY" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs text-slate-700 font-medium">
              {{ data.faculty_name }}
            </span>
          </template>
        </Column>

        <!-- Credits -->
        <Column field="credits" header="CREDITS" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-lg">
              {{ data.credits }} Credits
            </span>
          </template>
        </Column>

        <!-- Status -->
        <Column field="status" header="STATUS" sortable class="!py-3.5">
          <template #body="{ data }">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="data.status === 'Active' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
              {{ data.status }}
            </span>
          </template>
        </Column>

        <!-- Actions -->
        <Column header="ACTIONS" class="!text-right !py-3.5">
          <template #body="{ data }">
            <div class="flex items-center justify-end gap-1.5">
              <Button
                icon="pi pi-pencil"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-blue-50 !text-blue-600 hover:!bg-blue-100 hover:!text-blue-700 !border !border-blue-100"
                title="Edit Session"
                @click="editSession(data)"
              />
              <Button
                icon="pi pi-trash"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-rose-50 !text-rose-600 hover:!bg-rose-100 hover:!text-rose-700 !border !border-rose-100"
                title="Delete Session"
                @click="confirmDeleteSession(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog
      v-model:visible="sessionDialog"
      :header="isEdit ? 'Edit Session' : 'Create New Session'"
      :modal="true"
      class="w-full max-w-lg"
    >
      <div class="space-y-4 pt-2">
        <!-- Faculty / Degree / Major Selection -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty *</label>
          <Dropdown
            v-model="sessionForm.faculty_id"
            :options="faculties"
            optionLabel="name_en"
            optionValue="id"
            placeholder="Select Faculty"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            @change="sessionForm.degree_id = null; sessionForm.major_id = null"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Degree</label>
            <Dropdown
              v-model="sessionForm.degree_id"
              :options="degreesForSelectedFaculty"
              optionLabel="title_en"
              optionValue="id"
              placeholder="Any degree"
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
              :options="majorsForSelectedDegree"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Any major"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              :disabled="!sessionForm.degree_id"
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
import { ref, computed, onMounted } from 'vue'
import api, { extractError } from '../../../api'

// PrimeVue Components Import
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Textarea from 'primevue/textarea'

const studySessions = ref([])
const faculties = ref([])
const degrees = ref([])
const majors = ref([])

const fetchSessions = async () => {
  const { data } = await api.get('/study-sessions', { params: { per_page: 100 } })
  studySessions.value = data.data
}

const fetchLookups = async () => {
  const [facultiesRes, degreesRes, majorsRes] = await Promise.all([
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/degrees', { params: { per_page: 100 } }),
    api.get('/majors', { params: { per_page: 100 } }),
  ])
  faculties.value = facultiesRes.data.data
  degrees.value = degreesRes.data.data
  majors.value = majorsRes.data.data
}

onMounted(() => {
  fetchSessions()
  fetchLookups()
})

// Search Filter
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// ======= Filter Bar (Faculty / Major / Status) =======
const facultyFilter = ref(null)
const majorFilter = ref(null)
const statusFilter = ref(null)

const hasActiveFilters = computed(() =>
  !!(facultyFilter.value || majorFilter.value || statusFilter.value)
)

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

// Dialog States & Form
const sessionDialog = ref(false)
const isEdit = ref(false)
const sessionForm = ref({
  id: null,
  code: '',
  name_en: '',
  name_kh: '',
  faculty_id: null,
  degree_id: null,
  major_id: null,
  credits: 3,
  description: '',
  status: 'Active'
})

// Degree/Major options narrow down as Faculty, then Degree, is picked
const degreesForSelectedFaculty = computed(() =>
  degrees.value.filter(d => d.faculty_id === sessionForm.value.faculty_id)
)
const majorsForSelectedDegree = computed(() =>
  majors.value.filter(m => m.degree_id === sessionForm.value.degree_id)
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
    degree_id: null,
    major_id: null,
    credits: 3,
    description: '',
    status: 'Active'
  }
  isEdit.value = false
  sessionDialog.value = true
}

const editSession = (data) => {
  sessionForm.value = { ...data }
  isEdit.value = true
  sessionDialog.value = true
}

const saveSession = async () => {
  if (!sessionForm.value.code || !sessionForm.value.name_en || !sessionForm.value.faculty_id) {
    alert('Faculty, session code, and name (English) are required.')
    return
  }

  const payload = {
    faculty_id: sessionForm.value.faculty_id,
    degree_id: sessionForm.value.degree_id,
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
    } else {
      await api.post('/study-sessions', payload)
    }

    sessionDialog.value = false
    await fetchSessions()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteSession = async (data) => {
  if (confirm(`Are you sure you want to delete ${data.name_en}?`)) {
    try {
      await api.delete(`/study-sessions/${data.id}`)
      await fetchSessions()
    } catch (error) {
      alert(extractError(error))
    }
  }
}
</script>

<style scoped>
/* "Floating card row" table, matching the Subjects/Teachers page: header text
   sitting directly on the page background, and each row as its own
   white rounded card with a soft shadow — spacing does the separating,
   not gridlines. */
.sessions-table :deep(.p-datatable-table) {
  border-collapse: separate;
  border-spacing: 0 0.6rem;
}

.sessions-table :deep(.p-datatable-table-container),
.sessions-table :deep(.p-datatable-header),
.sessions-table :deep(.p-datatable-footer),
.sessions-table :deep(.p-datatable-thead),
.sessions-table :deep(.p-datatable),
.sessions-table :deep(.p-datatable-mask) {
  border: none !important;
  box-shadow: none;
  background: transparent;
}

.sessions-table :deep(.p-datatable-thead > tr > th) {
  background: #ffffff;
  border: none !important;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 1px 5px rgba(15, 23, 42, 0.05);
  color: #1d4ed8;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  line-height: 1.5rem;
  padding: 1rem 1rem;
  white-space: nowrap;
}

.sessions-table :deep(.p-datatable-thead > tr > th:first-child) {
  border-top-left-radius: 0.6rem;
  border-bottom-left-radius: 0.6rem;
}

.sessions-table :deep(.p-datatable-thead > tr > th:last-child) {
  border-top-right-radius: 0.6rem;
  border-bottom-right-radius: 0.6rem;
}

.sessions-table :deep(.p-datatable-tbody > tr > td) {
  background: #ffffff;
  border: none !important;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 1px 5px rgba(15, 23, 42, 0.05);
  line-height: 1.25rem;
  padding: 0.75rem 1rem;
  transition: background-color 0.15s ease;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sessions-table :deep(.p-datatable-tbody > tr > td:first-child) {
  border-top-left-radius: 0.6rem;
  border-bottom-left-radius: 0.6rem;
}

.sessions-table :deep(.p-datatable-tbody > tr > td:last-child) {
  border-top-right-radius: 0.6rem;
  border-bottom-right-radius: 0.6rem;
  overflow: visible;
}

.sessions-table :deep(.p-datatable-tbody > tr:hover > td) {
  background: #f8fafc;
}

.sessions-table :deep(.p-datatable-tbody > tr) {
  outline: none;
}

.sessions-table :deep(.p-paginator) {
  background: transparent;
  border: none;
  padding-top: 0.75rem;
}
</style>
