<template>
  <Dialog
    :visible="!!feedback"
    @update:visible="(val) => { if (!val) $emit('close') }"
    modal
    dismissable-mask
    class="w-full max-w-sm"
  >
    <template v-if="feedback" #header>
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
          <i class="pi pi-comment text-sm"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-800 m-0">{{ feedback.title || 'Feedback from your teacher' }}</h3>
      </div>
    </template>

    <template v-if="feedback">
      <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line m-0">{{ feedback.message }}</p>
      <p v-if="feedback.createdAt" class="text-[11px] text-slate-400 mt-3 mb-0">{{ formatDateTime(feedback.createdAt) }}</p>
    </template>

    <template v-if="feedback" #footer>
      <Button label="Close" size="small" class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="$emit('close')" />
    </template>
  </Dialog>
</template>

<script setup>
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'

defineProps({
  feedback: { type: Object, default: null },
})

defineEmits(['close'])

function formatDateTime(value) {
  if (!value) return ''
  return value.replace('T', ' ')
}
</script>
