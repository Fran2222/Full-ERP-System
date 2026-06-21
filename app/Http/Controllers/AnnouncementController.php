<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnnouncementController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('announcements.view'), 403);

        $announcements = Announcement::with(['user.roles', 'recipients.employeeProfile'])->latest()->get();
        $assets = [];

        return view('announcements.index', compact('announcements', 'assets'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('announcements.create'), 403);

        $assets = [];
        $recipientEmployees = $this->announcementRecipients();

        return view('announcements.form', compact('assets', 'recipientEmployees'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('announcements.create'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'memo_to' => ['nullable', 'string', 'max:255'],
            'memo_to_mode' => ['nullable', 'in:all,selected'],
            'memo_to_user_ids' => ['nullable', 'array'],
            'memo_to_user_ids.*' => ['nullable', 'integer', 'exists:users,id'],
            'memo_to_user_id' => ['nullable', 'exists:users,id'], // legacy fallback
            'memo_from' => ['nullable', 'string', 'max:255'],
            'memo_date' => ['nullable', 'date'],
            'content' => ['required', 'string'],
            'display_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $recipientIds = $this->selectedAnnouncementRecipientIds($request);
        $validated = $this->normalizeAnnouncementRecipients($validated, $recipientIds);
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

        unset($validated['memo_to_mode'], $validated['memo_to_user_ids']);

        $announcement = Announcement::create($validated);
        $announcement->recipients()->sync($recipientIds);

        return redirect()->route('announcements.index')
            ->withSuccess('Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        abort_unless(auth()->user()->can('announcements.edit'), 403);

        $assets = [];
        $recipientEmployees = $this->announcementRecipients();
        $announcement->load('recipients');

        return view('announcements.form', compact('announcement', 'assets', 'recipientEmployees'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        abort_unless(auth()->user()->can('announcements.edit'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'memo_to' => ['nullable', 'string', 'max:255'],
            'memo_to_mode' => ['nullable', 'in:all,selected'],
            'memo_to_user_ids' => ['nullable', 'array'],
            'memo_to_user_ids.*' => ['nullable', 'integer', 'exists:users,id'],
            'memo_to_user_id' => ['nullable', 'exists:users,id'], // legacy fallback
            'memo_from' => ['nullable', 'string', 'max:255'],
            'memo_date' => ['nullable', 'date'],
            'content' => ['required', 'string'],
            'display_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $recipientIds = $this->selectedAnnouncementRecipientIds($request);
        $validated = $this->normalizeAnnouncementRecipients($validated, $recipientIds);
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

        unset($validated['memo_to_mode'], $validated['memo_to_user_ids']);

        $announcement->update($validated);
        $announcement->recipients()->sync($recipientIds);

        return redirect()->route('announcements.index')
            ->withSuccess('Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        abort_unless(auth()->user()->can('announcements.delete'), 403);

        $announcement->recipients()->detach();
        $announcement->delete();

        return redirect()->route('announcements.index')
            ->withSuccess('Announcement deleted successfully.');
    }

    private function announcementRecipients()
    {
        return User::with(['employeeProfile', 'branch', 'department'])
            ->whereHas('employeeProfile')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereRaw("LOWER(TRIM(COALESCE(status, 'active'))) = ?", ['active']);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    private function selectedAnnouncementRecipientIds(Request $request): array
    {
        $mode = $request->input('memo_to_mode', 'all');

        $ids = collect($request->input('memo_to_user_ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '' && $id !== 'all')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        // Legacy fallback for older form submissions.
        if ($ids->isEmpty() && $request->filled('memo_to_user_id')) {
            $ids->push((int) $request->input('memo_to_user_id'));
        }

        // Important: do not ignore checked employees just because the hidden mode
        // accidentally stayed as "all" in the browser. If employee IDs were posted,
        // treat the announcement as selected-recipient only.
        if ($ids->isNotEmpty()) {
            return $ids->all();
        }

        if ($mode !== 'selected') {
            return [];
        }

        return [];
    }

    private function normalizeAnnouncementRecipients(array $validated, array $recipientIds): array
    {
        if (! empty($recipientIds)) {
            $recipients = User::whereIn('id', $recipientIds)->get();

            $names = $recipients
                ->map(fn ($recipient) => $this->formatRecipientName($recipient))
                ->filter()
                ->values();

            $validated['memo_to_user_id'] = $recipientIds[0] ?? null; // keeps old single-recipient column compatible

            if ($names->count() === 1) {
                $validated['memo_to'] = $names->first();
            } elseif ($names->count() <= 3) {
                $validated['memo_to'] = $names->implode(', ');
            } else {
                $validated['memo_to'] = $names->count() . ' Employees';
            }

            return $validated;
        }

        $validated['memo_to'] = 'All Employees';
        $validated['memo_to_user_id'] = null;

        return $validated;
    }

    private function formatRecipientName(User $recipient): string
    {
        $middleInitial = trim((string) ($recipient->middle_name ?? ''));
        $middleInitial = $middleInitial !== '' ? strtoupper(substr($middleInitial, 0, 1)) . '.' : '';

        $name = collect([
            trim((string) ($recipient->last_name ?? '')),
            trim(collect([
                trim((string) ($recipient->first_name ?? '')),
                $middleInitial,
            ])->filter()->implode(' ')),
        ])->filter()->implode(', ');

        return $name ?: trim($recipient->full_name ?: $recipient->email);
    }

    private function resolveAnnouncementExpiration($publishedAt, ?int $displayDays): ?Carbon
    {
        if (! $publishedAt || ! $displayDays) {
            return null;
        }

        return Carbon::parse($publishedAt)
            ->copy()
            ->addDays($displayDays)
            ->endOfDay();
    }
}
