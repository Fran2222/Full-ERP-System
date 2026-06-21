$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"

$controllerPath = "app\Http\Controllers\Chat\ChatController.php"
$showViewPath = "resources\views\chat\show.blade.php"

$backupDir = "backup_chat_unsend_realtime_v3_$stamp"
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
# 1) Controller: force deleted_ids helper
# ------------------------------------------------------------
$controller = Get-Content $controllerPath -Raw

if ($controller -notmatch "private function recentDeletedMessageIds") {
$helper = @'

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
        $marker = "    private function formatMessagePayload"
        $idx = $controller.IndexOf($marker)
    }

    if ($idx -lt 0) {
        throw "Could not find helper marker in ChatController."
    }

    $controller = $controller.Insert($idx, $helper + "`r`n")
    Write-Host "Added recentDeletedMessageIds helper."
} else {
    Write-Host "recentDeletedMessageIds helper already exists."
}

# Force deleted_ids in messages() response
if ($controller -notmatch "'deleted_ids'\s*=>\s*\$this->recentDeletedMessageIds") {
    $controller = $controller.Replace(
"            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
        ]);",
"            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
            'deleted_ids' => $this->recentDeletedMessageIds($conversation),
        ]);"
    )

    $controller = $controller.Replace(
"            'latest_id' => $messages->max('id') ?: $afterId,
        ]);",
"            'latest_id' => $messages->max('id') ?: $afterId,
            'deleted_ids' => $this->recentDeletedMessageIds($conversation),
        ]);"
    )

    Write-Host "Forced deleted_ids into messages polling response."
} else {
    Write-Host "messages response already has deleted_ids."
}

Set-Content $controllerPath $controller -NoNewline

# ------------------------------------------------------------
# 2) View: make sure all message rows have data-message-id
# ------------------------------------------------------------
$show = Get-Content $showViewPath -Raw

$show = $show.Replace(
'<div class="wmc-bubble-wrap {{ $mine ? ''mine'' : ''other'' }}">',
'<div class="wmc-bubble-wrap {{ $mine ? ''mine'' : ''other'' }}" data-message-id="{{ $chatMessage->id }}">'
)

# ------------------------------------------------------------
# 3) View JS: force removeDeletedMessages function
# ------------------------------------------------------------
if ($show -notmatch "function removeDeletedMessages") {
$removeFn = @'

    function removeDeletedMessages(ids) {
        if (!Array.isArray(ids)) return;

        ids.forEach(function (id) {
            const row = document.querySelector('[data-message-id="' + id + '"]');
            if (row) {
                row.remove();
            }
        });
    }

'@

    $show = $show.Replace("    function updateSeenLabels", $removeFn + "`r`n    function updateSeenLabels")
    Write-Host "Added removeDeletedMessages JS."
} else {
    Write-Host "removeDeletedMessages JS already exists."
}

# Force polling to remove deleted messages every poll.
if ($show -notmatch "removeDeletedMessages\(data\.deleted_ids") {
    $show = $show.Replace(
"            if (Array.isArray(data.read_statuses)) {",
"            if (Array.isArray(data.deleted_ids)) {
                removeDeletedMessages(data.deleted_ids);
            }

            if (Array.isArray(data.read_statuses)) {"
    )

    $show = $show.Replace(
"            if (Array.isArray(data.messages)) {",
"            if (Array.isArray(data.deleted_ids)) {
                removeDeletedMessages(data.deleted_ids);
            }

            if (Array.isArray(data.messages)) {"
    )

    Write-Host "Added deleted_ids removal to polling."
} else {
    Write-Host "Polling already removes deleted_ids."
}

# ------------------------------------------------------------
# 4) View JS: after sender unsends, also refresh immediately
# ------------------------------------------------------------
if ($show -notmatch "wmcPollNewMessages\(\); // WMC_UNSEND_REFRESH") {
    $show = $show.Replace(
"            removeDeletedMessages([data.deleted_id || id]);",
"            removeDeletedMessages([data.deleted_id || id]);
            wmcPollNewMessages(); // WMC_UNSEND_REFRESH"
    )
    Write-Host "Added immediate poll refresh after unsend."
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
Write-Host "Chat unsend realtime v3 fixed."
Write-Host "Backups saved to: $backupDir"