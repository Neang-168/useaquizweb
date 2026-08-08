<template>
  <div class="h-[calc(100vh-2rem)] flex flex-col gap-4 overflow-hidden">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          Faculties Management
        </h1>
        <p class="text-sm text-slate-500 m-0 mt-1">
          Manage faculties and their details. You can create, edit, or delete faculties as needed.
        </p>
      </div>

      <Button
        label="New Faculty"
        icon="pi pi-plus"
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-sm !py-2 !px-2.5 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= DATA TABLE CARD (fills remaining height, no page scroll) ======= -->
    <div class="bg-white rounded-sm shadow-sm border border-slate-200/80 overflow-hidden flex-1 flex flex-col p-5">

      <!-- Table Header Bar / Search -->
      <div class="flex flex-col sm:flex-row justify-between items-center gap-3 shrink-0">
        <div class="relative w-full sm:w-80">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <InputText
            v-model="filters['global'].value"
            placeholder="Search faculty name or code..."
            class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-sm !text-sm focus:!bg-white"
          />
        </div>
        <!-- <div class="text-xs text-slate-400 whitespace-nowrap">
          <b class="text-slate-600">{{ faculties.length }}</b> total faculties
        </div> -->
      </div>

      <!-- PrimeVue DataTable: body scrolls internally, paginator pinned to bottom -->
      <DataTable
        :value="faculties"
        v-model:filters="filters"
        dataKey="id"
        paginator
        paginatorPosition="bottom"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords}"
        :rows="10"
        :rowsPerPageOptions="[10, 20, 50]"
        scrollable
        scrollHeight="flex"
        responsiveLayout="scroll"
        :loading="loading"
        class="p-datatable-sm faculties-table flex-1 min-h-0 mt-5"
      >
        <template #empty>
          <div class="text-center py-16 text-slate-400 text-sm flex flex-col items-center gap-2">
            <i class="pi pi-inbox text-3xl text-slate-300"></i>
            No faculties found.
          </div>
        </template>

        <!-- Faculty Code -->
        <Column field="code" header="CODE" sortable style="width: 120px">
          <template #body="{ data }">
            <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-sm border border-blue-100">
              {{ data.code }}
            </span>
          </template>
        </Column>

        <!-- Faculty Name (English) -->
        <Column field="name_en" header="FACULTY NAME (EN)" sortable>
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-xs">{{ data.name_en }}</span>
          </template>
        </Column>

        <!-- Faculty Name (Khmer) -->
        <Column field="name_kh" header="FACULTY NAME (KH)" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-xs font-khmer">{{ data.name_kh || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Description -->
        <Column field="description" header="DESCRIPTION">
          <template #body="{ data }">
            <span class="text-xs text-slate-500 line-clamp-1 max-w-xs block">
              {{ data.description || 'N/A' }}
            </span>
          </template>
        </Column>

        <!-- Status -->
        <Column field="status" header="STATUS" sortable style="width: 130px">
          <template #body="{ data }">
            <span
              class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium"
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="data.status === 'Active' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
              {{ data.status }}
            </span>
          </template>
        </Column>

        <!-- Actions -->
        <Column header="ACTIONS" style="width: 170px" class="!text-center">
          <template #body="{ data }">
            <div class="flex items-center justify-center">
              <button
                type="button"
                title="Edit faculty"
                class="action-btn action-btn-edit"
                @click="editFaculty(data)"
              >
                <i class="pi pi-pencil"></i>
                <span>Edit</span>
              </button>
              <button
                type="button"
                title="Delete faculty"
                class="action-btn action-btn-delete"
                @click="confirmDeleteFaculty(data)"
              >
                <i class="pi pi-trash"></i>
                <span>Delete</span>
              </button>
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog
      v-model:visible="facultyDialog"
      :header="isEdit ? 'Edit Faculty' : 'Create New Faculty'"
      :modal="true"
      class="w-full max-w-lg"
    >
      <div class="space-y-4 pt-2">
        <!-- Faculty Code -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty Code *</label>
          <InputText
            v-model="faculty.code"
            placeholder="e.g. FST"
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-sm !text-sm"
          />
        </div>

        <!-- Faculty Name EN -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name (English) *</label>
          <InputText
            v-model="faculty.name_en"
            placeholder="e.g. Faculty of Science and Technology"
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-sm !text-sm"
          />
        </div>

        <!-- Faculty Name KH -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name (Khmer)</label>
          <InputText
            v-model="faculty.name_kh"
            placeholder="ឧ. មហាវិទ្យាល័យវិទ្យាសាស្ត្រ និងបច្ចេកវិទ្យា"
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-sm !text-sm"
          />
        </div>

        <!-- Status & Description -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown
              v-model="faculty.status"
              :options="['Active', 'Inactive']"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-sm text-sm"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Description</label>
          <Textarea
            v-model="faculty.description"
            rows="3"
            placeholder="Brief description about this faculty..."
            class="w-full !bg-slate-50 !border-slate-200 !rounded-sm !text-sm"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-sm !text-xs !font-semibold" @click="facultyDialog = false" />
          <Button label="Save Faculty" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-sm !text-xs !font-semibold" @click="saveFaculty" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { FilterMatchMode } from '@primevue/core/api'
