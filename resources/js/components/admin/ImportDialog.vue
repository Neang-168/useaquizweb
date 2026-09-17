<template>
  <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)" :header="title" :modal="true"
    class="w-full max-w-3xl !text-[#002060]" @hide="reset">
    <div class="space-y-5 pt-2 text-xs">
      <div>
        <label class="block font-bold text-slate-600 uppercase mb-1.5">File format</label>
        <SelectButton v-model="format" :options="formatOptions" option-label="label" option-value="value" :allow-empty="false" />
      </div>

      <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-2">
        <p class="text-slate-500 m-0">New here? Download a sample template, fill it in, then upload it below.</p>
        <Button label="Download sample data" icon="pi pi-download" text size="small"
          class="!text-indigo-600 !font-bold !p-0" :loading="downloadingTemplate" @click="downloadTemplate" />
      </div>

      <div>
        <label class="block font-bold text-slate-600 uppercase mb-1.5">Upload file</label>
        <FileUpload mode="basic" :auto="false" choose-label="Choose file" accept=".csv,.xlsx,.xls" custom-upload
          class="w-full" @select="onFileSelected" />
        <p v-if="selectedFile" class="text-slate-500 mt-1.5 flex items-center gap-1.5">
          <i v-if="previewing" class="pi pi-spin pi-spinner"></i>
          {{ selectedFile.name }}
        </p>
      </div>

      <!-- Preview -->
      <div v-if="preview" class="border-t border-slate-200 pt-4 space-y-3">
        <p class="font-bold text-sm m-0" :class="preview.skipped.length ? 'text-amber-700' : 'text-emerald-700'">
          {{ preview.imported }} {{ entityLabel }}{{ preview.imported === 1 ? '' : 's' }} ready to import
          <span v-if="preview.skipped.length" class="font-medium text-amber-600">· {{ preview.skipped.length }} will be skipped</span>
        </p>

        <div v-if="preview.rows.length" class="border border-slate-200 rounded-xl overflow-hidden">
          <DataTable :value="preview.rows" class="p-datatable-sm" scrollable scrollHeight="260px">
            <Column v-for="col in previewColumns" :key="col.field" :field="col.field" :header="col.header">
              <template #body="{ data }">
                <span class="text-slate-600">{{ data[col.field] ?? '—' }}</span>
              </template>
            </Column>
          </DataTable>
        </div>

        <div v-if="preview.skipped.length" class="space-y-1.5">
          <p class="text-xs font-bold text-amber-700 mb-1">Skipped rows</p>
          <div v-for="(item, idx) in preview.skipped" :key="idx"
            class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
            <span class="font-bold">{{ item.source }}:</span> {{ item.errors.join(' ') }}
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-2 pt-3">
        <Button label="Cancel" icon="pi pi-times"
          class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold"
          @click="$emit('update:visible', false)" />
        <Button label="Confirm Import" icon="pi pi-upload"
          class="!bg-indigo-600 hover:!bg-indigo-700 !border-0 !text-white !rounded-xl !text-xs !font-semibold"
          :disabled="!preview || preview.imported === 0" :loading="importing" @click="runImport" />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import SelectButton from 'primevue/selectbutton'
import FileUpload from 'primevue/fileupload'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../api'

const props = defineProps({
  visible: { type: Boolean, default: false },
  title: { type: String, required: true },
  entityLabel: { type: String, required: true }, // e.g. "teacher" / "student"
  templateUrl: { type: String, required: true },
  importUrl: { type: String, required: true },
})

const emit = defineEmits(['update:visible', 'imported'])

const toast = useToast()

const formatOptions = [
  { label: 'Excel (.xlsx)', value: 'xlsx' },
  { label: 'CSV', value: 'csv' },
]
const extensionFor = (fmt) => (fmt === 'csv' ? 'csv' : 'xlsx')

const format = ref('xlsx')
const downloadingTemplate = ref(false)
const selectedFile = ref(null)
const previewing = ref(false)
const importing = ref(false)
const preview = ref(null)

const previewColumns = computed(() => {
  if (!preview.value?.rows?.length) return []
  return Object.keys(preview.value.rows[0])
    .filter(key => key !== 'source')
    .map(key => ({
      field: key,
      header: key.replace(/([A-Z])/g, ' $1').replace(/_/g, ' ').replace(/^./, c => c.toUpperCase()),
    }))
})

function reset() {
  selectedFile.value = null
  preview.value = null
}

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

async function downloadTemplate() {
  downloadingTemplate.value = true
  try {
    await downloadBlob(props.templateUrl, { format: format.value }, `${props.entityLabel}-import-template.${extensionFor(format.value)}`)
  } catch (error) {
    toast.add({ summary: 'Failed to download template', ...toastFromError(error) })
  } finally {
    downloadingTemplate.value = false
  }
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
    const { data } = await api.post(props.importUrl, buildFormData(), {
      params: { preview: 1 },
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    preview.value = { imported: data.imported, skipped: data.skipped, rows: data.rows }
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

// If the format is switched after already previewing a file, re-parse under
// the new format so the preview doesn't go stale before Confirm.
watch(format, () => {
  if (selectedFile.value) {
    preview.value = null
    runPreview()
  }
})

async function runImport() {
  if (!selectedFile.value) return

  importing.value = true
  try {
    const { data } = await api.post(props.importUrl, buildFormData(), {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    emit('imported', data)
    emit('update:visible', false)
  } catch (error) {
    toast.add({ summary: `Failed to import ${props.entityLabel}s`, ...toastFromError(error) })
  } finally {
    importing.value = false
  }
}
</script>
