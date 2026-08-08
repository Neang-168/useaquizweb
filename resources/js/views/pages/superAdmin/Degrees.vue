<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-graduation-cap text-blue-600 text-2xl"></i>
          Degrees & Majors Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          គ្រប់គ្រងកម្រិតសិក្សា (Degrees) និងជំនាញសិក្សា (Majors) របស់សាកលវិទ្យាល័យ
        </p>
      </div>

      <Button 
        :label="activeTab === 0 ? 'Add New Degree' : 'Add New Major'" 
        icon="pi pi-plus" 
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Degrees</span>
          <span class="text-2xl font-bold text-slate-800">{{ degrees.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-bookmark"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Majors</span>
          <span class="text-2xl font-bold text-indigo-600">{{ majors.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
          <i class="pi pi-sitemap"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Majors</span>
          <span class="text-2xl font-bold text-emerald-600">{{ activeMajorsCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
          <i class="pi pi-check-circle"></i>
        </div>
      </div>
    </div>

    <!-- ======= TABS CONTAINER ======= -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
      
      <!-- Tab Header Buttons -->
      <div class="flex border-b border-slate-200 bg-slate-50/50 p-2 gap-2">
        <button 
          @click="activeTab = 0"
          class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all border-0 cursor-pointer"
          :class="activeTab === 0 ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800 bg-transparent'"
        >
          <i class="pi pi-bookmark text-base"></i>
          <span>Degrees List</span>
          <span class="ml-1 px-2 py-0.5 text-xs rounded-full" :class="activeTab === 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600'">
            {{ degrees.length }}
          </span>
        </button>

        <button 
          @click="activeTab = 1"
          class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all border-0 cursor-pointer"
          :class="activeTab === 1 ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800 bg-transparent'"
        >
          <i class="pi pi-sitemap text-base"></i>
          <span>Majors List</span>
          <span class="ml-1 px-2 py-0.5 text-xs rounded-full" :class="activeTab === 1 ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600'">
            {{ majors.length }}
          </span>
        </button>
      </div>

      <!-- ======= TAB 1: DEGREES TABLE ======= -->
      <div v-if="activeTab === 0">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
          <div class="relative w-full sm:w-80">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <InputText 
              v-model="degreeFilters['global'].value" 
              placeholder="Search degree level or code..." 
              class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
            />
          </div>
        </div>

        <DataTable 
          :value="degrees" 
          v-model:filters="degreeFilters"
          dataKey="id" 
          paginator 
          :rows="5" 
          responsiveLayout="scroll"
          class="p-datatable-sm"
        >
          <Column field="code" header="CODE" sortable class="!py-3.5">
            <template #body="{ data }">
              <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                {{ data.code }}
              </span>
            </template>
          </Column>

          <Column field="title_en" header="DEGREE LEVEL" sortable class="!py-3.5">
            <template #body="{ data }">
              <div>
                <div class="font-semibold text-slate-800 text-sm">{{ data.title_en }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ data.title_kh }}</div>
              </div>
            </template>
          </Column>

          <Column field="duration_years" header="DURATION" class="!py-3.5">
            <template #body="{ data }">
              <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                {{ data.duration_years }} Years
              </span>
            </template>
          </Column>

          <Column header="ACTIONS" class="!text-right !py-3.5">
            <template #body="{ data }">
              <div class="flex items-center justify-end gap-2">
                <Button icon="pi pi-pencil" class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-blue-600 hover:!bg-slate-100 !border-0" @click="editDegree(data)" />
                <Button icon="pi pi-trash" class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0" @click="confirmDeleteDegree(data)" />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- ======= TAB 2: MAJORS TABLE ======= -->
      <div v-if="activeTab === 1">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
          <div class="relative w-full sm:w-80">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <InputText 
              v-model="majorFilters['global'].value" 
              placeholder="Search major name or code..." 
              class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
            />
          </div>
        </div>

        <DataTable 
          :value="majors" 
          v-model:filters="majorFilters"
          dataKey="id" 
          paginator 
          :rows="5" 
          responsiveLayout="scroll"
          class="p-datatable-sm"
        >
          <Column field="code" header="MAJOR CODE" sortable class="!py-3.5">
            <template #body="{ data }">
              <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                {{ data.code }}
              </span>
            </template>
          </Column>

          <Column field="name_en" header="MAJOR NAME" sortable class="!py-3.5">
            <template #body="{ data }">
              <div>
                <div class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ data.name_kh }}</div>
              </div>
            </template>
          </Column>

          <Column field="faculty_name" header="FACULTY" sortable class="!py-3.5">
            <template #body="{ data }">
              <span class="text-xs text-slate-700 font-medium">
                {{ data.faculty_name }}
              </span>
            </template>
          </Column>

          <Column field="degree_title" header="DEGREE" sortable class="!py-3.5">
            <template #body="{ data }">
              <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                {{ data.degree_title }}
              </span>
            </template>
          </Column>

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

          <Column header="ACTIONS" class="!text-right !py-3.5">
            <template #body="{ data }">
              <div class="flex items-center justify-end gap-2">
                <Button icon="pi pi-pencil" class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-blue-600 hover:!bg-slate-100 !border-0" @click="editMajor(data)" />
                <Button icon="pi pi-trash" class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0" @click="confirmDeleteMajor(data)" />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

    </div>

    <!-- ======= DEGREE DIALOG ======= -->
    <Dialog v-model:visible="degreeDialog" :header="isEditDegree ? 'Edit Degree' : 'Create New Degree'" :modal="true" class="w-full max-w-lg">
      <div class="space-y-4 pt-2">
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty *</label>
          <Dropdown v-model="degreeForm.faculty_id" :options="faculties" optionLabel="name_en" optionValue="id" placeholder="Select Faculty" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Degree Code *</label>
          <InputText v-model="degreeForm.code" placeholder="e.g. BACHELOR" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Title (English) *</label>
          <InputText v-model="degreeForm.title_en" placeholder="e.g. Bachelor Degree" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Title (Khmer)</label>
          <InputText v-model="degreeForm.title_kh" placeholder="ឧ. បរិញ្ញាបត្រ" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Duration (Years) *</label>
          <InputText v-model="degreeForm.duration_years" type="number" placeholder="4" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
          <Dropdown v-model="degreeForm.status" :options="['Active', 'Inactive']" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="degreeDialog = false" />
          <Button label="Save Degree" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveDegree" />
        </div>
      </template>
    </Dialog>

    <!-- ======= MAJOR DIALOG ======= -->
    <Dialog v-model:visible="majorDialog" :header="isEditMajor ? 'Edit Major' : 'Create New Major'" :modal="true" class="w-full max-w-lg">
      <div class="space-y-4 pt-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty *</label>
            <Dropdown v-model="majorForm.faculty_id" :options="faculties" optionLabel="name_en" optionValue="id" placeholder="Select Faculty" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" @change="majorForm.degree_id = null" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Degree Level *</label>
            <Dropdown v-model="majorForm.degree_id" :options="degreesForSelectedFaculty" optionLabel="title_en" optionValue="id" placeholder="Select Degree" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" :disabled="!majorForm.faculty_id" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major Code *</label>
          <InputText v-model="majorForm.code" placeholder="e.g. CS" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major Name (English) *</label>
          <InputText v-model="majorForm.name_en" placeholder="e.g. Computer Science" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major Name (Khmer)</label>
          <InputText v-model="majorForm.name_kh" placeholder="ឧ. វិទ្យាសាស្ត្រកុំព្យូទ័រ" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
          <Dropdown v-model="majorForm.status" :options="['Active', 'Inactive']" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="majorDialog = false" />
          <Button label="Save Major" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveMajor" />
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

const activeTab = ref(0) // 0: Degrees, 1: Majors

const faculties = ref([])
const degrees = ref([])
const majors = ref([])

const fetchFaculties = async () => {
  const { data } = await api.get('/faculties', { params: { per_page: 100 } })
  faculties.value = data.data
}

const fetchDegrees = async () => {
  const { data } = await api.get('/degrees', { params: { per_page: 100 } })
  degrees.value = data.data
}

const fetchMajors = async () => {
  const { data } = await api.get('/majors', { params: { per_page: 100 } })
  majors.value = data.data
}

onMounted(() => {
  fetchFaculties()
  fetchDegrees()
  fetchMajors()
})

// Degrees available for the faculty currently selected in the major dialog
const degreesForSelectedFaculty = computed(() =>
  degrees.value.filter(d => d.faculty_id === majorForm.value.faculty_id)
)

// Search Filters (ប្រើ string 'contains' មិនបាច់ import FilterMatchMode)
const degreeFilters = ref({ global: { value: null, matchMode: 'contains' } })
const majorFilters = ref({ global: { value: null, matchMode: 'contains' } })

// Dialog States & Forms
const degreeDialog = ref(false)
const isEditDegree = ref(false)
const degreeForm = ref({ id: null, faculty_id: null, code: '', title_en: '', title_kh: '', duration_years: 4, status: 'Active' })

const majorDialog = ref(false)
const isEditMajor = ref(false)
const majorForm = ref({ id: null, faculty_id: null, degree_id: null, code: '', name_en: '', name_kh: '', status: 'Active' })

const activeMajorsCount = computed(() => majors.value.filter(m => m.status === 'Active').length)

// Actions
const openNewDialog = () => {
  if (activeTab.value === 0) {
    degreeForm.value = { id: null, faculty_id: null, code: '', title_en: '', title_kh: '', duration_years: 4, status: 'Active' }
    isEditDegree.value = false
    degreeDialog.value = true
  } else {
    majorForm.value = { id: null, faculty_id: null, degree_id: null, code: '', name_en: '', name_kh: '', status: 'Active' }
    isEditMajor.value = false
    majorDialog.value = true
  }
}

// Degree Actions
const editDegree = (data) => {
  degreeForm.value = { ...data }
  isEditDegree.value = true
  degreeDialog.value = true
}

const saveDegree = async () => {
  if (!degreeForm.value.faculty_id || !degreeForm.value.code || !degreeForm.value.title_en) {
    alert('Faculty, degree code, and title (English) are required.')
    return
  }

  const payload = {
    faculty_id: degreeForm.value.faculty_id,
    code: degreeForm.value.code,
    title_en: degreeForm.value.title_en,
    title_kh: degreeForm.value.title_kh,
    duration_years: degreeForm.value.duration_years,
    status: degreeForm.value.status,
  }

  try {
    if (isEditDegree.value) {
      await api.put(`/degrees/${degreeForm.value.id}`, payload)
    } else {
      await api.post('/degrees', payload)
    }

    degreeDialog.value = false
    await fetchDegrees()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteDegree = async (data) => {
  if (confirm(`Delete degree ${data.title_en}?`)) {
    try {
      await api.delete(`/degrees/${data.id}`)
      await fetchDegrees()
    } catch (error) {
      alert(extractError(error))
    }
  }
}

// Major Actions
const editMajor = (data) => {
  majorForm.value = { ...data }
  isEditMajor.value = true
  majorDialog.value = true
}

const saveMajor = async () => {
  if (!majorForm.value.faculty_id || !majorForm.value.degree_id || !majorForm.value.code || !majorForm.value.name_en) {
    alert('Faculty, degree, major code, and name (English) are required.')
    return
  }

  const payload = {
    degree_id: majorForm.value.degree_id,
    code: majorForm.value.code,
    name_en: majorForm.value.name_en,
    name_kh: majorForm.value.name_kh,
    status: majorForm.value.status,
  }

  try {
    if (isEditMajor.value) {
      await api.put(`/majors/${majorForm.value.id}`, payload)
    } else {
      await api.post('/majors', payload)
    }

    majorDialog.value = false
    await fetchMajors()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteMajor = async (data) => {
  if (confirm(`Delete major ${data.name_en}?`)) {
    try {
      await api.delete(`/majors/${data.id}`)
      await fetchMajors()
    } catch (error) {
      alert(extractError(error))
    }
  }
}
</script>