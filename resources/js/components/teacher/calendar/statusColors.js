// Chip color by quiz status, so a teacher can tell drafts, live and closed
// quizzes apart at a glance. Matches the status badge in QuizDetailModal.
export const STATUS_COLORS = {
  Draft: { bg: 'bg-slate-100', text: 'text-slate-600', border: 'border-slate-300', dot: 'bg-slate-400' },
  Published: { bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', dot: 'bg-emerald-500' },
  Closed: { bg: 'bg-rose-50', text: 'text-rose-700', border: 'border-rose-200', dot: 'bg-rose-500' },
}

export function colorForStatus(status) {
  return STATUS_COLORS[status] || STATUS_COLORS.Draft
}
