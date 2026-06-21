$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"

$controllerPath = "app\Http\Controllers\Chat\ChatController.php"
$routesPath = "routes\web.php"
$showViewPath = "resources\views\chat\show.blade.php"

$backupDir = "backup_chat_unsend_receiver_realtime_v4_$stamp"
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
# 1) Controller: add deletedIds() endpoint
# ------------------------------------------------------------
$controller = Get-Content $controllerPath -Raw

if ($controller -notmatch "public function deletedIds\s*\(") {
$method = @'

    public function deletedIds(Request $request, ChatConversation $conversation)
    {
        $user = $request->user();

        $this->touchPresence($user->id);
        $this->ensureParticipant($conversation, $user->id);

        $ids = ChatMessage::withTrashed()
            ->where('chat_conversation_id', $conversation->id)
            ->whereNotNull('deleted_at')
            ->latest('deleted_at')
            ->limit(100)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'deleted_ids' => $ids,
        ]);
    }

'@

    $marker = "    public function messages"
    $idx = $controller.IndexOf($marker)

    if ($idx -lt 0) {
        throw "Could not find messages() marker in ChatController."
    }

    $controller = $controller.Insert($idx, $method + "`r`n")
    Set-Content $controllerPath $controller -NoNewline
    Write-Host "Added deletedIds() endpoint."
} else {
    Write-Host "deletedIds() endpoint already exists."
}

# ------------------------------------------------------------
# 2) Route: add /deleted-ids before wildcard show route
# ------------------------------------------------------------
$routes = Get-Content $routesPath -Raw

if ($routes -notmatch "deleted-ids") {
    $needle = "    Route::get('/{conversation}/messages', [\App\Http\Controllers\Chat\ChatController::class, 'messages'])->name('messages');"
    $insert = $needle + "`r`n" + "    Route::get('/{conversation}/deleted-ids', [\App\Http\Controllers\Chat\ChatController::class, 'deletedIds'])->name('deleted-ids');"

    if ($routes.Contains($needle)) {
        $routes = $routes.Replace($needle, $insert)
        Set-Content $routesPath $routes -NoNewline
        Write-Host "Added chat.deleted-ids route."
    } else {
        throw "Could not find chat.messages route marker."
    }
} else {
    Write-Host "chat.deleted-ids route already exists."
}

# ------------------------------------------------------------
# 3) View JS: add independent deleted IDs polling
# ------------------------------------------------------------
$show = Get-Content $showViewPath -Raw

# Add route constant
if ($show -notmatch "wmcChatDeletedIdsEndpoint") {
    $show = $show.Replace(
'    const wmcChatMessagesEndpoint = "{{ route(''chat.messages'', $conversation) }}";',
'    const wmcChatMessagesEndpoint = "{{ route(''chat.messages'', $conversation) }}";
    const wmcChatDeletedIdsEndpoint = "{{ route(''chat.deleted-ids'', $conversation) }}";'
    )
    Write-Host "Added deleted IDs endpoint constant."
}

# Ensure removeDeletedMessages exists
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

    $show = $show.Replace("    function appendMessage(message) {", $removeFn + "`r`n    function appendMessage(message) {")
    Write-Host "Added removeDeletedMessages function."
}

# Add independent polling function
if ($show -notmatch "function wmcPollDeletedMessages") {
$pollDeletedJs = @'

    async function wmcPollDeletedMessages() {
        if (!wmcChatDeletedIdsEndpoint) return;

        try {
            const response = await fetch(wmcChatDeletedIdsEndpoint, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) return;

            const data = await response.json();

            if (Array.isArray(data.deleted_ids)) {
                removeDeletedMessages(data.deleted_ids);
            }
        } catch (error) {
            // Silent fail.
        }
    }

'@

    $show = $show.Replace("    async function wmcPollNewMessages() {", $pollDeletedJs + "`r`n    async function wmcPollNewMessages() {")
    Write-Host "Added independent deleted message polling function."
}

# Add interval call
if ($show -notmatch "setInterval\(wmcPollDeletedMessages") {
    $show = $show.Replace(
"    setInterval(wmcPollNewMessages, 3000);
    setTimeout(wmcPollNewMessages, 1200);",
"    setInterval(wmcPollNewMessages, 3000);
    setInterval(wmcPollDeletedMessages, 3000);
    setTimeout(wmcPollNewMessages, 1200);
    setTimeout(wmcPollDeletedMessages, 1500);"
    )
    Write-Host "Added deleted message polling interval."
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
Write-Host "Chat unsend receiver realtime v4 fixed."
Write-Host "Backups saved to: $backupDir"