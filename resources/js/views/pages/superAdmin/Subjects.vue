<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-book text-blue-600 text-2xl"></i>
          Subjects Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          គ្រប់គ្រង និងកំណត់បញ្ជីមុខវិជ្ជាសិក្សា ព្រមទាំងចំនួនក្រេឌីត (Credits) តាមមុខវិជ្ជានីមួយៗ
        </p>
      </div>

      <Button 
        label="Add New Subject" 
        icon="pi pi-plus" 
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Subjects</span>
          <span class="text-2xl font-bold text-slate-800">{{ subjects.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-book"></i>
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
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Subjects</span>
          <span class="text-2xl font-bold text-emerald-600">{{ activeSubjectsCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
          <i class="pi pi-check-circle"></i>
        </div>
      </div>
    </div>

    <!-- ======= DATA TABLE CARD ======= -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
      
      <!-- Table Header Bar / Search & Filter -->
      <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <InputText 
            v-model="filters['global'].value" 
            placeholder="Search subject code or name..." 
            class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
          />
        </div>
        <div class="text-xs text-slate-400">
          Showing <b>{{ subjects.length }}</b> entries
        </div>
      </div>

      <!-- PrimeVue DataTable -->
      <DataTable 
        :value="subjects" 
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
            No subjects found.
          </div>
        </template>

        <!-- Subject Code -->
        <Column field="code" header="CODE" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
              {{ data.code }}
            </span>
          </template>
        </Column>

        <!-- Subject Name (EN & KH) -->
        <Column field="name_en" header="SUBJECT NAME" sortable class="!py-3.5">
          <template #body="{ data }">
            <div>
              <div class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</div>
              <div class="text-xs text-slate-400 mt-0.5">{{ data.name_kh }}</div>
            </div>
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
            <div class="flex items-center justify-end gap-2">
              <Button 
                icon="pi pi-pencil" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-blue-600 hover:!bg-slate-100 !border-0"
                @click="editSubject(data)"
              />
              <Button 
                icon="pi pi-trash" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
                @click="confirmDeleteSubject(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog 
      v-model:visible="subjectDialog" 
      :header="isEdit ? 'Edit Subject' : 'Create New Subject'" 
      :modal="true" 
      class="w-full max-w-lg"
    >
      <div class="space-y-4 pt-2">
        <!-- Faculty / Degree / Major Selection -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty *</label>
          <Dropdown
            v-model="subjectForm.faculty_id"
            :options="faculties"
            optionLabel="name_en"
            optionValue="id"
            placeholder="Select Faculty"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            @change="subjectForm.degree_id = null; subjectForm.major_id = null"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Degree</label>
            <Dropdown
              v-model="subjectForm.degree_id"
              :options="degreesForSelectedFaculty"
              optionLabel="title_en"
              optionValue="id"
              placeholder="Any degree"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              :disabled="!subjectForm.faculty_id"
              @change="subjectForm.major_id = null"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major</label>
            <Dropdown
              v-model="subjectForm.major_id"
              :options="majorsForSelectedDegree"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Any major"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              :disabled="!subjectForm.degree_id"
            />
          </div>
        </div>

        <!-- Subject Code & Credits -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject Code *</label>
            <InputText 
              v-model="subjectForm.code" 
              placeholder="e.g. CS101" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Credits *</label>
            <InputText 
              v-model="subjectForm.credits" 
              type="number" 
              placeholder="3" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Subject Name EN -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject Name (English) *</label>
          <InputText 
            v-model="subjectForm.name_en" 
            placeholder="e.g. Database Management Systems" 
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Subject Name KH -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject Name (Khmer)</label>
          <InputText 
            v-model="subjectForm.name_kh" 
            placeholder="ឧ. ប្រព័ន្ធគ្រប់គ្រងមូលដ្ឋានទិន្នន័យ" 
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>

        <!-- Status & Description -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
          <Dropdown 
            v-model="subjectForm.status" 
            :options="['Active', 'Inactive']" 
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Description</label>
          <Textarea 
            v-model="subjectForm.description" 
            rows="3" 
            placeholder="Brief course overview..." 
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="subjectDialog = false" />
          <Button label="Save Subject" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveSubject" />
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

const subjects = ref([])
const faculties = ref([])
const degrees = ref([])
const majors = ref([])

const fetchSubjects = async () => {
  const { data } = await api.get('/subjects', { params: { per_page: 100 } })
  subjects.value = data.data
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
  fetchSubjects()
  fetchLookups()
})

// Search Filter (ប្រើ String 'contains')
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// Dialog States & Form
const subjectDialog = ref(false)
const isEdit = ref(false)
const subjectForm = ref({
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
  degrees.value.filter(d => d.faculty_id === subjectForm.value.faculty_id)
)
const majorsForSelectedDegree = computed(() =>
  majors.value.filter(m => m.degree_id === subjectForm.value.degree_id)
)

// Computed Properties
const activeSubjectsCount = computed(() => subjects.value.filter(s => s.status === 'Active').length)
const totalCredits = computed(() => subjects.value.reduce((sum, s) => sum + Number(s.credits || 0), 0))

// Actions
const openNewDialog = () => {
  subjectForm.value = {
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
  subjectDialog.value = true
}

const editSubject = (data) => {
  subjectForm.value = { ...data }
  isEdit.value = true
  subjectDialog.value = true
}

const saveSubject = async () => {
  if (!subjectForm.value.code || !subjectForm.value.name_en || !subjectForm.value.faculty_id) {
    alert('Faculty, subject code, and name (English) are required.')
    return
  }

  const payload = {
    faculty_id: subjectForm.value.faculty_id,
    degree_id: subjectForm.value.degree_id,
    major_id: subjectForm.value.major_id,
    code: subjectForm.value.code,
    name_en: subjectForm.value.name_en,
    name_kh: subjectForm.value.name_kh,
    credits: subjectForm.value.credits,
    description: subjectForm.value.description,
    status: subjectForm.value.status,
  }

  try {
    if (isEdit.value) {
      await api.put(`/subjects/${subjectForm.value.id}`, payload)
    } else {
      await api.post('/subjects', payload)
    }

    subjectDialog.value = false
    await fetchSubjects()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteSubject = async (data) => {
  if (confirm(`Are you sure you want to delete ${data.name_en}?`)) {
    try {
      await api.delete(`/subjects/${data.id}`)
      await fetchSubjects()
    } catch (error) {
      alert(extractError(error))
    }
  }
}
</script>