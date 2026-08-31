<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-[#002060] m-0 flex items-center gap-2">
          <i class="pi pi-book text-[#e4ac40] text-2xl"></i>
          Subjects Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Manage all the subject here.
        </p>
      </div>

      <Button 
        label="Add New Subject" 
        icon="pi pi-plus" 
        class="!bg-[#002060] hover:!bg-blue-900 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm cursor-pointer"
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

    <!-- ======= FILTER BAR ======= -->
    <div class="flex flex-wrap items-center gap-2.5 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-sm">
      <i class="pi pi-filter text-slate-400 text-sm ml-1"></i>
      <Dropdown v-model="facultyFilter" :options="faculties" optionLabel="name_en" optionValue="id"
        placeholder="All Faculties" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs"
        @change="departmentFilter = null; majorFilter = null" />
      <Dropdown v-model="departmentFilter" :options="departmentFilterOptions" optionLabel="name_en" optionValue="id"
        placeholder="All Departments" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs"
        @change="majorFilter = null" />
      <Dropdown v-model="majorFilter" :options="majorFilterOptions" optionLabel="name_en" optionValue="id"
        placeholder="All Majors" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Dropdown v-model="academicYearFilter" :options="academicYears" optionLabel="name_en" optionValue="id"
        placeholder="All Academic Years" showClear class="w-44 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Dropdown v-model="statusFilter" :options="['Active', 'Inactive']"
        placeholder="All Statuses" showClear class="w-40 !bg-slate-50 !border-slate-200 !rounded-xl text-xs" />
      <Button v-if="hasActiveFilters" label="Clear Filters" icon="pi pi-filter-slash"
        class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !py-2 !px-3 !text-xs !font-semibold cursor-pointer"
        @click="clearFilters" />
    </div>

    <!-- ======= DATA TABLE ======= -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="text-sm font-bold text-[#002060] m-0">Subjects List</h3>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <div class="relative w-full sm:w-56">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="filters['global'].value"
              size="small"
              placeholder="Search subject code or name..."
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
          </div>
          <!-- ======= TOGGLE MORE / FEWER COLUMNS BUTTON ======= -->
          <Button
            :icon="showAllColumns ? 'pi pi-angle-double-left' : 'pi pi-angle-double-right'"
            :label="showAllColumns ? 'Fewer Columns' : 'More Columns'"
            size="small"
            class="!bg-[#002060] !border-slate-100 !text-white hover:!bg-blue-900 !rounded-lg !text-xs !font-semibold !px-3 !py-1.5 whitespace-nowrap cursor-pointer"
            @click="showAllColumns = !showAllColumns"
          />
        </div>
      </div>

      <DataTable
          :value="filteredSubjects"
          v-model:filters="filters"
          dataKey="id"
          paginator
          :rows="rows"
          v-model:first="first"
          :rowsPerPageOptions="[10, 20, 50]"
          scrollable scrollHeight="calc(100vh - 428px)"
          scrollDirection="both"
          :tableStyle="showAllColumns ? 'min-width: 1500px' : 'min-width: 100%'"
          :loading="loading"
          responsiveLayout="scroll"
          class="p-datatable-sm subjects-table"
        >
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No subjects found.
          </div>
        </template>

        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ filteredSubjects.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredSubjects.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ filteredSubjects.length }}</span>
          </span>
        </template>

        <!-- Subject Code (Always Visible) -->
        <Column field="code" header="CODE" sortable style="padding-left: 1.25rem">
          <template #body="{ data }">
            <span class="font-mono font-bold text-indigo-600 text-sm">{{ data.code }}</span>
          </template>
        </Column>

        <!-- Subject Name English (Always Visible) -->
        <Column field="name_en" header="SUBJECT NAME" sortable>
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</span>
          </template>
        </Column>

        <!-- Subject Name Khmer (Toggleable) -->
        <Column v-if="showAllColumns" field="name_kh" header="SUBJECT NAME (KH)">
          <template #body="{ data }">
            <span v-if="data.name_kh" class="text-slate-600 text-sm font-khmer">{{ data.name_kh }}</span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Faculty (Always Visible) -->
        <Column field="faculty_name" header="FACULTY" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ data.faculty_name }}</span>
          </template>
        </Column>

        <!-- Department (Toggleable) -->
        <Column v-if="showAllColumns" field="department_name" header="DEPARTMENT" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ data.department_name || '—' }}</span>
          </template>
        </Column>

        <!-- Major (Toggleable) -->
        <Column v-if="showAllColumns" field="major_name" header="MAJOR" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ data.major_name || '—' }}</span>
          </template>
        </Column>

        <!-- Academic Year (Toggleable) -->
        <Column v-if="showAllColumns" field="academic_year_name" header="ACADEMIC YEAR" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ data.academic_year_name || '—' }}</span>
          </template>
        </Column>

        <!-- Credits (Always Visible) -->
        <Column field="credits" header="CREDITS" sortable>
          <template #body="{ data }">
            <span class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap bg-indigo-50 text-indigo-600 border-indigo-200">
              {{ data.credits }} Credits
            </span>
          </template>
        </Column>

        <!-- Status (Always Visible) -->
        <Column field="status" header="STATUS" sortable>
          <template #body="{ data }">
            <span
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
            >
              {{ data.status }}
            </span>
          </template>
        </Column>

        <!-- Actions (Always Visible) -->
        <Column header="ACTIONS" class="!text-right" style="padding-right: 1.25rem">
          <template #body="{ data }">
            <div class="flex items-center justify-end gap-1.5">
              <Button
                title="Edit Subject"
                class="!p-2 !w-8 !h-8 !rounded-xl !bg-slate-100 !text-[#e4ac14] !border-slate-100 shadow-xs  cursor-pointer"
                @click="editSubject(data)"
              >
              <i class="fa-solid fa-pen-to-square"></i>
              </Button>
              <Button
                class="!p-2 !w-8 !h-8 !rounded-xl !bg-slate-100 !text-[#d71818] !border-slate-100 shadow-xs cursor-pointer"
                title="Delete Subject"
                @click="confirmDeleteSubject(data)"
              >
              <i class="fa-solid fa-trash-can"></i>
              </Button>
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
      class="w-full max-w-3xl !text-[#002060]"
    >
      <div class="space-y-4 pt-2">
        <!-- Subject Code / Name / Name (Khmer) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject Code *</label>
            <InputText
              v-model="subjectForm.code"
              placeholder="Select a faculty to auto-fill"
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
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm font-khmer"
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
              @change="subjectForm.department_id = null; subjectForm.major_id = null; if (!isEdit) fetchNextCode()"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
            <Dropdown
              v-model="subjectForm.department_id"
              :options="departmentOptions"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Select Department"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              @change="subjectForm.major_id = null"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major</label>
            <Dropdown
              v-model="subjectForm.major_id"
              :options="majorsForSelectedDepartment"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Any major"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              :disabled="!subjectForm.department_id"
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
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer" @click="subjectDialog = false" />
          <Button 
              label="Save Subject" icon="pi pi-check" class="!bg-[#002060] hover:!bg-blue-900 !text-white !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer" @click="saveSubject" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
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

