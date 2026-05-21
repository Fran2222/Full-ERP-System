<?php

namespace App\Helpers;

class ActionButtonHelper
{
    public static function viewEdit(
        ?string $viewUrl,
        ?string $editUrl,
        string $viewTitle = 'View',
        string $editTitle = 'Edit'
    ): string {
        $html = '<div class="d-flex align-items-center justify-content-end gap-2">';

        if ($viewUrl) {
            $html .= '
                <a href="' . e($viewUrl) . '"
                   class="btn btn-sm btn-info d-inline-flex align-items-center justify-content-center"
                   title="' . e($viewTitle) . '"
                   aria-label="' . e($viewTitle) . '"
                   style="width: 34px; height: 30px; padding: 0; border-radius: 6px;">
                    <i class="icon d-inline-flex align-items-center justify-content-center" style="line-height: 1;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M2 12C2 12 5.6 5 12 5C18.4 5 22 12 22 12C22 12 18.4 19 12 19C5.6 19 2 12 2 12Z"
                                  stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"
                                  stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </i>
                </a>
            ';
        }

        if ($editUrl) {
            $html .= '
                <a href="' . e($editUrl) . '"
                   class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center"
                   title="' . e($editTitle) . '"
                   aria-label="' . e($editTitle) . '"
                   style="width: 34px; height: 30px; padding: 0; border-radius: 6px;">
                    <i class="icon d-inline-flex align-items-center justify-content-center" style="line-height: 1;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M13.747 3.41095L20.589 10.2529L7.84302 23H1.00098V16.157L13.747 3.41095Z"
                                  stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </i>
                </a>
            ';
        }

        $html .= '</div>';

        return $html;
    }

    public static function editDelete(
        ?string $editUrl,
        ?string $deleteUrl,
        string $name,
        string $deleteClass = 'delete-action-btn',
        string $editTitle = 'Edit',
        string $deleteTitle = 'Delete'
    ): string {
        $html = '<div class="d-flex align-items-center justify-content-end gap-2">';

        if ($editUrl) {
            $html .= '
                <a href="' . e($editUrl) . '"
                   class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center"
                   title="' . e($editTitle) . '"
                   aria-label="' . e($editTitle) . '"
                   style="width: 34px; height: 30px; padding: 0; border-radius: 6px;">
                    <i class="icon d-inline-flex align-items-center justify-content-center" style="line-height: 1;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M13.747 3.41095L20.589 10.2529L7.84302 23H1.00098V16.157L13.747 3.41095Z"
                                  stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </i>
                </a>
            ';
        }

        if ($deleteUrl) {
            $html .= '
                <form action="' . e($deleteUrl) . '" method="POST" class="delete-form d-inline-flex m-0">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '

                    <button type="submit"
                            class="btn btn-sm btn-danger ' . e($deleteClass) . ' d-inline-flex align-items-center justify-content-center"
                            data-name="' . e($name) . '"
                            title="' . e($deleteTitle) . '"
                            aria-label="' . e($deleteTitle) . '"
                            style="width: 34px; height: 30px; padding: 0; border-radius: 6px;">
                        <i class="icon d-inline-flex align-items-center justify-content-center" style="line-height: 1;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M3 6H5H21" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M19 6L18.2 19C18.1 20.1 17.2 21 16.1 21H7.9C6.8 21 5.9 20.1 5.8 19L5 6" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M10 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M14 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M9 6V4C9 3.4 9.4 3 10 3H14C14.6 3 15 3.4 15 4V6" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </i>
                    </button>
                </form>
            ';
        }

        $html .= '</div>';

        return $html;
    }
}
