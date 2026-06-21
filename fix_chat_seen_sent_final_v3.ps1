$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"

$controllerPath = "app\Http\Controllers\Chat\ChatController.php"
$showViewPath = "resources\views\chat\show.blade.php"

$backupDir = "backup_chat_seen_sent_final_v3_$stamp"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

foreach ($path in @($controllerPath, $showViewPath)) {
    if (Test-Path $path) {
        $dest = Join-Path $backupDir $path
        $destDir = Split-Path $dest -Parent
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        Copy-Item $path $dest -Force
    }
}

# ------------------------------------------------------------
# 1) Controller: make sure payload has read_label
# ------------------------------------------------------------
$controller = Get-Content $controllerPath -Raw

if ($controller -notmatch "private function messageReadLabel") {
$helper = @'

    private function messageReadLabel(ChatMessage $message, int $viewerId): string
    {
        if ((int) $message->sender_id !== (int) $viewerId) {
            return '';
        }

        $otherReadAt = ChatParticipant::where('chat_conversation_id', $message->chat_conversation_id)
            ->where('user_id', '!=', $viewerId)
            ->max('last_read_at');

        if (! $otherReadAt) {
            return 'Sent';
        }

        return \Carbon\Carbon::parse($otherReadAt)->greaterThanOrEqualTo($message->created_at)
            ? 'Seen'
            : 'Sent';
    }

'@

    $marker = "    private function formatMessagePayload"
    $idx = $controller.IndexOf($marker)

    if ($idx -lt 0) {
        throw "Could not find formatMessagePayload marker in ChatController."
    }

    $controller = $controller.Insert($idx, $helper + "`r`n")
    Write-Host "Added messageReadLabel helper."
}

if ($controller -notmatch "private function recentReadStatuses") {
$recentHelper = @'

    private function recentReadStatuses(ChatConversation $conversation, int $viewerId): array
    {
        return ChatMessage::query()
            ->where('chat_conversation_id', $conversation->id)
            ->where('sender_id', $viewerId)
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($message) use ($viewerId) {
                return [
                    'id' => $message->id,
                    'read_label' => $this->messageReadLabel($message, $viewerId),
                ];
            })
            ->values()
            ->all();
    }

'@

    $marker = "    private function messageReadLabel"
    $idx = $controller.IndexOf($marker)

    if ($idx -lt 0) {
        throw "Could not find messageReadLabel marker in ChatController."
    }

    $controller = $controller.Insert($idx, $recentHelper + "`r`n")
    Write-Host "Added recentReadStatuses helper."
}

if ($controller -notmatch "'read_label'\s*=>") {
    $controller = $controller.Replace(
"            'is_mine' => (int) $message->sender_id === (int) $viewerId,
            'attachments' => $message->attachments->map(function ($attachment) {",
"            'is_mine' => (int) $message->sender_id === (int) $viewerId,
            'read_label' => $this->messageReadLabel($message, $viewerId),
            'attachments' => $message->attachments->map(function ($attachment) {"
    )
    Write-Host "Added read_label to formatMessagePayload."
}

if ($controller -notmatch "'read_statuses'\s*=>\s*\$this->recentReadStatuses") {
    $controller = $controller.Replace(
"            'latest_id' => $messages->max('id') ?: $afterId,
        ]);",
"            'latest_id' => $messages->max('id') ?: $afterId,
            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
        ]);"
    )
    Write-Host "Added read_statuses to polling response."
}

Set-Content $controllerPath $controller -NoNewline

# ------------------------------------------------------------
# 2) View: remove bad icon/diamond/question-mark and force SVG status
# ------------------------------------------------------------
$show = Get-Content $showViewPath -Raw

# Remove old pseudo-check CSS that can show ? or weird symbol.
$show = [regex]::Replace(
    $show,
    '(?s)\.wmc-chat-seen-label::before\s*\{.*?\}',
    ''
)

# Remove weird separators before label.
$show = $show.Replace('· <span class="wmc-chat-seen-label">', '<span class="wmc-chat-seen-label">')
$show = $show.Replace('? <span class="wmc-chat-seen-label">', '<span class="wmc-chat-seen-label">')
$show = $show.Replace('Â· <span class="wmc-chat-seen-label">', '<span class="wmc-chat-seen-label">')
$show = [regex]::Replace($show, '[\u00B7\uFFFD]\s*<span class="wmc-chat-seen-label">', '<span class="wmc-chat-seen-label">')

if ($show -notmatch "WMC_CHAT_SEEN_SENT_FINAL_V3") {
$css = @'

    /* WMC_CHAT_SEEN_SENT_FINAL_V3 */
    .wmc-chat-seen-status {
        display: inline-flex !important;
        align-items: center !important;
        gap: 3px !important;
        margin-left: 5px !important;
        color: #64748b !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        vertical-align: middle !important;
    }

    .wmc-chat-seen-status svg {
        width: 12px !important;
        height: 12px !important;
        display: block !important;
        flex: 0 0 auto !important;
    }

    .wmc-chat-seen-label {
        display: inline !important;
        margin-left: 0 !important;
        color: #64748b !important;
        font-weight: 700 !important;
    }

    .wmc-chat-seen-label::before {
        content: none !important;
        display: none !important;
    }

'@
    $show = $show.Replace("</style>", $css + "</style>")
    Write-Host "Added final Sent/Seen SVG CSS."
}

