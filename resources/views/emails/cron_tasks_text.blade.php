CRM STATUS REPORT - {{ date('F j, Y') }}
==========================================

This is an internal report regarding pending projects and payments.

PENDING & RUNNING PROJECTS
--------------------------
@forelse($pendingProjects as $project)
- PROJECT: {{ $project->title }}
  CLIENT: {{ $project->client->user->name ?? 'N/A' }}
  STATUS: {{ $project->status }}
  DUE DATE: {{ $project->end_date ? $project->end_date->format('Y-m-d') : 'N/A' }}
  LATEST REMARK: {{ $project->projectRemarks->first()->remark ?? 'None' }}
@empty
No pending or running projects.
@endforelse

FINANCIAL OVERVIEW (PENDING PAYMENTS)
-------------------------------------
@forelse($pendingPayments as $project)
- PROJECT: {{ $project->title }}
  BUDGET: {{ $project->currency }} {{ number_format($project->budget, 2) }}
  PAID: {{ $project->currency }} {{ number_format($project->total_paid, 2) }}
  BALANCE: {{ $project->currency }} {{ number_format($project->balance, 2) }}
@empty
All payments are up to date.
@endforelse

TOTAL OUTSTANDING
-----------------
@foreach($totals as $currency => $amount)
- {{ $currency }}: {{ number_format($amount, 2) }}
@endforeach

------------------------------------------
This is a system-generated report. Please do not reply.
{{ config('app.name') }}
