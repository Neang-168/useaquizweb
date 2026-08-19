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
          Manage  all the subject here.
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

    <!-- ======= TABLE HEADER BAR / SEARCH & FILTER ======= -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
      <div class="relative w-full sm:w-80">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <InputText
          v-model="filters['global'].value"
          placeholder="Search subject code or name..."
          class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
        />
      </div>
      <div class="text-xs text-slate-400">
        Showing <b>{{ filteredSubjects.length }}</b> entries
      </div>
    </div>

    <!-- ======= FILTER BAR ======= -->
    <div class="flex flex-wrap items-center gap-2.5 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-sm">
      <i class="pi pi-filter text-slate-400 text-sm ml-1"></i>
      <Dropdown v-model="facultyFilter" :options="faculties" optionLabel="name_en" optionValue="id"
        placeholder="All Faculties" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Dropdown v-model="departmentFilter" :options="departments" optionLabel="name_en" optionValue="id"
        placeholder="All Departments" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Dropdown v-model="majorFilter" :options="majors" optionLabel="name_en" optionValue="id"
        placeholder="All Majors" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Dropdown v-model="academicYearFilter" :options="academicYears" optionLabel="name_en" optionValue="id"
        placeholder="All Academic Years" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Dropdown v-model="statusFilter" :options="['Active', 'Inactive']"
        placeholder="All Statuses" showClear class="w-40 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Button v-if="hasActiveFilters" label="Clear Filters" icon="pi pi-filter-slash"
        class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !py-2 !px-3 !text-xs !font-semibold cursor-pointer"
        @click="clearFilters" />
    </div>

    <!-- ======= DATA TABLE (floating card rows) ======= -->
    <DataTable
        :value="filteredSubjects"
        v-model:filters="filters"
        dataKey="id" 
        paginator 
        :rows="5" 
        :rowsPerPageOptions="[5, 10, 20]"
        responsiveLayout="scroll"
        class="p-datatable-sm custom-app-table"
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

        <!-- Subject Name (English) -->
        <Column field="name_en" header="SUBJECT NAME" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</span>
          </template>
        </Column>

        <!-- Subject Name (Khmer) -->
        <Column field="name_kh" header="SUBJECT NAME (KH)" class="!py-3.5">
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

        <!-- Department -->
        <Column field="department_name" header="DEPARTMENT" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs text-slate-600 font-medium">{{ data.department_name || '—' }}</span>
          </template>
        </Column>

        <!-- Major -->
        <Column field="major_name" header="MAJOR" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs text-slate-600 font-medium">{{ data.major_name || '—' }}</span>
          </template>
        </Column>

        <!-- Academic Year -->
        <Column field="academic_year_name" header="ACADEMIC YEAR" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs text-slate-600 font-medium">{{ data.academic_year_name || '—' }}</span>
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
              <!-- <Button
                icon="pi pi-sitemap"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-indigo-50 !text-indigo-600 hover:!bg-indigo-100 hover:!text-indigo-700 !border !border-indigo-100"
                title="Classes Teaching This Subject"
                @click="openClassesDialog(data)"
              /> -->
              <Button
                icon="pi pi-pencil"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-blue-50 !text-blue-600 hover:!bg-blue-100 hover:!text-blue-700 !border !border-blue-100"
                title="Edit Subject"
                @click="editSubject(data)"
              />
              <Button
                icon="pi pi-trash"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-rose-50 !text-rose-600 hover:!bg-rose-100 hover:!text-rose-700 !border !border-rose-100"
                title="Delete Subject"
                @click="confirmDeleteSubject(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog
      v-model:visible="subjectDialog"
      :header="isEdit ? 'Edit Subject' : 'Create New Subject'"
      :modal="true"
      class="w-full max-w-3xl"
    >
      <div class="space-y-4 pt-2">
        <!-- Subject Code / Name / Name (Khmer) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject Code *</label>
            <InputText
              v-model="subjectForm.code"
              placeholder="e.g. CS101"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject Name (English) *</label>
            <InputText
              v-model="subjectForm.name_en"
              placeholder="e.g. Database Management Systems"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject Name (Khmer)</label>
            <InputText
              v-model="subjectForm.name_kh"
              placeholder="ឧ. ប្រព័ន្ធគ្រប់គ្រងមូលដ្ឋានទិន្នន័យ"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Faculty / Department / Major -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
            <Dropdown
              v-model="subjectForm.department_id"
              :options="departments"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Select Department"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major</label>
            <Dropdown
              v-model="subjectForm.major_id"
              :options="majorsForSelectedFaculty"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Any major"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              :disabled="!subjectForm.faculty_id"
              @change="onMajorChange"
            />
          </div>
        </div>

        <!-- Academic Year / Credits / Status -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Academic Year</label>
            <Dropdown
              v-model="subjectForm.academic_year_id"
              :options="academicYears"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Select Academic Year"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
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
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown
              v-model="subjectForm.status"
              :options="['Active', 'Inactive']"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
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

    <!-- ======= CLASSES TEACHING THIS SUBJECT DIALOG (Class + Teacher per Subject) ======= -->
    <!-- <Dialog
      v-model:visible="classesDialog"
      :header="assignmentSubject ? `Classes - ${assignmentSubject.name_en}` : 'Classes'"
      :modal="true"
      class="w-full max-w-2xl"
    >
      <div class="space-y-4 pt-2">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end bg-slate-50 p-3 rounded-xl border border-slate-200">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class</label>
            <Dropdown
              v-model="classAssignForm.class_id"
              :options="classOptionsForSubject"
              optionLabel="name"
              optionValue="id"
              placeholder="Select Class"
              class="w-full !bg-white !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Teacher</label>
            <Dropdown
              v-model="classAssignForm.teacher_profile_id"
              :options="teacherOptionsForSubject"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Select Teacher"
              class="w-full !bg-white !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <Button
            label="Add Class"
            icon="pi pi-plus"
            class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !text-xs !font-semibold"
            @click="addClassToSubject"
          />
        </div>
        <p class="text-[11px] text-slate-400 -mt-2">
          Only classes and teachers within this subject's faculty are shown.
        </p>

        <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 max-h-72 overflow-y-auto">
          <div
            v-for="assignment in subjectClassAssignments"
            :key="assignment.id"
            class="flex items-center justify-between px-4 py-2.5"
          >
            <div>
              <span class="text-sm font-semibold text-slate-800">{{ assignment.class_name }}</span>
              <span class="text-xs text-slate-400 ml-2">taught by {{ assignment.teacher_name }}</span>
            </div>
            <Button
              icon="pi pi-trash"
              class="!p-1.5 !w-7 !h-7 !rounded-lg !text-slate-400 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
              @click="removeClassFromSubject(assignment)"
            />
          </div>
          <div v-if="subjectClassAssignments.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">
            No classes teach this subject yet.
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end pt-3">
          <Button label="Close" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="classesDialog = false" />
        </div>
      </template>
    </Dialog> -->

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
const departments = ref([])
const degrees = ref([])
const majors = ref([])
const academicYears = ref([])
const classes = ref([])
const teachers = ref([])

