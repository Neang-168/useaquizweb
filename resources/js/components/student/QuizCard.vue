<template>
  <div class="bg-white rounded-2xl border border-[#D8E7EC] shadow-xs hover:shadow-lg hover:border-[#63C7DF] transition-all flex flex-col justify-between overflow-hidden group">

    <div class="p-4 space-y-3">
      <!-- Badges -->
      <div class="flex items-center justify-between gap-2 flex-wrap">
        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shadow-xs" :class="statusBadge.class">
          {{ statusBadge.label }}
        </span>
        <span v-if="showSubject && exam.subject" class="inline-flex items-center text-[10px] font-bold px-2 py-1 rounded-md bg-[#002060]/5 text-[#002060] border border-[#002060]/10">
          <i class="pi pi-bookmark text-[9px] mr-1.5"></i>{{ exam.subject }}
        </span>
      </div>

      <!-- Title + description -->
      <div>
        <h3 class="text-sm font-bold text-slate-800 group-hover:text-[#002060] transition-colors m-0 leading-snug">
          {{ exam.title }}
        </h3>
        <p class="text-xs text-slate-500 m-0 mt-1 leading-relaxed line-clamp-1">
          {{ exam.description || 'No description provided for this quiz.' }}
        </p>
      </div>

      <!-- Info grid -->
      <div class="grid grid-cols-3 gap-1.5 text-center">
        <div class="bg-[#F8F8F8] p-2 rounded-lg border border-slate-100">
          <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Start Date</span>
          <span class="text-[11px] font-bold text-[#002060]">{{ exam.startAt ? formatFullDate(exam.startAt) : '—' }}</span>
        </div>
        <div class="bg-[#F8F8F8] p-2 rounded-lg border border-slate-100">
          <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">End Date</span>
          <span class="text-[11px] font-bold text-[#002060]">{{ exam.endAt ? formatFullDate(exam.endAt) : '—' }}</span>
        </div>
        <div class="bg-[#F8F8F8] p-2 rounded-lg border border-slate-100">
          <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Duration</span>
          <span class="text-[11px] font-bold text-[#002060]">{{ exam.duration }} min</span>
        </div>
        <div class="bg-[#F8F8F8] p-2 rounded-lg border border-slate-100">
          <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Questions</span>
          <span class="text-[11px] font-bold text-[#002060]">{{ exam.totalQuestions }}</span>
        </div>
        <div class="bg-[#F8F8F8] p-2 rounded-lg border border-slate-100">
          <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Points</span>
          <span class="text-[11px] font-bold text-[#002060]">{{ exam.totalPoints }}</span>
        </div>
        <div class="bg-[#F8F8F8] p-2 rounded-lg border border-slate-100">
          <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Pass Mark</span>
          <span class="text-[11px] font-bold text-[#002060]">{{ exam.passMark }}%</span>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-4 py-3 bg-[#F8F8F8] border-t border-[#D8E7EC] flex items-center justify-between gap-2 flex-wrap">
      <div class="flex items-center gap-1.5">
        <div class="w-2 h-2 rounded-full bg-[#E4AC40]"></div>
        <span class="text-xs font-bold text-slate-600">Attempts {{ exam.attemptsUsed }}/{{ exam.maxAttempts }}</span>
      </div>

      <div class="flex items-center gap-2">
        <router-link v-if="exam.attemptsUsed > 0" :to="historyRoute"
          class="px-2.5 py-1.5 bg-white hover:bg-[#D8E7EC]/50 text-[#002060] font-bold text-xs rounded-lg transition-all no-underline border border-[#D8E7EC] flex items-center gap-1.5">
          <i class="pi pi-history text-xs"></i> History
        </router-link>

        <button v-if="exam.isUpcoming" disabled type="button" title="This quiz has not opened yet"
          class="px-3 py-1.5 bg-slate-100 text-slate-400 font-bold text-xs rounded-lg cursor-not-allowed border border-slate-200 flex items-center gap-1.5">
          <i class="pi pi-lock text-xs"></i> Locked
        </button>
        <button v-else-if="exam.isClosed" disabled type="button" title="This quiz is closed and no longer accepting attempts"
          class="px-3 py-1.5 bg-slate-100 text-slate-400 font-bold text-xs rounded-lg cursor-not-allowed border border-slate-200 flex items-center gap-1.5">
          <i class="pi pi-calendar-times text-xs"></i> Closed
        </button>
        <button v-else-if="exam.attemptsExhausted && exam.status !== 'in_progress'" disabled type="button" title="You have used all of your attempts for this quiz"
          class="px-3 py-1.5 bg-slate-100 text-slate-400 font-bold text-xs rounded-lg cursor-not-allowed border border-slate-200 flex items-center gap-1.5">
          <i class="pi pi-lock text-xs"></i> No Attempts Left
        </button>
        <button v-else type="button" @click="onStartClick" class="px-3 py-1.5 bg-[#002060] hover:bg-[#001848] text-white font-bold text-xs rounded-lg transition-all border-0 cursor-pointer shadow-md shadow-[#002060]/20 flex items-center gap-1.5">
          <span>{{ exam.status === 'in_progress' ? 'Continue' : 'Start Exam' }}</span>
          <i class="pi pi-arrow-right text-xs text-[#E4AC40]"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useConfirm } from 'primevue/useconfirm'
import { formatFullDate } from '../../utils/formatDateTime'

const props = defineProps({
  exam: { type: Object, required: true },
  showSubject: { type: Boolean, default: false },
  classId: { type: [Number, String], default: null },
  subjectId: { type: [Number, String], default: null },
})

const router = useRouter()
const confirm = useConfirm()

const statusBadge = computed(() => {
  if (props.exam.isUpcoming) return { label: 'Upcoming', class: 'bg-[#D8E7EC]/60 text-[#002060]' }
  if (props.exam.isClosed) {
    return props.exam.attemptsUsed > 0
      ? { label: 'Completed', class: 'bg-emerald-100 text-emerald-700' }
      : { label: 'Missed', class: 'bg-red-100 text-[#D71818]' }
  }
  if (props.exam.status === 'in_progress') return { label: 'In Progress', class: 'bg-amber-100 text-amber-700' }
  if (props.exam.attemptsUsed > 0) return { label: 'Completed', class: 'bg-emerald-100 text-emerald-700' }
  return { label: 'Not Started', class: 'bg-[#002060] text-white' }
})

const historyRoute = computed(() => ({
  name: 'student.quizHistory',
  params: { quizId: props.exam.id },
  query: props.classId && props.subjectId ? { classId: props.classId, subjectId: props.subjectId } : {},
}))

function onStartClick() {
  if (props.exam.status === 'in_progress') {
    router.push(`/student/take-quiz?id=${props.exam.id}`)
    return
  }

  confirm.require({
    header: 'Start this quiz?',
    message: `Duration: ${props.exam.duration} minutes, ${props.exam.totalQuestions} questions. Once started, the timer begins immediately and cannot be paused.`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Start Exam',
    rejectLabel: 'Cancel',
    accept: () => {
      router.push(`/student/take-quiz?id=${props.exam.id}`)
    },
  })
}
</script>
