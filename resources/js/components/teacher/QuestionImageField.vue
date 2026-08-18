<template>
  <div class="flex items-start gap-1.5">
    <div
      v-if="previewSrc"
      class="relative w-14 h-14 rounded-lg overflow-hidden border border-slate-200 bg-slate-50 shrink-0"
    >
      <img :src="previewSrc" class="w-full h-full object-cover" :alt="altText || ''" />
      <div v-if="uploading" class="absolute inset-0 bg-white/70 flex items-center justify-center">
        <i class="pi pi-spin pi-spinner text-slate-500 text-sm"></i>
      </div>
      <button
        v-else
        type="button"
        class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-500 text-white text-[9px] flex items-center justify-center border border-white cursor-pointer"
        title="Remove image"
        @click="removeImage"
      >
        <i class="pi pi-times"></i>
      </button>
    </div>

    <label
      v-else
      class="w-14 h-14 rounded-lg border border-dashed border-slate-300 flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:border-indigo-300 cursor-pointer shrink-0 bg-slate-50 transition-colors"
      :title="label || 'Add image'"
    >
      <i v-if="!uploading" class="pi pi-image text-sm"></i>
      <i v-else class="pi pi-spin pi-spinner text-sm"></i>
      <input
        type="file"
        accept="image/jpeg,image/png,image/webp"
        class="hidden"
        :disabled="uploading"
        @change="onPick"
      />
    </label>

    <span v-if="error" class="text-[10px] text-rose-500 self-center max-w-[8rem]">{{ error }}</span>
  </div>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import api, { extractError } from '../../api'

const props = defineProps({
  token: { type: String, default: null },
  removed: { type: Boolean, default: false },
  existingUrl: { type: String, default: null },
  altText: { type: String, default: '' },
  label: { type: String, default: '' },
})

const emit = defineEmits(['update:token', 'update:removed'])

const uploading = ref(false)
const error = ref('')
const localPreview = ref(null)

const previewSrc = computed(() => {
  if (props.removed) return null
  if (localPreview.value) return localPreview.value
  return props.existingUrl
})

function clearLocalPreview() {
  if (localPreview.value) {
    URL.revokeObjectURL(localPreview.value)
    localPreview.value = null
  }
}

async function onPick(event) {
  const file = event.target.files?.[0]
  event.target.value = ''
  if (!file) return

  error.value = ''
  clearLocalPreview()
  localPreview.value = URL.createObjectURL(file)

  const formData = new FormData()
  formData.append('image', file)

  uploading.value = true
  try {
    const { data } = await api.post('/teacher/uploads/question-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    emit('update:token', data.token)
    emit('update:removed', false)
  } catch (err) {
    error.value = extractError(err)
    clearLocalPreview()
  } finally {
    uploading.value = false
  }
}

function removeImage() {
  clearLocalPreview()
  emit('update:token', null)
  emit('update:removed', true)
}

// A different question/option/pair was swapped in under this same field —
// drop any stale local preview so we don't show the wrong thumbnail.
watch(() => props.existingUrl, clearLocalPreview)

onBeforeUnmount(clearLocalPreview)
</script>
