<div class="row">
    <div class="col-md-12">
        <div class="card card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="hr-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="calc-tab" data-toggle="pill" href="#tab-calc" role="tab" wire:ignore.self>Pay Calculation</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="salary-tab" data-toggle="pill" href="#tab-salary" role="tab" wire:ignore.self>Salary Config</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="holiday-tab" data-toggle="pill" href="#tab-holiday" role="tab" wire:ignore.self>Festival & Holidays</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="hr-tabs-content">
                    
                    <!-- TAB: PAY CALCULATION -->
                    <div class="tab-pane fade show active" id="tab-calc" role="tabpanel" wire:ignore.self>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">Calculate Monthly Pay</div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Select User</label>
                                            <select class="form-control" wire:model="calcUserId">
                                                <option value="">-- Choose User --</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Month</label>
                                                    <select class="form-control" wire:model="calcMonth">
                                                        @for($i=1; $i<=12; $i++)
                                                            <option value="{{ sprintf('%02d', $i) }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Year</label>
                                                    <select class="form-control" wire:model="calcYear">
                                                        @for($y=date('Y'); $y>=date('Y')-2; $y--)
                                                            <option value="{{ $y }}">{{ $y }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-block" wire:click="calculateSalary">Calculate Now</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                @if($calcResult)
                                    <div class="card shadow-sm border-success">
                                        <div class="card-header bg-success text-white">Salary Summary: {{ $calcResult['user_name'] }}</div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 border-right">
                                                    <p><strong>Period:</strong> {{ $calcResult['month'] }}</p>
                                                    <p><strong>Base Salary:</strong> <span class="badge badge-info">INR {{ number_format($calcResult['base_salary'], 2) }}</span></p>
                                                    <hr>
                                                    <p><strong>Total Working Hours:</strong> {{ $calcResult['net_hours'] }} hrs</p>
                                                    <p><strong>Idle Hours (Deducted):</strong> <span class="text-danger">{{ $calcResult['idle_hours'] }} hrs</span></p>
                                                </div>
                                                <div class="col-md-6 p-4 text-center">
                                                    <p class="text-muted">Net Payable Salary</p>
                                                    <h2 class="text-success font-weight-bold">{{ $calcResult['currency'] }} {{ number_format($calcResult['payable'], 2) }}</h2>
                                                    <button class="btn btn-sm btn-outline-secondary mt-3" wire:click="generateSlip"><i class="fas fa-print"></i> Generate Slip</button>
                                                </div>
                                            </div>
                                            <div class="row mt-3 bg-light p-2 rounded">
                                                <div class="col-4 text-center"><strong>Holidays:</strong> {{ $calcResult['holidays'] }}</div>
                                                <div class="col-4 text-center"><strong>Leaves:</strong> {{ $calcResult['leaves'] }}</div>
                                                <div class="col-4 text-center"><strong>Final Days:</strong> --</div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info">Select a user and period to calculate salary.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- TAB: SALARY CONFIG -->
                    <div class="tab-pane fade" id="tab-salary" role="tabpanel" wire:ignore.self>
                        <div class="row">
                            <div class="col-md-4 border-right">
                                <h5 class="font-weight-bold mb-3">Set User Salary</h5>
                                <div class="form-group">
                                    <label>User</label>
                                    <select class="form-control" wire:model="userId">
                                        <option value="">-- Select --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Monthly Base Salary (Amount)</label>
                                    <input type="number" class="form-control" wire:model="baseSalary">
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Working Days/Mo</label>
                                            <input type="number" class="form-control" wire:model="workingDays">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Daily Hours</label>
                                            <input type="number" class="form-control" wire:model="workingHours">
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary" wire:click="saveSalaryConfig">{{ $editingSalaryId ? 'Update' : 'Save' }} Configuration</button>
                                @if($editingSalaryId)
                                    <button class="btn btn-link text-muted" wire:click="$set('editingSalaryId', null)">Cancel</button>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h5 class="font-weight-bold mb-3">Current Configurations</h5>
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Base Salary</th>
                                            <th>Hours/Day</th>
                                            <th>Days/Month</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salaries as $s)
                                            <tr>
                                                <td>{{ $s->user->name }}</td>
                                                <td>{{ $s->currency }} {{ number_format($s->base_salary, 2) }}</td>
                                                <td>{{ $s->daily_working_hours }}h</td>
                                                <td>{{ $s->working_days_per_month }} days</td>
                                                <td>
                                                    <button class="btn btn-xs btn-info" wire:click="editSalary({{ $s->id }})"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-xs btn-danger" onclick="confirm('Delete this configuration?') || event.stopImmediatePropagation()" wire:click="deleteSalary({{ $s->id }})"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: HOLIDAYS -->
                    <div class="tab-pane fade" id="tab-holiday" role="tabpanel" wire:ignore.self>
                        <div class="row">
                            <div class="col-md-4 border-right">
                                <h5 class="font-weight-bold mb-3">Add Festival / Holiday</h5>
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" class="form-control" wire:model="holidayDate">
                                </div>
                                <div class="form-group">
                                    <label>Holiday Name</label>
                                    <input type="text" class="form-control" wire:model="holidayName" placeholder="e.g. Diwali, Christmas">
                                </div>
                                <div class="form-group">
                                    <label>Type</label>
                                    <select class="form-control" wire:model="holidayType">
                                        <option value="Festival">Festival</option>
                                        <option value="Regular">Regular</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary" wire:click="saveHoliday">{{ $editingHolidayId ? 'Update' : 'Save' }} Holiday</button>
                                @if($editingHolidayId)
                                    <button class="btn btn-link text-muted" wire:click="$set('editingHolidayId', null)">Cancel</button>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h5 class="font-weight-bold mb-3">Upcoming / Past Holidays</h5>
                                <ul class="list-group">
                                    @foreach($holidays as $h)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <i class="fas fa-calendar-day text-primary mr-2"></i>
                                                {{ $h->date->format('M d, Y') }} - <strong>{{ $h->name }}</strong>
                                            </span>
                                            <div>
                                                <span class="badge badge-pill badge-primary mr-2">{{ $h->type }}</span>
                                                <button class="btn btn-xs btn-outline-info" wire:click="editHoliday({{ $h->id }})"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-xs btn-outline-danger" onclick="confirm('Delete this holiday?') || event.stopImmediatePropagation()" wire:click="deleteHoliday({{ $h->id }})"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
