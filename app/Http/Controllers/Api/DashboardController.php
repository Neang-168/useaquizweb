<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * SuperAdmin overview: headline totals, role breakdown, signup activity,
     * and the most recently created accounts.
     */
    public function index()
    {
        $usersByRole = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->select('roles.name as role', DB::raw('count(*) as count'))
            ->groupBy('roles.name')
            ->orderByDesc('count')
            ->get();

        // Teacher/Student headline counts come from role assignment (not the
        // teacher_profiles/student_profiles tables) so they always agree
        // with the role-breakdown chart below.
        $countForRole = fn (string $role) => (int) ($usersByRole->firstWhere('role', $role)->count ?? 0);

        $totals = [
            'users' => User::count(),
            'teachers' => $countForRole('Teacher'),
            'students' => $countForRole('Student'),
            'classes' => Classroom::count(),
            'subjects' => Subject::count(),
            'faculties' => Faculty::count(),
        ];

        $statusBreakdown = [
            'active' => User::where('status', true)->count(),
            'inactive' => User::where('status', false)->count(),
        ];

        $since = Carbon::today()->subDays(6);
        $rawSignups = User::query()
            ->where('created_at', '>=', $since)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date');

        $signupsLast7Days = collect(range(0, 6))->map(function ($daysAgo) use ($rawSignups) {
            $date = Carbon::today()->subDays(6 - $daysAgo)->toDateString();

            return [
                'date' => $date,
                'label' => Carbon::parse($date)->format('D'),
                'count' => (int) ($rawSignups[$date] ?? 0),
            ];
        })->values();

        $recentUsers = User::query()
            ->with('role')
            ->orderByDesc('created_at')
            ->take(8)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => trim("{$user->first_name} {$user->last_name}"),
                'email' => $user->email,
                'role' => $user->role?->name,
                'avatar_url' => $user->avatar_url,
                'status' => $user->status,
                'created_at' => $user->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'totals' => $totals,
            'users_by_role' => $usersByRole,
            'status_breakdown' => $statusBreakdown,
            'signups_last_7_days' => $signupsLast7Days,
            'recent_users' => $recentUsers,
        ]);
    }
}
