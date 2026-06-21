<div class="dropdown d-inline-block">
    <button class="wmc-action-dot" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="dynamic" aria-expanded="false" title="More actions">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="5" r="1.8" fill="currentColor"/>
            <circle cx="12" cy="12" r="1.8" fill="currentColor"/>
            <circle cx="12" cy="19" r="1.8" fill="currentColor"/>
        </svg>
    </button>

    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <button type="button"
                    class="dropdown-item wmc-preview-file-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#previewFileModal"
                    data-preview-url="{{ route('project-files.preview', $file->id) }}"
                    data-download-url="{{ route('project-files.download', $file->id) }}"
                    data-file-name="{{ $file->file_name }}"
                    data-file-mime="{{ $file->mime_type }}">
                <i class="ri-eye-line me-2"></i>Preview
            </button>
        </li>

        <li>
            <a class="dropdown-item" href="{{ route('project-files.download', $file->id) }}">
                <i class="ri-download-2-line me-2"></i>Download
            </a>
        </li>

        @can('projects_mgmt.edit')
            <li>
                <button type="button"
                        class="dropdown-item"
                        data-bs-toggle="modal"
                        data-bs-target="#renameFileModal"
                        data-file-id="{{ $file->id }}"
                        data-file-name="{{ $file->file_name }}">
                    <i class="ri-edit-line me-2"></i>Rename
                </button>
            </li>

            <li>
                <button type="button"
                        class="dropdown-item"
                        data-bs-toggle="modal"
                        data-bs-target="#moveFileModal"
                        data-file-id="{{ $file->id }}">
                    <i class="ri-folder-transfer-line me-2"></i>Organize / Move
                </button>
            </li>

            <li>
                <button type="button"
                        class="dropdown-item"
                        data-bs-toggle="modal"
                        data-bs-target="#folderColorModal">
                    <i class="ri-palette-line me-2"></i>Folder Color
                </button>
            </li>
        @endcan

        @can('projects_mgmt.delete')
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST"
                      action="{{ route('project-files.destroy', $file->id) }}"
                      class="wmc-swal-form"
                      data-swal-title="Delete this file?"
                      data-swal-text="This cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="ri-delete-bin-line me-2"></i>Delete
                    </button>
                </form>
            </li>
        @endcan
    </ul>
</div>
