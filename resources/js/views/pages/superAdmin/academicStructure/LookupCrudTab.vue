<template>
  <div class="flex-1 min-h-0 flex flex-col">
    <DataTable
      :value="filteredItems"
      dataKey="id"
      paginator
      paginatorPosition="bottom"
      :rows="rows"
      v-model:first="first"
      :rowsPerPageOptions="[10, 20, 50]"
      scrollable
      scrollHeight="flex"
      responsiveLayout="scroll"
      :loading="loading"
      tableStyle="min-width: 100%"
      class="p-datatable-sm academic-table flex-1 min-h-0 mt-3"
    >
      <template #empty>
        <div class="text-center py-10 text-xs text-slate-400">{{ emptyMessage }}</div>
      </template>

      <template #paginatorstart>
        <span class="text-xs text-slate-500">
          Showing <span class="font-semibold text-slate-700">{{ filteredItems.length ? first + 1 : 0 }}</span>
          to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredItems.length) }}</span>
          of <span class="font-semibold text-slate-700">{{ filteredItems.length }}</span>
        </span>
      </template>

      <Column
        v-for="field in tableColumns"
        :key="'col-' + field.key"
        :field="field.key"
        :header="(field.tableLabel || field.label).toUpperCase()"
        sortable
        style="padding-left: 1.25rem"
      >
        <template #body="{ data }">
          <span v-if="field.formatTable" class="text-slate-600 text-sm">{{ field.formatTable(data) }}</span>
          <span
            v-else-if="field.type === 'status'"
            class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
            :class="data[field.key] === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
          >
            {{ data[field.key] }}
          </span>
          <span
            v-else-if="field.type === 'boolean'"
            class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
            :class="data[field.key] ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-slate-50 text-slate-400 border-slate-200'"
          >
            {{ data[field.key] ? (field.trueLabel || 'Yes') : (field.falseLabel || '—') }}
          </span>
          <span v-else-if="field.type === 'select'" class="text-slate-600 text-sm">
            {{ data[field.displayKey || field.key] ?? '—' }}
          </span>
          <span v-else-if="field.key === 'code'" class="font-mono font-bold text-indigo-600 text-sm">
            {{ data[field.key] ?? '—' }}
          </span>
          <span v-else class="text-slate-700 text-sm" :class="field.khmer ? 'font-khmer' : ''">
            {{ data[field.key] ?? '—' }}
          </span>
        </template>
      </Column>

      <Column header="ACTIONS" style="width: 120px; padding-right: 1.25rem" class="!text-center">
        <template #body="{ data }">
          <div class="flex items-center justify-center gap-1.5">
            <Button
              v-if="supportsSetCurrent && !data.is_current"
              size="small"
              class="!bg-slate-100 !border-slate-100 !text-blue-600 !rounded-xl !text-xs !px-3 !py-1.5 shadow-xs"
              title="Set as current"
              @click="setCurrent(data)"
            ><i class="fa-solid fa-check"></i></Button>
            <Button
              size="small"
              class="!bg-slate-100 !border-slate-100 !text-[#e4ac14] !rounded-xl !text-xs !px-3 !py-1.5 shadow-xs"
              :title="`Edit ${singular}`"
              @click="openEdit(data)"
            ><i class="fa-solid fa-pen-to-square"></i></Button>
            <Button
              size="small"
              class="!bg-slate-100 !border-slate-100 !text-[#d71818] !rounded-xl !text-xs !px-3 !py-1.5 shadow-xs"
              :title="`Delete ${singular}`"
              @click="confirmDeleteItem(data)"
            ><i class="fa-solid fa-trash-can"></i></Button>
          </div>
        </template>
      </Column>
    </DataTable>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEdit ? `Edit ${singular}` : `Create New ${singular}`"
      :modal="true"
      class="w-full max-w-3xl !rounded-xl overflow-hidden"
      :pt="{
        root: { class: '!rounded-xl !border-0 shadow-2xl !bg-white' },
        header: { class: '!border-b !border-slate-100 !p-5 !bg-slate-50/50' },
        content: { class: '!p-6' },
        footer: { class: '!border-t !border-slate-100 !p-4 !bg-slate-50/50' }
      }"
    >
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div v-for="field in fields" :key="field.key" :class="colSpanClass(field)">
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
            {{ field.label }}<span v-if="field.required"> *</span>
          </label>

          <InputText
            v-if="field.type === 'text'"
            v-model="form[field.key]"
            :placeholder="field.placeholder"
            :class="['w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm', field.khmer ? 'font-khmer' : '']"
          />

          <Textarea
            v-else-if="field.type === 'textarea'"
            v-model="form[field.key]"
            rows="3"
            :placeholder="field.placeholder"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />

          <InputText
            v-else-if="field.type === 'number'"
            v-model="form[field.key]"
            type="number"
            :placeholder="field.placeholder"
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />

          <InputText
            v-else-if="field.type === 'date'"
            v-model="form[field.key]"
            type="date"
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />

          <InputText
            v-else-if="field.type === 'time'"
            v-model="form[field.key]"
            type="time"
            class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
          />

          <Dropdown
            v-else-if="field.type === 'select'"
            v-model="form[field.key]"
            :options="optionsFor(field)"
            :optionLabel="field.optionLabel"
            :optionValue="field.optionValue || 'id'"
            :placeholder="`Select ${field.label}`"
            showClear
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />

          <Dropdown
            v-else-if="field.type === 'status'"
            v-model="form[field.key]"
            :options="['Active', 'Inactive']"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />

          <label v-else-if="field.type === 'boolean'" class="flex items-center gap-2 mt-2 cursor-pointer">
            <Checkbox v-model="form[field.key]" :binary="true" />
            <span class="text-sm text-slate-600">{{ field.checkboxLabel || field.label }}</span>
          </label>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 hover:!bg-slate-200/80 !text-slate-600 !border-0 !rounded-lg !px-4 !py-2 !text-xs !font-semibold transition-all cursor-pointer" @click="dialogVisible = false" />
          <Button :label="`Save ${singular}`" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 active:!bg-blue-800 !text-white !border-0 !rounded-lg !px-4 !py-2 !text-xs !font-semibold shadow-xs transition-all cursor-pointer" @click="save" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api, { toastFromError } from '../../../../api'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Checkbox from 'primevue/checkbox'

