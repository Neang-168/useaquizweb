<template>
  <div
    class="border border-slate-100 flex flex-col p-1.5 gap-1 bg-white"
    :class="[
      isCurrentMonth ? '' : 'bg-slate-50/60',
      compact
        ? (viewMode === 'week' ? 'min-h-[6rem]' : 'min-h-[3.5rem]')
        : (viewMode === 'week' ? 'min-h-[10rem]' : 'min-h-[6rem]'),
    ]"
  >
    <div class="flex items-center justify-between">
      <span
        class="text-[11px] font-bold w-5 h-5 rounded-full flex items-center justify-center"
        :class="[
          isToday ? 'bg-indigo-600 text-white' : isCurrentMonth ? 'text-slate-600' : 'text-slate-300',
        ]"
      >
        {{ date.getDate() }}
      </span>
      <span v-if="viewMode === 'week'" class="text-[9px] font-semibold text-slate-400 uppercase">
        {{ weekdayLabel }}
      </span>
    </div>

    <div class="flex-1 space-y-1 overflow-hidden">
      <QuizChip
        v-for="quiz in visibleQuizzes"
        :key="quiz.id"
        :quiz="quiz"
        @click="$emit('quiz-click', quiz)"
      />

      <button
        v-if="hasOverflow && !expanded"
        type="button"
        @click.stop="expanded = true"
        class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 cursor-pointer px-1"
      >
        +{{ quizzes.length - maxVisible }} more
      </button>
      <button
        v-else-if="hasOverflow && expanded"
        type="button"
        @click.stop="expanded = false"
        class="text-[10px] font-bold text-slate-400 hover:text-slate-600 cursor-pointer px-1"
      >
        Show less
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import QuizChip from './QuizChip.vue'

const props = defineProps({
  date: { type: Date, required: true },
  quizzes: { type: Array, default: () => [] },
  isToday: { type: Boolean, default: false },
  isCurrentMonth: { type: Boolean, default: true },
  viewMode: { type: String, default: 'month' },
  maxVisible: { type: Number, default: 3 },
  compact: { type: Boolean, default: false },
})

defineEmits(['quiz-click'])

const expanded = ref(false)

const hasOverflow = computed(() => props.quizzes.length > props.maxVisible)
const visibleQuizzes = computed(() => (expanded.value ? props.quizzes : props.quizzes.slice(0, props.maxVisible)))

const weekdayLabel = computed(() => props.date.toLocaleDateString('en-US', { weekday: 'short' }))
</script>
