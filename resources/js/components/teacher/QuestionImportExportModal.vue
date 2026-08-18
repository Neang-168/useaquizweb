<template>
  <Dialog
    :visible="visible"
    @update:visible="(val) => emit('update:visible', val)"
    modal
    class="w-full max-w-lg"
  >
    <template #header>
      <div>
        <h3 class="text-sm font-bold text-slate-800 m-0">Import / Export Questions</h3>
        <p class="text-xs text-slate-400 mt-0.5 mb-0">Bulk-load questions from a file, or download your question bank.</p>
      </div>
    </template>

    <div class="space-y-4 text-xs">
      <!-- Tabs -->
      <SelectButton
        v-model="activeTab"
        :options="[{ label: 'Import', value: 'import' }, { label: 'Export', value: 'export' }]"
        option-label="label"
        option-value="value"
        :allow-empty="false"
      />

      <!-- Format picker (shared by both tabs) -->
      <div>
        <label class="block font-bold text-slate-700 mb-1">File format</label>
        <SelectButton
          v-model="format"
          :options="formatOptions"
          option-label="label"
          option-value="value"
          :allow-empty="false"
        />
      </div>

      <!-- ============ IMPORT ============ -->
      <div v-if="activeTab === 'import'" class="space-y-3">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-2">
          <p class="text-slate-500">New here? Download a template, fill it in, then upload it below. Text only — images can be added afterward in the question editor.</p>
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
          <label class="block font-bold text-slate-700 mb-1">Upload file</label>
          <FileUpload
            mode="basic"
            :auto="false"
            choose-label="Choose file"
            accept=".csv,.xlsx,.xls,.gift,.txt"
            custom-upload
            class="w-full"
            @select="onFileSelected"
          />
          <p v-if="selectedFile" class="text-slate-500 mt-1 flex items-center gap-1.5">
            <i v-if="previewing" class="pi pi-spin pi-spinner"></i>
            {{ selectedFile.name }}
          </p>
        </div>

        <!-- Preview (shown once the file has been parsed, before anything is saved) -->
        <div v-if="preview && !result" class="border rounded-xl p-3 space-y-2" :class="preview.skipped.length ? 'border-amber-200 bg-amber-50/60' : 'border-emerald-200 bg-emerald-50/60'">
          <p class="font-bold" :class="preview.skipped.length ? 'text-amber-700' : 'text-emerald-700'">
            {{ preview.imported }} question{{ preview.imported === 1 ? '' : 's' }} ready to import.
            <span v-if="preview.skipped.length">{{ preview.skipped.length }} will be skipped.</span>
          </p>

          <div v-if="preview.questions.length" class="space-y-1.5 max-h-56 overflow-y-auto">
            <div v-for="(q, idx) in preview.questions" :key="idx" class="bg-white border border-slate-200 rounded-lg p-2 text-[11px]">
              <div class="flex items-center gap-1.5 mb-1">
                <span class="font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">{{ formatType(q.type) }}</span>
                <span class="text-slate-400">{{ q.subjectCode }} • {{ q.difficulty }}</span>
              </div>
              <p class="font-semibold text-slate-800 m-0">{{ q.title || '(image question)' }}</p>

              <ul v-if="q.type === 'multiple_choice'" class="list-none p-0 m-0 mt-1 space-y-0.5">
                <li v-for="(opt, oIdx) in q.options" :key="oIdx" :class="opt.isCorrect ? 'text-emerald-700 font-semibold' : 'text-slate-500'">
                  <i :class="opt.isCorrect ? 'pi pi-check-circle' : 'pi pi-circle'" class="text-[9px] mr-1"></i>{{ opt.text }}
                </li>
              </ul>
              <p v-else-if="q.type === 'true_false'" class="text-slate-500 mt-1 mb-0">Correct answer: <span class="font-semibold text-emerald-700">{{ q.tfCorrect }}</span></p>
              <ul v-else-if="q.type === 'matching'" class="list-none p-0 m-0 mt-1 space-y-0.5 text-slate-500">
                <li v-for="(pair, pIdx) in q.matchingPairs" :key="pIdx">{{ pair.leftText }} ↔ {{ pair.rightText }}</li>
              </ul>
            </div>
          </div>

          <div v-if="preview.skipped.length" class="space-y-1 max-h-32 overflow-y-auto pt-1 border-t border-amber-200/60">
            <div v-for="(item, idx) in preview.skipped" :key="idx" class="text-[11px] text-amber-800">
              <span class="font-bold">{{ item.source }}:</span> {{ item.errors.join(' ') }}
            </div>
          </div>
        </div>

        <Button
          v-if="!result"
          label="Confirm Import"
          icon="pi pi-upload"
          size="small"
          class="w-full !bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg"
          :disabled="!preview || preview.imported === 0"
          :loading="importing"
          @click="runImport"
        />

        <!-- Final result, after the real import has run -->
        <div v-if="result" class="border rounded-xl p-3 space-y-2" :class="result.skipped.length ? 'border-amber-200 bg-amber-50/60' : 'border-emerald-200 bg-emerald-50/60'">
          <p class="font-bold" :class="result.skipped.length ? 'text-amber-700' : 'text-emerald-700'">
            {{ result.imported }} question{{ result.imported === 1 ? '' : 's' }} imported.
            <span v-if="result.skipped.length">{{ result.skipped.length }} skipped.</span>
          </p>
          <div v-if="result.skipped.length" class="space-y-1 max-h-40 overflow-y-auto">
            <div v-for="(item, idx) in result.skipped" :key="idx" class="text-[11px] text-amber-800">
              <span class="font-bold">{{ item.source }}:</span> {{ item.errors.join(' ') }}
            </div>
          </div>
        </div>
      </div>

      <!-- ============ EXPORT ============ -->
      <div v-else class="space-y-3">
        <p class="text-slate-500">
          Export
          <span class="font-semibold text-slate-700">{{ filterSummary }}</span>
          in the format above. Great for editing in bulk or keeping a backup.
        </p>
        <Button
          label="Download"
          icon="pi pi-download"
          size="small"
          class="w-full !bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg"
          :loading="exporting"
          @click="runExport"
        />
      </div>
    </div>

    <template #footer>
      <Button label="Close" size="small" class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="emit('update:visible', false)" />
    </template>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import SelectButton from 'primevue/selectbutton'
