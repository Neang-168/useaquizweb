<template>
  <div class="max-w-3xl mx-auto space-y-6 font-sans">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-slate-800 m-0">Notifications</h2>
        <p class="text-xs text-slate-400 m-0 mt-1">All reminders and important updates for you</p>
      </div>

      <!-- Mark all as read button -->
      <button @click="markAllAsRead" class="text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-xl transition-all border-0 cursor-pointer flex items-center gap-1.5">
        <i class="pi pi-check-circle"></i>
        <span>Mark all as read</span>
      </button>
    </div>

    <!-- ======= NOTIFICATION LIST ======= -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs divide-y divide-slate-100">
      
      <div v-for="item in notifications" :key="item.id" 
        :class="['p-4 transition-all flex items-start gap-4 hover:bg-slate-50/80 cursor-pointer', 
          !item.isRead ? 'bg-blue-50/40' : '']"
        @click="item.isRead = true">
        
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
            <span class="text-[11px] font-medium text-slate-400 shrink-0">{{ item.createdAt }}</span>
          </div>

          <p class="text-xs text-slate-500 m-0 mt-1 leading-relaxed">
            {{ item.message }}
          </p>

          <!-- Action Button if any -->
          <div v-if="item.actionUrl" class="mt-2.5">
            <router-link :to="item.actionUrl" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline no-underline">
              <span>{{ item.actionText }}</span>
              <i class="pi pi-arrow-right text-[10px]"></i>
            </router-link>
          </div>
        </div>

        <!-- Unread Indicator Dot -->
        <div v-if="!item.isRead" class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0 self-center"></div>

      </div>

      <!-- Empty State -->
      <div v-if="notifications.length === 0" class="p-10 text-center text-slate-400 text-xs">
        No notifications yet
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'

const notifications = ref([
  {
    id: 1,
    type: 'exam_reminder',
    title: 'Reminder: Quiz deadline approaching',
    message: 'The "Vue 3 Options API" quiz in Web Frontend Development is due today at 11:59 PM.',
    createdAt: '10 minutes ago',
    isRead: false,
    actionText: 'Take the quiz now',
    actionUrl: '/student/myexam'
  },
  {
    id: 2,
    type: 'grade_released',
    title: 'Your grade has been released',
    message: 'Your teacher has finished grading "MySQL Normalization Quiz". Your score is 85/100.',
    createdAt: '2 hours ago',
    isRead: false,
    actionText: 'View your result',
    actionUrl: '/student/gradeHistory'
  },
  {
    id: 3,
    type: 'announcement',
    title: 'Class schedule change',
    message: 'The Database Management Systems (M1) class tomorrow has been moved to 2:00 PM.',
    createdAt: 'Yesterday',
    isRead: true,
    actionText: null,
    actionUrl: null
  },
  {
    id: 4,
    type: 'new_exam',
    title: 'A new quiz has been added',
    message: 'Your teacher added a new quiz "Java OOP Concepts" in Java Programming.',
    createdAt: '2 days ago',
    isRead: true,
    actionText: 'View exam schedule',
    actionUrl: '/student/myexam'
  }
])

function markAllAsRead() {
  notifications.value.forEach(item => item.isRead = true)
}

function getTypeStyle(type) {
  switch (type) {
    case 'exam_reminder': return 'bg-amber-50 text-amber-600'
    case 'grade_released': return 'bg-emerald-50 text-emerald-600'
    case 'new_exam': return 'bg-blue-50 text-blue-600'
    default: return 'bg-slate-100 text-slate-600'
  }
}

function getIcon(type) {
  switch (type) {
    case 'exam_reminder': return 'pi pi-clock'
    case 'grade_released': return 'bg-emerald-50 pi pi-check-circle'
    case 'new_exam': return 'pi pi-file'
    default: return 'pi pi-bell'
  }
}
</script>