import api, { extractError } from '../../../api'

// PrimeVue Components Import
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Textarea from 'primevue/textarea'

const faculties = ref([])
const loading = ref(false)

const fetchFaculties = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/faculties', { params: { per_page: 100 } })
    faculties.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchFaculties)

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
})

const facultyDialog = ref(false)
const isEdit = ref(false)
const faculty = ref({
  id: null,
  code: '',
  name_en: '',
  name_kh: '',
  description: '',
  status: 'Active'
})

// Computed Properties
const activeCount = computed(() => faculties.value.filter(f => f.status === 'Active').length)

// Actions
const openNewDialog = () => {
  faculty.value = { id: null, code: '', name_en: '', name_kh: '', description: '', status: 'Active' }
  isEdit.value = false
  facultyDialog.value = true
}

const editFaculty = (data) => {
  faculty.value = { ...data }
  isEdit.value = true
  facultyDialog.value = true
}

const saveFaculty = async () => {
  if (!faculty.value.code || !faculty.value.name_en) {
    alert('Faculty code and name are required.')
    return
  }

  const payload = {
    code: faculty.value.code,
    name_en: faculty.value.name_en,
    name_kh: faculty.value.name_kh,
    description: faculty.value.description,
    status: faculty.value.status,
  }

  try {
    if (isEdit.value) {
      await api.put(`/faculties/${faculty.value.id}`, payload)
    } else {
      await api.post('/faculties', payload)
    }

    facultyDialog.value = false
    await fetchFaculties()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteFaculty = async (data) => {
  if (confirm(`Are you sure you want to delete ${data.name_en}?`)) {
    try {
      await api.delete(`/faculties/${data.id}`)
      await fetchFaculties()
    } catch (error) {
      alert(extractError(error))
    }
  }
}
</script>

<style scoped>
/* Full-grid bordered table look */
.faculties-table :deep(.p-datatable-table) {
  border-collapse: collapse;
}

.faculties-table :deep(.p-datatable-header),
.faculties-table :deep(.p-datatable-thead > tr > th) {
  background: #f8fafc;
  color: black;
  font-size: 0.875rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  border: 1px solid #D4D4D4;
  padding: 1rem 1rem;
}

.faculties-table :deep(.p-datatable-tbody > tr > td) {
  border: 1px solid #D4D4D4;
  padding: 0.6rem 1rem;
  font-size: 0.8rem;
}

.font-khmer {
  font-family: 'Roboto', ui-sans-serif, system-ui, sans-serif;
}

.faculties-table :deep(.p-datatable-tbody > tr:hover) {
  background: #f8fafc;
}

.faculties-table :deep(.p-datatable-tbody > tr:nth-child(even)) {
  background: #fbfcfe;
}

.faculties-table :deep(.p-datatable-tbody > tr:nth-child(even):hover) {
  background: #f1f5f9;
}

/* Outer frame so the grid reads as one contained block (no extra border — the cell grid already forms the edges) */
.faculties-table :deep(.p-datatable-table-container) {
  border-radius: 0.25rem;
  overflow: hidden;
}

/* ===== Action column buttons ===== */
.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.7rem;
  border-radius: 0;
  border: 0;
  color: #fff;
  font-size: 0.72rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.action-btn:active {
  transform: scale(0.94);
}

.action-btn-edit {
  background: #2563eb;
  border-radius: 0.25rem 0 0 0.25rem;
}

.action-btn-edit:hover {
  background: #1d4ed8;
}

.action-btn-delete {
  background: #dc2626;
  border-radius: 0 0.25rem 0.25rem 0;
}

.action-btn-delete:hover {
  background: #b91c1c;
}

/* Pinned, minimal paginator */
.faculties-table :deep(.p-datatable-paginator-bottom) {
  border-width: 0;
}

.faculties-table :deep(.p-paginator) {
  border-radius: 0 0 0.25rem 0.25rem;
  background: #ffffff;
  padding: 0.6rem 1rem;
  justify-content: flex-start;
}

.faculties-table :deep(.p-paginator .p-paginator-pages .p-paginator-page) {
  border-radius: 0.25rem;
  min-width: 2rem;
  height: 2rem;
  font-size: 0.75rem;
}

.faculties-table :deep(.p-paginator .p-paginator-page.p-highlight) {
  background: #2563eb;
  color: #fff;
}

.faculties-table :deep(.p-paginator-current) {
  margin-left: auto;
  font-size: 0.75rem;
  color: #64748b;
}

/* Ensure scrollable body fills available space, not the whole page */
.faculties-table :deep(.p-datatable-wrapper) {
  min-height: 0;
}
</style>