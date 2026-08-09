<template>
  <Dialog
    :visible="!!quiz"
    @update:visible="(val) => { if (!val) $emit('close') }"
    modal
    dismissable-mask
    class="w-full max-w-sm"
  >
    <template v-if="quiz" #header>
      <div>
        <span :class="statusBadgeClass(quiz.status)" class="text-[10px] font-bold px-2 py-0.5 rounded-full border">
          {{ quiz.status }}
        </span>
        <h3 class="text-sm font-bold text-slate-800 m-0 mt-1.5">{{ quiz.title }}</h3>
        <p class="text-xs text-slate-500 mt-0.5 mb-0">{{ quiz.subject?.name }} • {{ quiz.class?.name }}</p>
      </div>
    </template>

    <template v-if="quiz">
      <div class="space-y-3 text-xs">
        <div class="grid grid-cols-2 gap-2">
          <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Questions</span>
            <p class="text-sm font-bold text-slate-800 m-0 mt-0.5">{{ quiz.totalQuestions }}</p>
          </div>
          <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Duration</span>
            <p class="text-sm font-bold text-slate-800 m-0 mt-0.5">{{ quiz.duration }} min</p>
          </div>
          <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Pass Mark</span>
            <p class="text-sm font-bold text-slate-800 m-0 mt-0.5">{{ quiz.passMark ? quiz.passMark + '%' : '—' }}</p>
          </div>
          <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Attempts Allowed</span>
            <p class="text-sm font-bold text-slate-800 m-0 mt-0.5">{{ quiz.maxAttempts }}</p>
          </div>
        </div>

        <div class="border border-slate-200 rounded-lg p-2.5">
          <span class="text-[10px] font-bold text-slate-400 uppercase">Available</span>
          <p class="text-xs font-semibold text-slate-700 mt-1 m-0">{{ formattedWindow }}</p>
        </div>
      </div>
    </template>

    <template v-if="quiz" #footer>
      <Button label="Close" size="small" class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="$emit('close')" />
      <Button
        v-if="quiz.assignmentId"
        as="router-link"
        :to="{ name: 'teacher.classWorkspace', params: { assignmentId: quiz.assignmentId }, query: { tab: 'quizzes' } }"
        label="Manage Quiz"
        icon="pi pi-cog"
        size="small"
        class="!bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg !text-xs no-underline"
      />
    </template>
  </Dialog>
</template>

<script setup>
import { computed } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'

const props = defineProps({
  quiz: { type: Object, default: null },
})

defineEmits(['close'])

function statusBadgeClass(status) {
  if (status === 'Published') return 'bg-emerald-50 text-emerald-600 border-emerald-200'
  if (status === 'Draft') return 'bg-slate-100 text-slate-500 border-slate-200'
  return 'bg-rose-50 text-rose-500 border-rose-200'
}

function formatDateTime(value) {
  if (!value) return null
  return value.replace('T', ' ')
}

const formattedWindow = computed(() => {
  if (!props.quiz) return 'No fixed schedule'
  const start = formatDateTime(props.quiz.startAt)
  const end = formatDateTime(props.quiz.endAt)
  if (start && end) return `${start} → ${end}`
  if (start) return `From ${start}`
  if (end) return `Until ${end}`
  return 'No fixed schedule'
})
</script>
