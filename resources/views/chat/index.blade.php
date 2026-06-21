@extends('layouts.chat-dashboard')

@section('content')
@php
    $onlineCutoff = now()->subMinutes(2);
    $formatUserName = function ($user) {
        return $user ? ($user->full_name ?: $user->email ?: 'User') : 'User';
    };
    $presenceLabel = function ($user) use ($onlineCutoff) {
        if (! $user || ! $user->last_seen_at) {
            return 'Offline';
        }

        return $user->last_seen_at->greaterThanOrEqualTo($onlineCutoff)
            ? 'Active now'
            : 'Active ' . $user->last_seen_at->diffForHumans();
    };
    $isOnline = function ($user) use ($onlineCutoff) {
        return $user && $user->last_seen_at && $user->last_seen_at->greaterThanOrEqualTo($onlineCutoff);
    };
@endphp

<style>
    .wmc-chat-shell {
        min-height: calc(100vh - 185px);
    }

    .wmc-chat-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, .08);
        overflow: hidden;
        background: #fff;
    }

    .wmc-chat-sidebar {
        border-right: 1px solid #edf0f5;
        min-height: 560px;
        max-height: calc(100vh - 220px);
        overflow-y: auto;
        background: #fff;
    }

    .wmc-chat-main {
        min-height: 560px;
        max-height: calc(100vh - 220px);
        overflow-y: auto;
        background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
    }

    .wmc-chat-search {
        border-radius: 999px;
        background: #f1f4f9;
        border: 1px solid transparent;
        padding: .75rem 1rem;
    }

    .wmc-chat-search:focus {
        background: #fff;
        border-color: #3f51f4;
        box-shadow: 0 0 0 .2rem rgba(63, 81, 244, .08);
    }

    .wmc-chat-user-row {
        border-radius: 16px;
        transition: all .15s ease;
        cursor: pointer;
    }

    .wmc-chat-user-row:hover {
        background: #f5f7ff;
    }

    .wmc-chat-user-row.unread {
        background: #f5f7ff;
    }

    .wmc-chat-user-row.unread .wmc-chat-name {
        font-weight: 900;
    }

    .wmc-chat-avatar {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: #e9edff;
        color: #3f51f4;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        flex: 0 0 auto;
    }

    .wmc-chat-online-dot,
    .wmc-chat-offline-dot {
        width: 12px;
        height: 12px;
        border: 2px solid #fff;
        border-radius: 999px;
        position: absolute;
        right: 0;
        bottom: 1px;
    }

    .wmc-chat-online-dot { background: #22c55e; }
    .wmc-chat-offline-dot { background: #cbd5e1; }

    .wmc-chat-empty {
        min-height: 540px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .wmc-chat-conversation-link {
        color: inherit;
        text-decoration: none;
        display: block;
    }

    .wmc-chat-tabs .nav-link {
        border-radius: 999px;
        font-weight: 700;
        padding: .45rem .9rem;
    }

    .wmc-chat-tabs .nav-link.active {
        background: #3f51f4;
        color: #fff;
    }

    .wmc-chat-sync-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #22c55e;
        display: inline-block;
        margin-left: 6px;
        vertical-align: middle;
    }
</style>

<div class="container-fluid py-4">
    <div class="wmc-chat-shell">
        <div class="wmc-chat-card">
            <div class="row g-0">
                <div class="col-lg-4 col-xl-3">
                    <aside class="wmc-chat-sidebar p-3 p-lg-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h3 class="fw-bold mb-0">Chats <span class="wmc-chat-sync-dot" title="Realtime active"></span></h3>
                                <div class="text-muted small">Internal Messenger</div>
                            </div>
                        </div>

                        <input type="text" id="wmcChatSearch" class="form-control wmc-chat-search mb-3" placeholder="Search people or chats...">

                        <ul class="nav nav-pills wmc-chat-tabs gap-2 mb-3" id="wmcChatTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" type="button" data-wmc-tab="conversations">Chats</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" type="button" data-wmc-tab="people">People</button>
                            </li>
                        </ul>

                        <div id="wmcChatConversations">
                            @forelse($conversations as $conversation)
                                @php
                                    $other = $conversation->otherParticipantFor(auth()->id());
                                    $latest = $conversation->latestMessage;
                                    $unread = $unreadCounts[$conversation->id] ?? 0;
                                    $displayName = $conversation->displayTitleFor(auth()->id());
                                    $latestPreview = 'No messages yet.';

                                    if ($latest) {
                                        if(trim((string) $latest->body) !== '') {
                                            $senderDisplayName = $latest->sender ? $formatUserName($latest->sender) : 'User';
                                            $latestPreview = $senderDisplayName . ': ' . $latest->body;
                                        } elseif($latest->attachments && $latest->attachments->count()) {
                                            $latestPreview = 'Sent an attachment';
                                        }
                                    }

                                    $online = $isOnline($other);
                                @endphp

                                <a href="{{ route('chat.show', $conversation) }}"
                                   class="wmc-chat-conversation-link wmc-chat-filter-item"
                                   data-search="{{ strtolower($displayName . ' ' . optional($other)->email . ' ' . $latestPreview) }}">
                                    <div class="wmc-chat-user-row {{ $unread > 0 ? 'unread' : '' }} d-flex align-items-center gap-3 p-2 mb-1">
                                        <div class="wmc-chat-avatar">
                                            {{ strtoupper(substr($displayName, 0, 1)) }}
                                            <span class="{{ $online ? 'wmc-chat-online-dot' : 'wmc-chat-offline-dot' }}"></span>
                                        </div>
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="wmc-chat-name fw-bold text-dark text-truncate">{{ $displayName }}</div>
                                                @if($unread > 0)
                                                    <span class="badge bg-primary rounded-pill">{{ $unread }}</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted text-truncate">{{ $latestPreview }}</div>
                                            <div class="small {{ $online ? 'text-success' : 'text-muted' }}">
                                                {{ $presenceLabel($other) }}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="text-muted small py-3" id="wmcNoConversations">No conversations yet.</div>
                            @endforelse
                        </div>

                        <div id="wmcChatPeople" class="d-none">
                            @foreach($users as $chatUser)
                                @php
                                    $displayName = $formatUserName($chatUser);
                                    $online = $isOnline($chatUser);
                                @endphp
                                <form action="{{ route('chat.start') }}" method="POST"
                                      class="wmc-chat-filter-item"
                                      data-search="{{ strtolower($displayName . ' ' . $chatUser->email) }}">
                                    @csrf
                                    <input type="hidden" name="recipient_id" value="{{ $chatUser->id }}">
                                    <button type="submit" class="btn w-100 text-start p-0 border-0 bg-transparent">
                                        <div class="wmc-chat-user-row d-flex align-items-center gap-3 p-2 mb-1">
                                            <div class="wmc-chat-avatar">
                                                {{ strtoupper(substr($displayName, 0, 1)) }}
                                                <span class="{{ $online ? 'wmc-chat-online-dot' : 'wmc-chat-offline-dot' }}"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark text-truncate">{{ $displayName }}</div>
                                                <div class="small text-muted text-truncate">{{ $chatUser->email }}</div>
                                                <div class="small {{ $online ? 'text-success' : 'text-muted' }}">
                                                    {{ $presenceLabel($chatUser) }}
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </aside>
                </div>

                <div class="col-lg-8 col-xl-9">
                    <main class="wmc-chat-main">
                        <div class="wmc-chat-empty px-4">
                            <div>
                                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 82px; height: 82px;">
                                    <svg width="38" height="38" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 5.5C4 4.12 5.12 3 6.5 3H17.5C18.88 3 20 4.12 20 5.5V13.5C20 14.88 18.88 16 17.5 16H9L5.4 19.2C4.86 19.68 4 19.3 4 18.58V5.5Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M8 8H16M8 11H13.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <h4 class="fw-bold mb-2">Select a conversation</h4>
                                <p class="text-muted mb-0">Choose an existing chat or open the People tab to start a new message.</p>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const feedUrl = "{{ route('chat.feed') }}";
    const startUrl = "{{ route('chat.start') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const searchInput = document.getElementById('wmcChatSearch');
    const conversations = document.getElementById('wmcChatConversations');
    const people = document.getElementById('wmcChatPeople');
    const tabButtons = document.querySelectorAll('[data-wmc-tab]');
    let activeTab = 'conversations';
    let isLoadingFeed = false;

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function applySearch() {
        const value = (searchInput?.value || '').toLowerCase().trim();
        document.querySelectorAll('.wmc-chat-filter-item').forEach(function (item) {
            const haystack = item.getAttribute('data-search') || '';
            item.classList.toggle('d-none', value !== '' && !haystack.includes(value));
        });
    }

    function renderConversations(items) {
        if (!conversations) return;

        if (!items.length) {
            conversations.innerHTML = '<div class="text-muted small py-3" id="wmcNoConversations">No conversations yet.</div>';
            return;
        }

        conversations.innerHTML = items.map(function (item) {
            return `
                <a href="${escapeHtml(item.url)}"
                   class="wmc-chat-conversation-link wmc-chat-filter-item"
                   data-search="${escapeHtml(item.search)}">
                    <div class="wmc-chat-user-row ${item.unread > 0 ? 'unread' : ''} d-flex align-items-center gap-3 p-2 mb-1">
                        <div class="wmc-chat-avatar">
                            ${escapeHtml(item.initial || '?')}
                            <span class="${item.is_online ? 'wmc-chat-online-dot' : 'wmc-chat-offline-dot'}"></span>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="wmc-chat-name fw-bold text-dark text-truncate">${escapeHtml(item.name)}</div>
                                ${item.unread > 0 ? `<span class="badge bg-primary rounded-pill">${item.unread > 99 ? '99+' : item.unread}</span>` : ''}
                            </div>
                            <div class="small text-muted text-truncate">${escapeHtml(item.latest_preview)}</div>
                            <div class="small ${item.is_online ? 'text-success' : 'text-muted'}">${escapeHtml(item.presence)}</div>
                        </div>
                    </div>
                </a>
            `;
        }).join('');
    }

    function renderPeople(items) {
        if (!people) return;

        people.innerHTML = items.map(function (item) {
            return `
                <form action="${escapeHtml(startUrl)}" method="POST"
                      class="wmc-chat-filter-item"
                      data-search="${escapeHtml(item.search)}">
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken || '')}">
                    <input type="hidden" name="recipient_id" value="${escapeHtml(item.id)}">
                    <button type="submit" class="btn w-100 text-start p-0 border-0 bg-transparent">
                        <div class="wmc-chat-user-row d-flex align-items-center gap-3 p-2 mb-1">
                            <div class="wmc-chat-avatar">
                                ${escapeHtml(item.initial || '?')}
                                <span class="${item.is_online ? 'wmc-chat-online-dot' : 'wmc-chat-offline-dot'}"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark text-truncate">${escapeHtml(item.name)}</div>
                                <div class="small text-muted text-truncate">${escapeHtml(item.email || '')}</div>
                                <div class="small ${item.is_online ? 'text-success' : 'text-muted'}">${escapeHtml(item.presence)}</div>
                            </div>
                        </div>
                    </button>
                </form>
            `;
        }).join('');
    }

    async function refreshFeed() {
        if (isLoadingFeed) return;
        isLoadingFeed = true;

        try {
            const response = await fetch(feedUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) return;

            const data = await response.json();

            if (Array.isArray(data.conversations)) {
                renderConversations(data.conversations);
            }

            if (Array.isArray(data.people)) {
                renderPeople(data.people);
            }

            applySearch();

            if (window.wmcRefreshChatBadge) {
                window.wmcRefreshChatBadge();
            }
        } catch (error) {
            // Silent fail.
        } finally {
            isLoadingFeed = false;
        }
    }

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeTab = button.getAttribute('data-wmc-tab') || 'conversations';

            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            if (activeTab === 'people') {
                conversations.classList.add('d-none');
                people.classList.remove('d-none');
            } else {
                people.classList.add('d-none');
                conversations.classList.remove('d-none');
            }

            applySearch();
        });
    });

    searchInput?.addEventListener('input', applySearch);

    setInterval(refreshFeed, 5000);
    setTimeout(refreshFeed, 1500);

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshFeed();
        }
    });
})();
</script>
@endsection