import FileUpload from 'primevue/fileupload'
import api, { extractError } from '../../api'

const props = defineProps({
  visible: { type: Boolean, default: false },
  filters: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:visible', 'imported'])

const activeTab = ref('import')
const format = ref('xlsx')
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

const downloadingTemplate = ref(false)

async function downloadTemplate() {
  downloadingTemplate.value = true
  try {
    await downloadBlob('/teacher/questions/template', { format: format.value }, `question-import-template.${extensionFor(format.value)}`)
  } catch (error) {
    alert(extractError(error))
  } finally {
    downloadingTemplate.value = false
  }
}

const exporting = ref(false)

const filterSummary = computed(() => {
  const parts = []
  if (props.filters?.subject_id) parts.push('the selected subject')
  if (props.filters?.type) parts.push('the selected type')
  return parts.length ? `your questions matching ${parts.join(' and ')}` : 'your entire question bank'
})

async function runExport() {
  exporting.value = true
  try {
    await downloadBlob('/teacher/questions/export', {
      format: format.value,
      subject_id: props.filters?.subject_id || undefined,
      type: props.filters?.type || undefined,
    }, `my-questions.${extensionFor(format.value)}`)
  } catch (error) {
    alert(extractError(error))
  } finally {
    exporting.value = false
  }
}

const selectedFile = ref(null)
const previewing = ref(false)
const importing = ref(false)
const preview = ref(null)
const result = ref(null)

function formatType(type) {
  if (type === 'multiple_choice') return 'Multiple Choice'
  if (type === 'true_false') return 'True / False'
  if (type === 'matching') return 'Matching'
  return type
}

function buildFormData() {
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  formData.append('format', format.value)
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
    alert(extractError(error))
    selectedFile.value = null
  } finally {
    previewing.value = false
  }
}

function onFileSelected(event) {
  selectedFile.value = event.files?.[0] || null
  preview.value = null
  result.value = null
  runPreview()
}

// If the teacher switches format after already previewing a file, re-parse
// under the new format so the preview doesn't go stale before Confirm.
watch(format, () => {
  if (selectedFile.value && !result.value) {
    preview.value = null
    runPreview()
  }
})

async function runImport() {
  if (!selectedFile.value) return

  importing.value = true
  try {
    const { data } = await api.post('/teacher/questions/import', buildFormData(), {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    result.value = { imported: data.imported, skipped: data.skipped }
    if (data.imported > 0) {
      emit('imported')
    }
  } catch (error) {
    alert(extractError(error))
  } finally {
    importing.value = false
  }
}

// Reset transient state whenever the modal is (re)opened.
watch(() => props.visible, (isVisible) => {
  if (!isVisible) return
  selectedFile.value = null
  preview.value = null
  result.value = null
  activeTab.value = 'import'
})
</script>
