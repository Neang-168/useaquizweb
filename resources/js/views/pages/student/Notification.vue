<template>
  <div class="max-w-3xl mx-auto space-y-6 font-sans">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-slate-800 m-0">ការជូនដំណឹង (Notifications)</h2>
        <p class="text-xs text-slate-400 m-0 mt-1">រាល់ការរំលឹក និងព័ត៌មានសំខាន់ៗសម្រាប់អ្នក</p>
      </div>

      <!-- Mark all as read button -->
      <button @click="markAllAsRead" class="text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-xl transition-all border-0 cursor-pointer flex items-center gap-1.5">
        <i class="pi pi-check-circle"></i>
        <span>ដៅថាបានអានទាំងអស់</span>
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
        មិនទាន់មានការជូនដំណឹងនៅឡើយទេ
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
    title: 'ការរំលឹក៖ ជិតផុតកំណត់ធ្វើ Quiz',
    message: 'Quiz "Vue 3 Options API" ក្នុងមុខវិជ្ជា Web Frontend Development នឹងផុតកំណត់នៅថ្ងៃនេះ ម៉ោង 11:59 PM។',
    createdAt: '១០ នាទីមុន',
    isRead: false,
    actionText: 'ចូលធ្វើ Quiz ឥឡូវនេះ',
    actionUrl: '/student/quizzes?id=101'
  },
  {
    id: 2,
    type: 'grade_released',
    title: 'លទ្ធផលពិន្ទុត្រូវ បានចេញហើយ',
    message: 'គ្រូបង្រៀនបានបញ្ចូលពិន្ទុ "MySQL Normalization Quiz" រួចរាល់ហើយ។ ពិន្ទុរបស់អ្នកគឺ 85/100។',
    createdAt: '២ ម៉ោងមុន',
    isRead: false,
    actionText: 'ពិនិត្យមើលចម្លើយ',
    actionUrl: '/student/history/98'
  },
  {
    id: 3,
    type: 'announcement',
    title: 'ការផ្លាស់ប្តូរម៉ោងសិក្សា',
    message: 'ថ្នាក់ Database Management Systems (M1) នៅថ្ងៃស្អែក ត្រូវប្តូរម៉ោងសិក្សាទៅម៉ោង 2:00 PM វិញ។',
    createdAt: 'ម្សិលមិញ',
    isRead: true,
    actionText: null,
    actionUrl: null
  },
  {
    id: 4,
    type: 'new_exam',
    title: 'មាន Quiz ថ្មីត្រូវបានបន្ថែម',
    message: 'លោកគ្រូ មាស សុភ័ក្ត្រ បានបន្ថែម Quiz ថ្មី "Java OOP Concepts" ក្នុងមុខវិជ្ជា Java Programming។',
    createdAt: '២ ថ្ងៃមុន',
    isRead: true,
    actionText: 'មើលកាលវិភាគប្រឡង',
    actionUrl: '/student/quizzes'
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