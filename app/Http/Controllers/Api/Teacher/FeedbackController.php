<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuestionMatchingPair;
use App\Models\QuestionOption;
use App\Models\QuizSubmission;
use App\Models\SubmissionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

                return [
                    'questionId' => $question->id,
                    'title' => $question->title,
                    'type' => $question->type,
                    'imageUrl' => $question->image_path ? Storage::disk('public')->url($question->image_path) : null,
                    'imageAlt' => $question->image_alt,
                    'pointsPossible' => $quiz->pointsFor($question),
                    'pointsAwarded' => $answer->awarded_score ?? 0,
                    'isCorrect' => (bool) ($answer->is_correct ?? false),
                    'answered' => $answer !== null,
                    ...$this->describeAnswer($question, $answer),
                ];
            })->values();

            return [
                'submissionId' => $submission->id,
                'username' => $submission->studentProfile?->user?->username,
                'name' => trim($submission->studentProfile?->user?->first_name . ' ' . $submission->studentProfile?->user?->last_name),
                'nameKh' => $submission->studentProfile?->user?->name_kh,
                'score' => $score,
                'totalPoints' => $submissionTotalPoints,
                'scorePercentage' => round($percentage),
                'passMark' => $submissionPassMark,
                'passed' => $percentage >= $submissionPassMark,
                'hasFeedbackSent' => $submission->feedback->isNotEmpty(),
                'questions' => $questions,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Structured "what the student picked" vs "what was correct" for one
     * question — an `options` array (multiple_choice/true_false) or a
     * `matchingPairs` array (matching), each entry carrying enough detail
     * (text, image, correct/selected flags) for the feedback "View Answer"
     * dialog to render the same option-card look as the Question Bank.
     */
    private function describeAnswer(Question $question, ?SubmissionAnswer $answer): array
    {
        return match ($question->type) {
            'true_false', 'multiple_choice' => [
                'options' => $this->describeOptions($question, $answer),
            ],
            'matching' => [
                'matchingPairs' => $this->describeMatchingPairs($question, $answer),
            ],
            default => [],
        };
    }

    private function describeOptions(Question $question, ?SubmissionAnswer $answer): array
    {
        $selectedIds = $question->type === 'true_false'
            ? array_filter([$answer?->selected_option_id])
            : ($answer?->selectedOptions->pluck('question_option_id')->all() ?? []);

        return $question->options->sortBy('position')->values()
            ->map(fn (QuestionOption $option) => [
                'id' => $option->id,
                'text' => $option->text,
                'imageUrl' => $option->image_path ? Storage::disk('public')->url($option->image_path) : null,
                'isCorrect' => (bool) $option->is_correct,
                'isSelected' => in_array($option->id, $selectedIds, true),
            ])->all();
    }

    private function describeMatchingPairs(Question $question, ?SubmissionAnswer $answer): array
    {
        $matchesByLeftPair = $answer?->matches->keyBy('left_pair_id') ?? collect();

        return $question->matchingPairs->sortBy('position')->values()
            ->map(function (QuestionMatchingPair $pair) use ($matchesByLeftPair) {
                $match = $matchesByLeftPair->get($pair->id);
                $selected = $match?->selectedRightPair;

                return [
                    'id' => $pair->id,
                    'leftText' => $pair->left_text,
                    'leftImageUrl' => $pair->left_image_path ? Storage::disk('public')->url($pair->left_image_path) : null,
                    'rightText' => $pair->right_text,
                    'rightImageUrl' => $pair->right_image_path ? Storage::disk('public')->url($pair->right_image_path) : null,
                    'answered' => $match !== null,
                    'isCorrect' => $match !== null && $match->selected_right_pair_id === $pair->id,
                    'selectedRightText' => $selected?->right_text,
                    'selectedRightImageUrl' => $selected?->right_image_path ? Storage::disk('public')->url($selected->right_image_path) : null,
                ];
            })->all();
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
