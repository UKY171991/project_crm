<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="card-title font-weight-bold">Work Logs</h3>
                    </div>
                    <div class="col-auto">
                        <div class="input-group input-group-sm">
                            <select class="form-control" wire:model.live="dateRange">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="all">All Time</option>
                            </select>
                            
                            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                            <select class="form-control" wire:model.live="userId">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>User ID</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Duration (Net)</th>
                                <th>Idle</th>
                                <th>Screenshots</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td><span class="badge badge-light">#{{ $log->user->id }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded-circle mr-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.8rem; color: #fff;">
                                                {{ substr($log->user->name, 0, 1) }}
                                            </div>
                                            {{ $log->user->name }}
                                        </div>
                                    </td>
                                    <td>{{ $log->date->format('M d, Y') }}</td>
                                    <td><span class="badge badge-success px-2 py-1"><i class="far fa-clock mr-1"></i> {{ $log->clock_in->format('h:i A') }}</span></td>
                                    <td>
                                        @if($log->clock_out)
                                            <span class="badge badge-danger px-2 py-1"><i class="far fa-clock mr-1"></i> {{ $log->clock_out->format('h:i A') }}</span>
                                        @else
                                            <span class="text-muted small">--:--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->clock_out)
                                            @php $net = max(0, $log->total_seconds - $log->idle_seconds); @endphp
                                            <span class="font-weight-bold text-primary">
                                                {{ floor($net / 3600) }}h {{ floor(($net % 3600) / 60) }}m {{ $net % 60 }}s
                                            </span>
                                        @else
                                            @php
                                                $activeSeconds = \Carbon\Carbon::parse($log->clock_in)->diffInSeconds(\Carbon\Carbon::now());
                                                $net = max(0, $activeSeconds - $log->idle_seconds);
                                            @endphp
                                            <span class="text-success blink">
                                                {{ floor($net / 3600) }}h {{ floor(($net % 3600) / 60) }}m {{ $net % 60 }}s (Active)
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-secondary small">{{ floor($log->idle_seconds / 60) }}m {{ $log->idle_seconds % 60 }}s</span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            @foreach($log->screenshots()->latest()->take(3)->get() as $ss)
                                                <a href="{{ asset('storage/' . $ss->path) }}" target="_blank" class="mr-1 shadow-sm border rounded overflow-hidden" style="width: 40px; height: 30px;">
                                                    <img src="{{ asset('storage/' . $ss->path) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        @if(!$log->clock_out)
                                            <span class="badge badge-pill badge-success">In Progress</span>
                                        @else
                                            <span class="badge badge-pill badge-light border">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-history fa-3x mb-3"></i>
                                        <p>No work logs found for this period.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
    <style>
        .blink {
            animation: blinker 1.5s linear infinite;
        }
        @keyframes blinker {
            50% { opacity: 0.5; }
        }
    </style>
</div>
