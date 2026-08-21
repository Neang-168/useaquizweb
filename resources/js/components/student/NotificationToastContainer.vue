<template>
  <Teleport to="body">
    <div class="fixed top-20 right-4 z-[9999] flex flex-col gap-2.5 w-[calc(100%-2rem)] max-w-sm pointer-events-none">
      <TransitionGroup name="toast">
        <div
          v-for="toast in notificationState.toasts"
          :key="toast._key"
          class="pointer-events-auto bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden"
        >
          <div class="flex items-start gap-3 p-4">
            <div :class="['w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm', typeStyle(toast.type)]">
              <i :class="typeIcon(toast.type)"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-slate-800 m-0 truncate">{{ toast.title }}</p>
              <p class="text-xs text-slate-500 m-0 mt-0.5 leading-relaxed line-clamp-2">{{ toast.message }}</p>
              <button
                v-if="toast.type === 'feedback_received'"
                type="button"
                class="inline-block mt-1.5 text-[11px] font-bold text-blue-600 hover:underline border-0 bg-transparent p-0 cursor-pointer"
                @click="openFeedback(toast)"
              >
                View
              </button>
              <router-link
                v-else-if="toast.actionUrl"
                :to="toast.actionUrl"
                class="inline-block mt-1.5 text-[11px] font-bold text-blue-600 hover:underline no-underline"
                @click="dismissToast(toast._key)"
              >
                View
              </router-link>
            </div>
            <button
              type="button"
              class="shrink-0 text-slate-300 hover:text-slate-500 bg-transparent border-0 cursor-pointer p-0.5"
              @click="dismissToast(toast._key)"
            >
              <i class="pi pi-times text-xs"></i>
            </button>
          </div>
        </div>
      </TransitionGroup>
    </div>

    <FeedbackDetailDialog :feedback="selectedFeedback" @close="selectedFeedback = null" />
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { notificationState, dismissToast } from '../../store/notifications'
import FeedbackDetailDialog from './FeedbackDetailDialog.vue'

const selectedFeedback = ref(null)

function openFeedback(toast) {
  selectedFeedback.value = toast
  dismissToast(toast._key)
}

function typeStyle(type) {
  switch (type) {
    case 'quiz_published': return 'bg-blue-50 text-blue-600'
    case 'quiz_starting_soon': return 'bg-amber-50 text-amber-600'
    case 'quiz_closing_soon': return 'bg-orange-50 text-orange-600'
    case 'quiz_ending_soon': return 'bg-rose-50 text-rose-600'
    case 'feedback_received': return 'bg-emerald-50 text-emerald-600'
    default: return 'bg-slate-100 text-slate-600'
  }
}

function typeIcon(type) {
  switch (type) {
    case 'quiz_published': return 'pi pi-file'
    case 'quiz_starting_soon': return 'pi pi-hourglass'
    case 'quiz_closing_soon': return 'pi pi-exclamation-triangle'
    case 'quiz_ending_soon': return 'pi pi-clock'
    case 'feedback_received': return 'pi pi-comment'
    default: return 'pi pi-bell'
  }
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(24px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(24px);
}
.toast-leave-active {
  position: absolute;
  width: 100%;
}
</style>
