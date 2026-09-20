<template>
  <Dialog :visible="visible" @update:visible="(val) => emit('update:visible', val)" modal class="w-full max-w-3xl">
    <template #header>
      <div>
        <h3 class="text-sm font-bold text-slate-800 m-0">Your Answers</h3>
        <p v-if="result" class="text-xs text-slate-400 mt-0.5 mb-0">
          Score:
          <span class="font-bold" :class="result.passed ? 'text-emerald-600' : 'text-rose-600'">
            {{ result.score }}/{{ result.totalPoints }}
          </span>
        </p>
      </div>
    </template>

    <div v-if="loading" class="flex flex-col items-center justify-center py-10">
      <i class="pi pi-spin pi-spinner text-2xl text-[#002060] mb-2"></i>
      <span class="text-xs font-semibold text-slate-500">Loading answers...</span>
    </div>

    <div v-else-if="locked" class="flex flex-col items-center justify-center py-10 text-center gap-2">
      <i class="pi pi-lock text-3xl text-slate-300"></i>
      <p class="text-sm font-bold text-slate-600 m-0">Answers aren't available yet</p>
      <p class="text-xs text-slate-400 m-0 max-w-xs">
        You can view the correct answers once the quiz closes{{ availableAt ? ` on ${formatDateTime(availableAt)}` : '' }}.
      </p>
    </div>

    <div v-else class="space-y-4 max-h-[65vh] overflow-y-auto pr-1">
      <div v-for="(q, idx) in result?.questions || []" :key="q.questionId"
        class="border border-[#D8E7EC] rounded-2xl p-4 space-y-3">
        <div class="flex items-start justify-between gap-2 border-b border-[#D8E7EC] pb-3">
          <div class="flex items-start gap-2">
            <span class="text-xs font-bold text-slate-400 font-mono">Q{{ idx + 1 }}.</span>
            <h4 class="text-sm font-semibold text-[#002060] m-0 leading-relaxed">{{ q.title }}</h4>
          </div>
          <span class="shrink-0 font-bold px-2.5 py-0.5 rounded-full text-[11px] border whitespace-nowrap"
            :class="q.isCorrect ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'">
            {{ q.isCorrect ? 'Correct' : (q.answered ? 'Incorrect' : 'Not answered') }} · {{ q.pointsAwarded }}/{{ q.pointsPossible }} pts
          </span>
        </div>

        <div v-if="q.imageUrl"
          class="ml-6 inline-block rounded-xl border border-[#D8E7EC] bg-[#F8F8F8] overflow-hidden">
          <Image :src="q.imageUrl" :alt="q.imageAlt || ''" preview image-class="max-h-40 object-contain block" />
        </div>

        <!-- Multiple Choice / True False -->
        <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="grid grid-cols-1 gap-2 pl-6">
          <div v-for="(opt, oIdx) in q.options" :key="opt.id" :class="optionClass(opt)"
            class="p-2.5 rounded-xl border text-xs flex items-center gap-2">
            <Image v-if="opt.imageUrl" :src="opt.imageUrl" alt="" preview
              image-class="w-8 h-8 rounded-lg object-cover border border-[#D8E7EC] shrink-0 cursor-pointer" />
            <span class="flex-1">{{ String.fromCharCode(65 + oIdx) }}. {{ opt.text }}</span>
            <i v-if="opt.isCorrect" class="pi pi-check-circle text-[#002060] text-xs shrink-0"></i>
            <i v-else-if="opt.isSelected" class="pi pi-times-circle text-rose-500 text-xs shrink-0"></i>
          </div>
        </div>

        <!-- Matching -->
        <div v-else-if="q.type === 'matching'" class="space-y-2 pl-6">
          <div v-for="pair in q.matchingPairs" :key="pair.id" class="space-y-1.5">
            <div
              class="p-2.5 rounded-xl border text-xs flex items-center gap-2 bg-[#63C7DF]/15 border-[#63C7DF]/50 text-[#002060] font-bold">
              <Image v-if="pair.leftImageUrl" :src="pair.leftImageUrl" alt="" preview
                image-class="w-8 h-8 rounded-lg object-cover border border-[#D8E7EC] shrink-0 cursor-pointer" />
              <span>{{ pair.leftText }}</span>
              <i class="pi pi-arrow-right-arrow-left text-[#63C7DF] text-[10px] shrink-0"></i>
              <Image v-if="pair.rightImageUrl" :src="pair.rightImageUrl" alt="" preview
                image-class="w-8 h-8 rounded-lg object-cover border border-[#D8E7EC] shrink-0 cursor-pointer" />
              <span class="flex-1">{{ pair.rightText }}</span>
              <i class="pi pi-check-circle text-[#002060] text-xs shrink-0"></i>
            </div>
            <div v-if="pair.answered && !pair.isCorrect"
              class="p-2.5 rounded-xl border text-xs flex items-center gap-2 bg-rose-50 border-rose-300 text-rose-700">
              <Image v-if="pair.selectedRightImageUrl" :src="pair.selectedRightImageUrl" alt="" preview
                image-class="w-8 h-8 rounded-lg object-cover border border-rose-200 shrink-0 cursor-pointer" />
              <span class="text-[10px] font-bold uppercase tracking-wide shrink-0">Your answer:</span>
              <span class="flex-1">{{ pair.selectedRightText }}</span>
              <i class="pi pi-times-circle text-rose-500 text-xs shrink-0"></i>
            </div>
            <p v-else-if="!pair.answered" class="text-[11px] text-slate-400 italic m-0 pl-1">Not answered</p>
          </div>
        </div>
      </div>

      <div v-if="!(result?.questions || []).length" class="text-center py-8 text-xs text-slate-400">
        No question detail available.
      </div>
    </div>

    <template #footer>
      <Button label="Close" size="small"
        class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs"
        @click="emit('update:visible', false)" />
    </template>
  </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Image from 'primevue/image'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../api'
import { formatDateTime } from '../../utils/formatDateTime'

const props = defineProps({
  visible: { type: Boolean, default: false },
  submissionId: { type: [Number, String], default: null },
})

const emit = defineEmits(['update:visible'])

const toast = useToast()

const loading = ref(false)
const locked = ref(false)
const availableAt = ref(null)
const result = ref(null)

// Fetched lazily on open, never preloaded with the attempt list — the
// correct-answer payload should only ever leave the server once the quiz's
// deadline has actually passed.
async function fetchAnswers() {
  if (!props.submissionId) return

  loading.value = true
  locked.value = false
  result.value = null

  try {
    const { data } = await api.get(`/student/results/${props.submissionId}/answers`)
    result.value = data
  } catch (error) {
    if (error?.response?.status === 423) {
      locked.value = true
      availableAt.value = error.response.data?.availableAt || null
    } else {
      toast.add({ summary: 'Failed to load answers', ...toastFromError(error) })
    }
  } finally {
    loading.value = false
  }
}

watch(() => [props.visible, props.submissionId], ([visible]) => {
  if (visible) fetchAnswers()
})

// Correct option: same highlight Question Bank uses for its correct answer.
// The student's own wrong pick (when it isn't the correct one) gets a red
// highlight instead; anything else stays neutral.
function optionClass(opt) {
  if (opt.isCorrect) return 'bg-[#63C7DF]/15 border-[#63C7DF]/50 text-[#002060] font-bold'
  if (opt.isSelected) return 'bg-rose-50 border-rose-300 text-rose-700 font-bold'
  return 'bg-[#F8F8F8] border-[#D8E7EC] text-slate-600'
}
</script>
