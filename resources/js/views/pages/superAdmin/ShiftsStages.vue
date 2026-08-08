<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-clock text-blue-600 text-2xl"></i>
          Shifts & Stages Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          គ្រប់គ្រងវេនសិក្សា (Study Shifts) និងកម្រិតឆ្នាំសិក្សា (Academic Stages/Levels)
        </p>
      </div>

      <Button 
        :label="activeTab === 0 ? 'Add New Shift' : 'Add New Stage'" 
        icon="pi pi-plus" 
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Shifts</span>
          <span class="text-2xl font-bold text-slate-800">{{ shifts.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-clock"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Stages</span>
          <span class="text-2xl font-bold text-indigo-600">{{ stages.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
          <i class="pi pi-step-forward"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Status</span>
          <span class="text-2xl font-bold text-emerald-600">{{ activeTotalCount }}</span>
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
          <i class="pi pi-clock text-base"></i>
          <span>Study Shifts</span>
          <span class="ml-1 px-2 py-0.5 text-xs rounded-full" :class="activeTab === 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600'">
            {{ shifts.length }}
          </span>
        </button>

        <button 
          @click="activeTab = 1"
          class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all border-0 cursor-pointer"
          :class="activeTab === 1 ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800 bg-transparent'"
        >
          <i class="pi pi-step-forward text-base"></i>
          <span>Academic Stages / Years</span>
          <span class="ml-1 px-2 py-0.5 text-xs rounded-full" :class="activeTab === 1 ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600'">
            {{ stages.length }}
          </span>
        </button>
      </div>

      <!-- ======= TAB 1: SHIFTS TABLE ======= -->
      <div v-if="activeTab === 0">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
          <div class="relative w-full sm:w-80">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <InputText 
              v-model="shiftFilters['global'].value" 
              placeholder="Search shift code or name..." 
              class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
            />
          </div>
        </div>

        <DataTable 
          :value="shifts" 
          v-model:filters="shiftFilters"
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

          <Column field="name_en" header="SHIFT NAME" sortable class="!py-3.5">
            <template #body="{ data }">
              <div>
                <div class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ data.name_kh }}</div>
              </div>
            </template>
          </Column>

          <Column header="TIME SCHEDULE" class="!py-3.5">
            <template #body="{ data }">
              <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg">
                <i class="pi pi-clock text-slate-400"></i>
                {{ data.start_time }} - {{ data.end_time }}
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
                <Button icon="pi pi-pencil" class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-blue-600 hover:!bg-slate-100 !border-0" @click="editShift(data)" />
                <Button icon="pi pi-trash" class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0" @click="confirmDeleteShift(data)" />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- ======= TAB 2: STAGES TABLE ======= -->
      <div v-if="activeTab === 1">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
          <div class="relative w-full sm:w-80">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <InputText 
              v-model="stageFilters['global'].value" 
              placeholder="Search stage code or name..." 
              class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
            />
          </div>
        </div>

        <DataTable 
          :value="stages" 
          v-model:filters="stageFilters"
          dataKey="id" 
          paginator 
          :rows="5" 
          responsiveLayout="scroll"
          class="p-datatable-sm"
        >
          <Column field="level" header="LEVEL" sortable class="!py-3.5">
            <template #body="{ data }">
              <span class="w-7 h-7 flex items-center justify-center font-bold text-xs bg-indigo-50 text-indigo-600 rounded-lg border border-indigo-100">
                #{{ data.level }}
              </span>
            </template>
          </Column>

          <Column field="code" header="CODE" sortable class="!py-3.5">
            <template #body="{ data }">
              <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                {{ data.code }}
              </span>
            </template>
          </Column>

          <Column field="name_en" header="STAGE / YEAR LEVEL" sortable class="!py-3.5">
            <template #body="{ data }">
              <div>
                <div class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ data.name_kh }}</div>
              </div>
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
                <Button icon="pi pi-pencil" class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-blue-600 hover:!bg-slate-100 !border-0" @click="editStage(data)" />
                <Button icon="pi pi-trash" class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0" @click="confirmDeleteStage(data)" />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

    </div>

    <!-- ======= SHIFT DIALOG ======= -->
    <Dialog v-model:visible="shiftDialog" :header="isEditShift ? 'Edit Study Shift' : 'Create New Shift'" :modal="true" class="w-full max-w-lg">
      <div class="space-y-4 pt-2">
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shift Code *</label>
          <InputText v-model="shiftForm.code" placeholder="e.g. MORNING" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shift Name (English) *</label>
          <InputText v-model="shiftForm.name_en" placeholder="e.g. Morning Shift" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shift Name (Khmer)</label>
          <InputText v-model="shiftForm.name_kh" placeholder="ឧ. វេនព្រឹក" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Start Time *</label>
            <InputText v-model="shiftForm.start_time" type="time" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">End Time *</label>
            <InputText v-model="shiftForm.end_time" type="time" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
          <Dropdown v-model="shiftForm.status" :options="['Active', 'Inactive']" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="shiftDialog = false" />
          <Button label="Save Shift" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveShift" />
        </div>
      </template>
    </Dialog>

    <!-- ======= STAGE DIALOG ======= -->
    <Dialog v-model:visible="stageDialog" :header="isEditStage ? 'Edit Academic Stage' : 'Create New Stage'" :modal="true" class="w-full max-w-lg">
      <div class="space-y-4 pt-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Stage Code *</label>
            <InputText v-model="stageForm.code" placeholder="e.g. Y1" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Level Order *</label>
            <InputText v-model="stageForm.level" type="number" placeholder="1" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Stage Name (English) *</label>
          <InputText v-model="stageForm.name_en" placeholder="e.g. Year 1 (Foundation)" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Stage Name (Khmer)</label>
          <InputText v-model="stageForm.name_kh" placeholder="ឧ. ឆ្នាំទី ១ (ថ្នាក់ឆ្នាំមូលដ្ឋាន)" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
          <Dropdown v-model="stageForm.status" :options="['Active', 'Inactive']" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="stageDialog = false" />
          <Button label="Save Stage" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveStage" />
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

