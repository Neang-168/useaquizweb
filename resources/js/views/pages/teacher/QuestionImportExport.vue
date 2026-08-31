<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <Button
        icon="pi pi-arrow-left"
        text
        rounded
        class="!text-slate-500 hover:!text-slate-700 !bg-slate-100 hover:!bg-slate-200"
        @click="goBack"
      />
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0">Import / Export Questions</h1>
        <p class="text-xs text-slate-500 mt-1 mb-0">Bulk-load questions from a file, or download your question bank.</p>
      </div>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden max-w-3xl">
      <!-- Tabs -->
      <div class="flex border-b border-slate-200 bg-slate-50/60">
        <button
          type="button"
          class="flex-1 flex items-center justify-center gap-2 py-3.5 text-sm font-bold transition-colors"
          :class="activeTab === 'export' ? 'text-emerald-700 border-b-2 border-emerald-600 bg-white' : 'text-slate-400 hover:text-slate-600'"
          @click="activeTab = 'export'"
        >
          <i class="pi pi-download"></i> Export
        </button>
        <button
          type="button"
          class="flex-1 flex items-center justify-center gap-2 py-3.5 text-sm font-bold transition-colors"
          :class="activeTab === 'import' ? 'text-indigo-700 border-b-2 border-indigo-600 bg-white' : 'text-slate-400 hover:text-slate-600'"
          @click="activeTab = 'import'"
        >
          <i class="pi pi-upload"></i> Import
        </button>
      </div>

      <!-- ============ EXPORT TAB ============ -->
      <div v-if="activeTab === 'export'" class="p-6 space-y-5 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">Subject</label>
            <Dropdown
              v-model="exportSubjectId"
              :options="subjectFilterOptions"
              option-label="label"
              option-value="value"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">Question type</label>
            <Dropdown
              v-model="exportType"
              :options="typeFilterOptions"
              option-label="label"
              option-value="value"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1.5">File format</label>
          <SelectButton
            v-model="exportFormat"
            :options="formatOptions"
            option-label="label"
            option-value="value"
            :allow-empty="false"
          />
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5">
          <p class="text-slate-500 m-0">
            Export <span class="font-semibold text-slate-700">{{ filterSummary }}</span> in the format above. Great for editing in bulk or keeping a backup.
          </p>
        </div>

        <Button
          label="Download"
          icon="pi pi-download"
          size="small"
          class="w-full !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600 !text-white !rounded-lg !py-2.5"
          :loading="exporting"
          @click="runExport"
        />
      </div>

      <!-- ============ IMPORT TAB ============ -->
      <div v-else class="p-6 space-y-5 text-xs">
        <div>
          <label class="block font-bold text-slate-700 mb-1.5">File format</label>
          <SelectButton
            v-model="importFormat"
            :options="formatOptions"
            option-label="label"
            option-value="value"
            :allow-empty="false"
          />
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-2">
          <p class="text-slate-500 m-0">New here? Download a template, fill it in, then upload it below. Text only — images can be added afterward in the question editor.</p>
          <Button
            label="Download template"
            icon="pi pi-download"
            text
            size="small"
            class="!text-indigo-600 !font-bold !p-0"
            :loading="downloadingTemplate"
            @click="downloadTemplate"
          />
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1.5">Upload file</label>
          <FileUpload
            mode="basic"
            :auto="false"
            choose-label="Choose file"
            accept=".csv,.xlsx,.xls,.gift,.txt"
            custom-upload
            class="w-full"
            @select="onFileSelected"
          />
          <p v-if="selectedFile" class="text-slate-500 mt-1.5 flex items-center gap-1.5">
            <i v-if="previewing" class="pi pi-spin pi-spinner"></i>
            {{ selectedFile.name }}
          </p>
        </div>
      </div>
    </div>

    <!-- ============ FULL-PAGE PREVIEW (shown once the file has been parsed, before anything is saved) ============ -->
    <div v-if="activeTab === 'import' && preview" class="bg-white rounded-2xl border border-slate-200 shadow-sm">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-slate-200">
        <div>
          <p class="font-bold text-sm m-0" :class="preview.skipped.length ? 'text-amber-700' : 'text-emerald-700'">
            {{ preview.imported }} question{{ preview.imported === 1 ? '' : 's' }} ready to import
            <span v-if="preview.skipped.length" class="font-medium text-amber-600">· {{ preview.skipped.length }} will be skipped</span>
          </p>
          <p class="text-xs text-slate-400 m-0 mt-0.5">Review the questions below before confirming.</p>
        </div>
        <Button
          label="Confirm Import"
          icon="pi pi-upload"
          size="small"
          class="!bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg !py-2.5 !px-5 shrink-0"
          :disabled="preview.imported === 0"
          :loading="importing"
          @click="runImport"
        />
      </div>

      <div class="p-6 space-y-5">
        <!-- Ready-to-import questions, full-width grid -->
        <div v-if="preview.questions.length" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3">
          <div v-for="(q, idx) in preview.questions" :key="idx" class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs">
            <div class="flex items-center gap-1.5 mb-1.5">
              <span class="font-bold px-1.5 py-0.5 rounded bg-white text-slate-600 border border-slate-200">{{ formatType(q.type) }}</span>
              <span class="text-slate-400">{{ q.subjectCode }} • {{ q.difficulty }}</span>
            </div>
            <p class="font-semibold text-slate-800 m-0">{{ q.title || '(image question)' }}</p>

            <ul v-if="q.type === 'multiple_choice'" class="list-none p-0 m-0 mt-2 space-y-1">
              <li v-for="(opt, oIdx) in q.options" :key="oIdx" :class="opt.isCorrect ? 'text-emerald-700 font-semibold' : 'text-slate-500'">
                <i :class="opt.isCorrect ? 'pi pi-check-circle' : 'pi pi-circle'" class="text-[10px] mr-1.5"></i>{{ opt.text }}
              </li>
            </ul>
            <p v-else-if="q.type === 'true_false'" class="text-slate-500 mt-2 mb-0">Correct answer: <span class="font-semibold text-emerald-700">{{ q.tfCorrect }}</span></p>
            <ul v-else-if="q.type === 'matching'" class="list-none p-0 m-0 mt-2 space-y-1 text-slate-500">
              <li v-for="(pair, pIdx) in q.matchingPairs" :key="pIdx">{{ pair.leftText }} ↔ {{ pair.rightText }}</li>
            </ul>
          </div>
        </div>

        <!-- Skipped rows -->
        <div v-if="preview.skipped.length" class="border-t border-slate-200 pt-4">
          <p class="text-xs font-bold text-amber-700 mb-2">Skipped rows</p>
          <div class="space-y-1.5">
            <div v-for="(item, idx) in preview.skipped" :key="idx" class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
              <span class="font-bold">{{ item.source }}:</span> {{ item.errors.join(' ') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import SelectButton from 'primevue/selectbutton'
import FileUpload from 'primevue/fileupload'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../../api'

const router = useRouter()
const toast = useToast()

const goBack = () => router.push({ name: 'teacher.questionbank' })

const activeTab = ref('export')

const formatOptions = [
  { label: 'Excel (.xlsx)', value: 'xlsx' },
  { label: 'CSV', value: 'csv' },
  { label: 'GIFT', value: 'gift' },
]
const extensionFor = (fmt) => (fmt === 'gift' ? 'gift' : fmt === 'csv' ? 'csv' : 'xlsx')

async function downloadBlob(url, params, filename) {
  const response = await api.get(url, { params, responseType: 'blob' })
  const blobUrl = URL.createObjectURL(response.data)
  const link = document.createElement('a')
  link.href = blobUrl
  link.download = filename
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(blobUrl)
}

// ======= Subject / type lookups (for the Export filters) =======
const mySubjects = ref([])
const typeFilterOptions = [
  { label: 'All Types', value: '' },
  { label: 'Multiple Choice', value: 'multiple_choice' },
  { label: 'True / False', value: 'true_false' },
  { label: 'Matching', value: 'matching' },
]
const subjectFilterOptions = computed(() => [
  { label: 'All Subjects', value: '' },
  ...mySubjects.value.map(s => ({ label: `${s.name} (${s.code})`, value: s.id })),
])

onMounted(async () => {
  const { data } = await api.get('/teacher/subjects')
  mySubjects.value = data.data
})

// ======= Export =======
const exportSubjectId = ref('')
const exportType = ref('')
const exportFormat = ref('xlsx')
const exporting = ref(false)

const filterSummary = computed(() => {
  const parts = []
  if (exportSubjectId.value) parts.push('the selected subject')
  if (exportType.value) parts.push('the selected type')
  return parts.length ? `your questions matching ${parts.join(' and ')}` : 'your entire question bank'
})

async function runExport() {
  exporting.value = true
  try {
    await downloadBlob('/teacher/questions/export', {
      format: exportFormat.value,
      subject_id: exportSubjectId.value || undefined,
      type: exportType.value || undefined,
    }, `my-questions.${extensionFor(exportFormat.value)}`)
    toast.add({ severity: 'success', summary: 'Questions exported', detail: 'Your question bank export has downloaded.', life: 3000 })
  } catch (error) {
    toast.add({ summary: 'Failed to export questions', ...toastFromError(error) })
  } finally {
    exporting.value = false
  }
}

// ======= Import =======
const importFormat = ref('xlsx')
const downloadingTemplate = ref(false)
const selectedFile = ref(null)
const previewing = ref(false)
const importing = ref(false)
const preview = ref(null)

async function downloadTemplate() {
  downloadingTemplate.value = true
  try {
    await downloadBlob('/teacher/questions/template', { format: importFormat.value }, `question-import-template.${extensionFor(importFormat.value)}`)
  } catch (error) {
    toast.add({ summary: 'Failed to download template', ...toastFromError(error) })
  } finally {
    downloadingTemplate.value = false
  }
}

function formatType(type) {
  if (type === 'multiple_choice') return 'Multiple Choice'
  if (type === 'true_false') return 'True / False'
  if (type === 'matching') return 'Matching'
  return type
}

function buildFormData() {
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  formData.append('format', importFormat.value)
  return formData
}

async function runPreview() {
  if (!selectedFile.value) return

  previewing.value = true
  try {
    const { data } = await api.post('/teacher/questions/import', buildFormData(), {
      params: { preview: 1 },
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    preview.value = { imported: data.imported, skipped: data.skipped, questions: data.questions }
  } catch (error) {
    toast.add({ summary: 'Failed to preview import file', ...toastFromError(error) })
    selectedFile.value = null
  } finally {
    previewing.value = false
  }
}

function onFileSelected(event) {
  selectedFile.value = event.files?.[0] || null
  preview.value = null
  runPreview()
}

// If the teacher switches format after already previewing a file, re-parse
// under the new format so the preview doesn't go stale before Confirm.
watch(importFormat, () => {
  if (selectedFile.value) {
    preview.value = null
    runPreview()
  }
})

// On a successful import, head straight back to the Question Bank so the
// teacher lands on the list with their newly-imported questions in it.
async function runImport() {
  if (!selectedFile.value) return

  importing.value = true
  try {
    const { data } = await api.post('/teacher/questions/import', buildFormData(), {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    if (data.imported > 0) {
      toast.add({ severity: 'success', summary: 'Questions imported', detail: `${data.imported} question${data.imported === 1 ? '' : 's'} imported successfully.`, life: 3000 })
      router.push({ name: 'teacher.questionbank', query: { imported: data.imported } })
    } else {
      preview.value = { imported: data.imported, skipped: data.skipped, questions: [] }
      toast.add({ severity: 'warn', summary: 'No questions imported', detail: 'No rows could be imported. Check the skipped rows below.', life: 4000 })
    }
  } catch (error) {
    toast.add({ summary: 'Failed to import questions', ...toastFromError(error) })
  } finally {
    importing.value = false
  }
}
</script>