const subjects = ref([])
const faculties = ref([])
const departments = ref([])
const degrees = ref([])
const majors = ref([])
const academicYears = ref([])
const classes = ref([])
const teachers = ref([])
const loading = ref(false)

//Show Button More and less
const showAllColumns = ref(false);

const fetchSubjects = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/subjects', { params: { per_page: 100 } })
    subjects.value = data.data
  } finally {
    loading.value = false
  }
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

// Pagination display state (visual only)
const first = ref(0)
const rows = ref(10)

// ======= Filter Bar (Faculty / Department / Major / Academic Year / Status) =======
const facultyFilter = ref(null)
const departmentFilter = ref(null)
const majorFilter = ref(null)
const academicYearFilter = ref(null)
const statusFilter = ref(null)

const hasActiveFilters = computed(() =>
  !!(facultyFilter.value || departmentFilter.value || majorFilter.value || academicYearFilter.value || statusFilter.value)
)

// Same Faculty -> Department -> Major cascade as the form, scoped to the filter bar's own refs.
const departmentFilterOptions = computed(() =>
  departments.value.filter(d => d.faculty_id === facultyFilter.value)
)
const majorFilterOptions = computed(() =>
  majors.value.filter(m => m.department_id === departmentFilter.value)
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

// Department options are scoped to whichever faculty is currently selected
// in the form, since a department belongs to exactly one faculty.
const departmentOptions = computed(() =>
  departments.value.filter(d => d.faculty_id === subjectForm.value.faculty_id)
)

// Major options narrow down by the selected Department.
const majorsForSelectedDepartment = computed(() => {
  if (!subjectForm.value.department_id) return []
  return majors.value.filter(m => m.department_id === subjectForm.value.department_id)
})

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

const fetchNextCode = async () => {
  if (!subjectForm.value.faculty_id) return
  try {
    const { data } = await api.get('/subjects/next-code', { params: { faculty_id: subjectForm.value.faculty_id } })
    subjectForm.value.code = data.code
  } catch (error) {
    // Leave the field blank so the admin can type one manually.
  }
}

const editSubject = (data) => {
  subjectForm.value = { ...data }
  isEdit.value = true
  subjectDialog.value = true
}

const saveSubject = async () => {
  if (!subjectForm.value.code || !subjectForm.value.name_en || !subjectForm.value.faculty_id) {
    toast.add({ severity: 'warn', summary: 'Missing information', detail: 'Faculty, subject code, and name (English) are required.', life: 4000 })
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
      toast.add({ severity: 'success', summary: 'Subject updated', detail: `${subjectForm.value.name_en} was updated successfully.`, life: 3000 })
    } else {
      await api.post('/subjects', payload)
      toast.add({ severity: 'success', summary: 'Subject created', detail: `${subjectForm.value.name_en} was created successfully.`, life: 3000 })
    }

    subjectDialog.value = false
    await fetchSubjects()
  } catch (error) {
    toast.add({ summary: isEdit.value ? 'Failed to update subject' : 'Failed to create subject', ...toastFromError(error) })
  }
}