const props = defineProps({
  singular: { type: String, required: true },
  endpoint: { type: String, required: true },
  fields: { type: Array, required: true },
  lookups: { type: Object, default: () => ({}) },
  search: { type: String, default: '' },
  emptyMessage: { type: String, default: 'No records found.' },
  supportsSetCurrent: { type: Boolean, default: false },
})

const emit = defineEmits(['changed'])

const toast = useToast()
const confirm = useConfirm()

const items = ref([])
const loading = ref(false)
const first = ref(0)
const rows = ref(10)

const tableColumns = computed(() => props.fields.filter((f) => f.showInTable !== false && f.type !== 'textarea'))

const filteredItems = computed(() => {
  const q = props.search.trim().toLowerCase()
  if (!q) return items.value
  return items.value.filter((row) =>
    Object.values(row).some((v) => typeof v === 'string' && v.toLowerCase().includes(q))
  )
})

// Jump back to page 1 whenever the shared search box changes, so the
// paginator never gets stranded past the end of a smaller filtered list.
watch(() => props.search, () => {
  first.value = 0
})

const optionsFor = (field) => props.lookups[field.optionsKey] || []

const colSpanClass = (field) => {
  if (field.span === 3) return 'sm:col-span-3'
  if (field.span === 2) return 'sm:col-span-2'
  return 'sm:col-span-1'
}

const fetchItems = async () => {
  loading.value = true
  try {
    const { data } = await api.get(props.endpoint, { params: { per_page: 100 } })
    items.value = data.data
  } catch (error) {
    toast.add({ summary: `Failed to load ${props.singular.toLowerCase()}s`, ...toastFromError(error) })
  } finally {
    loading.value = false
  }
}

onMounted(fetchItems)

const blankForm = () => {
  const obj = { id: null }
  for (const f of props.fields) {
    if (f.type === 'status') obj[f.key] = 'Active'
    else if (f.type === 'boolean') obj[f.key] = false
    else obj[f.key] = null
  }
  return obj
}

const dialogVisible = ref(false)
const isEdit = ref(false)
const form = ref(blankForm())

const openNew = () => {
  form.value = blankForm()
  isEdit.value = false
  dialogVisible.value = true
}

const openEdit = (data) => {
  form.value = { ...blankForm(), ...data }
  isEdit.value = true
  dialogVisible.value = true
}

const validateForm = () => {
  const missing = props.fields.filter((f) => f.required && !form.value[f.key] && form.value[f.key] !== 0)
  if (missing.length) {
    toast.add({
      severity: 'warn',
      summary: 'Missing information',
      detail: `${missing.map((f) => f.label).join(', ')} ${missing.length > 1 ? 'are' : 'is'} required.`,
      life: 4000,
    })
    return false
  }
  return true
}

const buildPayload = () => {
  const payload = {}
  for (const f of props.fields) {
    payload[f.requestKey || f.key] = form.value[f.key]
  }
  return payload
}

const save = async () => {
  if (!validateForm()) return

  const payload = buildPayload()

  try {
    if (isEdit.value) {
      await api.put(`${props.endpoint}/${form.value.id}`, payload)
      toast.add({ severity: 'success', summary: `${props.singular} updated`, life: 3000 })
    } else {
      await api.post(props.endpoint, payload)
      toast.add({ severity: 'success', summary: `${props.singular} created`, life: 3000 })
    }

    dialogVisible.value = false
    await fetchItems()
    emit('changed')
  } catch (error) {
    toast.add({
      summary: isEdit.value ? `Failed to update ${props.singular.toLowerCase()}` : `Failed to create ${props.singular.toLowerCase()}`,
      ...toastFromError(error),
    })
  }
}

const displayName = (data) => data.name_en || data.code || data.name || `#${data.id}`

const confirmDeleteItem = (data) => {
  confirm.require({
    header: `Delete ${props.singular}`,
    message: `Are you sure you want to delete "${displayName(data)}"?`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Delete',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await api.delete(`${props.endpoint}/${data.id}`)
        toast.add({ severity: 'success', summary: `${props.singular} deleted`, life: 3000 })
        await fetchItems()
        emit('changed')
      } catch (error) {
        toast.add({ summary: `Failed to delete ${props.singular.toLowerCase()}`, ...toastFromError(error) })
      }
    },
  })
}

const setCurrent = async (data) => {
  try {
    await api.post(`${props.endpoint}/${data.id}/set-current`)
    toast.add({ severity: 'success', summary: `${displayName(data)} is now the current academic year.`, life: 3000 })
    await fetchItems()
    emit('changed')
  } catch (error) {
    toast.add({ summary: 'Failed to set current academic year', ...toastFromError(error) })
  }
}

defineExpose({ fetchItems, openNew })
</script>

<style scoped>
/* Header two sizes smaller, body two sizes larger, than the table's base text-xs. */
.academic-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.academic-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}
</style>
