<style>
    .header-notification-menu {
        width: 360px;
    }
    .header-notification-list {
        max-height: 320px;
        overflow-y: auto;
    }
    .header-notification-item {
        padding: 8px 10px;
        border-left: 3px solid transparent;
    }
    .header-notification-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }
    .header-notification-item .notif-title {
        font-size: 13px;
        line-height: 1.2;
        margin-bottom: 2px;
    }
    .header-notification-item .notif-message {
        font-size: 12px;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .header-notification-item .notif-time {
        font-size: 11px;
        line-height: 1.1;
        margin-top: 2px;
    }
    .header-notification-item.notif-unread {
        background: #eef4ff;
        border-left-color: #1f6feb;
    }
    .header-notification-item.notif-unread .notif-title {
        font-weight: 700;
    }
    .header-notification-item.notif-read {
        background: #fff;
        opacity: 0.78;
    }
    .header-notification-badge {
        position: absolute;
        top: 2px;
        right: 0;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 9px;
        background: #dc3545;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        line-height: 18px;
        text-align: center;
    }
</style>

<div class="header-block">
    <nav class="navbar navbar-expand navbar-theme">

        <div class="title-bar">
            <div class="main-title">
                @yield('title')
            </div>
            <div class="breadcrumbs">
                @yield('breadcrumbs')
            </div>
        </div>

        <div class="navbar-collapse collapse">
            <ul class="navbar-nav ml-auto">
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="search" data-toggle="dropdown">
                        <i class="fas fa-search"></i>
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="alertsDropdown" data-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        @if(!empty($unreadHeaderNotificationsCount))
                            <span class="header-notification-badge">{{ $unreadHeaderNotificationsCount > 99 ? '99+' : $unreadHeaderNotificationsCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right py-0 header-notification-menu" aria-labelledby="alertsDropdown">
                        <div class="dropdown-menu-header">
                            {{ $unreadHeaderNotificationsCount }} New Notification{{ $unreadHeaderNotificationsCount === 1 ? '' : 's' }}
                        </div>
                        <div class="list-group header-notification-list">
                            @forelse($headerNotifications as $notification)
                                <a href="{{ route('notifications.read', $notification->id) }}" class="list-group-item header-notification-item {{ $notification->is_read ? 'notif-read' : 'notif-unread' }}">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-2">
                                            @php
                                                $senderImage = optional($notification->sender)->profile_img ?: 'default.jpg';
                                            @endphp
                                            <img
                                                src="/images/profile/{{ $senderImage }}"
                                                class="header-notification-avatar ml-1"
                                                alt="sender"
                                                onerror="this.onerror=null; this.src='/images/profile/default.jpg';"
                                            />
                                        </div>
                                        <div class="col-10">
                                            <div class="text-dark notif-title">{{ $notification->display_title ?: 'Notification' }}</div>
                                            @if(!empty($notification->display_message))
                                                <div class="text-muted notif-message">{{ $notification->display_message }}</div>
                                            @endif
                                            <div class="text-muted notif-time">{{ optional($notification->created_at)->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="list-group-item">
                                    <div class="text-muted small">No notifications.</div>
                                </div>
                            @endforelse
                        </div>
                        <div class="dropdown-menu-footer">
                            <a href="{{ route('notifications.index') }}" class="text-muted">View All notifications</a>
                        </div>
                    </div>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="userDropdown" data-toggle="dropdown">
                        <div class="sidebar-user">
                            <div class="profile-img">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="profile-name">
                                {{ Auth::user()->firstname.' '.(Auth::user()->middlename !== null || Auth::user()->middlename !== ''?Auth::user()->middlename.' ':'').Auth::user()->lastname }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-catalog" aria-labelledby="userDropdown">
                        <div class="inner-profile-catalog">
                            <div class="profile-img">
                                <img src="/images/profile/{{ Auth::user()->profile_img }}" class="img-fluid rounded-circle mb-2" onerror="this.onerror=null; this.src='/images/profile/default.jpg';" />
                            </div>
                            <div class="profile-name">
                                <div class="disp-name">{{ Auth::user()->firstname.' '.(Auth::user()->middlename !== null || Auth::user()->middlename !== ''?Auth::user()->middlename.' ':'').Auth::user()->lastname }}</div>
                                <div class="disp-email">{{ Auth::user()->email }}</div>                                
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item" href="#">
                            <i class="align-middle mr-1 fas fa-fw fa-wrench"></i> Account Settings
                        </a>
                        
                       <a class="dropdown-item" href="#" data-toggle="modal" data-target="#changePasswordModal">
                            <i class="align-middle mr-1 fas fa-fw fa-lock"></i> Change Password
                        </a>

                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="align-middle mr-1 fas fa-fw fa-arrow-alt-circle-right"></i> Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
        
    </nav>

    <div class="action-buttons">
        @include('backend.partial.component.centralized_buttons')
    </div>
</div>
