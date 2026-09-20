<template>
  <div class="space-y-6">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-[#002060] m-0 flex items-center gap-2">
          <i class="pi pi-sitemap text-[#e4ac40] text-2xl"></i>
          Learning Outcomes
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Manage Course Learning Outcomes (CLOs) and Lesson Learning Outcomes (LLOs) for your subjects.
        </p>
      </div>
    </div>

    <!-- ======= SUBJECT SELECTOR ======= -->
    <div class="flex flex-wrap items-center gap-2.5 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-sm">
      <i class="pi pi-book text-slate-400 text-sm ml-1"></i>
      <Dropdown
        v-model="selectedSubjectId"
        :options="subjects"
        optionLabel="name"
        optionValue="id"
        placeholder="Select a subject"
        class="w-72 !bg-slate-50 !border-slate-200 !rounded-xl text-xs"
        @change="selectedCloId = null"
      >
        <template #option="{ option }">{{ option.code ? `${option.name} (${option.code})` : option.name }}</template>
        <template #value="{ value }">{{ subjectLabel(value) }}</template>
      </Dropdown>
    </div>

    <div v-if="!selectedSubjectId" class="bg-white p-10 rounded-2xl border border-slate-200/80 shadow-sm text-center text-sm text-slate-400">
      Select a subject above to manage its CLOs and LLOs.
    </div>

    <template v-else>
      <!-- ======= CLOs TABLE ======= -->
      <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h3 class="text-sm font-bold text-[#002060] m-0">Course Learning Outcomes (CLOs)</h3>
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <div class="relative w-full sm:w-56">
              <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
              <InputText
                v-model="cloSearch"
                size="small"
                placeholder="Search CLO code or title..."
                class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
              />
            </div>
            <Button
              label="Add CLO"
              icon="pi pi-plus"
              size="small"
              class="!bg-[#002060] hover:!bg-blue-900 !border-0 !rounded-lg !text-xs !font-semibold cursor-pointer whitespace-nowrap"
              @click="openNewClo"
            />
          </div>
        </div>

        <DataTable
          :value="filteredClos"
          dataKey="id"
          selectionMode="single"
          v-model:selection="selectedClo"
          @row-click="selectedCloId = $event.data.id"
          :loading="cloLoading"
          paginator
          :rows="cloRows"
          v-model:first="cloFirst"
          :rowsPerPageOptions="[10, 20, 50]"
          responsiveLayout="scroll"
          class="p-datatable-sm outcomes-table"
        >
          <template #empty>
            <div class="text-center py-10 text-xs text-slate-400">No CLOs found for this subject.</div>
          </template>

          <template #paginatorstart>
            <span class="text-xs text-slate-500">
              Showing <span class="font-semibold text-slate-700">{{ filteredClos.length ? cloFirst + 1 : 0 }}</span>
              to <span class="font-semibold text-slate-700">{{ Math.min(cloFirst + cloRows, filteredClos.length) }}</span>
              of <span class="font-semibold text-slate-700">{{ filteredClos.length }}</span>
            </span>
          </template>

          <Column field="code" header="CODE" sortable style="padding-left: 1.25rem">
            <template #body="{ data }"><span class="font-mono font-bold text-indigo-600 text-sm">{{ data.code }}</span></template>
          </Column>
          <Column field="title" header="TITLE" sortable>
            <template #body="{ data }"><span class="font-semibold text-slate-800 text-sm">{{ data.title }}</span></template>
          </Column>
          <Column field="plo_title" header="PLO" sortable>
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.plo_title || '—' }}</span></template>
          </Column>
          <Column field="bloom_level" header="BLOOM LEVEL" sortable>
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.bloom_level }}</span></template>
          </Column>
          <Column field="creator_name" header="CREATED BY" sortable>
            <template #body="{ data }"><span class="text-slate-500 text-xs">{{ data.creator_name || '—' }}</span></template>
          </Column>
          <Column field="status" header="STATUS" sortable>
            <template #body="{ data }">
              <span
                class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
                :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
              >{{ data.status }}</span>
            </template>
          </Column>
          <Column header="ACTIONS" class="!text-right" style="padding-right: 1.25rem">
            <template #body="{ data }">
              <div class="flex items-center justify-end gap-1.5">
                <Button icon="pi pi-pencil" class="!p-2 !w-8 !h-8 !rounded-xl !bg-slate-100 !text-[#e4ac14] !border-slate-100 shadow-xs cursor-pointer" title="Edit CLO" @click.stop="editClo(data)">
                  <i class="fa-solid fa-pen-to-square"></i>
                </Button>
                <Button icon="pi pi-trash" class="!p-2 !w-8 !h-8 !rounded-xl !bg-slate-100 !text-[#d71818] !border-slate-100 shadow-xs cursor-pointer" title="Delete CLO" @click.stop="confirmDeleteClo(data)">
                  <i class="fa-solid fa-trash-can"></i>
                </Button>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- ======= LLOs TABLE ======= -->
      <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h3 class="text-sm font-bold text-[#002060] m-0">
            Lesson Learning Outcomes (LLOs)
            <span v-if="selectedClo" class="text-slate-400 font-normal">— {{ selectedClo.title }}</span>
          </h3>
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <div class="relative w-full sm:w-56">
              <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
              <InputText
                v-model="lloSearch"
                size="small"
                placeholder="Search LLO code or title..."
                class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
              />
            </div>
            <Button
              label="Add LLO"
              icon="pi pi-plus"
              size="small"
              :disabled="!selectedCloId"
              class="!bg-[#002060] hover:!bg-blue-900 !border-0 !rounded-lg !text-xs !font-semibold cursor-pointer whitespace-nowrap"
              @click="openNewLlo"
            />
          </div>
        </div>

        <div v-if="!selectedCloId" class="text-center py-10 text-xs text-slate-400">Select a CLO above to manage its LLOs.</div>

        <DataTable
          v-else
          :value="filteredLlos"
          dataKey="id"
          :loading="lloLoading"
          paginator
          :rows="lloRows"
          v-model:first="lloFirst"
          :rowsPerPageOptions="[10, 20, 50]"
          responsiveLayout="scroll"
          class="p-datatable-sm outcomes-table"
        >
          <template #empty>
            <div class="text-center py-10 text-xs text-slate-400">No LLOs found for this CLO.</div>
          </template>

          <template #paginatorstart>
            <span class="text-xs text-slate-500">
              Showing <span class="font-semibold text-slate-700">{{ filteredLlos.length ? lloFirst + 1 : 0 }}</span>
              to <span class="font-semibold text-slate-700">{{ Math.min(lloFirst + lloRows, filteredLlos.length) }}</span>
              of <span class="font-semibold text-slate-700">{{ filteredLlos.length }}</span>
            </span>
          </template>

          <Column field="code" header="CODE" sortable style="padding-left: 1.25rem">
            <template #body="{ data }"><span class="font-mono font-bold text-indigo-600 text-sm">{{ data.code }}</span></template>
          </Column>
          <Column field="title" header="TITLE" sortable>
            <template #body="{ data }"><span class="font-semibold text-slate-800 text-sm">{{ data.title }}</span></template>
          </Column>
          <Column field="bloom_level" header="BLOOM LEVEL" sortable>
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.bloom_level }}</span></template>
          </Column>
          <Column field="lesson_no" header="LESSON #" sortable>
            <template #body="{ data }"><span class="text-slate-600 text-sm">{{ data.lesson_no ?? '—' }}</span></template>
          </Column>
          <Column field="creator_name" header="CREATED BY" sortable>
            <template #body="{ data }"><span class="text-slate-500 text-xs">{{ data.creator_name || '—' }}</span></template>
          </Column>
          <Column field="status" header="STATUS" sortable>
            <template #body="{ data }">
              <span
                class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
                :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
              >{{ data.status }}</span>
            </template>
          </Column>
          <Column header="ACTIONS" class="!text-right" style="padding-right: 1.25rem">
            <template #body="{ data }">
              <div class="flex items-center justify-end gap-1.5">
                <Button icon="pi pi-pencil" class="!p-2 !w-8 !h-8 !rounded-xl !bg-slate-100 !text-[#e4ac14] !border-slate-100 shadow-xs cursor-pointer" title="Edit LLO" @click="editLlo(data)">
                  <i class="fa-solid fa-pen-to-square"></i>
                </Button>
                <Button icon="pi pi-trash" class="!p-2 !w-8 !h-8 !rounded-xl !bg-slate-100 !text-[#d71818] !border-slate-100 shadow-xs cursor-pointer" title="Delete LLO" @click="confirmDeleteLlo(data)">
                  <i class="fa-solid fa-trash-can"></i>
                </Button>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </template>

    <!-- ======= CLO DIALOG ======= -->
    <Dialog v-model:visible="cloDialog" :header="isCloEdit ? 'Edit CLO' : 'Create New CLO'" :modal="true" class="w-full max-w-2xl">
      <div class="space-y-4 pt-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Code *</label>
            <InputText v-model="cloForm.code" placeholder="e.g. CLO1" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">PLO *</label>
            <Dropdown v-model="cloForm.plo_id" :options="plos" optionLabel="title" optionValue="id" placeholder="Select PLO" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Title *</label>
          <InputText v-model="cloForm.title" placeholder="e.g. Design and implement a relational database schema" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bloom Level *</label>
            <Dropdown v-model="cloForm.bloom_level" :options="bloomLevels" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown v-model="cloForm.status" :options="['Active', 'Inactive']" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Description</label>
          <Textarea v-model="cloForm.description" rows="3" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer" @click="cloDialog = false" />
          <Button label="Save CLO" icon="pi pi-check" class="!bg-[#002060] hover:!bg-blue-900 !text-white !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer" @click="saveClo" />
        </div>
      </template>
    </Dialog>

    <!-- ======= LLO DIALOG ======= -->
    <Dialog v-model:visible="lloDialog" :header="isLloEdit ? 'Edit LLO' : 'Create New LLO'" :modal="true" class="w-full max-w-2xl">
      <div class="space-y-4 pt-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Code *</label>
            <InputText v-model="lloForm.code" placeholder="e.g. LLO1.1" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Lesson #</label>
            <InputText v-model="lloForm.lesson_no" type="number" placeholder="e.g. 3" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Title *</label>
          <InputText v-model="lloForm.title" placeholder="e.g. Write a SELECT query with a JOIN" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bloom Level *</label>
            <Dropdown v-model="lloForm.bloom_level" :options="bloomLevels" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown v-model="lloForm.status" :options="['Active', 'Inactive']" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Description</label>
          <Textarea v-model="lloForm.description" rows="3" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer" @click="lloDialog = false" />
          <Button label="Save LLO" icon="pi pi-check" class="!bg-[#002060] hover:!bg-blue-900 !text-white !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer" @click="saveLlo" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<style scoped>
