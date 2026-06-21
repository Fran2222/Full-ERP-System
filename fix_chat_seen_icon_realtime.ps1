$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"

$controllerPath = "app\Http\Controllers\Chat\ChatController.php"
$showViewPath = "resources\views\chat\show.blade.php"

$backupDir = "backup_chat_seen_icon_realtime_$stamp"
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
# 1) Controller: add realtime read_statuses to messages() response
# ------------------------------------------------------------
$controller = Get-Content $controllerPath -Raw

if ($controller -notmatch "private function recentReadStatuses") {
$helper = @'

    private function recentReadStatuses(ChatConversation $conversation, int $viewerId): array
    {
        return ChatMessage::query()
            ->where('chat_conversation_id', $conversation->id)
            ->where('sender_id', $viewerId)
            ->latest()
            ->limit(30)
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

    $insertBefore = "    private function messageReadLabel"
    $idx = $controller.IndexOf($insertBefore)

    if ($idx -lt 0) {
        throw "Could not find messageReadLabel marker in ChatController."
    }

    $controller = $controller.Insert($idx, $helper + "`r`n")
    Write-Host "Added recentReadStatuses helper."
} else {
    Write-Host "recentReadStatuses helper already exists. Skipped."
}

# Add read_statuses to messages() JSON response
if ($controller -notmatch "'read_statuses'\s*=>\s*\$this->recentReadStatuses") {
    $controller = $controller.Replace(
"        return response()->json([
            'ok' => true,
            'messages' => $messages,
            'latest_id' => $messages->max('id') ?: $afterId,
        ]);",
"        return response()->json([
            'ok' => true,
            'messages' => $messages,
            'latest_id' => $messages->max('id') ?: $afterId,
            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
        ]);"
    )

    Write-Host "Added read_statuses to messages() response."
} else {
    Write-Host "messages() already returns read_statuses. Skipped."
}

Set-Content $controllerPath $controller -NoNewline

# ------------------------------------------------------------
# 2) View: clean Seen/Sent indicator icon and remove encoding separator
# ------------------------------------------------------------
$show = Get-Content $showViewPath -Raw

# Add clean seen/sent CSS
if ($show -notmatch "WMC_CHAT_SEEN_ICON_FIX") {
$css = @'

    /* WMC_CHAT_SEEN_ICON_FIX */
    .wmc-chat-seen-status {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        margin-left: 4px;
        font-weight: 700;
        color: #64748b;
        vertical-align: middle;
    }

    .wmc-chat-seen-status svg {
        width: 12px;
        height: 12px;
        display: block;
        flex: 0 0 auto;
    }

'@

    $show = $show.Replace("</style>", $css + "</style>")
    Write-Host "Added clean Seen/Sent CSS."
}

# Replace server-rendered weird separator/meta if present
$show = $show.Replace(
"                            {{ `$chatMessage->created_at->format('h:i A') }}
                            @if(`$mine)
                                · <span class=""wmc-chat-seen-label"">{{ `$readLabel }}</span>
                            @endif",
"                            {{ `$chatMessage->created_at->format('h:i A') }}
                            @if(`$mine)
                                <span class=""wmc-chat-seen-status"">
                                    <svg viewBox=""0 0 24 24"" fill=""none"" aria-hidden=""true"">
                                        <path d=""M4.5 12.5L9 17L19.5 6.5"" stroke=""currentColor"" stroke-width=""2.4"" stroke-linecap=""round"" stroke-linejoin=""round""/>
                                    </svg>
                                    <span class=""wmc-chat-seen-label"">{{ `$readLabel }}</span>
                                </span>
                            @endif"
)

# Fallback regex for any remaining diamond/separator before label
$show = [regex]::Replace(
    $show,
    '·\s*<span class="wmc-chat-seen-label">\{\{\s*\$readLabel\s*\}\}</span>',
    '<span class="wmc-chat-seen-status">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4.5 12.5L9 17L19.5 6.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="wmc-chat-seen-label">{{ $readLabel }}</span>
                                </span>'
)

