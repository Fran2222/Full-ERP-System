@push('scripts')
    {{-- Flatpickr Date Picker --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Sortable for CRM drag and drop --}}
    <script src="{{ asset('vendor/sortable/Sortable.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /*
             * Bootstrap tooltips
             */
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            /*
             * SweetAlert toast helper
             */
            function crmToast(icon, title) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: icon,
                        title: title,
                        showConfirmButton: false,
                        timer: 1800,
                        timerProgressBar: true
                    });
                } else {
                    if (icon === 'error') {
                        alert(title);
                    } else {
                        console.log(title);
                    }
                }
            }

            function crmFormatCount(count) {
                count = parseInt(count || 0, 10);
                return String(count).padStart(2, '0');
            }

            function crmUpdateStageCount(stageId, changeAmount) {
                const countEl = document.querySelector('[data-stage-count="' + stageId + '"]');

                if (!countEl) {
                    return;
                }

                const currentCount = parseInt(countEl.textContent.trim(), 10) || 0;
                const newCount = Math.max(0, currentCount + changeAmount);

                countEl.textContent = crmFormatCount(newCount);
            }

            /*
             * Bootstrap form validation
             */
            document.querySelectorAll('.needs-validation').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated');
                }, false);
            });

            /*
             * CRM New Lead date pickers
             */
            if (typeof flatpickr !== 'undefined') {
                const expectedCloseDate = document.getElementById('expected_close_date');
                const nextFollowUpDate = document.getElementById('next_follow_up_date');

                if (expectedCloseDate) {
                    flatpickr(expectedCloseDate, {
                        dateFormat: 'Y-m-d',
                        allowInput: false,
                        clickOpens: true,
                        disableMobile: true
                    });
                }

                if (nextFollowUpDate) {
                    flatpickr(nextFollowUpDate, {
                        dateFormat: 'Y-m-d',
                        allowInput: false,
                        clickOpens: true,
                        disableMobile: true
                    });
                }
            }

            /*
             * New Lead modal stage auto-fill
             */
            document.querySelectorAll('.js-crm-new-lead, .js-open-new-lead-modal').forEach(function (button) {
                button.addEventListener('click', function () {
                    const stageId = this.dataset.stageId || '';
                    const stageName = this.dataset.stageName || '';

                    const stageInput = document.getElementById('crm_new_lead_stage_id');
                    const stageNameInput = document.getElementById('crm_new_lead_stage_name');

                    if (stageInput) {
                        stageInput.value = stageId;
                    }

                    if (stageNameInput) {
                        stageNameInput.value = stageName;
                    }
                });
            });

            /*
             * Re-open New Lead modal after validation error
             */
            const oldStageId = "{{ old('stage_id') }}";

            if (oldStageId) {
                const modalEl = document.getElementById('crmNewLeadModal');

                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            }

            /*
             * Rename Stage modal auto-fill
             */
            document.querySelectorAll('.js-rename-stage').forEach(function (button) {
                button.addEventListener('click', function () {
                    const form = document.getElementById('crmRenameStageForm');
                    const input = document.getElementById('crmRenameStageName');

                    if (form && input) {
                        form.action = this.dataset.action;
                        input.value = this.dataset.stageName || '';
                    }
                });
            });

            /*
             * Change Stage Color modal auto-fill
             */
            document.querySelectorAll('.js-change-stage-color').forEach(function (button) {
                button.addEventListener('click', function () {
                    const form = document.getElementById('crmChangeStageColorForm');
                    const label = document.getElementById('crmChangeStageColorStageName');
                    const currentColor = this.dataset.currentColor || '';

                    if (form) {
                        form.action = this.dataset.action;
                    }

                    if (label) {
                        label.textContent = 'Select a color for "' + (this.dataset.stageName || 'this stage') + '".';
                    }

                    document.querySelectorAll('.crm-stage-color-option').forEach(function (option) {
                        option.checked = option.value === currentColor;
                    });
                });
            });

            /*
             * Archive Stage SweetAlert confirmation
             */
            document.querySelectorAll('.js-archive-stage-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    if (typeof Swal === 'undefined') {
                        if (confirm('Archive this stage? Make sure it has no leads inside.')) {
                            form.submit();
                        }

                        return;
                    }

                    Swal.fire({
                        title: 'Archive this stage?',
                        text: 'Make sure it has no leads inside before archiving.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, archive it',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            /*
             * Archive Lead SweetAlert confirmation
             */
            document.querySelectorAll('.js-archive-lead-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    if (typeof Swal === 'undefined') {
                        if (confirm('Archive this lead?')) {
                            form.submit();
                        }

                        return;
                    }

                    Swal.fire({
                        title: 'Archive this lead?',
                        text: 'This lead will be removed from the active pipeline.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, archive it',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            /*
             * Pipeline search
             */
            const searchInput = document.getElementById('crmPipelineSearch');

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const keyword = this.value.toLowerCase().trim();
                    const cards = document.querySelectorAll('.crm-lead-card');

                    cards.forEach(function (card) {
                        const haystack = (card.dataset.search || card.innerText || '').toLowerCase();

                        card.style.display = haystack.includes(keyword) ? '' : 'none';
                    });
                });
            }

            /*
             * Drag and drop stage / column reorder
             */
            const stageRow = document.getElementById('crmPipelineStageRow');

            if (stageRow && typeof Sortable !== 'undefined') {
                new Sortable(stageRow, {
                    animation: 150,
                    draggable: '[data-stage-column-id]',
                    handle: '[data-stage-drag-handle]',
                    ghostClass: 'crm-sortable-ghost',
                    chosenClass: 'crm-sortable-chosen',

                    onEnd: function () {
                        const stages = Array.from(stageRow.querySelectorAll('[data-stage-column-id]'))
                            .map(function (stage, index) {
                                return {
                                    id: stage.dataset.stageColumnId,
                                    position: index + 1
                                };
                            });

                        fetch("{{ route('crm.pipeline.stages.reorder') }}", {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                stages: stages
                            }),
                        })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Failed to update stage order.');
                            }

                            return response.json();
                        })
                        .then(function (data) {
                            if (!data.success) {
                                throw new Error(data.message || 'Failed to update stage order.');
                            }

                            crmToast('success', 'Stage order updated.');
                        })
                        .catch(function (error) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Unable to update stage order',
                                    text: error.message || 'Please try again.'
                                });
                            } else {
                                alert(error.message || 'Unable to update stage order.');
                            }
                        });
                    }
                });
            }

            /*
             * Drag and drop leads between stages
             */
            if (typeof Sortable !== 'undefined') {
                document.querySelectorAll('.crm-sortable-group').forEach(function (group) {
                    new Sortable(group, {
                        group: 'crm-pipeline',
                        animation: 150,
                        draggable: '.crm-lead-card',
                        ghostClass: 'crm-sortable-ghost',
                        chosenClass: 'crm-sortable-chosen',

                        onEnd: function (event) {
                            const card = event.item;
                            const leadId = card.dataset.leadId;
                            const newStageId = event.to.dataset.stageId;
                            const oldStageId = event.from.dataset.stageId;

                            if (!leadId || !newStageId) {
                                return;
                            }

                            if (String(newStageId) === String(oldStageId)) {
                                return;
                            }

                            fetch("{{ url('/crm/leads') }}/" + leadId + "/stage", {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                },
                                body: JSON.stringify({
                                    stage_id: newStageId,
                                }),
                            })
                            .then(function (response) {
                                if (!response.ok) {
                                    throw new Error('Failed to update lead stage.');
                                }

                                return response.json();
                            })
                            .then(function (data) {
                                if (!data.success) {
                                    throw new Error(data.message || 'Failed to update lead stage.');
                                }

                                crmUpdateStageCount(oldStageId, -1);
                                crmUpdateStageCount(newStageId, 1);

                                crmToast('success', 'Lead moved successfully.');
                            })
                            .catch(function (error) {
                                /*
                                 * Revert card position if database update fails.
                                 */
                                if (event.from && event.item) {
                                    const children = event.from.children;

                                    if (event.oldIndex >= children.length) {
                                        event.from.appendChild(event.item);
                                    } else {
                                        event.from.insertBefore(event.item, children[event.oldIndex]);
                                    }
                                }

                                crmToast('error', error.message || 'Unable to move lead. Please try again.');
                            });
                        },
                    });
                });
            }

            /*
             * Session toasts
             */
            @if(session('warning'))
                crmToast('warning', @json(session('warning')));
            @endif

            @if(session('success'))
                crmToast('success', @json(session('success')));
            @endif
        });
    </script>
@endpush