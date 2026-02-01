<x-admin-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_projects'] }}</h3>

                    <p>Total Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <a href="{{ route('projects.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_projects'] }}</h3>

                    <p>Active Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_clients'] }}</h3>

                    <p>Clients</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($stats['total_revenue'] ?? 0, 0) }}</h3>

                    <p>Total Revenue</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <a href="{{ route('payments.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->

    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Recent Projects
                    </h3>
                </div><!-- /.card-header -->
                <div class="card-body p-0 table-responsive">
                   <table class="table table-striped text-nowrap">
                       <thead>
                           <tr>
                               <th>Title</th>
                               <th>Client</th>
                               <th>Status</th>
                               <th>Action</th>
                           </tr>
                       </thead>
                       <tbody>
                           @foreach($recentProjects as $project)
                           <tr>
                               <td>{{ $project->title }}</td>
                               <td>{{ $project->client->company_name ?? 'N/A' }}</td>
                               <td>
                                    <span class="badge {{ $project->status == 'Running' ? 'badge-success' : ($project->status == 'Pending' ? 'badge-warning' : 'badge-secondary') }}">
                                        {{ $project->status }}
                                    </span>
                               </td>
                               <td>
                                   <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-primary">View</a>
                               </td>
                           </tr>
                           @endforeach
                       </tbody>
                   </table>
                </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
        </section>
        <!-- /.Left col -->
        
        <!-- Right col -->
        <section class="col-lg-5 connectedSortable">
            <!-- Map card -->
            <div class="card bg-gradient-primary">
                <div class="card-header border-0">
                    <h3 class="card-title">
                        <i class="fas fa-bullhorn mr-1"></i>
                        System Notices
                    </h3>
                </div>
                <div class="card-body">
                    <p>Welcome to your new CRM Dashboard. Use the sidebar to navigate between modules.</p>
                </div>
            </div>
            <!-- /.card -->
        </section>
        <!-- right col -->
    </div>
    <!-- /.row (main row) -->

</x-admin-layout>