const fetchSubjects = async () => {
  const { data } = await api.get('/subjects', { params: { per_page: 100 } })
  subjects.value = data.data
}

const fetchLookups = async () => {
  const [facultiesRes, departmentsRes, degreesRes, majorsRes, academicYearsRes, classesRes, teachersRes] = await Promise.all([
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/departments', { params: { per_page: 100 } }),
    api.get('/degrees', { params: { per_page: 100 } }),
    api.get('/majors', { params: { per_page: 100 } }),
    api.get('/academic-years', { params: { per_page: 100 } }),
    api.get('/classes', { params: { per_page: 200 } }),
    api.get('/teachers', { params: { per_page: 200 } }),
  ])
  faculties.value = facultiesRes.data.data
  departments.value = departmentsRes.data.data
  degrees.value = degreesRes.data.data
  majors.value = majorsRes.data.data
  academicYears.value = academicYearsRes.data.data
  classes.value = classesRes.data.data
  teachers.value = teachersRes.data.data.map(t => ({ id: t.id, faculty_id: t.faculty_id, name_en: t.name_en }))
}

onMounted(() => {
  fetchSubjects()
  fetchLookups()
})

// Search Filter (ប្រើ String 'contains')
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// ======= Filter Bar (Faculty / Department / Major / Academic Year / Status) =======
const facultyFilter = ref(null)
const departmentFilter = ref(null)
const majorFilter = ref(null)
const academicYearFilter = ref(null)
const statusFilter = ref(null)

const hasActiveFilters = computed(() =>
  !!(facultyFilter.value || departmentFilter.value || majorFilter.value || academicYearFilter.value || statusFilter.value)
)

const clearFilters = () => {
  facultyFilter.value = null
  departmentFilter.value = null
  majorFilter.value = null
  academicYearFilter.value = null
  statusFilter.value = null
}

const filteredSubjects = computed(() => subjects.value.filter((s) => {
  if (facultyFilter.value && s.faculty_id !== facultyFilter.value) return false
  if (departmentFilter.value && s.department_id !== departmentFilter.value) return false
  if (majorFilter.value && s.major_id !== majorFilter.value) return false
  if (academicYearFilter.value && s.academic_year_id !== academicYearFilter.value) return false
  if (statusFilter.value && s.status !== statusFilter.value) return false
  return true
}))

// Dialog States & Form
const subjectDialog = ref(false)
const isEdit = ref(false)
const subjectForm = ref({
  id: null,
  code: '',
  name_en: '',
  name_kh: '',
  faculty_id: null,
  department_id: null,
  degree_id: null,
  major_id: null,
  academic_year_id: null,
  credits: 3,
  description: '',
  status: 'Active'
})

