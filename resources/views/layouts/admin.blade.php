<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>{{ $systemSettings['system_title'] ?? config('app.name', 'Laravel') }}</title>
  
  @if(!empty($systemSettings['system_favicon']))
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $systemSettings['system_favicon']) }}">
  @endif

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  @stack('styles')
  @livewireStyles
  <style>
    /* Fancy Layout Enhancements */
    body { font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; }
    
    /* Sidebar */
    .fancy-sidebar {
        background: linear-gradient(180deg, #1e1e2d 0%, #151521 100%) !important;
        box-shadow: 4px 0 15px rgba(0,0,0,0.05) !important;
    }
    .fancy-sidebar .brand-link {
        border-bottom: 1px solid rgba(255,255,255,0.05) !important;
        padding: 1.2rem 0.5rem;
    }
    .fancy-sidebar .nav-sidebar .nav-item .nav-link {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border-radius: 8px;
        margin: 2px 12px;
        padding: 10px 15px;
    }
    .fancy-sidebar .nav-sidebar .nav-item .nav-link:hover {
        background: rgba(255,255,255,0.05);
        transform: translateX(4px);
    }
    .fancy-sidebar .nav-sidebar .nav-item .nav-link.active {
        background: linear-gradient(135deg, #3699ff 0%, #187de4 100%) !important;
        box-shadow: 0 4px 10px rgba(54, 153, 255, 0.3) !important;
        color: #fff !important;
    }
    .fancy-sidebar .nav-header {
        letter-spacing: 1px;
        opacity: 0.6;
        font-size: 0.75rem;
    }

    /* Cards & Boxes */
    .small-box {
        border-radius: 12px !important;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border: none !important;
        position: relative;
        z-index: 1;
    }
    .small-box::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 100%);
        z-index: -1;
    }
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0,0,0,0.1) !important;
    }
    .small-box .icon > i {
        transition: all 0.3s ease;
    }
    .small-box:hover .icon > i {
        transform: scale(1.1) rotate(5deg);
    }
    
    .card {
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03) !important;
        border: none !important;
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.06) !important;
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.03) !important;
        background-color: transparent !important;
    }

    /* Gradients */
    .bg-gradient-warning-custom { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important; color: #fff; }
    .bg-gradient-success-custom { background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important; color: #fff; }
    .bg-gradient-info-custom { background: linear-gradient(135deg, #17a2b8 0%, #3699ff 100%) !important; color: #fff; }
    .bg-gradient-danger-custom { background: linear-gradient(135deg, #f64e60 0%, #dc3545 100%) !important; color: #fff; }
    .bg-gradient-secondary-custom { background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important; color: #fff; }
    .bg-gradient-primary-custom { background: linear-gradient(135deg, #6610f2 0%, #6f42c1 100%) !important; color: #fff; }
    .bg-gradient-teal-custom { background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%) !important; color: #fff; }
    .bg-gradient-olive-custom { background: linear-gradient(135deg, #4caf50 0%, #8bc34a 100%) !important; color: #fff; }
    .bg-gradient-maroon-custom { background: linear-gradient(135deg, #d81b60 0%, #e91e63 100%) !important; color: #fff; }
    .bg-gradient-purple-custom { background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%) !important; color: #fff; }
    .bg-gradient-orange-custom { background: linear-gradient(135deg, #fd7e14 0%, #ff9800 100%) !important; color: #fff; }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- User Info -->
      <li class="nav-item d-flex align-items-center mr-2">
        <div class="info">
          <a href="#" class="d-block">{{ Auth::user()->name }}</a>
          <small class="text-muted">{{ Auth::user()->role->name ?? 'User' }}</small>
        </div>
      </li>
      

      
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="badge badge-warning navbar-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">{{ auth()->user()->unreadNotifications->count() }} Notifications</span>
          <div class="dropdown-divider"></div>
          @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
            <a href="{{ $notification->data['url'] ?? '#' }}" class="dropdown-item text-wrap">
              <i class="fas fa-envelope mr-2"></i> {{ Str::limit($notification->data['message'] ?? 'New Notification', 50) }}
              <span class="float-right text-muted text-sm">{{ $notification->created_at->diffForHumans(null, true, true) }}</span>
            </a>
            <div class="dropdown-divider"></div>
          @empty
            <a href="#" class="dropdown-item dropdown-footer">No new notifications</a>
          @endforelse
          
          @if(auth()->user()->unreadNotifications->count() > 0)
            <a href="{{ route('notifications.mark-read') }}" class="dropdown-item dropdown-footer">Mark all as read</a>
          @endif
        </div>
      </li>

      <li class="nav-item">
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
            @csrf
        </form>
        <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4 fancy-sidebar">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
      @if(!empty($systemSettings['system_logo']))
        <img src="{{ asset('storage/' . $systemSettings['system_logo']) }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      @endif
      <span class="brand-text font-weight-light">{{ $systemSettings['system_title'] ?? 'Project CRM' }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
          <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('clients.non-clients') }}" class="nav-link {{ request()->routeIs('clients.non-clients') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-friends"></i>
              <p>Non-Clients</p>
            </a>
          </li>



          <li class="nav-item">
            <a href="{{ route('websites.index') }}" class="nav-link {{ request()->routeIs('websites.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-globe"></i>
              <p>Websites</p>
            </a>
          </li>
          @if(!auth()->user()->hasRole('client'))
          <li class="nav-item">
            <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tasks"></i>
              <p>Projects</p>
            </a>
          </li>
          @endif

          @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
          <li class="nav-item">
            <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-receipt"></i>
              <p>Expenses</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-hand-holding-usd"></i>
              <p>Loans</p>
            </a>
          </li>
          @endif
          @if(Auth::user()->hasRole('master') || Auth::user()->hasRole('admin'))
          <li class="nav-item">
            <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-money-bill-wave"></i>
              <p>Payments</p>
            </a>
          </li>
          @endif

          @if(Auth::user()->hasRole('master') || Auth::user()->hasRole('admin'))
          <li class="nav-header">ADMINISTRATION</li>
          <li class="nav-item">
            <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-users"></i>
              <p>Clients</p>
            </a>
          </li>



          <li class="nav-item">
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-cog"></i>
              <p>Users</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-cogs"></i>
              <p>Settings</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('hr.index') }}" class="nav-link {{ request()->routeIs('hr.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-file-invoice-dollar"></i>
              <p>HR & Salary</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('whatsapp.settings') }}" class="nav-link {{ request()->routeIs('whatsapp.settings') ? 'active' : '' }}">
              <i class="nav-icon fab fa-whatsapp"></i>
              <p>WhatsApp Settings</p>
            </a>
          </li>
          @endif

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    @php
      $layoutToday = \Carbon\Carbon::today();
      $layoutDateString = $layoutToday->format('l, F d, Y');
      $layoutHolidayToday = \App\Models\Holiday::whereDate('date', $layoutToday)->first();
      $layoutFestival = $layoutHolidayToday ? ($layoutHolidayToday->name . ' (' . $layoutHolidayToday->type . ')') : null;
    @endphp

    <!-- Marquee Banner for Today's Date and Festival/Holiday -->
    <div class="container-fluid pt-2">
      @if($layoutFestival)
          <div class="alert alert-warning p-2 mb-2 shadow-sm border-warning" style="overflow: hidden; border-radius: 8px;">
              <marquee behavior="scroll" direction="left" scrollamount="5" class="font-weight-bold mb-0">
                  <i class="fas fa-gift text-danger mr-2"></i> Today is {{ $layoutDateString }} | <span class="text-danger font-weight-bold">Today's Festival/Holiday: {{ $layoutFestival }}</span> 🎉
              </marquee>
          </div>
      @else
          <div class="alert alert-info p-2 mb-2 shadow-sm border-info" style="overflow: hidden; border-radius: 8px;">
              <marquee behavior="scroll" direction="left" scrollamount="5" class="font-weight-bold mb-0 text-dark">
                  <i class="fas fa-calendar-day text-primary mr-2"></i> Today is {{ $layoutDateString }} | No festival scheduled for today. Have a wonderful and productive day!
              </marquee>
          </div>
      @endif
    </div>

    @if(isset($header))
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{ $header }}</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    @endif

    <!-- Main content -->
    <section class="content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Success!</h5>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; {{ date('Y') }} Project CRM.</strong> All rights reserved.
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@livewireScripts
@stack('scripts')
</body>
</html>
