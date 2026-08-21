<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\SubmissionAnswer;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Get the students of a quiz along with their score, feedback status, and
     * a per-question correctness/points breakdown (for the feedback tree table).
     */
    public function index(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher || $quiz->teacher_profile_id !== $teacher->id) {
            abort(403);
        }

        $submissions = $quiz->submissions()
            ->with([
                'studentProfile.user',
                'feedback',
                'answers.selectedOption',
                'answers.selectedOptions.option',
                'answers.matches.leftPair',
                'answers.matches.selectedRightPair',
            ])
            ->orderBy('submitted_at')
            ->get();

        $quiz->load(['questions', 'orderedQuestions.options', 'orderedQuestions.matchingPairs']);
        $passMark = $quiz->pass_mark ?? 50;
        $totalPoints = $quiz->computeTotalPoints();
        $orderedQuestions = $quiz->orderedQuestions;

        $data = $submissions->map(function (QuizSubmission $submission) use ($passMark, $totalPoints, $quiz, $orderedQuestions) {
            // Prefer the submission's own score snapshot (taken at grading
            // time) so editing the quiz's questions/points later doesn't
            // retroactively change an already-graded attempt's pass/fail.
            $submissionTotalPoints = $submission->total_points ?? $totalPoints;
            $submissionPassMark = $submission->pass_mark ?? $passMark;
            $score = $submission->mcq_score + ($submission->essay_score ?? 0);
            $percentage = $submissionTotalPoints > 0 ? ($score / $submissionTotalPoints) * 100 : 0;

            $answersByQuestion = $submission->answers->keyBy('question_id');

            $questions = $orderedQuestions->map(function (Question $question) use ($answersByQuestion, $quiz) {
                $answer = $answersByQuestion->get($question->id);
                $described = $this->describeAnswer($question, $answer);

                return [
                    'questionId' => $question->id,
                    'title' => $question->title,
                    'type' => $question->type,
                    'pointsPossible' => $quiz->pointsFor($question),
                    'pointsAwarded' => $answer->awarded_score ?? 0,
                    'isCorrect' => (bool) ($answer->is_correct ?? false),
                    'answered' => $answer !== null,
                    'studentAnswer' => $described['studentAnswer'],
                    'correctAnswer' => $described['correctAnswer'],
                ];
            })->values();

            return [
                'submissionId' => $submission->id,
                'studentId' => $submission->studentProfile?->student_code,
                'name' => trim($submission->studentProfile?->user?->first_name . ' ' . $submission->studentProfile?->user?->last_name),
                'score' => $score,
                'totalPoints' => $submissionTotalPoints,
                'passMark' => $submissionPassMark,
                'passed' => $percentage >= $submissionPassMark,
                'hasFeedbackSent' => $submission->feedback->isNotEmpty(),
                'questions' => $questions,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Human-readable "what the student answered" vs "what the correct
     * answer was" for one question, used by the feedback tree table to show
     * incorrect (and any) answers in context.
     */
    private function describeAnswer(Question $question, ?SubmissionAnswer $answer): array
    {
        return match ($question->type) {
            'true_false' => [
                'studentAnswer' => $answer?->selectedOption?->text ?? 'No answer',
                'correctAnswer' => $question->options->firstWhere('is_correct', true)?->text ?? '—',
            ],
            'multiple_choice' => [
                'studentAnswer' => $answer && $answer->selectedOptions->isNotEmpty()
                    ? $answer->selectedOptions->map(fn ($selected) => $selected->option?->text)->filter()->implode(', ')
                    : 'No answer',
                'correctAnswer' => $question->options->where('is_correct', true)->pluck('text')->implode(', ') ?: '—',
            ],
            'matching' => [
                'studentAnswer' => $answer && $answer->matches->isNotEmpty()
                    ? $answer->matches->map(fn ($match) => sprintf(
                        '%s → %s',
                        $match->leftPair?->left_text ?? '?',
                        $match->selectedRightPair?->right_text ?? 'No match'
                    ))->implode('; ')
                    : 'No answer',
                'correctAnswer' => $question->matchingPairs->map(fn ($pair) => sprintf('%s → %s', $pair->left_text, $pair->right_text))->implode('; ') ?: '—',
            ],
            default => ['studentAnswer' => '—', 'correctAnswer' => '—'],
        };
    }

    /**
     * Send feedback to a student for a specific submission.
     */
    public function store(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        $validated = $request->validate([
            'quiz_submission_id' => ['required', 'exists:quiz_submissions,id'],
            'message' => ['required', 'string'],
        ]);

        $submission = QuizSubmission::with('quiz')->findOrFail($validated['quiz_submission_id']);

        if (! $teacher || $submission->quiz?->teacher_profile_id !== $teacher->id) {
            abort(403);
        }

        $feedback = Feedback::create([
            'teacher_profile_id' => $teacher->id,
            'student_profile_id' => $submission->student_profile_id,
            'quiz_submission_id' => $submission->id,
            'message' => $validated['message'],
            'sent_at' => now(),
        ]);

        AppNotification::notifyFeedback($feedback);

        return response()->json([
            'message' => 'Feedback sent successfully.',
            'feedback' => [
                'id' => $feedback->id,
                'sentAt' => $feedback->sent_at->format('Y-m-d H:i'),
            ],
        ], 201);
    }
}