# Replace JS meta rendering with clean SVG icon, no weird dot.
$oldJsMeta = @'
                <div class="wmc-chat-message-meta ${mine ? 'text-end me-2' : 'ms-2'}">${escapeHtml(message.time || '')}${mine && message.read_label ? ` · <span class="wmc-chat-seen-label">${escapeHtml(message.read_label)}</span>` : ''}</div>
'@

$newJsMeta = @'
                <div class="wmc-chat-message-meta ${mine ? 'text-end me-2' : 'ms-2'}">
                    ${escapeHtml(message.time || '')}
                    ${mine && message.read_label ? `
                        <span class="wmc-chat-seen-status">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4.5 12.5L9 17L19.5 6.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="wmc-chat-seen-label">${escapeHtml(message.read_label)}</span>
                        </span>
                    ` : ''}
                </div>
'@

if ($show.Contains($oldJsMeta)) {
    $show = $show.Replace($oldJsMeta, $newJsMeta)
    Write-Host "Updated JS rendered Seen/Sent meta."
} else {
    Write-Host "Exact JS meta block not found. Applying fallback replace."
    $show = [regex]::Replace(
        $show,
        '\$\{mine && message\.read_label \? `\s*·\s*<span class="wmc-chat-seen-label">\$\{escapeHtml\(message\.read_label\)\}</span>` : ''''\}',
        '${mine && message.read_label ? `
                        <span class="wmc-chat-seen-status">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4.5 12.5L9 17L19.5 6.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="wmc-chat-seen-label">${escapeHtml(message.read_label)}</span>
                        </span>
                    ` : ''''}'
    )
}

# ------------------------------------------------------------
# 3) JS: update existing labels from read_statuses every poll
# ------------------------------------------------------------

# Make updateSeenLabels accept read_statuses too
$show = $show.Replace(
"    function updateSeenLabels(messagesPayload) {
        if (!Array.isArray(messagesPayload)) return;

        messagesPayload.forEach(function (message) {
            if (!message || !message.id || !message.is_mine || !message.read_label) return;

            const row = document.querySelector(`[data-message-id=""`${message.id}`""]`);
            if (!row) return;

            const label = row.querySelector('.wmc-chat-seen-label');
            if (label) {
                label.textContent = message.read_label;
            }
        });
    }",
"    function updateSeenLabels(messagesPayload) {
        if (!Array.isArray(messagesPayload)) return;

        messagesPayload.forEach(function (message) {
            if (!message || !message.id || !message.read_label) return;

            const row = document.querySelector(`[data-message-id=""`${message.id}`""]`);
            if (!row) return;

            const label = row.querySelector('.wmc-chat-seen-label');
            if (label) {
                label.textContent = message.read_label;
            }
        });
    }"
)

# If exact replacement missed due escaping, use a safer regex for the is_mine guard
$show = $show.Replace("if (!message || !message.id || !message.is_mine || !message.read_label) return;", "if (!message || !message.id || !message.read_label) return;")

# Add updateSeenLabels(data.read_statuses) after polling response.
if ($show -notmatch "updateSeenLabels\(data\.read_statuses") {
    $show = $show.Replace(
"            if (Array.isArray(data.messages)) {",
"            if (Array.isArray(data.read_statuses)) {
                updateSeenLabels(data.read_statuses);
            }

            if (Array.isArray(data.messages)) {"
    )
    Write-Host "Added realtime read_statuses update in polling."
} else {
    Write-Host "Polling already updates read_statuses. Skipped."
}

Set-Content $showViewPath $show -NoNewline

# ------------------------------------------------------------
# 4) Checks/cache
# ------------------------------------------------------------
php -l $controllerPath
php -l $showViewPath

php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Chat Seen/Sent icon and realtime update fixed."
Write-Host "Backups saved to: $backupDir"