<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <span>Dashboard</span>
            <form action="{{ route('dashboard') }}" method="GET" class="form-inline">
                <div class="input-group input-group-sm">
                    <select name="month" class="form-control">
                        <option value="">All Month</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                            </option>
                        @endforeach
                    </select>
                    <select name="year" class="form-control">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary d-flex align-items-center">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-slot>

    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['pending_projects'] }}</h3>
                    <p>Pending Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('projects.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['running_projects'] }}</h3>
                    <p>Running Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <a href="{{ route('projects.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['completed_projects'] }}</h3>
                    <p>Completed Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <a href="{{ route('projects.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-secondary shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['canceled_projects'] }}</h3>
                    <p>Canceled Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <a href="{{ route('projects.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <!-- /.row -->

    @if(!auth()->user()->hasRole('client'))
    <div class="row">
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-indigo shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['today_work_hours'] }}</h3>
                    <p>Today's Work</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>
                <a href="{{ route('attendance.index') }}" class="small-box-footer">View Logs <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-primary shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['total_clients'] }}</h3>
                    <p>Total Clients</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <a href="{{ route('clients.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-teal shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['total_users'] }}</h3>
                    <p>Team Members</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('users.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif
    </div>
    @endif

    @if(!auth()->user()->hasRole('user'))
    <div class="row">
        <div class="col-lg col-md-4 col-6">
            <!-- small box -->
            <div class="small-box bg-olive shadow-sm">
                <div class="inner">
                    <h3 style="font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $stats['total_revenue'] }}">
                        {{ $stats['total_revenue'] }}
                    </h3>
                    <p>Total Income</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <a href="{{ route('payments.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg col-md-4 col-6">
            <!-- small box -->
            <div class="small-box bg-maroon shadow-sm">
                <div class="inner">
                    <h3 style="font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $stats['total_expense'] }}">
                        {{ $stats['total_expense'] }}
                    </h3>
                    <p>Total Expenses</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <a href="{{ route('expenses.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg col-md-4 col-6">
            <!-- small box -->
            <div class="small-box bg-purple shadow-sm">
                <div class="inner">
                    <h3 style="font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $stats['total_profit'] }}">
                        {{ $stats['total_profit'] }}
                    </h3>
                    <p>Total Net Profit</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="small-box-footer" style="min-height: 30px;">&nbsp;</div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-6">
            <!-- small box -->
            <div class="small-box bg-danger shadow-sm">
                <div class="inner">
                    <h3 style="font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $stats['total_pending'] }}">
                        {{ $stats['total_pending'] }}
                    </h3>
                    <p>Pending Payment</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="small-box-footer" style="min-height: 30px;">&nbsp;</div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-6">
            <!-- small box -->
            <div class="small-box bg-orange shadow-sm">
                <div class="inner">
                    <h3 style="font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $stats['total_pending_expense'] }}">
                        {{ $stats['total_pending_expense'] }}
                    </h3>
                    <p>Pending Expense</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <a href="{{ route('expenses.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Revenue Chart -->
        @if(!auth()->user()->hasRole('user'))
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header border-0 pb-0">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-chart-bar mr-1 text-primary"></i>
                        Monthly Income vs Expense
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="incomeExpenseChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Status Chart -->
        <div class="col-md-{{ auth()->user()->hasRole('user') ? '12' : '4' }}">
            <div class="card card-outline card-info shadow-sm border-0">
                <div class="card-header border-0 pb-0">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-chart-pie mr-1 text-info"></i>
                        Project Status
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-8">
            <!-- Recent Projects -->
            <div class="card card-outline card-secondary shadow-sm border-0">
                <div class="card-header border-0 pb-0">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-list mr-1 text-secondary"></i>
                        Recent Projects
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                   <table class="table table-striped table-valign-middle mb-0">
                       <thead class="bg-light">
                           <tr class="small text-muted text-uppercase">
                               <th class="pl-3" style="width: 5%">Sr.</th>
                               <th>Title</th>
                               <th>Client</th>
                               <th>Status</th>
                               <th class="text-right pr-3">Action</th>
                           </tr>
                       </thead>
                       <tbody>
                           @forelse($recentProjects as $project)
                           <tr>
                               <td class="pl-3">{{ $loop->iteration }}</td>
                               <td>
                                   <span class="font-weight-bold d-block">{{ $project->title }}</span>
                                   <small class="text-muted">Created {{ $project->created_at->format('d M Y') }}</small>
                               </td>
                               <td>{{ $project->client->company_name ?? 'N/A' }}</td>
                               <td>
                                    <span class="badge {{ $project->status == 'Running' ? 'badge-success' : ($project->status == 'Pending' ? 'badge-warning' : 'badge-secondary') }}">
                                        {{ $project->status }}
                                    </span>
                               </td>
                               <td class="text-right pr-3">
                                   <a href="{{ route('projects.show', $project) }}" class="btn btn-xs btn-primary shadow-sm px-2">
                                       <i class="fas fa-eye mr-1"></i> View
                                   </a>
                               </td>
                           </tr>
                           @empty
                           <tr>
                               <td colspan="4" class="text-center text-muted py-5">
                                   <i class="fas fa-folder-open fa-2x mb-3 d-block"></i>
                                   No projects found.
                                </td>
                           </tr>
                           @endforelse
                       </tbody>
                   </table>
                </div>
            </div>

            <!-- Recent Transactions - Only Master & Admin -->
            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
            <div class="card card-outline card-success shadow-sm border-0 mt-3">
                <div class="card-header border-0 pb-0">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-history mr-1 text-success"></i>
                        Recent Transactions
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                   <table class="table table-striped table-valign-middle mb-0">
                       <thead class="bg-light">
                           <tr class="small text-muted text-uppercase">
                               <th class="pl-3" style="width: 5%">Sr.</th>
                               <th>Date</th>
                               <th>Project/Client</th>
                               <th>Amount</th>
                               <th class="text-right pr-3">Status</th>
                           </tr>
                       </thead>
                       <tbody>
                           @forelse($recentTransactions as $transaction)
                           <tr>
                               <td class="pl-3">{{ $loop->iteration }}</td>
                               <td>{{ $transaction->payment_date ? $transaction->payment_date->format('d M Y') : 'N/A' }}</td>
                               <td>
                                   <span class="font-weight-bold d-block text-truncate" style="max-width: 200px;">{{ $transaction->project->title ?? 'N/A' }}</span>
                                   <small class="text-muted">{{ $transaction->project->client->company_name ?? 'N/A' }}</small>
                               </td>
                               <td class="text-success font-weight-bold">
                                   {{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}
                               </td>
                               <td class="text-right pr-3">
                                    <span class="badge {{ $transaction->payment_status == 'Paid' ? 'badge-success' : 'badge-warning' }}">
                                        {{ $transaction->payment_status }}
                                    </span>
                               </td>
                           </tr>
                           @empty
                           <tr>
                               <td colspan="5" class="text-center text-muted py-5">
                                   <i class="fas fa-money-bill-wave fa-2x mb-3 d-block"></i>
                                   No transactions found.
                                </td>
                           </tr>
                           @endforelse
                       </tbody>
                   </table>
                </div>
            </div>
            @endif
        </section>
        
        <!-- Right col -->
        <section class="col-lg-4">
            <!-- Quick Actions - Only Client & Master -->
            @if(auth()->user()->hasRole('client') || auth()->user()->hasRole('master'))
            <div class="card card-primary shadow-sm border-0">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-star mr-1"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-primary btn-block text-left mb-2 py-2">
                        <i class="fas fa-plus-circle mr-2"></i> Create New Project
                    </a>
                    @if(!auth()->user()->hasRole('user'))
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-success btn-block text-left mb-2 py-2">
                        <i class="fas fa-hand-holding-usd mr-2"></i> Record Payment
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-info btn-block text-left py-2">
                        <i class="fas fa-user-plus mr-2"></i> Add New Client
                    </a>
                    @endif
                </div>
            </div>
            @endif

            @if(auth()->user()->hasRole('master'))
            <div class="card card-warning shadow-sm border-0">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold text-dark">
                        <i class="fas fa-tools mr-1"></i>
                        System Maintenance
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('system.clear-cache') }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-block btn-light text-left border py-2" onclick="return confirm('Clear all system cache?')">
                            <i class="fas fa-eraser mr-2 text-warning"></i> Clear System Cache
                        </button>
                    </form>
                    <form action="{{ route('system.run-migration') }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-block btn-light text-left border py-2" onclick="return confirm('Run database migrations? This will attempt to update the database schema.')">
                            <i class="fas fa-database mr-2 text-danger"></i> Run Database Migrations
                        </button>
                    </form>
                    <form action="{{ route('system.composer-update') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-block btn-light text-left border py-2" onclick="return confirm('Run composer update? This may take several minutes.')">
                            <i class="fas fa-sync mr-2 text-primary"></i> Composer Update
                        </button>
                    </form>
                </div>
            </div>
            @endif
            
            <div class="card bg-gradient-dark shadow-sm border-0">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-info-circle mr-1 text-info"></i>
                        System Info
                    </h3>
                </div>
                <div class="card-body small py-3">
                    <p class="mb-1"><strong>Environment:</strong> {{ ucfirst(config('app.env')) }}</p>
                    <p class="mb-1"><strong>Server Time:</strong> {{ now()->format('d M Y, h:i A') }}</p>
                    <p class="mb-0">Logged in as <strong>{{ auth()->user()->name }}</strong></p>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
        $(function () {
            // Income vs Expense Bar Chart
            var barCanvas = $('#incomeExpenseChart').get(0).getContext('2d')
            var barData = {
                labels  : {!! $barLabels !!},
                datasets: {!! $barDatasets !!}
            }
            var barOptions = {
                maintainAspectRatio : false,
                responsive : true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display : false,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            callback: function(value, index, values) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
            new Chart(barCanvas, {
                type: 'bar',
                data: barData,
                options: barOptions
            })

            // Status Chart
            var statusCanvas = $('#statusChart').get(0).getContext('2d')
            var statusData = {
                labels: {!! $statusLabels !!},
                datasets: [
                    {
                        data: {!! $statusData !!},
                        backgroundColor : ['#ffc107', '#28a745', '#17a2b8', '#dc3545'],
                        borderWidth: 0
                    }
                ]
            }
            var statusOptions = {
                maintainAspectRatio : false,
                responsive : true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                cutout: '70%'
            }
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: statusData,
                options: statusOptions
            })
        })
    </script>
    @endpush

</x-admin-layout>
