$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"

$controllerPath = "app\Http\Controllers\Chat\ChatController.php"
$routesPath = "routes\web.php"
$showViewPath = "resources\views\chat\show.blade.php"

$backupDir = "backup_chat_unsend_message_v2_$stamp"
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
# 1) Controller: add destroyMessage()
# ------------------------------------------------------------
$controller = Get-Content $controllerPath -Raw

if ($controller -notmatch "public function destroyMessage") {
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
    Write-Host "Added destroyMessage()."
} else {
    Write-Host "destroyMessage() already exists. Skipped."
}

# ------------------------------------------------------------
# 2) Controller: add deleted IDs to polling
# ------------------------------------------------------------
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
        throw "Could not find helper marker in ChatController."
    }

    $controller = $controller.Insert($idx, $deletedHelper + "`r`n")
    Write-Host "Added recentDeletedMessageIds()."
} else {
    Write-Host "recentDeletedMessageIds() already exists. Skipped."
}

if ($controller -notmatch "'deleted_ids'\s*=>") {
    $controller = $controller.Replace(
"            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
        ]);",
"            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
            'deleted_ids' => $this->recentDeletedMessageIds($conversation),
        ]);"
    )
    Write-Host "Added deleted_ids to messages() response."
} else {
    Write-Host "messages() already has deleted_ids. Skipped."
}

Set-Content $controllerPath $controller -NoNewline

# ------------------------------------------------------------
# 3) Route: add DELETE route
# ------------------------------------------------------------
$routes = Get-Content $routesPath -Raw

if ($routes -notmatch "messages\.destroy") {
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
    Write-Host "chat.messages.destroy route already exists. Skipped."
}

# ------------------------------------------------------------
# 4) View: add Unsend CSS + JS
# ------------------------------------------------------------
$show = Get-Content $showViewPath -Raw

# Ensure server-rendered rows have data-message-id.
if ($show -notmatch 'data-message-id="{{ $chatMessage->id }}"') {
    $show = $show.Replace(
'<div class="wmc-bubble-wrap {{ $mine ? ''mine'' : ''other'' }}">',
'<div class="wmc-bubble-wrap {{ $mine ? ''mine'' : ''other'' }}" data-message-id="{{ $chatMessage->id }}">'
    )
    Write-Host "Added data-message-id to server-rendered rows."
}

if ($show -notmatch "WMC_CHAT_UNSEND_V2_CSS") {
$css = @'

    /* WMC_CHAT_UNSEND_V2_CSS */
    .wmc-bubble-wrap {
        position: relative;
    }

    .wmc-chat-unsend-btn {
        width: 28px;
        height: 28px;
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
        margin-right: 6px;
        flex: 0 0 auto;
    }

    .wmc-bubble-wrap.mine:hover .wmc-chat-unsend-btn {
        opacity: 1;
    }

    .wmc-chat-unsend-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .wmc-chat-unsend-btn svg {
        width: 15px;
        height: 15px;
        display: block;
    }

    .wmc-chat-unsend-holder {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .wmc-bubble-wrap.mine > div {
        align-items: flex-end !important;
    }

'@

    $show = $show.Replace("</style>", $css + "</style>")
    Write-Host "Added unsend CSS."
}

# Add destroy URL template.
if ($show -notmatch "wmcChatDestroyUrlTemplate") {
    $show = $show.Replace(
'    const wmcChatMessagesEndpoint = "{{ route(''chat.messages'', $conversation) }}";',
'    const wmcChatMessagesEndpoint = "{{ route(''chat.messages'', $conversation) }}";
    const wmcChatDestroyUrlTemplate = @json(route(''chat.messages.destroy'', [$conversation, ''__MESSAGE_ID__'']));'
    )
    Write-Host "Added destroy URL template."
}

if ($show -notmatch "function wmcUnsendButtonHtml") {
$js = @'

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

    function enhanceUnsendButtons() {
        document.querySelectorAll('.wmc-bubble-wrap.mine').forEach(function (row) {
            const id = row.getAttribute('data-message-id');
            if (!id || row.querySelector('[data-unsend-message-id]')) return;

            const bubble = row.querySelector('.wmc-bubble');
            if (!bubble) return;

            bubble.insertAdjacentHTML('beforebegin', wmcUnsendButtonHtml(id));
        });
    }

    function removeDeletedMessages(ids) {
        if (!Array.isArray(ids)) return;

        ids.forEach(function (id) {
            const row = document.querySelector('[data-message-id="' + id + '"]');
            if (row) row.remove();
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

    $show = $show.Replace("    function wmcSeenStatusHtml(label) {", $js + "`r`n    function wmcSeenStatusHtml(label) {")
    Write-Host "Added unsend JS helpers."
}

# Call enhanceUnsendButtons after append and initial load.
if ($show -notmatch "enhanceUnsendButtons\(\);") {
    $show = $show.Replace(
"        if (shouldScroll || mine) {
            scrollToBottom();
        }",
"        enhanceUnsendButtons();

        if (shouldScroll || mine) {
            scrollToBottom();
        }"
    )

    $show = $show.Replace(
"    initializeSentLabels();",
"    initializeSentLabels();
    enhanceUnsendButtons();"
    )

    Write-Host "Added enhanceUnsendButtons calls."
}

# Remove deleted messages during polling.
if ($show -notmatch "removeDeletedMessages\(data\.deleted_ids") {
    $show = $show.Replace(
"            if (Array.isArray(data.read_statuses)) {",
"            if (Array.isArray(data.deleted_ids)) {
                removeDeletedMessages(data.deleted_ids);
            }

            if (Array.isArray(data.read_statuses)) {"
    )
    Write-Host "Added deleted_ids polling removal."
}

# Click listener.
if ($show -notmatch "data-unsend-message-id") {
    Write-Host "WARNING: data-unsend-message-id not found after helper insertion."
}

if ($show -notmatch "unsendMessage\(btn\.getAttribute") {
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
# 5) Checks/cache
# ------------------------------------------------------------
php -l $controllerPath
php -l $routesPath
php -l $showViewPath

php artisan route:clear
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Chat unsend message v2 applied."
Write-Host "Backups saved to: $backupDir"