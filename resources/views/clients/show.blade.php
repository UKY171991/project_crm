<x-admin-layout>
    <x-slot name="header">
        Client Profile: {{ $client->company_name }}
    </x-slot>

    <div class="row">
        <div class="col-md-3">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-primary mx-auto" style="width: 100px; height: 100px; font-size: 40px; color: white;">
                             {{ strtoupper(substr($client->company_name, 0, 1)) }}
                        </div>
                    </div>

                    <h3 class="profile-username text-center">{{ $client->company_name }}</h3>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Contact Person</b> <a class="float-right">{{ $client->user->name }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right">{{ $client->user->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Phone</b> <a class="float-right">{{ $client->phone ?? 'N/A' }}</a>
                        </li>
                    </ul>

                    @if($client->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->phone) }}" target="_blank" class="btn btn-success btn-block"><b><i class="fab fa-whatsapp"></i> Chat on WhatsApp</b></a>
                    @endif
                </div>
            </div>

            <!-- About Me Box -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">About</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                    <p class="text-muted">
                        {{ $client->address ?? 'No address provided.' }}
                    </p>
                    <hr>
                    <strong><i class="fas fa-calendar-alt mr-1"></i> Client Since</strong>
                    <p class="text-muted">{{ $client->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#projects" data-toggle="tab">Projects</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="projects">
                             <table class="table table-striped">
                                 <thead>
                                     <tr>
                                         <th>Title</th>
                                         <th>Budget</th>
                                         <th>Due Date</th>
                                         <th>Status</th>
                                         <th>Action</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     @forelse($client->projects as $project)
                                     <tr>
                                         <td>{{ $project->title }}</td>
                                         <td>{{ $project->currency }} {{ number_format($project->budget, 2) }}</td>
                                         <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : 'N/A' }}</td>
                                         <td>
                                             <span class="badge {{ $project->status == 'Running' ? 'badge-success' : ($project->status == 'Pending' ? 'badge-warning' : 'badge-secondary') }}">
                                                 {{ $project->status }}
                                             </span>
                                         </td>
                                         <td>
                                             <a href="{{ route('projects.show', $project) }}" class="btn btn-xs btn-primary">View</a>
                                         </td>
                                     </tr>
                                     @empty
                                     <tr>
                                         <td colspan="5" class="text-center text-muted">No projects found.</td>
                                     </tr>
                                     @endforelse
                                 </tbody>
                             </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
