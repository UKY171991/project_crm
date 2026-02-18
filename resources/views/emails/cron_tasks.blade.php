<!DOCTYPE html>
<html>
<head>
    <title>Daily CRM Update</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #444; background-color: #f4f7f6; padding: 20px; }
        .container { width: 100%; max-width: 900px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: #1a73e8; color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 10px 0 0; opacity: 0.9; font-size: 14px; }
        .section { padding: 30px; border-bottom: 1px solid #eee; }
        .section:last-child { border-bottom: none; }
        h2 { color: #1a73e8; font-size: 20px; margin-top: 0; display: flex; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e0e0e0; padding: 12px 15px; text-align: left; vertical-align: top; }
        th { background: #f8f9fa; color: #555; font-weight: 600; text-transform: uppercase; font-size: 12px; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-pending { background-color: #fff3e0; color: #ef6c00; }
        .status-running { background-color: #e3f2fd; color: #1565c0; }
        .status-completed { background-color: #e8f5e9; color: #2e7d32; }
        .balance-box { background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; text-align: center; font-weight: bold; }
        .project-details { font-size: 13px; color: #666; margin-top: 5px; }
        .urls-list { margin: 5px 0; padding-left: 15px; font-size: 12px; }
        .remarks-box { font-style: italic; color: #888; background: #fafafa; padding: 8px; border-left: 3px solid #ddd; margin-top: 5px; font-size: 12px; }
        .assignees { font-size: 11px; margin-top: 5px; color: #444; }
        .footer { background: #333; color: #ccc; padding: 20px; text-align: center; font-size: 12px; }
        .footer p { margin: 5px 0; }
        .footer a { color: #fff; text-decoration: underline; }
        .text-right { text-align: right; }
        .unsub { font-size: 10px; color: #999; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CRM Internal Status Report</h1>
            <p>Notification for {{ date('F j, Y') }}</p>
        </div>

        <!-- Pending Projects Section -->
        <div class="section">
            <h2>Work Progress Report</h2>
            @if(count($pendingProjects) > 0)
                <table>
                    <thead>
                        <tr>
                            <th width="40%">Project Info</th>
                            <th width="20%">Client</th>
                            <th width="15%">Status</th>
                            <th width="25%">Timeline</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingProjects as $project)
                            <tr>
                                <td>
                                    <strong>{{ $project->title }}</strong>
                                    <div class="project-details">
                                        {{ \Illuminate\Support\Str::limit($project->description, 100) }}
                                    </div>
                                    @if($project->urls && count($project->urls) > 0)
                                        <ul class="urls-list">
                                            @foreach($project->urls as $url)
                                                <li><a href="{{ $url['url'] }}" target="_blank">{{ $url['label'] ?: $url['url'] }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if($project->projectRemarks->isNotEmpty())
                                        <div class="remarks-box">
                                            <strong>Last update:</strong> {{ $project->projectRemarks->first()->remark }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ $project->client->user->name ?? 'N/A' }}
                                    <div style="font-size: 11px; color: #888;">{{ $project->client->company_name ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($project->status) }}">
                                        {{ $project->status }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 12px;">
                                        <strong>Start:</strong> {{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}<br>
                                        <strong>Due:</strong> {{ $project->end_date ? $project->end_date->format('Y-m-d') : 'N/A' }}
                                    </div>
                                    @if($project->assignees->isNotEmpty())
                                        <div class="assignees">
                                            <strong>Team:</strong> {{ $project->assignees->pluck('name')->implode(', ') }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="text-align: center; color: #999; padding: 20px;">No pending projects.</p>
            @endif
        </div>

        <!-- Pending Payments Section -->
        <div class="section">
            <h2>Financial Overview</h2>
            @if(count($pendingPayments) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th class="text-right">Budget</th>
                            <th class="text-right">Paid</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingPayments as $project)
                            <tr>
                                <td>
                                    <strong>{{ $project->title }}</strong><br>
                                    <span style="font-size: 11px; color: #888;">{{ $project->client->user->name ?? 'N/A' }}</span>
                                </td>
                                <td class="text-right">{{ $project->currency }} {{ number_format($project->budget, 2) }}</td>
                                <td class="text-right">{{ $project->currency }} {{ number_format($project->total_paid, 2) }}</td>
                                <td class="text-right">
                                    <div class="balance-box">
                                        {{ $project->currency }} {{ number_format($project->balance, 2) }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if(count($totals) > 0)
                    <tfoot>
                        @foreach($totals as $currency => $amount)
                        <tr style="background: #fdfdfd; font-weight: bold;">
                            <td colspan="3" class="text-right">Total Outstanding ({{ $currency }}):</td>
                            <td class="text-right" style="color: #c62828; font-size: 16px;">
                                {{ $currency }} {{ number_format($amount, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tfoot>
                    @endif
                </table>
            @else
                <p style="text-align: center; color: #999; padding: 20px;">No pending payments.</p>
            @endif
        </div>

        <div class="footer">
            <p><strong>{{ config('app.name') }} Administrator</strong></p>
            <p>This email was sent to {{ config('mail.from.address') }} as requested.</p>
            <p>You can adjust these notification settings in the <a href="{{ url('/settings') }}">Admin Panel</a>.</p>
            <div class="unsub">This is a transaction/internal report. To stop these emails, please disable the cron job on your server.</div>
        </div>
    </div>
</body>
</html>
