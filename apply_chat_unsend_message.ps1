$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"

$controllerPath = "app\Http\Controllers\Chat\ChatController.php"
$routesPath = "routes\web.php"
$showViewPath = "resources\views\chat\show.blade.php"

$backupDir = "backup_chat_unsend_message_$stamp"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

foreach ($path in @($controllerPath, $routesPath, $showViewPath)) {
    if (Test-Path $path) {
        $dest = Join-Path $backupDir $path
        $destDir = Split-Path $dest -Parent
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        Copy-Item $path $dest -Force
    }
}

# ------------------------------------------------------------
# 1) ChatController: add unsend endpoint + deleted ids in polling
# ------------------------------------------------------------
$controller = Get-Content $controllerPath -Raw

if ($controller -notmatch "public function destroyMessage\s*\(") {
$destroyMethod = @'

    public function destroyMessage(Request $request, ChatConversation $conversation, ChatMessage $message)
    {
        $user = $request->user();

        $this->touchPresence($user->id);
        $this->ensureParticipant($conversation, $user->id);

        abort_unless((int) $message->chat_conversation_id === (int) $conversation->id, 404);
        abort_unless((int) $message->sender_id === (int) $user->id, 403);

        $message->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'deleted_id' => $message->id,
            ]);
        }

        return redirect()->route('chat.show', $conversation);
    }

'@

    $marker = "    public function messages"
    $idx = $controller.IndexOf($marker)

    if ($idx -lt 0) {
        throw "Could not find messages() marker in ChatController."
    }

    $controller = $controller.Insert($idx, $destroyMethod + "`r`n")
    Write-Host "Added destroyMessage() endpoint."
} else {
    Write-Host "destroyMessage() already exists."
}

# Add deleted_ids to polling response.
if ($controller -notmatch "'deleted_ids'\s*=>\s*\$this->recentDeletedMessageIds") {
    $controller = $controller.Replace(
"            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
        ]);",
"            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
            'deleted_ids' => $this->recentDeletedMessageIds($conversation),
        ]);"
    )
    Write-Host "Added deleted_ids to messages() response."
} else {
    Write-Host "messages() already has deleted_ids."
}

