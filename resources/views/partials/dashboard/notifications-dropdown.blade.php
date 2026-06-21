@php
    use App\Services\SystemNotificationService;

    $wmcNotificationUserId = auth()->id();
    $wmcUnreadNotificationCount = SystemNotificationService::unreadCountForUser($wmcNotificationUserId);
    $wmcLatestNotifications = SystemNotificationService::latestForUser($wmcNotificationUserId, 5);
@endphp

<style>
    .wmc-notification-bell-wrap { position: relative; display: inline-flex; align-items: center; justify-content: center; }
    .wmc-notification-badge {
        position: absolute;
        top: -7px;
        right: -10px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 999px;
        background: #dc3545;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        line-height: 18px;
        text-align: center;
        box-shadow: 0 0 0 2px #fff;
    }
    .wmc-notification-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #dc3545;
        display: inline-block;
    }
    .wmc-notification-card { min-width: 360px; max-width: 390px; }
    .wmc-notification-item.unread { background: rgba(220, 53, 69, .055); }
    .wmc-notification-item:hover { background: rgba(58, 87, 232, .08); }
</style>

<li class="nav-item dropdown">
    <a href="#" class="nav-link" id="notification-drop" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="wmc-notification-bell-wrap">
            <svg width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.7695 11.6453C19.039 10.7923 18.7071 10.0531 18.7071 8.79716V8.37013C18.7071 6.73354 18.3304 5.67907 17.5115 4.62459C16.2493 2.98699 14.1244 2 12.0442 2H11.9558C9.91935 2 7.86106 2.94167 6.577 4.5128C5.71333 5.58842 5.29293 6.68822 5.29293 8.37013V8.79716C5.29293 10.0531 4.98284 10.7923 4.23049 11.6453C3.67691 12.2738 3.5 13.0815 3.5 13.9557C3.5 14.8309 3.78723 15.6598 4.36367 16.3336C5.11602 17.1413 6.17846 17.6569 7.26375 17.7466C8.83505 17.9258 10.4063 17.9933 12.0005 17.9933C13.5937 17.9933 15.165 17.8805 16.7372 17.7466C17.8215 17.6569 18.884 17.1413 19.6363 16.3336C20.2118 15.6598 20.5 14.8309 20.5 13.9557C20.5 13.0815 20.3231 12.2738 19.7695 11.6453Z" fill="currentColor"></path>
                <path opacity="0.4" d="M14.0088 19.2283C13.5088 19.1215 10.4627 19.1215 9.96275 19.2283C9.53539 19.327 9.07324 19.5566 9.07324 20.0602C9.09809 20.5406 9.37935 20.9646 9.76895 21.2335L9.76795 21.2345C10.2718 21.6273 10.8632 21.877 11.4824 21.9667C11.8123 22.012 12.1482 22.01 12.4901 21.9667C13.1083 21.877 13.6997 21.6273 14.2036 21.2345L14.2026 21.2335C14.5922 20.9646 14.8734 20.5406 14.8983 20.0602C14.8983 19.5566 14.4361 19.327 14.0088 19.2283Z" fill="currentColor"></path>
            </svg>

            @if($wmcUnreadNotificationCount > 0)
                <span id="wmc-notification-badge" class="wmc-notification-badge">{{ $wmcUnreadNotificationCount > 99 ? '99+' : $wmcUnreadNotificationCount }}</span>
            @endif
        </span>
    </a>

    <div class="sub-drop dropdown-menu dropdown-menu-end p-0" aria-labelledby="notification-drop">
        <div class="card shadow-none m-0 wmc-notification-card">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary py-3">
                <div class="header-title">
                    <h5 class="mb-0 text-white">Notifications</h5>
                </div>

                @if($wmcUnreadNotificationCount > 0)
                    <form id="wmc-mark-all-read-form" action="{{ url('/notifications/mark-all-read') }}" method="POST" class="mb-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light">Mark all read</button>
                    </form>
                @endif
            </div>

            <div class="card-body p-0">
                <div id="wmc-notification-list">
                @forelse($wmcLatestNotifications as $notification)
                    <a href="{{ url('/notifications/' . $notification->id . '/open') }}"
                       class="iq-sub-card text-decoration-none wmc-notification-item {{ $notification->is_read ? '' : 'unread' }}">
                        <div class="d-flex align-items-start gap-3">
                            <span class="mt-1">{{ $notification->is_read ? '' : '🔴' }}</span>
                            <div class="w-100">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h6 class="mb-1 text-dark">{{ $notification->title }}</h6>
                                    <small class="text-muted text-nowrap">{{ optional($notification->created_at)->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0 text-muted small">{{ $notification->message }}</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-4 text-center text-muted">
                        <div class="mb-1">No notifications</div>
                        <small>You're all caught up.</small>
                    </div>
                @endforelse
            </div>
                </div>

            <div class="card-footer bg-white text-center">
                <a href="{{ url('/notifications') }}" class="btn btn-link btn-sm text-decoration-none">View all notifications</a>
            </div>
        </div>
    </div>
</li>

<script>
(function wmcNotificationRealtimePoll() {
    const pollUrl = "{{ url('/notifications/poll') }}";
    const markAllForm = document.getElementById('wmc-mark-all-read-form');

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderNotifications(items) {
        const list = document.getElementById('wmc-notification-list');
        if (!list) return;

        if (!items || items.length === 0) {
            list.innerHTML = `
                <div class="text-center p-4 text-muted">
                    <div class="mb-1">No notifications</div>
                    <small>You're all caught up.</small>
                </div>
            `;
            return;
        }

        list.innerHTML = items.map((item) => {
            const unreadClass = item.is_read ? '' : 'unread';
            const dot = item.is_read ? '' : '<span class="wmc-notification-dot mt-1"></span>';

            return `
                <a href="${escapeHtml(item.open_url)}"
                   class="iq-sub-card text-decoration-none wmc-notification-item ${unreadClass}">
                    <div class="d-flex gap-3 align-items-start">
                        ${dot}
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-3">
                                <h6 class="mb-1 text-dark">${escapeHtml(item.title)}</h6>
                                <small class="text-muted text-nowrap">${escapeHtml(item.created_at_human)}</small>
                            </div>
                            <p class="mb-0 text-muted small">${escapeHtml(item.message)}</p>
                        </div>
                    </div>
                </a>
            `;
        }).join('');
    }

    function updateBadge(count, badgeText) {
        const bellWrap = document.querySelector('.wmc-notification-bell-wrap');
        if (!bellWrap) return;

        let badge = document.getElementById('wmc-notification-badge');

        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.id = 'wmc-notification-badge';
                badge.className = 'wmc-notification-badge';
                bellWrap.appendChild(badge);
            }
            badge.textContent = badgeText || String(count);
            badge.style.display = '';
        } else if (badge) {
            badge.remove();
        }
    }

    async function pollNotifications() {
        try {
            const response = await fetch(pollUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) return;

            const data = await response.json();
            updateBadge(Number(data.unread_count || 0), data.badge || '');
            renderNotifications(data.notifications || []);

            if (markAllForm) {
                markAllForm.style.display = Number(data.unread_count || 0) > 0 ? '' : 'none';
            }
        } catch (error) {
            // Silent fail: notification polling should never interrupt the current page.
        }
    }

    if (markAllForm) {
        markAllForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            try {
                const response = await fetch(markAllForm.action, {
                    method: 'POST',
                    body: new FormData(markAllForm),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    await pollNotifications();
                } else {
                    markAllForm.submit();
                }
            } catch (error) {
                markAllForm.submit();
            }
        });
    }

    pollNotifications();
    setInterval(pollNotifications, 7000);
})();
</script>
