<style>
/* WMC_CHAT_HEADER_BADGE_STYLE */
.wmc-chat-header-badge {
    position: absolute;
    top: 2px;
    right: 0;
    min-width: 17px;
    height: 17px;
    padding: 0 5px;
    border-radius: 999px;
    background: #dc3545;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    line-height: 17px;
    text-align: center;
    box-shadow: 0 0 0 2px #fff;
    z-index: 3;
}
</style>
<nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
  <div class="container-fluid navbar-inner">

    <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
      <i class="icon">
        <svg width="20px" height="20px" viewBox="0 0 24 24">
          <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
        </svg>
      </i>
    </div>

    <div class="input-group search-input">
      <span class="input-group-text" id="search-input">
        <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="11.7669" cy="11.7666" r="8.98856" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
          <path d="M18.0186 18.4851L21.5426 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      </span>
      <input type="search" class="form-control" placeholder="Search...">
    </div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
      <span class="navbar-toggler-icon">
        <span class="navbar-toggler-bar bar1 mt-2"></span>
        <span class="navbar-toggler-bar bar2"></span>
        <span class="navbar-toggler-bar bar3"></span>
      </span>
    </button>

    @php
      $authUser = auth()->user();

      $displayName =
          $authUser?->full_name
          ?? $authUser?->name
          ?? 'User';

      $displayRole =
          $authUser && method_exists($authUser, 'getRoleNames') && $authUser->getRoleNames()->isNotEmpty()
              ? $authUser->getRoleNames()->first()
              : ($authUser?->user_type
                  ? str_replace('_', ' ', $authUser->user_type)
                  : 'User');
    @endphp

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto navbar-list mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a href="#" class="search-toggle nav-link" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <img src="{{ asset('images/Flag/flag001.png') }}" class="img-fluid rounded-circle" alt="user" style="height: 30px; min-width: 30px; width: 30px;">
            <span class="bg-primary"></span>
          </a>
          <div class="sub-drop dropdown-menu dropdown-menu-end p-0" aria-labelledby="dropdownMenuButton2">
            <div class="card shadow-none m-0 border-0">
              <div class="p-0">
                <ul class="list-group list-group-flush">
                  <li class="iq-sub-card list-group-item"><a class="p-0" href="#"><img src="{{ asset('images/Flag/flag-03.png') }}" alt="img-flaf" class="img-fluid me-2" style="width: 15px;height: 15px;min-width: 15px;"/>Spanish</a></li>
                  <li class="iq-sub-card list-group-item"><a class="p-0" href="#"><img src="{{ asset('images/Flag/flag-04.png') }}" alt="img-flaf" class="img-fluid me-2" style="width: 15px;height: 15px;min-width: 15px;"/>Italian</a></li>
                  <li class="iq-sub-card list-group-item"><a class="p-0" href="#"><img src="{{ asset('images/Flag/flag-02.png') }}" alt="img-flaf" class="img-fluid me-2" style="width: 15px;height: 15px;min-width: 15px;"/>French</a></li>
                  <li class="iq-sub-card list-group-item"><a class="p-0" href="#"><img src="{{ asset('images/Flag/flag-05.png') }}" alt="img-flaf" class="img-fluid me-2" style="width: 15px;height: 15px;min-width: 15px;"/>German</a></li>
                  <li class="iq-sub-card list-group-item"><a class="p-0" href="#"><img src="{{ asset('images/Flag/flag-06.png') }}" alt="img-flaf" class="img-fluid me-2" style="width: 15px;height: 15px;min-width: 15px;"/>Japanese</a></li>
                </ul>
              </div>
            </div>
          </div>
        </li>

                @include('partials.dashboard.notifications-dropdown')

        <li class="nav-item dropdown">
          @php
              // WMC_CHAT_UNREAD_BADGE_COUNT
              $wmcChatUnreadCount = 0;

              try {
                  if (auth()->check()
                      && \Illuminate\Support\Facades\Schema::hasTable('chat_participants')
                      && \Illuminate\Support\Facades\Schema::hasTable('chat_messages')) {
                      $wmcChatParticipantRows = \Illuminate\Support\Facades\DB::table('chat_participants')
                          ->where('user_id', auth()->id())
                          ->get(['chat_conversation_id', 'last_read_at']);

                      foreach ($wmcChatParticipantRows as $wmcChatParticipantRow) {
                          $wmcChatUnreadCount += \Illuminate\Support\Facades\DB::table('chat_messages')
                              ->where('chat_conversation_id', $wmcChatParticipantRow->chat_conversation_id)
                              ->where('sender_id', '!=', auth()->id())
                              ->when($wmcChatParticipantRow->last_read_at, function ($query) use ($wmcChatParticipantRow) {
                                  $query->where('created_at', '>', $wmcChatParticipantRow->last_read_at);
                              })
                              ->count();
                      }
                  }
              } catch (\Throwable $e) {
                  $wmcChatUnreadCount = 0;
              }
          @endphp
          <a href="{{ route('chat.index') }}" class="nav-link position-relative" id="mail-drop" title="Chat">
            <svg width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path opacity="0.4" d="M22 15.94C22 18.73 19.76 20.99 16.97 21H16.96H7.05C4.27 21 2 18.75 2 15.96V15.95C2 15.95 2.006 11.524 2.014 9.298C2.015 8.88 2.495 8.646 2.822 8.906C5.198 10.791 9.447 14.228 9.5 14.273C10.21 14.842 11.11 15.163 12.03 15.163C12.95 15.163 13.85 14.842 14.56 14.262C14.613 14.227 18.767 10.893 21.179 8.977C21.507 8.716 21.989 8.95 21.99 9.367C22 11.576 22 15.94 22 15.94Z" fill="currentColor"></path>
              <path d="M21.4759 5.67351C20.6099 4.04151 18.9059 2.99951 17.0299 2.99951H7.04988C5.17388 2.99951 3.46988 4.04151 2.60388 5.67351C2.40988 6.03851 2.50188 6.49351 2.82488 6.75151L10.2499 12.6905C10.7699 13.1105 11.3999 13.3195 12.0299 13.3195C12.0339 13.3195 12.0369 13.3195 12.0399 13.3195C12.0429 13.3195 12.0469 13.3195 12.0499 13.3195C12.6799 13.3195 13.3099 13.1105 13.8299 12.6905L21.2549 6.75151C21.5779 6.49351 21.6699 6.03851 21.4759 5.67351Z" fill="currentColor"></path>
            </svg>
            @if($wmcChatUnreadCount > 0)
              <span id="wmcChatHeaderBadge" class="wmc-chat-header-badge">{{ $wmcChatUnreadCount > 99 ? '99+' : $wmcChatUnreadCount }}</span>
            @endif
          </a>

        </li>

        <li class="nav-item dropdown">
          <a class="nav-link py-0 d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ asset('images/avatars/01.png') }}" alt="User-Profile" class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{ asset('images/avatars/avtar_1.png') }}" alt="User-Profile" class="theme-color-purple-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{ asset('images/avatars/avtar_2.png') }}" alt="User-Profile" class="theme-color-blue-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{ asset('images/avatars/avtar_4.png') }}" alt="User-Profile" class="theme-color-green-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{ asset('images/avatars/avtar_5.png') }}" alt="User-Profile" class="theme-color-yellow-img img-fluid avatar avatar-50 avatar-rounded">
            <img src="{{ asset('images/avatars/avtar_3.png') }}" alt="User-Profile" class="theme-color-pink-img img-fluid avatar avatar-50 avatar-rounded">

            <div class="caption ms-3 d-none d-md-block">
              <h6 class="mb-0 caption-title">{{ $displayName }}</h6>
              <p class="mb-0 caption-sub-title text-capitalize">{{ $displayRole }}</p>
            </div>
          </a>

          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="{{ route('users.show', auth()->id()) }}">Profile</a></li>
            <li><a class="dropdown-item" href="{{ route('auth.userprivacysetting') }}">Privacy Setting</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="javascript:void(0)" class="dropdown-item"
                   onclick="event.preventDefault(); this.closest('form').submit();">
                  {{ __('Log out') }}
                </a>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>