# ------------------------------------------------------------
# 3) Force server-rendered meta block to use SVG status
# ------------------------------------------------------------
$metaPattern = '(?s)<div class="wmc-chat-message-meta \{\{ \$mine \? ''text-end me-2'' : ''ms-2'' \}\}">\s*\{\{ \$chatMessage->created_at->format\(''h:i A''\) \}\}.*?</div>'

$metaReplacement = @'
<div class="wmc-chat-message-meta {{ $mine ? 'text-end me-2' : 'ms-2' }}">
                            {{ $chatMessage->created_at->format('h:i A') }}
                            @if($mine)
                                @php
                                    $otherReadAtForMeta = \App\Models\Chat\ChatParticipant::where('chat_conversation_id', $conversation->id)
                                        ->where('user_id', '!=', auth()->id())
                                        ->max('last_read_at');

                                    $readLabelForMeta = $otherReadAtForMeta && \Carbon\Carbon::parse($otherReadAtForMeta)->greaterThanOrEqualTo($chatMessage->created_at)
                                        ? 'Seen'
                                        : 'Sent';
                                @endphp
                                <span class="wmc-chat-seen-status">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4.5 12.5L9 17L19.5 6.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <span class="wmc-chat-seen-label">{{ $readLabelForMeta }}</span>
                                </span>
                            @endif
                        </div>
'@

$newShow = [regex]::Replace($show, $metaPattern, $metaReplacement)
if ($newShow -ne $show) {
    $show = $newShow
    Write-Host "Rewrote server-rendered message meta with SVG status."
} else {
    Write-Host "Server meta regex did not match. JS fallback will still fix visible labels."
}

# ------------------------------------------------------------
# 4) JS: create/update Sent/Seen labels in realtime
# ------------------------------------------------------------

# Add SVG status helpers before appendMessage
if ($show -notmatch "function wmcSeenStatusHtml") {
$jsHelpers = @'

    function wmcSeenStatusHtml(label) {
        if (!label) return '';

        return `
            <span class="wmc-chat-seen-status">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4.5 12.5L9 17L19.5 6.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="wmc-chat-seen-label">${escapeHtml(label)}</span>
            </span>
        `;
    }

    function ensureSeenStatus(row, label) {
        if (!row || !label) return;

        let meta = row.querySelector('.wmc-chat-message-meta');
        if (!meta) return;

        let status = meta.querySelector('.wmc-chat-seen-status');

        if (!status) {
            meta.insertAdjacentHTML('beforeend', wmcSeenStatusHtml(label));
            status = meta.querySelector('.wmc-chat-seen-status');
        }

        const labelEl = status ? status.querySelector('.wmc-chat-seen-label') : null;
        if (labelEl) {
            labelEl.textContent = label;
        }
    }

    function updateSeenLabels(payload) {
        if (!Array.isArray(payload)) return;

        payload.forEach(function (message) {
            if (!message || !message.id || !message.read_label) return;

            const row = document.querySelector('[data-message-id="' + message.id + '"]');
            if (!row) return;

            ensureSeenStatus(row, message.read_label);
        });
    }

    function initializeSentLabels() {
        document.querySelectorAll('.wmc-bubble-wrap.mine').forEach(function (row) {
            const status = row.querySelector('.wmc-chat-seen-status');
            if (!status) {
                ensureSeenStatus(row, 'Sent');
            }
        });
    }

'@

    $show = $show.Replace("    function appendMessage(message) {", $jsHelpers + "`r`n    function appendMessage(message) {")
    Write-Host "Added final JS Sent/Seen helpers."
}

# Replace JS meta rendering if it has old label or no label.
$oldJsMetaRegex = '(?s)<div class="wmc-chat-message-meta \$\{mine \? ''text-end me-2'' : ''ms-2''\}">.*?\$\{escapeHtml\(message\.time \|\| ''''\)\}.*?</div>'

$newJsMeta = @'
<div class="wmc-chat-message-meta ${mine ? 'text-end me-2' : 'ms-2'}">
                    ${escapeHtml(message.time || '')}
                    ${mine ? wmcSeenStatusHtml(message.read_label || 'Sent') : ''}
                </div>
'@

$show = [regex]::Replace($show, $oldJsMetaRegex, $newJsMeta, 1)

# Ensure polling updates read_statuses even when there are no new messages.
if ($show -notmatch "updateSeenLabels\(data\.read_statuses") {
    $show = $show.Replace(
"            if (Array.isArray(data.messages)) {",
"            if (Array.isArray(data.read_statuses)) {
                updateSeenLabels(data.read_statuses);
            }

            if (Array.isArray(data.messages)) {"
    )
    Write-Host "Added read_statuses update inside polling."
}

# Ensure initialization runs after scrollToBottom
if ($show -notmatch "initializeSentLabels\(\);") {
    $show = $show.Replace(
"    scrollToBottom();",
"    scrollToBottom();
    initializeSentLabels();"
    )
    Write-Host "Added initial Sent label setup."
}

Set-Content $showViewPath $show -NoNewline

# ------------------------------------------------------------
# 5) Checks/cache
# ------------------------------------------------------------
php -l $controllerPath
php -l $showViewPath

php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Chat Sent/Seen final v3 applied."
Write-Host "Backups saved to: $backupDir"