/* Header two sizes smaller, body two sizes larger, than the table's base text-xs — same convention as Subjects.vue / LookupCrudTab.vue. */
.outcomes-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.outcomes-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}
</style>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api, { toastFromError } from '../../../api'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'

const toast = useToast()
const confirm = useConfirm()

const bloomLevels = ['Remember', 'Understand', 'Apply', 'Analyze', 'Evaluate', 'Create']

const subjects = ref([])
const plos = ref([])
const clos = ref([])
const llos = ref([])
const cloLoading = ref(false)
const lloLoading = ref(false)

const selectedSubjectId = ref(null)
const selectedCloId = ref(null)
const selectedClo = computed(() => clos.value.find((c) => c.id === selectedCloId.value) || null)

const cloSearch = ref('')
const cloFirst = ref(0)
const cloRows = ref(10)
const lloSearch = ref('')
const lloFirst = ref(0)
const lloRows = ref(10)

const filteredClos = computed(() => {
  const q = cloSearch.value.trim().toLowerCase()
  if (!q) return clos.value
  return clos.value.filter((c) => (c.code || '').toLowerCase().includes(q) || (c.title || '').toLowerCase().includes(q))
})

const filteredLlos = computed(() => {
  const q = lloSearch.value.trim().toLowerCase()
  if (!q) return llos.value
  return llos.value.filter((l) => (l.code || '').toLowerCase().includes(q) || (l.title || '').toLowerCase().includes(q))
})

