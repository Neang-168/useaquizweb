<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class AppNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'quiz_id',
        'feedback_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function notifyQuizPublished(Quiz $quiz): void
    {
        static::notifyClassStudents($quiz, 'quiz_published', 'New quiz published', "\"{$quiz->title}\" is now available in {$quiz->subject?->name}.");
    }

    public static function notifyQuizStartingSoon(Quiz $quiz): void
    {
        static::notifyClassStudents($quiz, 'quiz_starting_soon', 'Quiz starting soon', "\"{$quiz->title}\" ({$quiz->subject?->name}) opens in about 5 minutes.");
    }

    public static function notifyFeedback(Feedback $feedback): void
    {
        $userId = StudentProfile::where('id', $feedback->student_profile_id)->value('user_id');

        if (! $userId) {
            return;
        }

        $feedback->loadMissing('teacherProfile.user');
        $teacherName = $feedback->teacherProfile?->user?->full_name ?: 'Your teacher';

        static::create([
            'user_id' => $userId,
            'type' => 'feedback_received',
            'title' => "New message from {$teacherName}",
            'message' => $feedback->message,
            'action_url' => '/student/dashboard',
            'feedback_id' => $feedback->id,
        ]);
    }

    private static function notifyClassStudents(Quiz $quiz, string $type, string $title, string $message): void
    {
        $userIds = DB::table('class_student')
            ->where('class_id', $quiz->class_id)
            ->where('status', 'Active')
            ->join('student_profiles', 'student_profiles.id', '=', 'class_student.student_profile_id')
            ->pluck('student_profiles.user_id')
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $actionUrl = "/student/courses/{$quiz->class_id}/{$quiz->subject_id}";
        $now = now();

        $rows = $userIds->map(fn (int $userId) => [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'quiz_id' => $quiz->id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('app_notifications')->insert($rows);
    }
}