const confirmDeleteSubject = (data) => {
  confirm.require({
    header: 'Delete subject',
    message: `Are you sure you want to delete ${data.name_en}?`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Delete',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await api.delete(`/subjects/${data.id}`)
        toast.add({ severity: 'success', summary: 'Subject deleted', detail: `${data.name_en} was deleted.`, life: 3000 })
        await fetchSubjects()
      } catch (error) {
        toast.add({ summary: 'Failed to delete subject', ...toastFromError(error) })
      }
    },
  })
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
    toast.add({ summary: 'Failed to load class assignments', ...toastFromError(error) })
  }
}

const addClassToSubject = async () => {
  if (!classAssignForm.value.class_id || !classAssignForm.value.teacher_profile_id) {
    toast.add({ severity: 'warn', summary: 'Missing information', detail: 'Please select both a class and a teacher.', life: 4000 })
    return
  }

  try {
    await api.post('/teacher-assignments', {
      teacher_profile_id: classAssignForm.value.teacher_profile_id,
      subject_id: assignmentSubject.value.id,
      class_id: classAssignForm.value.class_id,
    })
    toast.add({ severity: 'success', summary: 'Class assigned', detail: 'The class and teacher were assigned to this subject.', life: 3000 })
    classAssignForm.value = { class_id: null, teacher_profile_id: null }
    await fetchSubjectClassAssignments(assignmentSubject.value.id)
  } catch (error) {
    toast.add({ summary: 'Failed to assign class', ...toastFromError(error) })
  }
}

const removeClassFromSubject = (assignment) => {
  confirm.require({
    header: 'Remove class assignment',
    message: `Remove ${assignment.class_name} (${assignment.teacher_name}) from this subject?`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Remove',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await api.delete(`/teacher-assignments/${assignment.id}`)
        toast.add({ severity: 'success', summary: 'Class assignment removed', detail: `${assignment.class_name} (${assignment.teacher_name}) was removed from this subject.`, life: 3000 })
        await fetchSubjectClassAssignments(assignmentSubject.value.id)
      } catch (error) {
        toast.add({ summary: 'Failed to remove class assignment', ...toastFromError(error) })
      }
    },
  })
}
</script>

<style scoped>
/* Header two sizes smaller, body two sizes larger, than the table's base text-xs. */
.subjects-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.subjects-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}
</style>