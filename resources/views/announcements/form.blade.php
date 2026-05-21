<x-app-layout :assets="$assets ?? []">
    <div class="row">
        <div class="col-sm-12">
            <div class="card rounded-4 border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap bg-white border-0 pb-0">
                    <div class="header-title">
                        <h4 class="card-title mb-1">
                            {{ isset($announcement) ? 'Edit Announcement' : 'New Announcement' }}
                        </h4>
                        <p class="mb-0 text-secondary">Create a memorandum-style announcement for the HR dashboard.</p>
                    </div>

                    <a href="{{ route('announcements.index') }}" class="btn btn-light mt-2 mt-md-0">
                        Back
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ isset($announcement) ? route('announcements.update', $announcement->id) : route('announcements.store') }}"
                          method="POST">
                        @csrf
                        @if(isset($announcement))
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">To</label>
                                <input type="text"
                                       name="memo_to"
                                       class="form-control"
                                       placeholder="All Employees"
                                       value="{{ old('memo_to', $announcement->memo_to ?? 'All Employees') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">From</label>
                                <input type="text"
                                       name="memo_from"
                                       class="form-control"
                                       placeholder="Management"
                                       value="{{ old('memo_from', $announcement->memo_from ?? 'Management') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date"
                                       name="memo_date"
                                       class="form-control"
                                       value="{{ old('memo_date', isset($announcement) && $announcement->memo_date ? $announcement->memo_date->format('Y-m-d') : now()->toDateString()) }}">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Subject</label>
                                <input type="text"
                                       name="title"
                                       class="form-control"
                                       placeholder="Example: Declaration of Holidays – Holy Week Schedule"
                                       value="{{ old('title', $announcement->title ?? '') }}"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Show for how many days?</label>
                                <input type="number"
                                       name="display_days"
                                       class="form-control"
                                       min="1"
                                       max="365"
                                       placeholder="Example: 5"
                                       value="{{ old('display_days', $announcement->display_days ?? '') }}">
                                <small class="text-secondary">
                                    Leave blank if this announcement should stay visible until manually unpublished.
                                </small>
                            </div>

                            @if(isset($announcement) && $announcement->expires_at)
                                <div class="col-md-12">
                                    <div class="alert alert-info rounded-3 mb-0">
                                        This announcement is visible until
                                        <strong>{{ $announcement->expires_at->format('M d, Y h:i A') }}</strong>.
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-12">
                                <label class="form-label">Content</label>
                                <textarea name="content"
                                          rows="10"
                                          class="form-control"
                                          placeholder="Type the full announcement content here..."
                                          required>{{ old('content', $announcement->content ?? '') }}</textarea>
                                <small class="text-secondary">Tip: You may use line breaks and bullet-style text. It will display cleanly inside the dashboard pop-up.</small>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="is_published"
                                           value="1"
                                           id="is_published"
                                           {{ old('is_published', isset($announcement) && $announcement->is_published ? 1 : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Publish this announcement
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-12 d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ isset($announcement) ? 'Update Announcement' : 'Create Announcement' }}
                                </button>
                                <a href="{{ route('announcements.index') }}" class="btn btn-light px-4">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
