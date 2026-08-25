<template>
  <Teleport to="body">
    <Transition name="confirm-fade">
      <div v-if="modelValue" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40" @click.self="$emit('cancel')">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-5 space-y-4">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
              <i class="pi pi-exclamation-triangle text-lg"></i>
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-800 m-0">{{ title }}</h3>
              <p class="text-xs text-slate-500 m-0 mt-1 leading-relaxed">{{ message }}</p>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" @click="$emit('cancel')"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl border-0 cursor-pointer transition-all">
              {{ cancelText }}
            </button>
            <button type="button" @click="$emit('confirm')"
              class="px-4 py-2 bg-[#002060] hover:bg-[#001848] text-white font-bold text-xs rounded-xl border-0 cursor-pointer transition-all shadow-md shadow-[#002060]/20">
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: 'Are you sure?' },
  message: { type: String, default: '' },
  confirmText: { type: String, default: 'Confirm' },
  cancelText: { type: String, default: 'Cancel' },
})
defineEmits(['confirm', 'cancel'])
</script>

<style scoped>
.confirm-fade-enter-active,
.confirm-fade-leave-active {
  transition: opacity 0.15s ease;
}
.confirm-fade-enter-from,
.confirm-fade-leave-to {
  opacity: 0;
}
</style>
