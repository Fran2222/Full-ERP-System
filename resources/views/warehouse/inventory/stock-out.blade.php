<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')
        <x-warehouse.item-picker-modal />
        <script>window.WMC_ITEM_PICKER_BASE = "{{ url('') }}";</script>

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            $canUseStockOut = $user && (
                (method_exists($user, 'canUseStockOut') && $user->canUseStockOut())
                || $user->hasAnyRole(['super-admin', 'super admin', 'Super Admin', 'Super Administrator', 'admin', 'Admin', 'bod', 'BOD', 'Bod', 'Board of Directors', 'Board Of Directors'])
            );

            abort_unless($canUseStockOut, 403, 'Only BOD or Admin can use Stock Out.');
        @endphp

        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Stock Out / Issuance</h4>
                        <p class="text-secondary mb-0">
                            Issue stock out from a selected warehouse location.
                        </p>
                    </div>

                    @can('warehouse.inventory.view')
                        <a href="{{ route('warehouse.inventory') }}" class="btn btn-outline-secondary">
                            Back to Inventory
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4">
                        <div class="fw-semibold mb-2">Please fix the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('warehouse.stock-out.store') }}">
                    @csrf

                    @include('warehouse.inventory._movement-form-fields')

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        @can('warehouse.inventory.view')
                            <a href="{{ route('warehouse.inventory') }}" class="btn btn-outline-secondary px-4">
                                Cancel
                            </a>
                        @endcan

                        @if($canUseStockOut)
                            <button type="submit" class="btn btn-primary px-4">
                                Save Stock Out
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            #wmcItemPickerModal .modal-content {
                border: 1px solid rgba(58, 87, 232, 0.24) !important;
                border-top: 5px solid #3a57e8 !important;
                border-radius: 18px !important;
                box-shadow: 0 25px 70px rgba(35, 45, 66, 0.28) !important;
                overflow: hidden !important;
            }

            #wmcItemPickerModal .modal-header {
                background: linear-gradient(90deg, rgba(58, 87, 232, 0.10), rgba(255, 255, 255, 1)) !important;
                border-bottom: 1px solid rgba(58, 87, 232, 0.14) !important;
            }

            #wmcItemPickerModal .wmc-item-picker-photo {
                border: 2px solid rgba(58, 87, 232, 0.35) !important;
                background: linear-gradient(145deg, #f7f9ff, #ffffff) !important;
                box-shadow: 0 10px 22px rgba(58, 87, 232, 0.16) !important;
                cursor: pointer !important;
                position: relative !important;
                border-radius: 14px !important;
            }

            #wmcItemPickerModal .wmc-item-picker-photo::after {
                content: "Zoom";
                position: absolute;
                right: 6px;
                bottom: 6px;
                background: #3a57e8;
                color: #fff;
                font-size: 10px;
                font-weight: 700;
                padding: 5px 7px;
                border-radius: 999px;
                z-index: 2;
                pointer-events: none;
            }

            .wmc-stockout-photo-zoom-overlay {
                position: fixed;
                inset: 0;
                background: rgba(20, 24, 38, 0.76);
                backdrop-filter: blur(3px);
                z-index: 99999;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .wmc-stockout-photo-zoom-overlay.show { display: flex; }

            .wmc-stockout-photo-zoom-panel {
                width: min(900px, 96vw);
                max-height: 90vh;
                background: #fff;
                border-radius: 18px;
                border: 1px solid rgba(58, 87, 232, 0.25);
                border-top: 5px solid #3a57e8;
                box-shadow: 0 30px 90px rgba(0, 0, 0, 0.38);
                overflow: hidden;
                position: relative;
            }

            .wmc-stockout-photo-zoom-close {
                position: absolute;
                top: 13px;
                right: 16px;
                width: 36px;
                height: 36px;
                border: 0;
                background: rgba(58, 87, 232, 0.10);
                color: #232d42;
                border-radius: 999px;
                font-size: 26px;
                line-height: 30px;
                cursor: pointer;
                z-index: 2;
            }

            .wmc-stockout-photo-zoom-header {
                padding: 18px 62px 14px 22px;
                background: linear-gradient(90deg, rgba(58, 87, 232, 0.10), rgba(255, 255, 255, 1));
                border-bottom: 1px solid rgba(58, 87, 232, 0.14);
            }

            .wmc-stockout-photo-zoom-title {
                font-size: 18px;
                font-weight: 700;
                color: #232d42;
            }

            .wmc-stockout-photo-zoom-subtitle {
                font-size: 13px;
                color: #6c757d;
                margin-top: 2px;
            }

            .wmc-stockout-photo-zoom-body {
                min-height: 420px;
                max-height: calc(90vh - 90px);
                overflow: auto;
                padding: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                background:
                    linear-gradient(45deg, rgba(58, 87, 232, 0.04) 25%, transparent 25%),
                    linear-gradient(-45deg, rgba(58, 87, 232, 0.04) 25%, transparent 25%),
                    linear-gradient(45deg, transparent 75%, rgba(58, 87, 232, 0.04) 75%),
                    linear-gradient(-45deg, transparent 75%, rgba(58, 87, 232, 0.04) 75%);
                background-size: 24px 24px;
                background-position: 0 0, 0 12px, 12px -12px, -12px 0px;
            }

            .wmc-stockout-photo-zoom-body img {
                max-width: 100%;
                max-height: calc(90vh - 150px);
                object-fit: contain;
                border-radius: 12px;
                background: #fff;
            }
        </style>

        <div id="wmcStockoutPhotoZoomOverlay" class="wmc-stockout-photo-zoom-overlay" aria-hidden="true">
            <div class="wmc-stockout-photo-zoom-panel">
                <button type="button" class="wmc-stockout-photo-zoom-close" aria-label="Close preview">&times;</button>
                <div class="wmc-stockout-photo-zoom-header">
                    <div class="wmc-stockout-photo-zoom-title">Item Photo Preview</div>
                    <div class="wmc-stockout-photo-zoom-subtitle" id="wmcStockoutPhotoZoomSubtitle">Warehouse item image</div>
                </div>
                <div class="wmc-stockout-photo-zoom-body">
                    <img id="wmcStockoutPhotoZoomImg" src="" alt="Item Photo Preview">
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const overlay = document.getElementById('wmcStockoutPhotoZoomOverlay');
                const closeBtn = overlay ? overlay.querySelector('.wmc-stockout-photo-zoom-close') : null;
                const zoomImg = document.getElementById('wmcStockoutPhotoZoomImg');
                const subtitle = document.getElementById('wmcStockoutPhotoZoomSubtitle');

                function visiblePhoto() {
                    const img = document.getElementById('wmcItemPickerPhoto');
                    if (!img || img.classList.contains('d-none') || !img.getAttribute('src')) return null;
                    return img;
                }

                function openZoom() {
                    const img = visiblePhoto();
                    if (!img || !overlay || !zoomImg) return;

                    zoomImg.src = img.src;
                    const code = document.getElementById('wmcItemPickerCode')?.textContent?.trim() || '';
                    const name = document.getElementById('wmcItemPickerName')?.textContent?.trim() || '';
                    if (subtitle) subtitle.textContent = [code, name].filter(Boolean).join(' - ') || 'Warehouse item image';
                    overlay.classList.add('show');
                    overlay.setAttribute('aria-hidden', 'false');
                }

                function closeZoom() {
                    if (!overlay) return;
                    overlay.classList.remove('show');
                    overlay.setAttribute('aria-hidden', 'true');
                }

                document.addEventListener('click', function (event) {
                    const photoBox = event.target.closest('#wmcItemPickerModal .wmc-item-picker-photo');
                    if (photoBox) openZoom();
                    if (event.target === overlay) closeZoom();
                });

                if (closeBtn) closeBtn.addEventListener('click', closeZoom);
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') closeZoom();
                });
            });
        </script>
    @endpush

</x-app-layout>