<!-- Topbar Start -->
<header class="app-topbar">
    <div class="page-container topbar-menu">
        <div class="d-flex align-items-center gap-2">

            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="logo">
                <span class="logo-light">
                    <span class="logo-lg"><img src="{{ asset('boron/assets/images/logo.png') }}" alt="logo"></span>
                    <span class="logo-sm"><img src="{{ asset('boron/assets/images/logo-sm.png') }}" alt="small logo"></span>
                </span>
                <span class="logo-dark">
                    <span class="logo-lg"><img src="{{ asset('boron/assets/images/logo-dark.png') }}" alt="dark logo"></span>
                    <span class="logo-sm"><img src="{{ asset('boron/assets/images/logo-sm.png') }}" alt="small logo"></span>
                </span>
            </a>

            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button btn btn-secondary btn-icon">
                <i class="ti ti-menu-deep fs-24"></i>
            </button>

        </div>

        <div class="d-flex align-items-center gap-2">

            <!-- Light/Dark Mode Button -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link btn btn-outline-primary btn-icon" id="light-dark-mode" type="button">
                    <i class="ti ti-moon fs-22"></i>
                </button>
            </div>

            <!-- Button Trigger Customizer Offcanvas -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link btn btn-outline-primary btn-icon" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" type="button">
                    <i class="ti ti-settings fs-22"></i>
                </button>
            </div>

            <!-- Notification Bell -->
            <div class="topbar-item">
                <div class="dropdown">
                    <a class="topbar-link btn btn-outline-primary btn-icon position-relative" id="notif-bell"
                       data-bs-toggle="dropdown" data-bs-offset="0,22" type="button" href="{{ route('notifications.index') }}">
                        <i class="ti ti-bell fs-22"></i>
                        <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size:10px;padding:3px 5px;"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="min-width:min(320px, calc(100vw - 20px));" id="notif-dropdown">
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                            <span class="fw-semibold">Notifications</span>
                            <a href="{{ route('notifications.index') }}" class="small text-primary">View all</a>
                        </div>
                        <div id="notif-list">
                            <div class="text-center text-muted py-3 small">Loading…</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item">
                <div class="dropdown">
                    <a class="topbar-link btn btn-outline-primary dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,22" type="button">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" width="24" class="rounded-circle me-lg-2 d-flex" alt="avatar">
                        @else
                            <span class="avatar-sm bg-primary text-white rounded-circle me-lg-2 d-flex align-items-center justify-content-center fw-semibold" style="width:24px;height:24px;font-size:11px;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        @endif
                        <span class="d-lg-flex flex-column gap-1 d-none">
                            {{ auth()->user()->name }}
                        </span>
                        <i class="ti ti-chevron-down d-none d-lg-block align-middle ms-2"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome, {{ auth()->user()->name }}!</h6>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="ti ti-user-hexagon me-1 fs-17 align-middle"></i>
                            <span class="align-middle">My Profile</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item fw-semibold text-danger">
                                <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                                <span class="align-middle">Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>
<!-- Topbar End -->

<script>
(function () {
    var countUrl  = '{{ route('notifications.count') }}';
    var markAllUrl = '{{ route('notifications.read-all') }}';
    var csrfToken = '{{ csrf_token() }}';

    function timeAgo(dateStr) {
        var diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)  return diff + 's ago';
        if (diff < 3600) return Math.floor(diff/60) + 'm ago';
        if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
        return Math.floor(diff/86400) + 'd ago';
    }

    function fetchNotifications() {
        fetch(countUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var badge = document.getElementById('notif-badge');
                if (data.unread > 0) {
                    badge.textContent = data.unread > 99 ? '99+' : data.unread;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }

                var list = document.getElementById('notif-list');
                if (!data.recent || data.recent.length === 0) {
                    list.innerHTML = '<div class="text-center text-muted py-3 small">No notifications</div>';
                    return;
                }

                var html = '';
                data.recent.forEach(function (n) {
                    var unreadClass = n.read_at ? '' : 'fw-semibold';
                    var dot = n.read_at ? '' : '<span class="badge bg-primary rounded-circle p-1 ms-1" style="width:7px;height:7px;"></span>';
                    html += '<div class="px-3 py-2 border-bottom">';
                    html += '<div class="d-flex justify-content-between">';
                    html += '<span class="small ' + unreadClass + '">' + n.title + dot + '</span>';
                    html += '<span class="text-muted" style="font-size:11px;">' + timeAgo(n.created_at) + '</span>';
                    html += '</div>';
                    html += '<div class="text-muted" style="font-size:12px;">' + n.body + '</div>';
                    html += '</div>';
                });

                if (data.unread > 0) {
                    html += '<div class="px-3 py-2">';
                    html += '<button class="btn btn-link btn-sm text-muted p-0" onclick="markAllRead()">Mark all as read</button>';
                    html += '</div>';
                }

                list.innerHTML = html;
            })
            .catch(function () {});
    }

    window.markAllRead = function () {
        fetch(markAllUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function () { fetchNotifications(); });
    };

    // Load on dropdown open
    document.getElementById('notif-bell').addEventListener('show.bs.dropdown', fetchNotifications);

    // Poll every 30 seconds
    setInterval(fetchNotifications, 30000);
    fetchNotifications();
})();
</script>
