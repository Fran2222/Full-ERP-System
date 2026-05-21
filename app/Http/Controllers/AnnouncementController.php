<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnnouncementController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('announcements.view'), 403);

        $announcements = Announcement::with('user.roles')->latest()->get();
        $assets = [];

        return view('announcements.index', compact('announcements', 'assets'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('announcements.create'), 403);

        $assets = [];

        return view('announcements.form', compact('assets'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('announcements.create'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'memo_to' => ['nullable', 'string', 'max:255'],
            'memo_from' => ['nullable', 'string', 'max:255'],
            'memo_date' => ['nullable', 'date'],
            'content' => ['required', 'string'],
            'display_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['memo_to'] = $validated['memo_to'] ?: 'All Employees';
        $validated['memo_from'] = $validated['memo_from'] ?: 'Management';
        $validated['memo_date'] = $validated['memo_date'] ?: now()->toDateString();
        $validated['display_days'] = $validated['display_days'] ?? null;
        $validated['user_id'] = auth()->id();
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? now() : null;
        $validated['expires_at'] = $this->resolveAnnouncementExpiration(
            $validated['published_at'],
            $validated['display_days']
        );

        Announcement::create($validated);

        return redirect()->route('announcements.index')
            ->withSuccess('Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        abort_unless(auth()->user()->can('announcements.edit'), 403);

        $assets = [];

        return view('announcements.form', compact('announcement', 'assets'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        abort_unless(auth()->user()->can('announcements.edit'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'memo_to' => ['nullable', 'string', 'max:255'],
            'memo_from' => ['nullable', 'string', 'max:255'],
            'memo_date' => ['nullable', 'date'],
            'content' => ['required', 'string'],
            'display_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['memo_to'] = $validated['memo_to'] ?: 'All Employees';
        $validated['memo_from'] = $validated['memo_from'] ?: 'Management';
        $validated['memo_date'] = $validated['memo_date'] ?: now()->toDateString();
        $validated['display_days'] = $validated['display_days'] ?? null;
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published']
            ? ($announcement->published_at ?? now())
            : null;
        $validated['expires_at'] = $this->resolveAnnouncementExpiration(
            $validated['published_at'],
            $validated['display_days']
        );

        $announcement->update($validated);

        return redirect()->route('announcements.index')
            ->withSuccess('Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        abort_unless(auth()->user()->can('announcements.delete'), 403);

        $announcement->delete();

        return redirect()->route('announcements.index')
            ->withSuccess('Announcement deleted successfully.');
    }

    private function resolveAnnouncementExpiration($publishedAt, ?int $displayDays): ?Carbon
    {
        if (!$publishedAt || !$displayDays) {
            return null;
        }

        return Carbon::parse($publishedAt)
            ->copy()
            ->addDays($displayDays)
            ->endOfDay();
    }
}