// Jump back to page 1 whenever the search box changes, so the paginator
// never gets stranded past the end of a smaller filtered list.
watch(cloSearch, () => { cloFirst.value = 0 })
watch(lloSearch, () => { lloFirst.value = 0 })

const subjectLabel = (id) => {
  const subject = subjects.value.find((s) => s.id === id)
  return subject ? (subject.code ? `${subject.name} (${subject.code})` : subject.name) : ''
}

const fetchLookups = async () => {
  const [subjectsRes, plosRes] = await Promise.all([
    api.get('/teacher/subjects'),
    api.get('/teacher/plos'),
  ])
  subjects.value = subjectsRes.data.data
  plos.value = plosRes.data.data

  // Default to the first subject so the page isn't empty on arrival —
  // the subject watcher below picks up from here and loads its CLOs.
  if (!selectedSubjectId.value && subjects.value.length) {
    selectedSubjectId.value = subjects.value[0].id
  }
}

const fetchClos = async () => {
  if (!selectedSubjectId.value) return
  cloLoading.value = true
  try {
    const { data } = await api.get('/teacher/clos', { params: { subject_id: selectedSubjectId.value, include_inactive: 1 } })
    clos.value = data.data
  } catch (error) {
    toast.add({ summary: 'Failed to load CLOs', ...toastFromError(error) })
  } finally {
    cloLoading.value = false
  }
}

const fetchLlos = async () => {
  if (!selectedCloId.value) {
    llos.value = []
    return
  }
  lloLoading.value = true
  try {
    const { data } = await api.get('/teacher/llos', { params: { clo_id: selectedCloId.value, include_inactive: 1 } })
    llos.value = data.data
  } catch (error) {
    toast.add({ summary: 'Failed to load LLOs', ...toastFromError(error) })
  } finally {
    lloLoading.value = false
  }
}

