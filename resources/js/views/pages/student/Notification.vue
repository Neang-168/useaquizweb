<template>
  <div class="max-w-3xl mx-auto space-y-6 font-sans">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-slate-800 m-0">Notifications</h2>
        <p class="text-xs text-slate-400 m-0 mt-1">All reminders and important updates for you</p>
      </div>

      <div class="flex items-center gap-2">
        <button @click="markAllRead" class="text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-xl transition-all border-0 cursor-pointer flex items-center gap-1.5">
          <i class="pi pi-check-circle"></i>
          <span>Mark all as read</span>
        </button>
        <button v-if="notificationState.items.length" @click="onClearAll" class="text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 px-3 py-1.5 rounded-xl transition-all border-0 cursor-pointer flex items-center gap-1.5">
          <i class="pi pi-trash"></i>
          <span>Clear all</span>
        </button>
      </div>
    </div>

    <!-- ======= NOTIFICATION LIST ======= -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs divide-y divide-slate-100">

      <div v-for="item in notificationState.items" :key="item.id"
        :class="['p-4 transition-all flex items-start gap-4 hover:bg-slate-50/80 cursor-pointer',
          !item.isRead ? 'bg-blue-50/40' : '']"
        @click="onItemClick(item)">

        <!-- Icon based on Notification Type -->
        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg', getTypeStyle(item.type)]">
          <i :class="getIcon(item.type)"></i>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <h4 :class="['text-sm m-0 truncate', !item.isRead ? 'font-bold text-slate-800' : 'font-semibold text-slate-700']">
              {{ item.title }}
            </h4>
            <span class="text-[11px] font-medium text-slate-400 shrink-0">{{ formatRelative(item.createdAt) }}</span>
          </div>

          <p class="text-xs text-slate-500 m-0 mt-1 leading-relaxed">
            {{ item.message }}
          </p>

          <!-- Action Button if any -->
          <div v-if="item.type === 'feedback_received'" class="mt-2.5">
            <button
              type="button"
              class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline border-0 bg-transparent p-0 cursor-pointer"
              @click.stop="openFeedback(item)"
            >
              <span>View feedback</span>
              <i class="pi pi-arrow-right text-[10px]"></i>
            </button>
          </div>
          <div v-else-if="item.actionUrl" class="mt-2.5">
            <router-link :to="item.actionUrl" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline no-underline">
              <span>{{ actionText(item.type) }}</span>
              <i class="pi pi-arrow-right text-[10px]"></i>
            </router-link>
          </div>
        </div>

        <!-- Unread Indicator Dot -->
        <div v-if="!item.isRead" class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0 self-center"></div>

        <!-- Delete Button -->
        <button
          type="button"
          title="Delete notification"
          class="shrink-0 self-center text-slate-300 hover:text-rose-500 bg-transparent border-0 cursor-pointer p-1"
          @click.stop="deleteNotification(item.id)"
        >
          <i class="pi pi-times text-xs"></i>
        </button>

      </div>

      <!-- Empty State -->
      <div v-if="notificationState.items.length === 0" class="p-10 text-center text-slate-400 text-xs">
        No notifications yet
      </div>

    </div>

    <FeedbackDetailDialog :feedback="selectedFeedback" @close="selectedFeedback = null" />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import { notificationState, fetchNotifications, markRead, markAllRead, deleteNotification, clearAllNotifications } from '../../../store/notifications'
import FeedbackDetailDialog from '../../../components/student/FeedbackDetailDialog.vue'

const confirm = useConfirm()
const toast = useToast()

const selectedFeedback = ref(null)

onMounted(fetchNotifications)

function onItemClick(item) {
  if (!item.isRead) markRead(item.id)
  if (item.type === 'feedback_received') openFeedback(item)
}

function onClearAll() {
  confirm.require({
    header: 'Clear all notifications?',
    message: 'This will remove every notification and cannot be undone.',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Clear All',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      await clearAllNotifications()
      toast.add({ severity: 'success', summary: 'Notifications cleared', detail: 'All notifications were removed.', life: 3000 })
    },
  })
}

function openFeedback(item) {
  if (!item.isRead) markRead(item.id)
  selectedFeedback.value = item
}

function actionText(type) {
  switch (type) {
    case 'quiz_published': return 'View course'
    case 'quiz_starting_soon': return 'Take the quiz now'
    case 'quiz_closing_soon': return 'View quiz'
    case 'quiz_ending_soon': return 'Finish the quiz now'
    case 'feedback_received': return 'View feedback'
    default: return 'View'
  }
}

function getTypeStyle(type) {
  switch (type) {
    case 'quiz_published': return 'bg-blue-50 text-blue-600'
    case 'quiz_starting_soon': return 'bg-amber-50 text-amber-600'
    case 'quiz_closing_soon': return 'bg-orange-50 text-orange-600'
    case 'quiz_ending_soon': return 'bg-rose-50 text-rose-600'
    case 'feedback_received': return 'bg-emerald-50 text-emerald-600'
    default: return 'bg-slate-100 text-slate-600'
  }
}

function getIcon(type) {
  switch (type) {
    case 'quiz_published': return 'pi pi-file'
    case 'quiz_starting_soon': return 'pi pi-hourglass'
    case 'quiz_closing_soon': return 'pi pi-exclamation-triangle'
    case 'quiz_ending_soon': return 'pi pi-clock'
    case 'feedback_received': return 'pi pi-comment'
    default: return 'pi pi-bell'
  }
}

function formatRelative(value) {
  if (!value) return ''
  const then = new Date(value.replace(' ', 'T'))
  const diffMs = Date.now() - then.getTime()
  const diffMin = Math.floor(diffMs / 60000)

  if (diffMin < 1) return 'Just now'
  if (diffMin < 60) return `${diffMin} min ago`
  const diffHour = Math.floor(diffMin / 60)
  if (diffHour < 24) return `${diffHour} hour${diffHour === 1 ? '' : 's'} ago`
  const diffDay = Math.floor(diffHour / 24)
  if (diffDay === 1) return 'Yesterday'
  return `${diffDay} days ago`
}
</script>