// Major options narrow down by Faculty (via the Faculty -> Degree -> Major chain).
// Degree itself is resolved automatically from the picked Major and never shown in the UI.
const majorsForSelectedFaculty = computed(() => {
  if (!subjectForm.value.faculty_id) return []
  const degreeIds = degrees.value
    .filter(d => d.faculty_id === subjectForm.value.faculty_id)
    .map(d => d.id)
  return majors.value.filter(m => degreeIds.includes(m.degree_id))
})

const onMajorChange = () => {
  const major = majors.value.find(m => m.id === subjectForm.value.major_id)
  subjectForm.value.degree_id = major?.degree_id ?? null
}

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
    department_id: null,
    degree_id: null,
    major_id: null,
    academic_year_id: null,
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
    department_id: subjectForm.value.department_id,
    degree_id: subjectForm.value.degree_id,
    major_id: subjectForm.value.major_id,
    academic_year_id: subjectForm.value.academic_year_id,
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

// ======= Classes Teaching This Subject Dialog (Class + Teacher per Subject) =======
const classesDialog = ref(false)
const assignmentSubject = ref(null)
const subjectClassAssignments = ref([])
const classAssignForm = ref({ class_id: null, teacher_profile_id: null })

// Classes/teachers are scoped to the faculty of whichever subject's dialog
// is open, mirroring the same faculty-scoping used on the Teachers page.
const classOptionsForSubject = computed(() =>
  assignmentSubject.value
    ? classes.value.filter(c => c.faculty_id === assignmentSubject.value.faculty_id)
    : []
)
const teacherOptionsForSubject = computed(() =>
  assignmentSubject.value
    ? teachers.value.filter(t => t.faculty_id === assignmentSubject.value.faculty_id)
    : []
)

const fetchSubjectClassAssignments = async (subjectId) => {
  const { data } = await api.get('/teacher-assignments', { params: { subject_id: subjectId } })
  subjectClassAssignments.value = data.data
}

const openClassesDialog = async (data) => {
  assignmentSubject.value = data
  classAssignForm.value = { class_id: null, teacher_profile_id: null }
  classesDialog.value = true
  try {
    await fetchSubjectClassAssignments(data.id)
  } catch (error) {
    alert(extractError(error))
  }
}

const addClassToSubject = async () => {
  if (!classAssignForm.value.class_id || !classAssignForm.value.teacher_profile_id) {
    alert('Please select both a class and a teacher.')
    return
  }

  try {
    await api.post('/teacher-assignments', {
      teacher_profile_id: classAssignForm.value.teacher_profile_id,
      subject_id: assignmentSubject.value.id,
      class_id: classAssignForm.value.class_id,
    })
    classAssignForm.value = { class_id: null, teacher_profile_id: null }
    await fetchSubjectClassAssignments(assignmentSubject.value.id)
  } catch (error) {
    alert(extractError(error))
  }
}

const removeClassFromSubject = async (assignment) => {
  if (!confirm(`Remove ${assignment.class_name} (${assignment.teacher_name}) from this subject?`)) return

  try {
    await api.delete(`/teacher-assignments/${assignment.id}`)
    await fetchSubjectClassAssignments(assignmentSubject.value.id)
  } catch (error) {
    alert(extractError(error))
  }
}
</script>

<!-- <style scoped>
/* "Floating card row" table, matching the Teachers page: header text
   sitting directly on the page background, and each row as its own
   white rounded card with a soft shadow — spacing does the separating,
   not gridlines. */
.subjects-table :deep(.p-datatable-table) {
  border-collapse: separate;
  border-spacing: 0 0.6rem;
}

.subjects-table :deep(.p-datatable-table-container),
.subjects-table :deep(.p-datatable-header),
.subjects-table :deep(.p-datatable-footer),
.subjects-table :deep(.p-datatable-thead),
.subjects-table :deep(.p-datatable),
.subjects-table :deep(.p-datatable-mask) {
  border: none !important;
  box-shadow: none;
  background: transparent;
}

.subjects-table :deep(.p-datatable-thead > tr > th) {
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

.subjects-table :deep(.p-datatable-thead > tr > th:first-child) {
  border-top-left-radius: 0.6rem;
  border-bottom-left-radius: 0.6rem;
}

.subjects-table :deep(.p-datatable-thead > tr > th:last-child) {
  border-top-right-radius: 0.6rem;
  border-bottom-right-radius: 0.6rem;
}

.subjects-table :deep(.p-datatable-tbody > tr > td) {
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

.subjects-table :deep(.p-datatable-tbody > tr > td:first-child) {
  border-top-left-radius: 0.6rem;
  border-bottom-left-radius: 0.6rem;
}

.subjects-table :deep(.p-datatable-tbody > tr > td:last-child) {
  border-top-right-radius: 0.6rem;
  border-bottom-right-radius: 0.6rem;
  overflow: visible;
}

.subjects-table :deep(.p-datatable-tbody > tr:hover > td) {
  background: #f8fafc;
}

.subjects-table :deep(.p-datatable-tbody > tr) {
  outline: none;
}

.subjects-table :deep(.p-paginator) {
  background: transparent;
  border: none;
  padding-top: 0.75rem;
}
</style> -->