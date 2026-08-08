<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-calendar text-blue-600 text-2xl"></i>
          Academic Years Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          គ្រប់គ្រង និងកំណត់ឆ្នាំសិក្សារបស់សាកលវិទ្យាល័យ ព្រមទាំងកំណត់ឆ្នាំសិក្សាបច្ចុប្បន្ន (Current Term)
        </p>
      </div>

      <Button 
        label="Add Academic Year" 
        icon="pi pi-plus" 
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Academic Years</span>
          <span class="text-2xl font-bold text-slate-800">{{ academicYears.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-calendar"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Current Academic Year</span>
          <span class="text-base font-bold text-blue-600 mt-1 block">{{ currentYearName }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
          <i class="pi pi-star"></i>
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
    </div>

    <!-- ======= DATA TABLE CARD ======= -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
      
      <!-- Table Header Bar / Search -->
      <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <InputText 
            v-model="filters['global'].value" 
            placeholder="Search academic year code or name..." 
            class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
          />
        </div>
        <div class="text-xs text-slate-400">
          Showing <b>{{ academicYears.length }}</b> entries
        </div>
      </div>

      <!-- PrimeVue DataTable -->
      <DataTable 
        :value="academicYears" 
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
            No academic years found.
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

        <!-- Academic Year Name (EN & KH) -->
        <Column field="name_en" header="ACADEMIC YEAR" sortable class="!py-3.5">
          <template #body="{ data }">
            <div>
              <div class="font-semibold text-slate-800 text-sm flex items-center gap-2">
                {{ data.name_en }}
                <span v-if="data.is_current" class="text-[10px] bg-blue-600 text-white font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                  Current
                </span>
              </div>
              <div class="text-xs text-slate-400 mt-0.5">{{ data.name_kh }}</div>
            </div>
          </template>
        </Column>

        <!-- Start Date -->
        <Column field="start_date" header="START DATE" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs text-slate-600 font-medium">
              <i class="pi pi-calendar-plus text-slate-400 mr-1"></i>
              {{ data.start_date }}
            </span>
          </template>
        </Column>

        <!-- End Date -->
        <Column field="end_date" header="END DATE" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs text-slate-600 font-medium">
              <i class="pi pi-calendar-minus text-slate-400 mr-1"></i>
              {{ data.end_date }}
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
                v-if="!data.is_current"
                v-tooltip="'Set as Current Academic Year'"
                icon="pi pi-star" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-400 hover:!text-amber-500 hover:!bg-amber-50 !border-0"
                @click="setCurrentYear(data)"
              />
              <Button 
                icon="pi pi-pencil" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-blue-600 hover:!bg-slate-100 !border-0"
                @click="editAcademicYear(data)"
              />
              <Button 
                icon="pi pi-trash" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
                @click="confirmDeleteAcademicYear(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog 
      v-model:visible="yearDialog" 
      :header="isEdit ? 'Edit Academic Year' : 'Create Academic Year'" 
      :modal="true" 
      class="w-full max-w-lg"
    >
      <div class="space-y-4 pt-2">
        <!-- Code -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Code *</label>
          <InputText 
            v-model="yearForm.code" 
            placeholder="e.g. AY2025-2026" 
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Name EN -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name (English) *</label>
          <InputText 
            v-model="yearForm.name_en" 
            placeholder="e.g. Academic Year 2025-2026" 
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Name KH -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name (Khmer)</label>
          <InputText 
            v-model="yearForm.name_kh" 
            placeholder="ឧ. ឆ្នាំសិក្សា ២០២៥-២០២៦" 
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Start Date & End Date -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Start Date *</label>
            <InputText 
              v-model="yearForm.start_date" 
              type="date" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">End Date *</label>
            <InputText 
              v-model="yearForm.end_date" 
              type="date" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Options: Status & Is Current -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown 
              v-model="yearForm.status" 
              :options="['Active', 'Inactive']" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div class="flex items-center gap-2 pt-6">
            <input 
              id="is_current" 
              type="checkbox" 
              v-model="yearForm.is_current" 
              class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
            />
            <label for="is_current" class="text-xs font-bold text-slate-700 cursor-pointer">
              Set as Current Academic Year
            </label>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="yearDialog = false" />
          <Button label="Save Academic Year" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveAcademicYear" />
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

const academicYears = ref([])

const fetchAcademicYears = async () => {
  const { data } = await api.get('/academic-years', { params: { per_page: 100 } })
  academicYears.value = data.data
}

onMounted(fetchAcademicYears)

// Search Filter (ប្រើ String 'contains')
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// Dialog States & Form
const yearDialog = ref(false)
const isEdit = ref(false)
const yearForm = ref({
  id: null,
  code: '',
  name_en: '',
  name_kh: '',
  start_date: '',
  end_date: '',
  is_current: false,
  status: 'Active'
})

// Computed Properties
const activeCount = computed(() => academicYears.value.filter(y => y.status === 'Active').length)
const currentYearName = computed(() => {
  const current = academicYears.value.find(y => y.is_current)
  return current ? current.name_en : 'None'
})

// Actions
const openNewDialog = () => {
  yearForm.value = {
    id: null,
    code: '',
    name_en: '',
    name_kh: '',
    start_date: '',
    end_date: '',
    is_current: false,
    status: 'Active'
  }
  isEdit.value = false
  yearDialog.value = true
}

const editAcademicYear = (data) => {
  yearForm.value = { ...data }
  isEdit.value = true
  yearDialog.value = true
}

const setCurrentYear = async (data) => {
  try {
    await api.post(`/academic-years/${data.id}/set-current`)
    await fetchAcademicYears()
  } catch (error) {
    alert(extractError(error))
  }
}

const saveAcademicYear = async () => {
  if (!yearForm.value.code || !yearForm.value.name_en || !yearForm.value.start_date || !yearForm.value.end_date) {
    alert('Code, name (English), start date, and end date are required.')
    return
  }

  const payload = {
    code: yearForm.value.code,
    name_en: yearForm.value.name_en,
    name_kh: yearForm.value.name_kh,
    start_date: yearForm.value.start_date,
    end_date: yearForm.value.end_date,
    is_current: yearForm.value.is_current,
    status: yearForm.value.status,
  }

  try {
    if (isEdit.value) {
      await api.put(`/academic-years/${yearForm.value.id}`, payload)
    } else {
      await api.post('/academic-years', payload)
    }

    yearDialog.value = false
    await fetchAcademicYears()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteAcademicYear = async (data) => {
  if (confirm(`Are you sure you want to delete ${data.name_en}?`)) {
    try {
      await api.delete(`/academic-years/${data.id}`)
      await fetchAcademicYears()
    } catch (error) {
      alert(extractError(error))
    }
  }
}
</script>