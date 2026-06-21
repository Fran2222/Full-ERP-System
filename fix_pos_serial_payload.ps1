$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$bladePath = "resources\views\pos\terminal.blade.php"

Copy-Item $bladePath "$bladePath.bak-pos-serial-payload-fix-$stamp" -Force

$content = Get-Content $bladePath -Raw

$oldSubmit = @'
                submitItems.value = JSON.stringify(Array.from(cart.values()).map(item => ({
                    id: item.id,
                    qty: item.qty,
                })));
'@

$newSubmit = @'
                const preparedItems = Array.from(cart.values()).map(item => {
                    const serialIds = Array.isArray(item.serial_ids)
                        ? item.serial_ids.map(id => String(id)).filter(Boolean)
                        : [];
                    const serialNumbers = Array.isArray(item.serial_numbers)
                        ? item.serial_numbers.map(serial => String(serial)).filter(Boolean)
                        : [];

                    return {
                        id: item.id,
                        item_id: item.id,
                        qty: item.serialized ? serialIds.length : item.qty,
                        quantity: item.serialized ? serialIds.length : item.qty,
                        is_serialized: !!item.serialized,
                        serial_ids: serialIds,
                        serial_numbers: serialNumbers,
                    };
                });

                const missingSerialItem = preparedItems.find(item => item.is_serialized && (!item.serial_ids || item.serial_ids.length < 1));
                if (missingSerialItem) {
                    event.preventDefault();
                    alert('Please select serial number(s) before completing the sale.');
                    completeBtn.disabled = false;
                    completeBtn.textContent = 'Complete Sale';
                    return;
                }

                submitItems.value = JSON.stringify(preparedItems);
'@

if ($content -notmatch "preparedItems" -and $content.Contains($oldSubmit)) {
    $content = $content.Replace($oldSubmit, $newSubmit)
    Write-Host "Updated POS submit payload to include serial_ids / serial_numbers."
} elseif ($content -match "preparedItems") {
    Write-Host "POS submit payload fix already exists."
} else {
    throw "Could not find old submitItems payload block. Please send terminal.blade.php current copy."
}

$oldCartControls = @'
                        <div class="pos-cart-controls">
                            <div class="pos-qty-controls">
                                <button type="button" data-action="minus" data-id="${item.id}">&minus;</button>
                                <span class="pos-line-qty">${item.qty}</span>
                                <button type="button" data-action="plus" data-id="${item.id}">+</button>
                            </div>
                            <button type="button" class="pos-remove-line" data-action="remove" data-id="${item.id}">Remove</button>
                        </div>
'@

$newCartControls = @'
                        ${item.serialized && Array.isArray(item.serial_numbers) && item.serial_numbers.length ? `
                            <div class="pos-cart-serials small text-muted mt-2">Serial(s): ${escapeHtml(item.serial_numbers.join(', '))}</div>
                        ` : ''}
                        <div class="pos-cart-controls">
                            <div class="pos-qty-controls">
                                <button type="button" data-action="minus" data-id="${item.id}">&minus;</button>
                                <span class="pos-line-qty">${item.qty}</span>
                                <button type="button" data-action="plus" data-id="${item.id}">+</button>
                            </div>
                            ${item.serialized ? `<button type="button" class="pos-remove-line" data-action="edit-serials" data-id="${item.id}">Edit Serials</button>` : ''}
                            <button type="button" class="pos-remove-line" data-action="remove" data-id="${item.id}">Remove</button>
                        </div>
'@

if ($content -notmatch "pos-cart-serials" -and $content.Contains($oldCartControls)) {
    $content = $content.Replace($oldCartControls, $newCartControls)
    Write-Host "Added serial display + Edit Serials button in cart."
} elseif ($content -match "pos-cart-serials") {
    Write-Host "Serial display in cart already exists."
} else {
    Write-Host "Cart display block not changed. Submit payload fix still applied."
}

Set-Content $bladePath $content -NoNewline

php -l $bladePath
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "POS serial payload fix applied. Backup: $bladePath.bak-pos-serial-payload-fix-$stamp"
