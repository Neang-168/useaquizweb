<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-building text-blue-600 text-2xl"></i>
          Faculties Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          គ្រប់គ្រង និងចាត់ចែងបញ្ជីមហាវិទ្យាល័យទាំងអស់ក្នុងសាកលវិទ្យាល័យ
        </p>
      </div>

      <Button 
        label="Add New Faculty" 
        icon="pi pi-plus" 
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Faculties</span>
          <span class="text-2xl font-bold text-slate-800">{{ faculties.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-building"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Status</span>
          <span class="text-2xl font-bold text-emerald-600">{{ activeCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
          <i class="pi pi-check-circle"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Inactive Status</span>
          <span class="text-2xl font-bold text-rose-500">{{ faculties.length - activeCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl">
          <i class="pi pi-times-circle"></i>
        </div>
      </div>
    </div>

    <!-- ======= DATA TABLE CARD ======= -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
      
      <!-- Table Header Bar / Search -->
      <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <InputText 
            v-model="filters['global'].value" 
            placeholder="Search faculty name or code..." 
            class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
          />
        </div>
        <div class="text-xs text-slate-400">
          Showing <b>{{ faculties.length }}</b> entries
        </div>
      </div>

      <!-- PrimeVue DataTable -->
      <DataTable 
        :value="faculties" 
        v-model:filters="filters"
        dataKey="id" 
        paginator 
        :rows="5" 
        :rowsPerPageOptions="[5, 10, 20]"
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <template #empty>
          <div class="text-center py-8 text-slate-400 text-sm">
            No faculties found.
          </div>
        </template>

        <!-- Faculty Code -->
        <Column field="code" header="CODE" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
              {{ data.code }}
            </span>
          </template>
        </Column>

        <!-- Faculty Name (Khmer & English) -->
        <Column field="name_en" header="FACULTY NAME" sortable class="!py-3.5">
          <template #body="{ data }">
            <div>
              <div class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</div>
              <div class="text-xs text-slate-400 mt-0.5">{{ data.name_kh }}</div>
            </div>
          </template>
        </Column>

        <!-- Description -->
        <Column field="description" header="DESCRIPTION" class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs text-slate-500 line-clamp-1 max-w-xs">
              {{ data.description || 'N/A' }}
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
            <div class="flex items-center justify-end gap-2">
              <Button 
                icon="pi pi-pencil" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-blue-600 hover:!bg-slate-100 !border-0"
                @click="editFaculty(data)"
              />
              <Button 
                icon="pi pi-trash" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
                @click="confirmDeleteFaculty(data)"
              />
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
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Faculty Name EN -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name (English) *</label>
          <InputText 
            v-model="faculty.name_en" 
            placeholder="e.g. Faculty of Science and Technology" 
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Faculty Name KH -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name (Khmer)</label>
          <InputText 
            v-model="faculty.name_kh" 
            placeholder="ឧ. មហាវិទ្យាល័យវិទ្យាសាស្ត្រ និងបច្ចេកវិទ្យា" 
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Status & Description -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown 
              v-model="faculty.status" 
              :options="['Active', 'Inactive']" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Description</label>
          <Textarea 
            v-model="faculty.description" 
            rows="3" 
            placeholder="Brief description about this faculty..." 
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="facultyDialog = false" />
          <Button label="Save Faculty" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveFaculty" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { FilterMatchMode } from '@primevue/core/api'

// PrimeVue Components Import
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Textarea from 'primevue/textarea'

// Mock Data
const faculties = ref([
  { id: 1, code: 'FST', name_en: 'Faculty of Science & Technology', name_kh: 'មហាវិទ្យាល័យវិទ្យាសាស្ត្រ និងបច្ចេកវិទ្យា', description: 'Focuses on Computer Science, IT, and Engineering', status: 'Active' },
  { id: 2, code: 'FBA', name_en: 'Faculty of Business Administration', name_kh: 'មហាវិទ្យាល័យគ្រប់គ្រងពាណិជ្ជកម្ម', description: 'Focuses on Management, Accounting, and Marketing', status: 'Active' },
  { id: 3, code: 'FOL', name_en: 'Faculty of Law & Social Sciences', name_kh: 'មហាវិទ្យាល័យនីតិសាស្ត្រ និងវិទ្យាសាស្ត្រសង្គម', description: 'Focuses on Public Law and International Relations', status: 'Active' },
  { id: 4, code: 'FOE', name_en: 'Faculty of Education & Languages', name_kh: 'មហាវិទ្យាល័យអប់រំ និងភាសាបរទេស', description: 'Focuses on English Literature and Pedagogy', status: 'Inactive' },
])

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

const saveFaculty = () => {
  if (!faculty.value.code || !faculty.value.name_en) return

  if (isEdit.value) {
    const index = faculties.value.findIndex(f => f.id === faculty.value.id)
    if (index !== -1) faculties.value[index] = { ...faculty.value }
  } else {
    faculty.value.id = Date.now()
    faculties.value.unshift({ ...faculty.value })
  }

  facultyDialog.value = false
}

const confirmDeleteFaculty = (data) => {
  if (confirm(`Are you sure you want to delete ${data.name_en}?`)) {
    faculties.value = faculties.value.filter(f => f.id !== data.id)
  }
}
</script>