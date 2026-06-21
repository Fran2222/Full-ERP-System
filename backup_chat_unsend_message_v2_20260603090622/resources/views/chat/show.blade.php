@extends('layouts.chat-dashboard')

@section('content')
@php
    $onlineCutoff = now()->subMinutes(2);
    $other = $conversation->otherParticipantFor(auth()->id());

    $formatUserName = function ($user) {
        return $user ? ($user->full_name ?: $user->email ?: 'User') : 'User';
    };

    $isOnline = $other && $other->last_seen_at && $other->last_seen_at->greaterThanOrEqualTo($onlineCutoff);
    $presenceText = ! $other || ! $other->last_seen_at
        ? 'Offline'
        : ($isOnline ? 'Active now' : 'Active ' . $other->last_seen_at->diffForHumans());
@endphp

<style>
    .wmc-chat-show-card {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(15, 23, 42, .08);
        background: #fff;
    }

    .wmc-chat-thread-header {
        min-height: 78px;
        border-bottom: 1px solid #edf0f5;
        background: #fff;
    }

    .wmc-chat-icon-btn {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid #e6eaf2;
        background: #f8f9fc;
        color: #344054;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all .15s ease;
        flex: 0 0 auto;
    }

    .wmc-chat-icon-btn:hover {
        background: #eef2ff;
        color: #3f51f4;
        border-color: #dfe4ff;
    }

    .wmc-chat-avatar {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        background: #e9edff;
        color: #3f51f4;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        flex: 0 0 auto;
    }

    .wmc-chat-online-dot,
    .wmc-chat-offline-dot {
        width: 13px;
        height: 13px;
        border: 2px solid #fff;
        border-radius: 999px;
        position: absolute;
        right: 0;
        bottom: 1px;
    }

    .wmc-chat-online-dot { background: #22c55e; }
    .wmc-chat-offline-dot { background: #cbd5e1; }

    .wmc-chat-messages {
        height: 430px;
        min-height: 360px;
        overflow-y: auto;
        background:
            radial-gradient(circle at top left, rgba(63, 81, 244, .08), transparent 28%),
            linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
    }

    .wmc-bubble-wrap {
        display: flex;
        margin-bottom: 12px;
    }

    .wmc-bubble-wrap.mine {
        justify-content: flex-end;
    }

    .wmc-bubble {
        display: inline-block;
        width: auto;
        min-width: 44px;
        max-width: min(680px, 78vw);
        border-radius: 20px;
        padding: 10px 14px;
        white-space: pre-wrap;
        word-break: normal;
        overflow-wrap: anywhere;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .07);
    }

    .wmc-bubble.mine {
        background: #3f51f4;
        color: #fff;
        border-bottom-right-radius: 6px;
    }

    .wmc-bubble.other {
        background: #fff;
        color: #172033;
        border-bottom-left-radius: 6px;
    }

    .wmc-chat-composer {
        border-top: 1px solid #edf0f5;
        background: #fff;
    }

    .wmc-chat-input {
        border-radius: 18px;
        resize: none;
        background: #f4f6fb;
        border: 1px solid transparent;
        min-height: 46px;
        max-height: 140px;
    }

    .wmc-chat-input:focus {
        background: #fff;
        border-color: #3f51f4;
        box-shadow: 0 0 0 .2rem rgba(63, 81, 244, .08);
    }

    .wmc-chat-send-btn {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .wmc-chat-send-btn:disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    /* WMC_CHAT_BUBBLE_FIX */
    .wmc-bubble-wrap > div {
        max-width: 78%;
    }

    .wmc-bubble-wrap.mine > div {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .wmc-bubble-wrap.other > div {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .wmc-bubble {
        line-height: 1.45;
    }

    @media (max-width: 768px) {
        .wmc-chat-messages {
            height: 380px;
            min-height: 340px;
        }

        .wmc-bubble-wrap > div {
            max-width: 88%;
        }
    }

    /* WMC_CHAT_SEND_ICON_FIX */
    .wmc-chat-send-btn {
        color: #ffffff !important;
        background: #3f51f4 !important;
        border-color: #3f51f4 !important;
        padding: 0 !important;
    }

    .wmc-chat-send-btn svg {
        display: block !important;
        width: 22px !important;
        height: 22px !important;
        flex: 0 0 auto !important;
    }

    .wmc-chat-send-btn:hover {
        background: #2f3fd8 !important;
        border-color: #2f3fd8 !important;
    }

    /* WMC_CHAT_SERVER_BUBBLE_WIDTH_FIX */
    .wmc-bubble {
        width: fit-content !important;
        max-width: 100% !important;
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: normal !important;
    }

    .wmc-bubble-wrap > div {
        max-width: 78% !important;
    }

    .wmc-bubble-wrap.mine > div {
        align-items: flex-end !important;
    }

    .wmc-bubble-wrap.other > div {
        align-items: flex-start !important;
    }

    /* WMC_CHAT_ATTACHMENTS_CSS */
    .wmc-chat-attachments {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 8px;
    }

    .wmc-chat-attachment-image {
        max-width: min(300px, 100%);
        max-height: 220px;
        border-radius: 14px;
        display: block;
        object-fit: cover;
    }

    .wmc-chat-attachment-video {
        max-width: min(360px, 100%);
        max-height: 260px;
        border-radius: 14px;
        display: block;
        background: #000;
    }

    .wmc-chat-attachment-file {
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 360px;
        padding: 10px 12px;
        border-radius: 14px;
        background: rgba(255,255,255,.92);
        color: #172033;
        text-decoration: none;
        border: 1px solid #e7ebf3;
    }

    .wmc-bubble.mine .wmc-chat-attachment-file {
        background: rgba(255,255,255,.16);
        color: #fff;
        border-color: rgba(255,255,255,.25);
    }

    .wmc-chat-file-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #eef2ff;
        color: #3f51f4;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-weight: 800;
        font-size: 12px;
    }

    .wmc-bubble.mine .wmc-chat-file-icon {
        background: rgba(255,255,255,.22);
        color: #fff;
    }

    .wmc-chat-file-meta {
        min-width: 0;
    }

    .wmc-chat-file-name {
        font-weight: 700;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .wmc-chat-file-size {
        font-size: 11px;
        opacity: .75;
    }

    .wmc-chat-attach-btn {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        border: 1px solid #e6eaf2;
        background: #f8f9fc;
        color: #3f51f4;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex: 0 0 auto;
    }

    .wmc-chat-attach-btn:hover {
        background: #eef2ff;
    }

    .wmc-chat-selected-files {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 0 0 8px 54px;
    }

    .wmc-chat-selected-file-pill {
        border-radius: 999px;
        background: #eef2ff;
        color: #334155;
        padding: 5px 10px;
        font-size: 12px;
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* WMC_CHAT_ATTACH_FORCE_CSS */
    .wmc-chat-attach-btn {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        border: 1px solid #e6eaf2;
        background: #f8f9fc;
        color: #3f51f4;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex: 0 0 auto;
        margin-right: 2px;
    }

    .wmc-chat-attach-btn:hover {
        background: #eef2ff;
        border-color: #dfe4ff;
    }

    .wmc-chat-selected-files {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 0 0 8px 54px;
    }

    .wmc-chat-selected-file-pill {
        border-radius: 999px;
        background: #eef2ff;
        color: #334155;
        padding: 5px 10px;
        font-size: 12px;
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* WMC_CHAT_PAPERCLIP_FORCE_VISIBLE */
    .wmc-chat-attach-btn {
        width: 48px !important;
        height: 48px !important;
        min-width: 48px !important;
        border-radius: 999px !important;
        border: 1px solid #e6eaf2 !important;
        background: #f8f9fc !important;
        color: #3f51f4 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        flex: 0 0 48px !important;
        padding: 0 !important;
    }

    .wmc-chat-attach-btn svg {
        display: block !important;
        width: 21px !important;
        height: 21px !important;
    }

    .wmc-chat-selected-files {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 0 0 8px 54px;
    }

    .wmc-chat-selected-files.d-none {
        display: none !important;
    }

    .wmc-chat-selected-file-pill {
        border-radius: 999px;
        background: #eef2ff;
        color: #334155;
        padding: 5px 10px;
        font-size: 12px;
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* WMC_CHAT_IMAGE_MODAL_CSS */
    .wmc-chat-image-preview-btn {
        border: 0;
        background: transparent;
        padding: 0;
        display: block;
        cursor: zoom-in;
        max-width: 100%;
        text-align: left;
    }

    .wmc-chat-image-preview-btn:focus {
        outline: 2px solid rgba(63, 81, 244, .45);
        outline-offset: 3px;
        border-radius: 14px;
    }

    .wmc-chat-image-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .82);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .wmc-chat-image-modal-backdrop.show {
        display: flex;
    }

    .wmc-chat-image-modal-panel {
        width: min(1100px, 96vw);
        height: min(780px, 92vh);
        background: #0f172a;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 80px rgba(0, 0, 0, .38);
        display: flex;
        flex-direction: column;
    }

    .wmc-chat-image-modal-header {
        min-height: 58px;
        padding: 12px 16px;
        color: #fff;
        border-bottom: 1px solid rgba(255,255,255,.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .wmc-chat-image-modal-title {
        font-weight: 700;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .wmc-chat-image-modal-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
    }

    .wmc-chat-image-modal-btn {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.18);
        background: rgba(255,255,255,.1);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        cursor: pointer;
    }

    .wmc-chat-image-modal-btn:hover {
        background: rgba(255,255,255,.2);
    }

    .wmc-chat-image-modal-body {
        position: relative;
        flex: 1;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: grab;
        user-select: none;
    }

    .wmc-chat-image-modal-body.dragging {
        cursor: grabbing;
    }

    #wmcChatPreviewImage {
        max-width: 100%;
        max-height: 100%;
        transform-origin: center center;
        transition: transform .08s ease-out;
        user-select: none;
        pointer-events: none;
        border-radius: 8px;
    }

    .wmc-chat-image-zoom-label {
        color: rgba(255,255,255,.78);
        font-size: 12px;
        min-width: 48px;
        text-align: center;
    }

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

    /* WMC_CHAT_SEEN_ICON_FIX_V2 */
    .wmc-chat-seen-label {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-weight: 700;
        color: #64748b;
        margin-left: 4px;
    }

    

    /* WMC_CHAT_SEEN_SENT_FINAL_V3 */
    .wmc-chat-seen-status {
        display: inline-flex !important;
        align-items: center !important;
        gap: 3px !important;
        margin-left: 5px !important;
        color: #64748b !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        vertical-align: middle !important;
    }

    .wmc-chat-seen-status svg {
        width: 12px !important;
        height: 12px !important;
        display: block !important;
        flex: 0 0 auto !important;
    }

    .wmc-chat-seen-label {
        display: inline !important;
        margin-left: 0 !important;
        color: #64748b !important;
        font-weight: 700 !important;
    }

    .wmc-chat-seen-label::before {
        content: none !important;
        display: none !important;
    }
</style>

<div class="container-fluid py-4">
    <div class="wmc-chat-show-card">
        <div class="wmc-chat-thread-header d-flex align-items-center justify-content-between px-4 py-3">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <a href="{{ route('chat.index') }}" class="wmc-chat-icon-btn" title="Back to chats" aria-label="Back to chats">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                @php $title = $conversation->displayTitleFor(auth()->id()); @endphp
                <div class="wmc-chat-avatar">
                    {{ strtoupper(substr($title, 0, 1)) }}
                    <span class="{{ $isOnline ? 'wmc-chat-online-dot' : 'wmc-chat-offline-dot' }}"></span>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold mb-0 text-truncate">{{ $title }}</h4>
                    <div class="small {{ $isOnline ? 'text-success' : 'text-muted' }}">{{ $presenceText }}</div>
                </div>
            </div>
        </div>

        <div id="wmcChatMessages" class="wmc-chat-messages p-4">
            @forelse($messages as $chatMessage)
                @php
                    $mine = (int) $chatMessage->sender_id === (int) auth()->id();
                    $senderName = $formatUserName($chatMessage->sender);
                @endphp

                <div class="wmc-bubble-wrap {{ $mine ? 'mine' : 'other' }}" data-message-id="{{ $chatMessage->id }}">
                    <div>
                        @unless($mine)
                            <div class="small text-muted ms-2 mb-1">{{ $senderName }}</div>
                        @endunless
                        <div class="wmc-bubble {{ $mine ? 'mine' : 'other' }}">
                            @if(trim((string) $chatMessage->body) !== '')
                                <div>{!! nl2br(e($chatMessage->body)) !!}</div>
                            @endif

                            @if($chatMessage->attachments->count())
                                <div class="wmc-chat-attachments">
                                    @foreach($chatMessage->attachments as $attachment)
                                        @if($attachment->is_image)
                                            <button type="button"
                                                    class="wmc-chat-image-preview-btn"
                                                    data-chat-image-src="{{ $attachment->url }}"
                                                    data-chat-image-name="{{ e($attachment->original_name) }}">
                                                <img src="{{ $attachment->url }}" alt="{{ $attachment->original_name }}" class="wmc-chat-attachment-image">
                                            </button>
                                        @elseif($attachment->is_video)
                                            <video class="wmc-chat-attachment-video" controls preload="metadata">
                                                <source src="{{ $attachment->url }}" type="{{ $attachment->mime_type }}">
                                                Your browser does not support the video tag.
                                            </video>
                                        @else
                                            <a href="{{ $attachment->url }}" target="_blank" rel="noopener" class="wmc-chat-attachment-file">
                                                <span class="wmc-chat-file-icon">{{ $attachment->is_pdf ? 'PDF' : 'FILE' }}</span>
                                                <span class="wmc-chat-file-meta">
                                                    <span class="wmc-chat-file-name">{{ $attachment->original_name }}</span>
                                                    <span class="wmc-chat-file-size">{{ $attachment->human_size }}</span>
                                                </span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
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
                                @php
                                    $otherReadAtForMeta = \App\Models\Chat\ChatParticipant::where('chat_conversation_id', $conversation->id)
                                        ->where('user_id', '!=', auth()->id())
                                        ->max('last_read_at');

                                    $readLabelForMeta = $otherReadAtForMeta && \Carbon\Carbon::parse($otherReadAtForMeta)->greaterThanOrEqualTo($chatMessage->created_at)
                                        ? 'Seen'
                                        : 'Sent';
                                @endphp
                                <span class="wmc-chat-seen-status">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4.5 12.5L9 17L19.5 6.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <span class="wmc-chat-seen-label">{{ $readLabelForMeta }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div id="wmcChatEmptyState" class="text-center text-muted py-5">
                    No messages yet. Send the first message below.
                </div>
            @endforelse
        </div>

        <div class="wmc-chat-composer p-3">
            <div id="wmcChatSelectedFiles" class="wmc-chat-selected-files d-none"></div>

            <form id="wmcChatSendForm" action="{{ route('chat.send', $conversation) }}" method="POST" class="d-flex align-items-end gap-2" enctype="multipart/form-data">
                @csrf
                <label for="wmcChatAttachments" class="wmc-chat-attach-btn" title="Attach files" aria-label="Attach files">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M21.44 11.05L12.25 20.24C9.9 22.59 6.09 22.59 3.75 20.24C1.41 17.9 1.41 14.09 3.75 11.75L12.94 2.56C14.5 1 17.04 1 18.6 2.56C20.16 4.12 20.16 6.66 18.6 8.22L9.41 17.41C8.63 18.19 7.36 18.19 6.58 17.41C5.8 16.63 5.8 15.36 6.58 14.58L15.07 6.1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </label>
                <input id="wmcChatAttachments" name="attachments[]" type="file" multiple class="d-none"
                       accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.mp4,.mov,.avi,.webm,.mkv,image/*,video/*,application/pdf">

                <textarea id="wmcChatInput" name="body" rows="1" class="form-control wmc-chat-input" maxlength="5000"
                          placeholder="Type a message...">{{ old('body') }}</textarea>
                                <button id="wmcChatSendButton" type="submit" class="btn btn-primary wmc-chat-send-btn" title="Send" aria-label="Send message">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="display:block;">
                        <path d="M3.8 11.2L19.6 4.1C20.25 3.81 20.92 4.48 20.63 5.13L13.53 20.93C13.22 21.62 12.22 21.56 12 20.84L10.05 14.5L3.71 12.55C2.99 12.33 2.93 11.51 3.8 11.2Z" fill="#ffffff"/>
                        <path d="M10.25 14.25L14.25 10.25" stroke="#3f51f4" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                </button>
            </form>
            <div class="small text-muted mt-2 ms-1">Press Enter to send. Shift + Enter for a new line. You can also send attachments without text.</div>
        </div>
    </div>
</div>


<div id="wmcChatImageModal" class="wmc-chat-image-modal-backdrop" aria-hidden="true">
    <div class="wmc-chat-image-modal-panel" role="dialog" aria-modal="true" aria-label="Image preview">
        <div class="wmc-chat-image-modal-header">
            <div id="wmcChatImageModalTitle" class="wmc-chat-image-modal-title">Image preview</div>
            <div class="wmc-chat-image-modal-actions">
                <button type="button" class="wmc-chat-image-modal-btn" id="wmcChatZoomOut" title="Zoom out">-</button>
                <span class="wmc-chat-image-zoom-label" id="wmcChatZoomLabel">100%</span>
                <button type="button" class="wmc-chat-image-modal-btn" id="wmcChatZoomIn" title="Zoom in">+</button>
                <button type="button" class="wmc-chat-image-modal-btn" id="wmcChatZoomReset" title="Reset zoom">1:1</button>
                <button type="button" class="wmc-chat-image-modal-btn" id="wmcChatImageClose" title="Close">×</button>
            </div>
        </div>
        <div class="wmc-chat-image-modal-body" id="wmcChatImageModalBody">
            <img id="wmcChatPreviewImage" src="" alt="Image preview">
        </div>
    </div>
</div>
<script>
(function () {
    const messages = document.getElementById('wmcChatMessages');
    const form = document.getElementById('wmcChatSendForm');
    const textarea = document.getElementById('wmcChatInput');
    const sendButton = document.getElementById('wmcChatSendButton');
    const fileInput = document.getElementById('wmcChatAttachments');
    const selectedFiles = document.getElementById('wmcChatSelectedFiles');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const wmcChatMessagesEndpoint = "{{ route('chat.messages', $conversation) }}";
    let wmcLastMessageId = {{ (int) ($messages->max('id') ?? 0) }};
    let wmcIsPolling = false;

    function scrollToBottom() {
        if (messages) {
            messages.scrollTop = messages.scrollHeight;
        }
    }

    function isNearBottom() {
        if (!messages) return true;
        return (messages.scrollHeight - messages.scrollTop - messages.clientHeight) < 120;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderAttachments(attachments, mine) {
        if (!Array.isArray(attachments) || attachments.length === 0) {
            return '';
        }

        const rendered = attachments.map(function (attachment) {
            const url = escapeHtml(attachment.url || '#');
            const name = escapeHtml(attachment.name || 'Attachment');
            const mime = escapeHtml(attachment.mime_type || '');
            const size = escapeHtml(attachment.size || '');

            if (attachment.is_image) {
                return `<a href="${url}" target="_blank" rel="noopener">
                    <img src="${url}" alt="${name}" class="wmc-chat-attachment-image">
                </a>`;
            }

            if (attachment.is_video) {
                return `<video class="wmc-chat-attachment-video" controls preload="metadata">
                    <source src="${url}" type="${mime}">
                    Your browser does not support the video tag.
                </video>`;
            }

            return `<a href="${url}" target="_blank" rel="noopener" class="wmc-chat-attachment-file">
                <span class="wmc-chat-file-icon">${attachment.is_pdf ? 'PDF' : 'FILE'}</span>
                <span class="wmc-chat-file-meta">
                    <span class="wmc-chat-file-name">${name}</span>
                    <span class="wmc-chat-file-size">${size}</span>
                </span>
            </a>`;
        }).join('');

        return `<div class="wmc-chat-attachments">${rendered}</div>`;
    }


    function updateSeenLabels(messagesPayload) {
        if (!Array.isArray(messagesPayload)) return;

        messagesPayload.forEach(function (message) {
            if (!message || !message.id || !message.read_label) return;

            const row = document.querySelector(`[data-message-id="${message.id}"]`);
            if (!row) return;

            const label = row.querySelector('.wmc-chat-seen-label');
            if (label) {
                label.textContent = message.read_label;
            }
        });
    }


    function wmcSeenStatusHtml(label) {
        if (!label) return '';

        return `
            <span class="wmc-chat-seen-status">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4.5 12.5L9 17L19.5 6.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="wmc-chat-seen-label">${escapeHtml(label)}</span>
            </span>
        `;
    }

    function ensureSeenStatus(row, label) {
        if (!row || !label) return;

        let meta = row.querySelector('.wmc-chat-message-meta');
        if (!meta) return;

        let status = meta.querySelector('.wmc-chat-seen-status');

        if (!status) {
            meta.insertAdjacentHTML('beforeend', wmcSeenStatusHtml(label));
            status = meta.querySelector('.wmc-chat-seen-status');
        }

        const labelEl = status ? status.querySelector('.wmc-chat-seen-label') : null;
        if (labelEl) {
            labelEl.textContent = label;
        }
    }

    function updateSeenLabels(payload) {
        if (!Array.isArray(payload)) return;

        payload.forEach(function (message) {
            if (!message || !message.id || !message.read_label) return;

            const row = document.querySelector('[data-message-id="' + message.id + '"]');
            if (!row) return;

            ensureSeenStatus(row, message.read_label);
        });
    }

    function initializeSentLabels() {
        document.querySelectorAll('.wmc-bubble-wrap.mine').forEach(function (row) {
            const status = row.querySelector('.wmc-chat-seen-status');
            if (!status) {
                ensureSeenStatus(row, 'Sent');
            }
        });
    }

    function appendMessage(message) {
        if (!messages || !message) return;

        if (message.id && document.querySelector(`[data-message-id="${message.id}"]`)) {
            return;
        }

        const empty = document.getElementById('wmcChatEmptyState');
        if (empty) empty.remove();

        const shouldScroll = isNearBottom();
        const mine = !!message.is_mine;
        const wrapper = document.createElement('div');
        wrapper.className = `wmc-bubble-wrap ${mine ? 'mine' : 'other'}`;

        if (message.id) {
            wrapper.setAttribute('data-message-id', message.id);
        }

        const bodyHtml = message.body ? `<div>${escapeHtml(message.body).replace(/\n/g, '<br>')}</div>` : '';
        const attachmentHtml = renderAttachments(message.attachments || [], mine);

        wrapper.innerHTML = `
            <div>
                ${mine ? '' : `<div class="small text-muted ms-2 mb-1">${escapeHtml(message.sender_name || 'User')}</div>`}
                <div class="wmc-bubble ${mine ? 'mine' : 'other'}">${bodyHtml}${attachmentHtml}</div>
                <div class="wmc-chat-message-meta ${mine ? 'text-end me-2' : 'ms-2'}">
                    ${escapeHtml(message.time || '')}
                    ${mine ? wmcSeenStatusHtml(message.read_label || 'Sent') : ''}
                </div>
            </div>
        `;

        messages.appendChild(wrapper);

        if (message.id) {
            wmcLastMessageId = Math.max(wmcLastMessageId, parseInt(message.id, 10) || 0);
        }

        if (shouldScroll || mine) {
            scrollToBottom();
    initializeSentLabels();
    function initChatImagePreviewModal() {
        const modal = document.getElementById('wmcChatImageModal');
        const modalBody = document.getElementById('wmcChatImageModalBody');
        const preview = document.getElementById('wmcChatPreviewImage');
        const title = document.getElementById('wmcChatImageModalTitle');
        const closeBtn = document.getElementById('wmcChatImageClose');
        const zoomInBtn = document.getElementById('wmcChatZoomIn');
        const zoomOutBtn = document.getElementById('wmcChatZoomOut');
        const resetBtn = document.getElementById('wmcChatZoomReset');
        const zoomLabel = document.getElementById('wmcChatZoomLabel');

        if (!modal || !modalBody || !preview) return;

        let scale = 1;
        let translateX = 0;
        let translateY = 0;
        let isDragging = false;
        let startX = 0;
        let startY = 0;

        function updateTransform() {
            preview.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
            if (zoomLabel) {
                zoomLabel.textContent = Math.round(scale * 100) + '%';
            }
        }

        function resetZoom() {
            scale = 1;
            translateX = 0;
            translateY = 0;
            updateTransform();
        }

        function openModal(src, name) {
            preview.src = src;
            preview.alt = name || 'Image preview';
            if (title) title.textContent = name || 'Image preview';
            resetZoom();
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            preview.src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (event) {
            const btn = event.target.closest('.wmc-chat-image-preview-btn');
            if (!btn) return;

            event.preventDefault();
            openModal(btn.getAttribute('data-chat-image-src'), btn.getAttribute('data-chat-image-name'));
        });

        closeBtn?.addEventListener('click', closeModal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });

        zoomInBtn?.addEventListener('click', function () {
            scale = Math.min(scale + 0.25, 5);
            updateTransform();
        });

        zoomOutBtn?.addEventListener('click', function () {
            scale = Math.max(scale - 0.25, 0.5);
            updateTransform();
        });

        resetBtn?.addEventListener('click', resetZoom);

        modalBody.addEventListener('wheel', function (event) {
            event.preventDefault();
            const delta = event.deltaY < 0 ? 0.15 : -0.15;
            scale = Math.min(Math.max(scale + delta, 0.5), 5);
            updateTransform();
        }, { passive: false });

        modalBody.addEventListener('mousedown', function (event) {
            if (scale <= 1) return;
            isDragging = true;
            modalBody.classList.add('dragging');
            startX = event.clientX - translateX;
            startY = event.clientY - translateY;
        });

        window.addEventListener('mousemove', function (event) {
            if (!isDragging) return;
            translateX = event.clientX - startX;
            translateY = event.clientY - startY;
            updateTransform();
        });

        window.addEventListener('mouseup', function () {
            isDragging = false;
            modalBody.classList.remove('dragging');
        });

        document.addEventListener('keydown', function (event) {
            if (!modal.classList.contains('show')) return;

            if (event.key === 'Escape') {
                closeModal();
            }

            if (event.key === '+' || event.key === '=') {
                scale = Math.min(scale + 0.25, 5);
                updateTransform();
            }

            if (event.key === '-') {
                scale = Math.max(scale - 0.25, 0.5);
                updateTransform();
            }

            if (event.key === '0') {
                resetZoom();
            }
        });
    }

    initChatImagePreviewModal();

        }
    }

    function autoResize() {
        if (!textarea) return;
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 140) + 'px';
    }

    function updateSelectedFiles() {
        if (!fileInput || !selectedFiles) return;

        const files = Array.from(fileInput.files || []);
        selectedFiles.innerHTML = '';

        if (files.length === 0) {
            selectedFiles.classList.add('d-none');
            return;
        }

        files.slice(0, 5).forEach(function (file) {
            const pill = document.createElement('span');
            pill.className = 'wmc-chat-selected-file-pill';
            pill.textContent = file.name;
            selectedFiles.appendChild(pill);
        });

        selectedFiles.classList.remove('d-none');
    }

    async function sendMessage() {
        if (!form || !textarea || !csrfToken) return;

        const body = textarea.value.trim();
        const files = fileInput ? Array.from(fileInput.files || []) : [];

        if (!body && files.length === 0) return;

        sendButton.disabled = true;

        try {
            const formData = new FormData();
            formData.append('body', body);

            files.slice(0, 5).forEach(function (file) {
                formData.append('attachments[]', file);
            });

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Send failed');
            }

            const data = await response.json();
            appendMessage(data.message || {
                body: body,
                time: '',
                sender_name: 'You',
                is_mine: true,
                attachments: []
            });

            textarea.value = '';
            if (fileInput) fileInput.value = '';
            updateSelectedFiles();
            autoResize();
            textarea.focus();

            if (window.wmcRefreshChatBadge) {
                window.wmcRefreshChatBadge();
            }
        } catch (error) {
            alert('Message failed to send. Please check the file type/size and try again.');
        } finally {
            sendButton.disabled = false;
        }
    }

    async function wmcPollNewMessages() {
        if (!wmcChatMessagesEndpoint || wmcIsPolling) return;

        wmcIsPolling = true;

        try {
            const separator = wmcChatMessagesEndpoint.includes('?') ? '&' : '?';
            const response = await fetch(`${wmcChatMessagesEndpoint}${separator}after_id=${wmcLastMessageId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (Array.isArray(data.read_statuses)) {
                updateSeenLabels(data.read_statuses);
            }

            if (Array.isArray(data.messages)) {
                data.messages.forEach(appendMessage);
                updateSeenLabels(data.messages);

                if (data.messages.length > 0 && window.wmcRefreshChatBadge) {
                    window.wmcRefreshChatBadge();
                }
            }

            if (data.latest_id) {
                wmcLastMessageId = Math.max(wmcLastMessageId, parseInt(data.latest_id, 10) || 0);
            }
        } catch (error) {
            // Silent fail to avoid interrupting typing.
        } finally {
            wmcIsPolling = false;
        }
    }

    scrollToBottom();
    initializeSentLabels();
    function initChatImagePreviewModal() {
        const modal = document.getElementById('wmcChatImageModal');
        const modalBody = document.getElementById('wmcChatImageModalBody');
        const preview = document.getElementById('wmcChatPreviewImage');
        const title = document.getElementById('wmcChatImageModalTitle');
        const closeBtn = document.getElementById('wmcChatImageClose');
        const zoomInBtn = document.getElementById('wmcChatZoomIn');
        const zoomOutBtn = document.getElementById('wmcChatZoomOut');
        const resetBtn = document.getElementById('wmcChatZoomReset');
        const zoomLabel = document.getElementById('wmcChatZoomLabel');

        if (!modal || !modalBody || !preview) return;

        let scale = 1;
        let translateX = 0;
        let translateY = 0;
        let isDragging = false;
        let startX = 0;
        let startY = 0;

        function updateTransform() {
            preview.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
            if (zoomLabel) {
                zoomLabel.textContent = Math.round(scale * 100) + '%';
            }
        }

        function resetZoom() {
            scale = 1;
            translateX = 0;
            translateY = 0;
            updateTransform();
        }

        function openModal(src, name) {
            preview.src = src;
            preview.alt = name || 'Image preview';
            if (title) title.textContent = name || 'Image preview';
            resetZoom();
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            preview.src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (event) {
            const btn = event.target.closest('.wmc-chat-image-preview-btn');
            if (!btn) return;

            event.preventDefault();
            openModal(btn.getAttribute('data-chat-image-src'), btn.getAttribute('data-chat-image-name'));
        });

        closeBtn?.addEventListener('click', closeModal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });

        zoomInBtn?.addEventListener('click', function () {
            scale = Math.min(scale + 0.25, 5);
            updateTransform();
        });

        zoomOutBtn?.addEventListener('click', function () {
            scale = Math.max(scale - 0.25, 0.5);
            updateTransform();
        });

        resetBtn?.addEventListener('click', resetZoom);

        modalBody.addEventListener('wheel', function (event) {
            event.preventDefault();
            const delta = event.deltaY < 0 ? 0.15 : -0.15;
            scale = Math.min(Math.max(scale + delta, 0.5), 5);
            updateTransform();
        }, { passive: false });

        modalBody.addEventListener('mousedown', function (event) {
            if (scale <= 1) return;
            isDragging = true;
            modalBody.classList.add('dragging');
            startX = event.clientX - translateX;
            startY = event.clientY - translateY;
        });

        window.addEventListener('mousemove', function (event) {
            if (!isDragging) return;
            translateX = event.clientX - startX;
            translateY = event.clientY - startY;
            updateTransform();
        });

        window.addEventListener('mouseup', function () {
            isDragging = false;
            modalBody.classList.remove('dragging');
        });

        document.addEventListener('keydown', function (event) {
            if (!modal.classList.contains('show')) return;

            if (event.key === 'Escape') {
                closeModal();
            }

            if (event.key === '+' || event.key === '=') {
                scale = Math.min(scale + 0.25, 5);
                updateTransform();
            }

            if (event.key === '-') {
                scale = Math.max(scale - 0.25, 0.5);
                updateTransform();
            }

            if (event.key === '0') {
                resetZoom();
            }
        });
    }

    initChatImagePreviewModal();


    textarea?.addEventListener('input', autoResize);
    fileInput?.addEventListener('change', updateSelectedFiles);

    textarea?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    form?.addEventListener('submit', function (event) {
        event.preventDefault();
        sendMessage();
    });

    setInterval(wmcPollNewMessages, 3000);
    setTimeout(wmcPollNewMessages, 1200);
})();
</script>
@endsection