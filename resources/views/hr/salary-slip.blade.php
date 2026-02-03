<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; padding: 40px; background: #f5f5f5; }
        .slip-container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #007bff; font-size: 28px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 14px; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-box { flex: 1; }
        .info-box h3 { color: #333; font-size: 14px; margin-bottom: 10px; text-transform: uppercase; }
        .info-box p { color: #666; font-size: 13px; line-height: 1.6; }
        .info-box strong { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th { background: #007bff; color: white; padding: 12px; text-align: left; font-size: 13px; }
        table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 13px; color: #333; }
        table tr:last-child td { border-bottom: none; }
        .total-row { background: #f8f9fa; font-weight: bold; font-size: 16px; }
        .total-row td { color: #007bff; padding: 15px 12px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #eee; text-align: center; color: #999; font-size: 12px; }
        .print-btn { background: #007bff; color: white; border: none; padding: 12px 30px; font-size: 14px; cursor: pointer; border-radius: 5px; margin-bottom: 20px; }
        .print-btn:hover { background: #0056b3; }
        @media print {
            body { padding: 0; background: white; }
            .print-btn { display: none; }
            .slip-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="slip-container">
        <button class="print-btn" onclick="window.print()">🖨️ Print Salary Slip</button>
        
        <div class="header">
            <h1>SALARY SLIP</h1>
            <p>{{ $month }}</p>
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>Employee Details</h3>
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Role:</strong> {{ $user->role->name ?? 'N/A' }}</p>
            </div>
            <div class="info-box" style="text-align: right;">
                <h3>Salary Configuration</h3>
                <p><strong>Base Salary:</strong> {{ $salary_config->currency }} {{ number_format($salary_config->base_salary, 2) }}</p>
                <p><strong>Working Days:</strong> {{ $salary_config->working_days_per_month }} days/month</p>
                <p><strong>Daily Hours:</strong> {{ $salary_config->daily_working_hours }} hours/day</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Working Hours</td>
                    <td style="text-align: right;">{{ $total_hours }} hrs</td>
                </tr>
                <tr>
                    <td>Idle Time (Deducted)</td>
                    <td style="text-align: right; color: #dc3545;">- {{ $idle_hours }} hrs</td>
                </tr>
                <tr>
                    <td><strong>Net Working Hours</strong></td>
                    <td style="text-align: right;"><strong>{{ $net_hours }} hrs</strong></td>
                </tr>
                <tr>
                    <td>Holidays</td>
                    <td style="text-align: right;">{{ $holidays }} days</td>
                </tr>
                <tr>
                    <td>Approved Leaves</td>
                    <td style="text-align: right;">{{ $leaves }} days</td>
                </tr>
            </tbody>
        </table>

        <table>
            <tr class="total-row">
                <td>NET PAYABLE SALARY</td>
                <td style="text-align: right;">{{ $salary_config->currency }} {{ number_format($payable, 2) }}</td>
            </tr>
        </table>

        <div class="footer">
            <p>Generated on {{ $generated_at }}</p>
            <p>This is a computer-generated document. No signature is required.</p>
        </div>
    </div>
</body>
</html>
