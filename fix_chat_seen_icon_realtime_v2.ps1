$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"

$controllerPath = "app\Http\Controllers\Chat\ChatController.php"
$showViewPath = "resources\views\chat\show.blade.php"

$backupDir = "backup_chat_seen_icon_realtime_v2_$stamp"
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
# 1) Controller: return read statuses every polling request
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

    $marker = "    private function messageReadLabel"
    $idx = $controller.IndexOf($marker)

    if ($idx -lt 0) {
        throw "Could not find messageReadLabel marker in ChatController."
    }

    $controller = $controller.Insert($idx, $helper + "`r`n")
    Write-Host "Added recentReadStatuses helper."
} else {
    Write-Host "recentReadStatuses helper already exists."
}

if ($controller -notmatch "'read_statuses'\s*=>\s*\$this->recentReadStatuses") {
    $oldResponse = @'
        return response()->json([
            'ok' => true,
            'messages' => $messages,
            'latest_id' => $messages->max('id') ?: $afterId,
        ]);
'@

    $newResponse = @'
        return response()->json([
            'ok' => true,
            'messages' => $messages,
            'latest_id' => $messages->max('id') ?: $afterId,
            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
        ]);
'@

    if ($controller.Contains($oldResponse)) {
        $controller = $controller.Replace($oldResponse, $newResponse)
        Write-Host "Added read_statuses to messages response."
    } else {
        Write-Host "WARNING: Exact messages response block not found. No response change made."
    }
} else {
    Write-Host "messages response already has read_statuses."
}

Set-Content $controllerPath $controller -NoNewline

# ------------------------------------------------------------
# 2) View: remove weird diamond/separator and add clean check icon using CSS
# ------------------------------------------------------------
$show = Get-Content $showViewPath -Raw

if ($show -notmatch "WMC_CHAT_SEEN_ICON_FIX_V2") {
$css = @'

    /* WMC_CHAT_SEEN_ICON_FIX_V2 */
    .wmc-chat-seen-label {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-weight: 700;
        color: #64748b;
        margin-left: 4px;
    }

    .wmc-chat-seen-label::before {
        content: "?";
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 12px;
        height: 12px;
        font-size: 11px;
        line-height: 1;
        color: #64748b;
    }

'@

    $show = $show.Replace("</style>", $css + "</style>")
    Write-Host "Added clean Sent/Seen CSS."
}

# Remove middle dot / weird replacement character before seen label.
$show = $show.Replace('· <span class="wmc-chat-seen-label">', '<span class="wmc-chat-seen-label">')
$show = $show.Replace('? <span class="wmc-chat-seen-label">', '<span class="wmc-chat-seen-label">')
$show = $show.Replace('Â· <span class="wmc-chat-seen-label">', '<span class="wmc-chat-seen-label">')
$show = [regex]::Replace($show, '[\u00B7\uFFFD]\s*<span class="wmc-chat-seen-label">', '<span class="wmc-chat-seen-label">')

# ------------------------------------------------------------
# 3) JS: make Sent -> Seen update realtime even without new messages
# ------------------------------------------------------------

# Make existing updateSeenLabels accept read_statuses payload without is_mine guard.
$show = $show.Replace(
    "if (!message || !message.id || !message.is_mine || !message.read_label) return;",
    "if (!message || !message.id || !message.read_label) return;"
)

# If updateSeenLabels does not exist, add it.
if ($show -notmatch "function updateSeenLabels") {
$updateSeenFunction = @'

    function updateSeenLabels(messagesPayload) {
        if (!Array.isArray(messagesPayload)) return;

        messagesPayload.forEach(function (message) {
            if (!message || !message.id || !message.read_label) return;

            const row = document.querySelector('[data-message-id="' + message.id + '"]');
            if (!row) return;

            const label = row.querySelector('.wmc-chat-seen-label');
            if (label) {
                label.textContent = message.read_label;
            }
        });
    }

'@

    $show = $show.Replace("    function appendMessage(message) {", $updateSeenFunction + "`r`n    function appendMessage(message) {")
    Write-Host "Added updateSeenLabels JS function."
} else {
    Write-Host "updateSeenLabels JS function already exists."
}

# Add read_statuses update inside polling.
if ($show -notmatch "updateSeenLabels\(data\.read_statuses") {
    $show = $show.Replace(
        "            if (Array.isArray(data.messages)) {",
        "            if (Array.isArray(data.read_statuses)) {
                updateSeenLabels(data.read_statuses);
            }

            if (Array.isArray(data.messages)) {"
    )

    Write-Host "Added read_statuses update to polling."
} else {
    Write-Host "Polling already updates read_statuses."
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