<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));

        $users = User::query()
            ->select(['id', 'username', 'email', 'profile_photo_path', 'reputation_score', 'is_banned', 'role', 'created_at'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(8)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function toggleBan(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot ban your own account.');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'Admin accounts cannot be banned from this panel.');
        }

        $user->forceFill([
            'is_banned' => ! $user->is_banned,
        ])->save();

        $message = $user->is_banned
            ? 'User has been banned successfully.'
            : 'User has been unbanned successfully.';

        return back()->with('success', $message);
    }
}
