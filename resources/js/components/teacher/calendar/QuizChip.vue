<template>
  <button
    type="button"
    @click.stop="$emit('click', quiz)"
    :class="[color.bg, color.text, color.border]"
    class="w-full text-left px-1.5 py-0.5 rounded-md border text-[10px] font-semibold truncate flex items-center gap-1 cursor-pointer hover:opacity-80 transition-opacity"
    :title="tooltip"
  >
    <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="color.dot"></span>
    <span class="truncate">{{ quiz.title }}</span>
  </button>
</template>

<script setup>
import { computed } from 'vue'
import { colorForSubject } from './subjectColors'

const props = defineProps({
  quiz: { type: Object, required: true },
})

defineEmits(['click'])

const color = computed(() => colorForSubject(props.quiz.subject?.id))

const tooltip = computed(() => {
  const parts = [props.quiz.title]
  if (props.quiz.subject?.name) parts.push(props.quiz.subject.name)
  if (props.quiz.class?.name) parts.push(props.quiz.class.name)
  return parts.join(' • ')
})
</script>
