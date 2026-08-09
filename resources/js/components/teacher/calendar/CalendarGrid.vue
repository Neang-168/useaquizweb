<template>
  <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
    <!-- Weekday header -->
    <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200">
      <div
        v-for="label in weekdayLabels"
        :key="label"
        class="py-2 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider"
      >
        {{ label }}
      </div>
    </div>

    <!-- Day grid -->
    <div class="grid grid-cols-7">
      <DayCell
        v-for="day in days"
        :key="day.toISOString()"
        :date="day"
        :quizzes="quizzesForDay(day)"
        :is-today="isToday(day)"
        :is-current-month="viewMode === 'week' || day.getMonth() === referenceDate.getMonth()"
        :view-mode="viewMode"
        :max-visible="viewMode === 'week' ? 6 : 3"
        @quiz-click="$emit('quiz-click', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import DayCell from './DayCell.vue'

const props = defineProps({
  viewMode: { type: String, default: 'month' }, // 'month' | 'week'
  referenceDate: { type: Date, required: true },
  quizzes: { type: Array, default: () => [] },
})

defineEmits(['quiz-click'])

const weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

function toDateKey(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

function buildMonthDays(referenceDate) {
  const year = referenceDate.getFullYear()
  const month = referenceDate.getMonth()
  const firstOfMonth = new Date(year, month, 1)
  const gridStart = new Date(year, month, 1 - firstOfMonth.getDay())

  return Array.from({ length: 42 }, (_, i) => {
    const d = new Date(gridStart)
    d.setDate(gridStart.getDate() + i)
    return d
  })
}

function buildWeekDays(referenceDate) {
  const start = new Date(referenceDate)
  start.setDate(referenceDate.getDate() - referenceDate.getDay())

  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(start)
    d.setDate(start.getDate() + i)
    return d
  })
}

const days = computed(() => {
  return props.viewMode === 'week' ? buildWeekDays(props.referenceDate) : buildMonthDays(props.referenceDate)
})

function isToday(date) {
  return toDateKey(date) === toDateKey(new Date())
}

function quizzesForDay(date) {
  const key = toDateKey(date)
  return props.quizzes.filter(q => key >= q.startDate && key <= q.endDate)
}
</script>
