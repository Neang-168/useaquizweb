<template>
  <div class="flex flex-col gap-4 font-sans text-slate-800">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0 bg-white/60 backdrop-blur-md p-4 rounded-xl border border-slate-200/60 shadow-xs">
      <div>
        <h1 class="text-xl font-bold text-[#002060] tracking-tight flex items-center gap-2.5">
          <i class="pi pi-cog text-[#e4ac40] text-2xl"></i>
          Settings
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 m-0 mt-1 pl-0.5">System-wide configuration for the platform.</p>
      </div>
    </div>

    <!-- ======= BACKUP & RESTORE ======= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

      <!-- ======= BACKUP CARD ======= -->
      <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold shrink-0">
            <i class="pi pi-download text-sm"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold text-[#002060] m-0">Backup</h3>
            <p class="text-xs text-slate-400 m-0 mt-0.5">Download a full copy of the database</p>
          </div>
        </div>

        <div class="p-4 space-y-4">
          <p class="text-xs text-slate-500 m-0">
            Download a full snapshot of the database — every user, class, quiz, and submission — as a single file you can store safely.
          </p>
          <Button
            label="Download Backup"
            icon="pi pi-download"
            class="!bg-emerald-600 hover:!bg-emerald-700 !border-0 !text-white !rounded-lg !text-xs !font-semibold !py-2.5 !px-4"
            :loading="downloading"
            @click="downloadBackup"
          />
        </div>
      </div>

      <!-- ======= RESTORE CARD ======= -->
      <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 flex items-center justify-center font-bold shrink-0">
            <i class="pi pi-upload text-sm"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold text-[#002060] m-0">Restore</h3>
            <p class="text-xs text-slate-400 m-0 mt-0.5">Replace the live database from a backup file</p>
          </div>
        </div>

        <div class="p-4 space-y-4">
          <div class="bg-rose-50 border border-rose-200 rounded-xl p-3.5 flex items-start gap-2.5">
            <i class="pi pi-exclamation-triangle text-rose-500 text-sm mt-0.5"></i>
            <p class="text-xs text-rose-700 m-0">
              This replaces the <span class="font-bold">entire live database</span> with the uploaded file. A safety backup of the
              current database is taken automatically right before restoring, so it can be undone — but proceed carefully.
            </p>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Backup file</label>
            <FileUpload mode="basic" :auto="false" choose-label="Choose file" accept=".sqlite,.sql,.db" custom-upload
              class="w-full" @select="onFileSelected" />
            <p v-if="selectedFile" class="text-xs text-slate-500 mt-1.5">{{ selectedFile.name }}</p>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">
              Type <span class="font-mono text-rose-600">RESTORE</span> to confirm
            </label>
            <InputText v-model="confirmPhrase" placeholder="RESTORE"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-sm" />
          </div>

          <Button
            label="Restore Database"
            icon="pi pi-upload"
            class="!bg-rose-600 hover:!bg-rose-700 !border-0 !text-white !rounded-lg !text-xs !font-semibold !py-2.5 !px-4"
            :disabled="!canRestore"
            :loading="restoring"
            @click="confirmRestore"
          />
        </div>
      </div>
    </div>

    <!-- ======= SAFETY BACKUPS HISTORY ======= -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-[#002060] m-0">Safety Backups</h3>
        <p class="text-xs text-slate-400 m-0 mt-0.5">Automatically saved right before each restore, in case one needs to be undone.</p>
      </div>

      <DataTable :value="history" :loading="loadingHistory" class="p-datatable-sm">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">No safety backups yet — one is taken automatically the first time you restore.</div>
        </template>

        <Column field="name" header="FILE" style="padding-left: 1.25rem">
          <template #body="{ data }"><span class="font-mono text-slate-700 text-sm">{{ data.name }}</span></template>
        </Column>
        <Column field="size" header="SIZE">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ formatSize(data.size) }}</span></template>
        </Column>
        <Column field="created_at" header="TAKEN">
          <template #body="{ data }"><span class="text-slate-600 text-sm">{{ formatDate(data.created_at) }}</span></template>
        </Column>
        <Column header="" style="padding-right: 1.25rem">
          <template #body="{ data }">
            <Button icon="pi pi-download"
              class="!p-1.5 !w-8 !h-8 !rounded-xl !bg-slate-100 !text-slate-600 !border !border-slate-100 shadow-xs cursor-pointer text-xs"
              title="Download this backup" @click="downloadHistoryFile(data.name)" />
          </template>
        </Column>
      </DataTable>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import FileUpload from 'primevue/fileupload'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../../api'

const confirm = useConfirm()
const toast = useToast()

const downloading = ref(false)
const restoring = ref(false)
const selectedFile = ref(null)
const confirmPhrase = ref('')
const history = ref([])
const loadingHistory = ref(false)

const canRestore = computed(() => !!selectedFile.value && confirmPhrase.value === 'RESTORE')

function onFileSelected(event) {
  selectedFile.value = event.files?.[0] || null
}

function formatSize(bytes) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function formatDate(iso) {
  return new Date(iso).toLocaleString()
}

// The server names the file (backup-<timestamp>.sql or .sqlite, depending on
// the DB driver) via the Content-Disposition header — read that instead of
// guessing an extension, so the download doesn't lose it.
async function downloadBlob(url, fallbackFilename) {
  const response = await api.get(url, { responseType: 'blob' })
  const disposition = response.headers['content-disposition'] || ''
  const match = disposition.match(/filename="?([^";]+)"?/)
  const filename = match ? match[1] : fallbackFilename

  const blobUrl = URL.createObjectURL(response.data)
  const link = document.createElement('a')
  link.href = blobUrl
  link.download = filename
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(blobUrl)
}

async function downloadBackup() {
  downloading.value = true
  try {
    await downloadBlob('/backup', `backup-${Date.now()}.sql`)
    toast.add({ severity: 'success', summary: 'Backup downloaded', life: 3000 })
  } catch (error) {
    toast.add({ summary: 'Failed to download backup', ...toastFromError(error) })
  } finally {
    downloading.value = false
  }
}

async function fetchHistory() {
  loadingHistory.value = true
  try {
    const { data } = await api.get('/backup/history')
    history.value = data.data
  } catch (error) {
    toast.add({ summary: 'Failed to load backup history', ...toastFromError(error) })
  } finally {
    loadingHistory.value = false
  }
}

async function downloadHistoryFile(filename) {
  try {
    await downloadBlob(`/backup/history/${encodeURIComponent(filename)}`, filename)
  } catch (error) {
    toast.add({ summary: 'Failed to download backup', ...toastFromError(error) })
  }
}

function confirmRestore() {
  if (!canRestore.value) return

  confirm.require({
    header: 'Restore database',
    message: 'This will overwrite the entire live database with the uploaded file. A safety backup of the current database will be taken first. Continue?',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Restore',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: runRestore,
  })
}

async function runRestore() {
  restoring.value = true
  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('confirm', confirmPhrase.value)

    const { data } = await api.post('/backup/restore', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    toast.add({ severity: 'success', summary: 'Database restored', detail: data.message, life: 5000 })
    selectedFile.value = null
    confirmPhrase.value = ''
    await fetchHistory()
  } catch (error) {
    toast.add({ summary: 'Failed to restore database', ...toastFromError(error) })
  } finally {
    restoring.value = false
  }
}

onMounted(fetchHistory)
</script>