watch(selectedSubjectId, () => {
  selectedCloId.value = null
  clos.value = []
  cloSearch.value = ''
  cloFirst.value = 0
  fetchClos()
})
watch(selectedCloId, () => {
  lloSearch.value = ''
  lloFirst.value = 0
  fetchLlos()
})

onMounted(fetchLookups)

// ======= CLO dialog =======
const cloDialog = ref(false)
const isCloEdit = ref(false)
const cloForm = ref({ id: null, code: '', plo_id: null, title: '', description: '', bloom_level: 'Understand', status: 'Active' })

const openNewClo = () => {
  cloForm.value = { id: null, code: '', plo_id: null, title: '', description: '', bloom_level: 'Understand', status: 'Active' }
  isCloEdit.value = false
  cloDialog.value = true
}

const editClo = (data) => {
  cloForm.value = { ...data }
  isCloEdit.value = true
  cloDialog.value = true
}

const saveClo = async () => {
  if (!cloForm.value.code || !cloForm.value.title || !cloForm.value.plo_id) {
    toast.add({ severity: 'warn', summary: 'Missing information', detail: 'Code, title, and PLO are required.', life: 4000 })
    return
  }

  const payload = {
    plo_id: cloForm.value.plo_id,
    subject_id: selectedSubjectId.value,
    code: cloForm.value.code,
    title: cloForm.value.title,
    description: cloForm.value.description,
    bloom_level: cloForm.value.bloom_level,
    status: cloForm.value.status,
  }

  try {
    if (isCloEdit.value) {
      await api.put(`/teacher/clos/${cloForm.value.id}`, payload)
      toast.add({ severity: 'success', summary: 'CLO updated', life: 3000 })
    } else {
      await api.post('/teacher/clos', payload)
      toast.add({ severity: 'success', summary: 'CLO created', life: 3000 })
    }
    cloDialog.value = false
    await fetchClos()
  } catch (error) {
    toast.add({ summary: isCloEdit.value ? 'Failed to update CLO' : 'Failed to create CLO', ...toastFromError(error) })
  }
}

const confirmDeleteClo = (data) => {
  confirm.require({
    header: 'Delete CLO',
    message: `Are you sure you want to delete "${data.title}"?`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Delete',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await api.delete(`/teacher/clos/${data.id}`)
        toast.add({ severity: 'success', summary: 'CLO deleted', life: 3000 })
        if (selectedCloId.value === data.id) selectedCloId.value = null
        await fetchClos()
      } catch (error) {
        toast.add({ summary: 'Failed to delete CLO', ...toastFromError(error) })
      }
    },
  })
}

// ======= LLO dialog =======
const lloDialog = ref(false)
const isLloEdit = ref(false)
const lloForm = ref({ id: null, code: '', title: '', description: '', bloom_level: 'Understand', lesson_no: null, status: 'Active' })

const openNewLlo = () => {
  lloForm.value = { id: null, code: '', title: '', description: '', bloom_level: 'Understand', lesson_no: null, status: 'Active' }
  isLloEdit.value = false
  lloDialog.value = true
}

const editLlo = (data) => {
  lloForm.value = { ...data }
  isLloEdit.value = true
  lloDialog.value = true
}

const saveLlo = async () => {
  if (!lloForm.value.code || !lloForm.value.title) {
    toast.add({ severity: 'warn', summary: 'Missing information', detail: 'Code and title are required.', life: 4000 })
    return
  }

  const payload = {
    clo_id: selectedCloId.value,
    code: lloForm.value.code,
    title: lloForm.value.title,
    description: lloForm.value.description,
    bloom_level: lloForm.value.bloom_level,
    lesson_no: lloForm.value.lesson_no || null,
    status: lloForm.value.status,
  }

  try {
    if (isLloEdit.value) {
      await api.put(`/teacher/llos/${lloForm.value.id}`, payload)
      toast.add({ severity: 'success', summary: 'LLO updated', life: 3000 })
    } else {
      await api.post('/teacher/llos', payload)
      toast.add({ severity: 'success', summary: 'LLO created', life: 3000 })
    }
    lloDialog.value = false
    await fetchLlos()
  } catch (error) {
    toast.add({ summary: isLloEdit.value ? 'Failed to update LLO' : 'Failed to create LLO', ...toastFromError(error) })
  }
}

const confirmDeleteLlo = (data) => {
  confirm.require({
    header: 'Delete LLO',
    message: `Are you sure you want to delete "${data.title}"?`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Delete',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await api.delete(`/teacher/llos/${data.id}`)
        toast.add({ severity: 'success', summary: 'LLO deleted', life: 3000 })
        await fetchLlos()
      } catch (error) {
        toast.add({ summary: 'Failed to delete LLO', ...toastFromError(error) })
      }
    },
  })
}
</script>
