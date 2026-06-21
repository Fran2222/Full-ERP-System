$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"

$controllerPath = "app\Http\Controllers\Chat\ChatController.php"
$showViewPath = "resources\views\chat\show.blade.php"

$backupDir = "backup_chat_seen_indicator_$stamp"
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
# 1) Add read status to ChatController payload
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

    $insertBefore = "    private function formatMessagePayload"
    $idx = $controller.IndexOf($insertBefore)

    if ($idx -lt 0) {
        throw "Could not find formatMessagePayload marker in ChatController."
    }

    $controller = $controller.Insert($idx, $helper + "`r`n")
    Write-Host "Added messageReadLabel helper."
} else {
    Write-Host "messageReadLabel helper already exists. Skipped."
}

if ($controller -notmatch "'read_label'\s*=>") {
    $controller = $controller.Replace(
"            'is_mine' => (int) $message->sender_id === (int) $viewerId,
            'attachments' => $message->attachments->map(function ($attachment) {",
"            'is_mine' => (int) $message->sender_id === (int) $viewerId,
            'read_label' => $this->messageReadLabel($message, $viewerId),
            'attachments' => $message->attachments->map(function ($attachment) {"
    )

    Write-Host "Added read_label to message payload."
} else {
    Write-Host "read_label payload already exists. Skipped."
}

Set-Content $controllerPath $controller -NoNewline

# ------------------------------------------------------------
# 2) Update chat show Blade: server-rendered read label
# ------------------------------------------------------------
$show = Get-Content $showViewPath -Raw

if ($show -notmatch "WMC_CHAT_SEEN_INDICATOR_CSS") {
$css = @'

    /* WMC_CHAT_SEEN_INDICATOR_CSS */
    .wmc-chat-message-meta {
        font-size: 11px;
        color: #718096;
        margin-top: 4px;
    }

    .wmc-chat-seen-label {
        font-weight: 700;
        color: #64748b;
    }

'@

    $show = $show.Replace("</style>", $css + "</style>")
    Write-Host "Added seen indicator CSS."
}

# Replace server-rendered time meta block to include Sent/Seen for my messages.
$oldMeta = @'
                        <div class="small text-muted mt-1 {{ $mine ? 'text-end me-2' : 'ms-2' }}">
                            {{ $chatMessage->created_at->format('h:i A') }}
                        </div>
'@

$newMeta = @'
                        @php
                            $readLabel = '';
                            if ($mine) {
                                $otherReadAt = \App\Models\Chat\ChatParticipant::where('chat_conversation_id', $conversation->id)
                                    ->where('user_id', '!=', auth()->id())
                                    ->max('last_read_at');

                                $readLabel = $otherReadAt && \Carbon\Carbon::parse($otherReadAt)->greaterThanOrEqualTo($chatMessage->created_at)
                                    ? 'Seen'
                                    : 'Sent';
                            }
                        @endphp
                        <div class="wmc-chat-message-meta {{ $mine ? 'text-end me-2' : 'ms-2' }}">
                            {{ $chatMessage->created_at->format('h:i A') }}
                            @if($mine)
                                · <span class="wmc-chat-seen-label">{{ $readLabel }}</span>
                            @endif
                        </div>
'@

if ($show.Contains($oldMeta)) {
    $show = $show.Replace($oldMeta, $newMeta)
    Write-Host "Updated server-rendered message meta."
} else {
    Write-Host "Server-rendered old meta block not found. Skipped."
}

# ------------------------------------------------------------
# 3) Update JS-rendered message meta
# ------------------------------------------------------------
# Change appendMessage meta rendering from time only to time + read_label.
$show = $show.Replace(
'                <div class="small text-muted mt-1 ${mine ? ''text-end me-2'' : ''ms-2''}">${escapeHtml(message.time || '''')}</div>',
'                <div class="wmc-chat-message-meta ${mine ? ''text-end me-2'' : ''ms-2''}">${escapeHtml(message.time || '''')}${mine && message.read_label ? ` · <span class="wmc-chat-seen-label">${escapeHtml(message.read_label)}</span>` : ''''}</div>'
)

# Add function to update visible Sent -> Seen without duplicate messages.
if ($show -notmatch "function updateSeenLabels") {
$seenJs = @'

    function updateSeenLabels(messagesPayload) {
        if (!Array.isArray(messagesPayload)) return;

        messagesPayload.forEach(function (message) {
            if (!message || !message.id || !message.is_mine || !message.read_label) return;

            const row = document.querySelector(`[data-message-id="${message.id}"]`);
            if (!row) return;

            const label = row.querySelector('.wmc-chat-seen-label');
            if (label) {
                label.textContent = message.read_label;
            }
        });
    }

'@

    $show = $show.Replace("    function appendMessage(message) {", $seenJs + "`r`n    function appendMessage(message) {")
    Write-Host "Added updateSeenLabels JS."
}

# In polling, after appending messages, update labels too.
$show = $show.Replace(
"                data.messages.forEach(appendMessage);

                if (data.messages.length > 0 && window.wmcRefreshChatBadge) {",
"                data.messages.forEach(appendMessage);
                updateSeenLabels(data.messages);

                if (data.messages.length > 0 && window.wmcRefreshChatBadge) {"
)

# After send, appended message should show Sent/Seen from payload.
$show = $show.Replace(
"            appendMessage(data.message || {",
"            appendMessage(data.message || {"
)

Set-Content $showViewPath $show -NoNewline

# ------------------------------------------------------------
# 4) Checks/cache
# ------------------------------------------------------------
php -l $controllerPath
php -l $showViewPath

php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Chat seen/read indicator applied."
Write-Host "Backups saved to: $backupDir"