if ($controller -notmatch "private function recentDeletedMessageIds") {
$deletedHelper = @'

    private function recentDeletedMessageIds(ChatConversation $conversation): array
    {
        return ChatMessage::withTrashed()
            ->where('chat_conversation_id', $conversation->id)
            ->whereNotNull('deleted_at')
            ->latest('deleted_at')
            ->limit(100)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

'@

    $marker = "    private function recentReadStatuses"
    $idx = $controller.IndexOf($marker)

    if ($idx -lt 0) {
        $marker = "    private function messageReadLabel"
        $idx = $controller.IndexOf($marker)
    }

    if ($idx -lt 0) {
        throw "Could not find helper insertion marker in ChatController."
    }

    $controller = $controller.Insert($idx, $deletedHelper + "`r`n")
    Write-Host "Added recentDeletedMessageIds helper."
} else {
    Write-Host "recentDeletedMessageIds already exists."
}

Set-Content $controllerPath $controller -NoNewline

# ------------------------------------------------------------
# 2) Route: add DELETE message route before wildcard show route
# ------------------------------------------------------------
$routes = Get-Content $routesPath -Raw

if ($routes -notmatch "destroyMessage") {
    $needle = "    Route::get('/{conversation}/messages', [\App\Http\Controllers\Chat\ChatController::class, 'messages'])->name('messages');"
    $insert = $needle + "`r`n" + "    Route::delete('/{conversation}/messages/{message}', [\App\Http\Controllers\Chat\ChatController::class, 'destroyMessage'])->name('messages.destroy');"

    if ($routes.Contains($needle)) {
        $routes = $routes.Replace($needle, $insert)
        Set-Content $routesPath $routes -NoNewline
        Write-Host "Added chat.messages.destroy route."
    } else {
        throw "Could not find chat.messages route marker."
    }
} else {
    Write-Host "destroyMessage route already exists."
}

# ------------------------------------------------------------
# 3) View: add unsend UI + JS
# ------------------------------------------------------------
$show = Get-Content $showViewPath -Raw

if ($show -notmatch "WMC_CHAT_UNSEND_CSS") {
$css = @'

    /* WMC_CHAT_UNSEND_CSS */
    .wmc-bubble-action-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .wmc-bubble-wrap.mine .wmc-bubble-action-wrap {
        flex-direction: row-reverse;
    }

    .wmc-chat-unsend-btn {
        width: 26px;
        height: 26px;
        border: 0;
        border-radius: 999px;
        background: #eef2ff;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all .15s ease;
        padding: 0;
        cursor: pointer;
    }

    .wmc-bubble-wrap.mine:hover .wmc-chat-unsend-btn {
        opacity: 1;
    }

    .wmc-chat-unsend-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .wmc-chat-unsend-btn svg {
        width: 14px;
        height: 14px;
        display: block;
    }

'@
    $show = $show.Replace("</style>", $css + "</style>")
    Write-Host "Added unsend CSS."
}

# Add data message id if not already added correctly.
$show = $show.Replace(
'<div class="wmc-bubble-wrap {{ $mine ? ''mine'' : ''other'' }}" data-message-id="{{ $chatMessage->id }}">',
'<div class="wmc-bubble-wrap {{ $mine ? ''mine'' : ''other'' }}" data-message-id="{{ $chatMessage->id }}">'
)

if ($show -notmatch "data-message-id=\"\{\{ \$chatMessage->id \}\}\"") {
    $show = $show.Replace(
'<div class="wmc-bubble-wrap {{ $mine ? ''mine'' : ''other'' }}">',
'<div class="wmc-bubble-wrap {{ $mine ? ''mine'' : ''other'' }}" data-message-id="{{ $chatMessage->id }}">'
    )
}

# Insert unsend button beside server-rendered own bubbles.
if ($show -notmatch "data-unsend-message-id") {
    $show = $show.Replace(
'                        <div class="wmc-bubble {{ $mine ? ''mine'' : ''other'' }}">',
'                        <div class="{{ $mine ? ''wmc-bubble-action-wrap'' : '''' }}">
                            @if($mine)
                                <button type="button" class="wmc-chat-unsend-btn" data-unsend-message-id="{{ $chatMessage->id }}" title="Unsend message" aria-label="Unsend message">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M9 3H15M4 6H20M18 6L17.2 19.2C17.14 20.21 16.3 21 15.29 21H8.71C7.7 21 6.86 20.21 6.8 19.2L6 6M10 10V17M14 10V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            @endif
                            <div class="wmc-bubble {{ $mine ? ''mine'' : ''other'' }}">'
    )

    # Close action wrapper after bubble div before meta. This targets the first closing block after attachments.
    $show = $show.Replace(
'                        </div>
                        @php',
'                        </div>
                        @if($mine)
                            </div>
                        @endif
                        @php'
    )

    Write-Host "Added server-rendered unsend buttons."
} else {
    Write-Host "Server-rendered unsend button already exists."
}

# Add destroy route template JS constant if missing.
if ($show -notmatch "wmcChatDestroyUrlTemplate") {
    $show = $show.Replace(
'    const wmcChatMessagesEndpoint = "{{ route(''chat.messages'', $conversation) }}";',
'    const wmcChatMessagesEndpoint = "{{ route(''chat.messages'', $conversation) }}";
    const wmcChatDestroyUrlTemplate = "{{ route(''chat.messages.destroy'', [$conversation, ''__MESSAGE_ID__'']) }}";'
    )
}

# Add helper for unsend button HTML for AJAX messages.
if ($show -notmatch "function wmcUnsendButtonHtml") {
$unsendJsHelpers = @'

    function wmcUnsendButtonHtml(id) {
        if (!id) return '';

        return `
            <button type="button" class="wmc-chat-unsend-btn" data-unsend-message-id="${escapeHtml(id)}" title="Unsend message" aria-label="Unsend message">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M9 3H15M4 6H20M18 6L17.2 19.2C17.14 20.21 16.3 21 15.29 21H8.71C7.7 21 6.86 20.21 6.8 19.2L6 6M10 10V17M14 10V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>
        `;
    }

    function removeDeletedMessages(ids) {
        if (!Array.isArray(ids)) return;

        ids.forEach(function (id) {
            const row = document.querySelector('[data-message-id="' + id + '"]');
            if (row) {
                row.remove();
            }
        });
    }

    async function unsendMessage(id) {
        if (!id || !csrfToken || !wmcChatDestroyUrlTemplate) return;

        if (!confirm('Unsend this message?')) return;

        const url = wmcChatDestroyUrlTemplate.replace('__MESSAGE_ID__', encodeURIComponent(id));

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Unsend failed');
            }

            const data = await response.json();
            removeDeletedMessages([data.deleted_id || id]);
        } catch (error) {
            alert('Unable to unsend message. Please try again.');
        }
    }

'@

    $show = $show.Replace("    function wmcSeenStatusHtml(label) {", $unsendJsHelpers + "`r`n    function wmcSeenStatusHtml(label) {")
    Write-Host "Added unsend JS helpers."
}

# Update JS appendMessage rendering to include action wrap/button for own AJAX messages.
$show = $show.Replace(
'                <div class="wmc-bubble ${mine ? ''mine'' : ''other''}">${bodyHtml}${attachmentHtml}</div>',
'                <div class="${mine ? ''wmc-bubble-action-wrap'' : ''''}">
                    ${mine ? wmcUnsendButtonHtml(message.id) : ''''}
                    <div class="wmc-bubble ${mine ? ''mine'' : ''other''}">${bodyHtml}${attachmentHtml}</div>
                </div>'
)

# Add polling deleted_ids removal.
if ($show -notmatch "removeDeletedMessages\(data\.deleted_ids") {
    $show = $show.Replace(
'            if (Array.isArray(data.read_statuses)) {',
'            if (Array.isArray(data.deleted_ids)) {
                removeDeletedMessages(data.deleted_ids);
            }

            if (Array.isArray(data.read_statuses)) {'
    )
    Write-Host "Added deleted_ids removal in polling."
}

# Add click listener for unsend.
if ($show -notmatch "data-unsend-message-id'\)") {
$listener = @'

    document.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-unsend-message-id]');
        if (!btn) return;

        event.preventDefault();
        unsendMessage(btn.getAttribute('data-unsend-message-id'));
    });

'@
    $show = $show.Replace("    setInterval(wmcPollNewMessages, 3000);", $listener + "`r`n    setInterval(wmcPollNewMessages, 3000);")
    Write-Host "Added unsend click listener."
}

Set-Content $showViewPath $show -NoNewline

# ------------------------------------------------------------
# 4) Checks/cache
# ------------------------------------------------------------
php -l $controllerPath
php -l $routesPath
php -l $showViewPath

php artisan route:clear
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Chat unsend message feature applied."
Write-Host "Backups saved to: $backupDir"