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
                        <span class="indicator"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right py-0" aria-labelledby="alertsDropdown">
                        <div class="dropdown-menu-header">
                            4 New Notifications
                        </div>
                        <div class="list-group">
                            <a href="#" class="list-group-item">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-2">
                                        <i class="ml-1 text-danger fas fa-fw fa-bell"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-dark">Update completed</div>
                                        <div class="text-muted small mt-1">Restart server 12 to complete the update.</div>
                                        <div class="text-muted small mt-1">2h ago</div>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-2">
                                        <i class="ml-1 text-warning fas fa-fw fa-envelope-open"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-dark">Lorem ipsum</div>
                                        <div class="text-muted small mt-1">Aliquam ex eros, imperdiet vulputate hendrerit et.</div>
                                        <div class="text-muted small mt-1">6h ago</div>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-2">
                                        <i class="ml-1 text-primary fas fa-fw fa-building"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-dark">Login from 192.186.1.1</div>
                                        <div class="text-muted small mt-1">8h ago</div>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-2">
                                        <i class="ml-1 text-success fas fa-fw fa-bell-slash"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-dark">New connection</div>
                                        <div class="text-muted small mt-1">Anna accepted your request.</div>
                                        <div class="text-muted small mt-1">12h ago</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="dropdown-menu-footer">
                            <a href="#" class="text-muted">Show all notifications</a>
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