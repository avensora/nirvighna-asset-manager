<!-- Sidenav Menu Start -->
<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="{{ asset('boron/assets/images/logo.png') }}" alt="logo"></span>
            <span class="logo-sm text-center"><img src="{{ asset('boron/assets/images/logo-sm.png') }}" alt="small logo"></span>
        </span>
        <span class="logo-dark">
            <span class="logo-lg"><img src="{{ asset('boron/assets/images/logo-dark.png') }}" alt="dark logo"></span>
            <span class="logo-sm text-center"><img src="{{ asset('boron/assets/images/logo-sm.png') }}" alt="small logo"></span>
        </span>
    </a>

    <button class="button-sm-hover">
        <i class="ti ti-circle align-middle"></i>
    </button>

    <button class="button-close-fullsidebar">
        <i class="ti ti-x align-middle"></i>
    </button>

    <div data-simplebar>
        <ul class="side-nav">

            <li class="side-nav-item">
                <a href="{{ route('dashboard') }}" class="side-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <li class="side-nav-title mt-2">Business</li>

            <li class="side-nav-item">
                <a href="{{ route('projects.index') }}" class="side-nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-layout-kanban"></i></span>
                    <span class="menu-text">Projects</span>
                </a>
            </li>

            @if(auth()->user()->isManager())
            <li class="side-nav-item">
                <a href="{{ route('leads.index') }}" class="side-nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-target"></i></span>
                    <span class="menu-text">Leads</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->isManager())
            <li class="side-nav-item">
                <a href="{{ route('reports.index') }}" class="side-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-chart-pie fs-18"></i></span>
                    <span class="menu-text">Reports</span>
                </a>
            </li>
            @endif

            <li class="side-nav-item">
                <a href="{{ route('clients.index') }}" class="side-nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-users"></i></span>
                    <span class="menu-text">Clients</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarInvoice" aria-expanded="{{ request()->routeIs('invoices.*') ? 'true' : 'false' }}" class="side-nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
                    <span class="menu-text">Invoices</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('invoices.*') ? 'show' : '' }}" id="sidebarInvoice">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('invoices.index') }}" class="side-nav-link {{ request()->routeIs('invoices.index') ? 'active' : '' }}">
                                <span class="menu-text">All Invoices</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('invoices.create') }}" class="side-nav-link {{ request()->routeIs('invoices.create') ? 'active' : '' }}">
                                <span class="menu-text">Create Invoice</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            @if(auth()->user()->isManager())
            <li class="side-nav-item">
                <a href="{{ route('owes.index') }}" class="side-nav-link {{ request()->routeIs('owes.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-arrows-exchange-2"></i></span>
                    <span class="menu-text">Who Owes Whom</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->isManager())
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarFinance"
                   aria-expanded="{{ request()->routeIs('transactions.*', 'scheduled-expenses.*', 'reimbursements.*', 'loans.*') ? 'true' : 'false' }}"
                   class="side-nav-link {{ request()->routeIs('transactions.*', 'scheduled-expenses.*', 'reimbursements.*', 'loans.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-report-money"></i></span>
                    <span class="menu-text">Finances</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('transactions.*', 'scheduled-expenses.*', 'reimbursements.*', 'loans.*') ? 'show' : '' }}" id="sidebarFinance">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('transactions.index') }}" class="side-nav-link {{ request()->routeIs('transactions.index') ? 'active' : '' }}">
                                <span class="menu-text">Income & Expenses</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('transactions.create') }}" class="side-nav-link {{ request()->routeIs('transactions.create') ? 'active' : '' }}">
                                <span class="menu-text">Add Transaction</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('scheduled-expenses.index') }}" class="side-nav-link {{ request()->routeIs('scheduled-expenses.*') ? 'active' : '' }}">
                                <span class="menu-text">Scheduled Expenses</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('reimbursements.index') }}" class="side-nav-link {{ request()->routeIs('reimbursements.*') ? 'active' : '' }}">
                                <span class="menu-text">Reimbursements</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('loans.index') }}" class="side-nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                                <span class="menu-text">Loan Register</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('settings.company') }}" class="side-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                <span class="menu-text">Opening Balance</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('month-closings.index') }}" class="side-nav-link {{ request()->routeIs('month-closings.*') ? 'active' : '' }}">
                                <span class="menu-text">Month Closing</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @else
            {{-- Non-managers see Reimbursements as a top-level item --}}
            <li class="side-nav-item">
                <a href="{{ route('reimbursements.index') }}" class="side-nav-link {{ request()->routeIs('reimbursements.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-receipt-refund"></i></span>
                    <span class="menu-text">Reimbursements</span>
                </a>
            </li>
            @endif

            <li class="side-nav-title mt-2">Team</li>

            <li class="side-nav-item">
                <a href="{{ route('chat.index') }}" class="side-nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-message-filled"></i></span>
                    <span class="menu-text">Team Chat</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('calendar.index') }}" class="side-nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-calendar-filled"></i></span>
                    <span class="menu-text">Calendar</span>
                </a>
            </li>

            @if(auth()->user()->isManager())
            <li class="side-nav-item">
                <a href="{{ route('team.index') }}" class="side-nav-link {{ request()->routeIs('team.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-users-group"></i></span>
                    <span class="menu-text">Team Members</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('activity.index') }}" class="side-nav-link {{ request()->routeIs('activity.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-timeline-event-text"></i></span>
                    <span class="menu-text">Activity Log</span>
                </a>
            </li>
            @endif

        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<!-- Sidenav Menu End -->
