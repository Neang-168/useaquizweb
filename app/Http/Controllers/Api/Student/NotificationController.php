<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\QuizTimingNotifier;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Self-heals timing alerts (starting soon / closing in 1hr / closing soon)
        // even if the scheduled command isn't wired to a system cron in this
        // environment — cheap and idempotent thanks to the *_reminder_sent_at guards.
        QuizTimingNotifier::sweep();

        $userId = $request->user()->id;

        $notifications = AppNotification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return response()->json([
            'data' => $notifications->map(fn (AppNotification $n) => $this->transform($n)),
            'unreadCount' => AppNotification::where('user_id', $userId)->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request, AppNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(Request $request, AppNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }

    public function clearAll(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'All notifications cleared.']);
    }

    private function transform(AppNotification $n): array
    {
        return [
            'id' => $n->id,
            'type' => $n->type,
            'title' => $n->title,
            'message' => $n->message,
            'actionUrl' => $n->action_url,
            'isRead' => (bool) $n->read_at,
            'createdAt' => $n->created_at->format('Y-m-d\TH:i:s'),
        ];
    }
}