const activeTab = ref(0) // 0: Shifts, 1: Stages

const shifts = ref([])
const stages = ref([])

const fetchShifts = async () => {
  const { data } = await api.get('/shifts', { params: { per_page: 100 } })
  shifts.value = data.data
}

const fetchStages = async () => {
  const { data } = await api.get('/stages', { params: { per_page: 100 } })
  stages.value = data.data
}

onMounted(() => {
  fetchShifts()
  fetchStages()
})

// Search Filters (ប្រើ String 'contains')
const shiftFilters = ref({ global: { value: null, matchMode: 'contains' } })
const stageFilters = ref({ global: { value: null, matchMode: 'contains' } })

// Dialog States & Forms
const shiftDialog = ref(false)
const isEditShift = ref(false)
const shiftForm = ref({ id: null, code: '', name_en: '', name_kh: '', start_time: '', end_time: '', status: 'Active' })

const stageDialog = ref(false)
const isEditStage = ref(false)
const stageForm = ref({ id: null, code: '', level: 1, name_en: '', name_kh: '', status: 'Active' })

// Computed
const activeTotalCount = computed(() => {
  const activeShifts = shifts.value.filter(s => s.status === 'Active').length
  const activeStages = stages.value.filter(s => s.status === 'Active').length
  return activeShifts + activeStages
})

// Actions
const openNewDialog = () => {
  if (activeTab.value === 0) {
    shiftForm.value = { id: null, code: '', name_en: '', name_kh: '', start_time: '', end_time: '', status: 'Active' }
    isEditShift.value = false
    shiftDialog.value = true
  } else {
    stageForm.value = { id: null, code: '', level: stages.value.length + 1, name_en: '', name_kh: '', status: 'Active' }
    isEditStage.value = false
    stageDialog.value = true
  }
}

// Shift Actions
const editShift = (data) => {
  shiftForm.value = { ...data }
  isEditShift.value = true
  shiftDialog.value = true
}

const saveShift = async () => {
  if (!shiftForm.value.code || !shiftForm.value.name_en) {
    alert('Shift code and name (English) are required.')
    return
  }

  const payload = {
    code: shiftForm.value.code,
    name_en: shiftForm.value.name_en,
    name_kh: shiftForm.value.name_kh,
    start_time: shiftForm.value.start_time,
    end_time: shiftForm.value.end_time,
    status: shiftForm.value.status,
  }

  try {
    if (isEditShift.value) {
      await api.put(`/shifts/${shiftForm.value.id}`, payload)
    } else {
      await api.post('/shifts', payload)
    }

    shiftDialog.value = false
    await fetchShifts()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteShift = async (data) => {
  if (confirm(`Delete shift ${data.name_en}?`)) {
    try {
      await api.delete(`/shifts/${data.id}`)
      await fetchShifts()
    } catch (error) {
      alert(extractError(error))
    }
  }
}

// Stage Actions
const editStage = (data) => {
  stageForm.value = { ...data }
  isEditStage.value = true
  stageDialog.value = true
}

const saveStage = async () => {
  if (!stageForm.value.code || !stageForm.value.name_en) {
    alert('Stage code and name (English) are required.')
    return
  }

  const payload = {
    code: stageForm.value.code,
    name_en: stageForm.value.name_en,
    name_kh: stageForm.value.name_kh,
    level: stageForm.value.level,
    status: stageForm.value.status,
  }

  try {
    if (isEditStage.value) {
      await api.put(`/stages/${stageForm.value.id}`, payload)
    } else {
      await api.post('/stages', payload)
    }

    stageDialog.value = false
    await fetchStages()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteStage = async (data) => {
  if (confirm(`Delete stage ${data.name_en}?`)) {
    try {
      await api.delete(`/stages/${data.id}`)
      await fetchStages()
    } catch (error) {
      alert(extractError(error))
    }
  }
}
</script>