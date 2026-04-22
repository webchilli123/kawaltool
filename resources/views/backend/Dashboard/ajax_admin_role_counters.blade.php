<style>
    .dashboard-card {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 6px solid transparent;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    }

    .dashboard-card .card-icon {
        font-size: 40px;
    }

    .dashboard-card .card-content h6 {
        margin: 0;
        font-size: 14px;
        color: #777;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .dashboard-card .card-content h2 {
        margin: 4px 0;
        font-size: 30px;
        font-weight: 600;
    }

    .dashboard-card .card-content p {
        margin: 0;
        font-size: 13px;
        color: #888;
    }

    /* COLORS */
    .loan-card {
        border-left-color: #6F42C1;
    }

    .partner-card {
        border-left-color: #D63384;
    }

    .staff-card {
        border-left-color: #FFC107;
    }

    /* blinking badge */
    .blink-badge {
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
</style>

<ul class="nav nav-tabs mb-3" id="leadTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button">
            Overview
        </button>
    </li>

    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#today-leads" type="button">
            Today Follow-Ups <span class="badge bg-warning {{ $todayLeadCount > 0 ? 'blink-badge' : '' }}">{{ $todayLeadCount }}</span>
        </button>
    </li>

    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#next-leads" type="button">
            Next 7 Days Follow-Ups <span class="badge bg-success {{ $nextDaysLeadCount > 0 ? 'blink-badge' : '' }}">{{ $nextDaysLeadCount }}</span>
        </button>
    </li>

    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#missing-leads" type="button">
            Missing Follow-Ups <span class="badge bg-danger {{ $missingLeadCount > 0 ? 'blink-badge' : '' }}">
                {{ $missingLeadCount }}
            </span>
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="overview">
        <div class="row g-4 mb-4">
            <!-- Complaints -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="dashboard-card border-danger">
                    <div class="card-icon text-danger">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <a href="{{ route('complaint.index') }}"><div class="card-content">
                        <h6>Complaints</h6>
                        <h2>{{ $tComplaints }}</h2>
                        <p>Total Complaints</p>
                    </div></a>
                </div>
            </div>

            <!-- Leads -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="dashboard-card border-primary">
                    <div class="card-icon text-primary">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <a href="{{ route('lead.index') }}"><div class="card-content">
                        <h6>Leads</h6>
                        <h2>{{ $tLeads }}</h2>
                        <p>Total Leads</p>
                    </div></a>
                </div>
            </div>

            <!-- Proforma -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="dashboard-card border-info">
                    <div class="card-icon text-info">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <a href="{{ route('proforma-invoice.index') }}"><div class="card-content">
                        <h6>Proforma Invoices</h6>
                        <h2>{{ $tPI }}</h2>
                        <p>Total PI</p>
                    </div></a>
                </div>
            </div>

            <!-- Parties -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="dashboard-card border-success">
                    <div class="card-icon text-success">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <a href="{{ route('party.index') }}"><div class="card-content">
                        <h6>Parties</h6>
                        <h2>{{ $tParties }}</h2>
                        <p>Total Parties</p>
                    </div></a>
                    
                </div>
            </div>

            <!-- Lead Levels -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="dashboard-card border-danger">
                    <div class="card-icon text-danger">
                        <i class="fa-solid fa-temperature-high"></i>
                    </div>

                    <div class="card-content">
                        <h6>Lead Levels</h6>
                        <p class="text-muted mb-2">Hot / Warm / Cold</p>

                        @php
                            $levelColors = [
                                'Hot' => 'danger',
                                'Warm' => 'warning',
                                'Cold' => 'primary',
                            ];
                        @endphp

                        @if (!empty($leadLevel) && count($leadLevel))
                            <div class="small">
                                @foreach ($leadLevel as $level => $count)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-{{ $levelColors[$level] ?? 'dark' }}">
                                            <i class="fa-solid fa-circle me-1 text-{{ $levelColors[$level] ?? 'secondary' }}"
                                                style="font-size:8px;"></i>
                                            {{ $level }}
                                        </span>
                                        <strong>{{ $count }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <small class="text-muted">No level data</small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Lead Status -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="dashboard-card border-success">
                    <div class="card-icon text-success">
                        <i class="fa-solid fa-list-check"></i>
                    </div>

                    <div class="card-content">
                        <h6>Lead Status</h6>
                        <p class="text-muted mb-2">Current lead state</p>

                        @php
                            $statusColors = [
                                'Pending' => 'secondary',
                                'Follow Up' => 'info',
                                'Not-interested' => 'dark',
                                'Mature' => 'success',
                            ];
                        @endphp

                        @if (!empty($leadStatus) && count($leadStatus))
                            <div class="small">
                                @foreach ($leadStatus as $status => $count)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-{{ $statusColors[$status] ?? 'dark' }}">
                                            <i class="fa-solid fa-circle me-1 text-{{ $statusColors[$status] ?? 'secondary' }}"
                                                style="font-size:8px;"></i>
                                            {{ $status }}
                                        </span>
                                        <strong>{{ $count }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <small class="text-muted">No status data</small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Complaint Levels -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="dashboard-card border-danger">
                    <div class="card-icon text-danger">
                        <i class="fa-solid fa-fire"></i>
                    </div>

                    <div class="card-content">
                        <h6>Complaint Levels</h6>
                        <p class="text-muted mb-2">Hot / Warm / Cold</p>

                        @php
                            $complaintLevelColors = [
                                'Hot' => 'danger',
                                'Warm' => 'warning',
                                'Cold' => 'primary',
                            ];
                        @endphp

                        @if (!empty($complaintLevel) && count($complaintLevel))
                            <div class="small">
                                @foreach ($complaintLevel as $level => $count)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-{{ $complaintLevelColors[$level] ?? 'dark' }}">
                                            <i class="fa-solid fa-circle me-1 text-{{ $complaintLevelColors[$level] ?? 'secondary' }}"
                                                style="font-size:8px;"></i>
                                            {{ $level }}
                                        </span>
                                        <strong>{{ $count }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <small class="text-muted">No level data</small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Complaint Status -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="dashboard-card border-success">
                    <div class="card-icon text-success">
                        <i class="fa-solid fa-list-check"></i>
                    </div>

                    <div class="card-content">
                        <h6>Complaint Status</h6>
                        <p class="text-muted mb-2">Current complaint state</p>

                        @php
                            $complaintStatusColors = [
                                'Pending' => 'secondary',
                                'In-Progress' => 'info',
                                'Hold' => 'warning',
                                'Done' => 'success',
                            ];
                        @endphp

                        @if (!empty($complaintStatus) && count($complaintStatus))
                            <div class="small">
                                @foreach ($complaintStatus as $status => $count)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-{{ $complaintStatusColors[$status] ?? 'dark' }}">
                                            <i class="fa-solid fa-circle me-1 text-{{ $complaintStatusColors[$status] ?? 'secondary' }}"
                                                style="font-size:8px;"></i>
                                            {{ $status }}
                                        </span>
                                        <strong>{{ $count }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <small class="text-muted">No status data</small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Complaint Status -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="dashboard-card border-success">
                    <div class="card-icon text-success">
                        <i class="fa-solid fa-list-check"></i>
                    </div>

                    <div class="card-content">
                        <h6>Payment Status</h6>
                        <p class="text-muted mb-2">Current payment state</p>

                        @php
                            $paymentStatusColors = [
                                'Pending' => 'secondary',
                                'Received' => 'success',
                            ];
                        @endphp

                        @if (!empty($paymentStatus) && count($paymentStatus))
                            <div class="small">
                                @foreach ($paymentStatus as $status => $count)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-{{ $paymentStatusColors[$status] ?? 'dark' }}">
                                            <i class="fa-solid fa-circle me-1 text-{{ $paymentStatusColors[$status] ?? 'secondary' }}"
                                                style="font-size:8px;"></i>
                                            {{ $status }}
                                        </span>
                                        <strong>{{ $count }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <small class="text-muted">No status data</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <hr>

        {{-- graphs --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12 col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header card-no-border pb-3">
                            <h5 class="mb-1">Complaints by Status</h5>

                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <span><b>Total:</b>
                                    {{ $complaint_counters['Pending'] + $complaint_counters['In-Progress'] + $complaint_counters['Hold'] + $complaint_counters['Done'] }}</span>

                                <span style="color:#ff4d4d">● Pending ({{ $complaint_counters['Pending'] }})</span>
                                <span style="color:#ffd11a">● In-Progress
                                    ({{ $complaint_counters['In-Progress'] }})</span>
                                <span style="color:#80bfff">● Hold ({{ $complaint_counters['Hold'] }})</span>
                                <span style="color:#43A047">● Done ({{ $complaint_counters['Done'] }})</span>
                            </div>
                        </div>

                        <div class="card-body apex-chart text-center">
                            <canvas id="complaintPieChart" style="max-height:300px;"></canvas>
                        </div>
                    </div>
                </div>


                <!-- RIGHT CARD -->
                <div class="col-sm-12 col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header card-no-border pb-3">
                            <h5 class="mb-1">Complaint by Level</h5>

                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <span><b>Total:</b>
                                    {{ $complaint_counters['Hot'] + $complaint_counters['Cold'] + $complaint_counters['Warm'] }}</span>

                                <span style="color:#E53935">● Hot ({{ $complaint_counters['Hot'] }})</span>
                                <span style="color:#FBC02D">● Cold ({{ $complaint_counters['Cold'] }})</span>
                                <span style="color:#43A047">● Warm ({{ $complaint_counters['Warm'] }})</span>
                            </div>
                        </div>

                        <div class="card-body apex-chart text-center">
                            <canvas id="statusComplaintPieChart" style="height:300px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header card-no-border pb-3">
                            <h5 class="mb-1">Leads by Level</h5>

                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <span><b>Total:</b>
                                    {{ $lead_counters['Hot'] + $lead_counters['Warm'] + $lead_counters['Cold'] }}
                                </span>

                                <span style="color:#E53935">● Hot ({{ $lead_counters['Hot'] }})</span>
                                <span style="color:#FBC02D">● Cold ({{ $lead_counters['Cold'] }})</span>
                                <span style="color:#43A047">● Warm ({{ $lead_counters['Warm'] }})</span>
                            </div>
                        </div>

                        <div class="card-body apex-chart text-center">
                            <canvas id="leadLevelPieChart" style="max-height:300px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-xl-6 box-col-6">
                    <div class="card">
                        <div class="card-header card-no-border pb-3">
                            <h5 class="mb-1">Leads by Status</h5>

                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <span><b>Total:</b>
                                    {{ $lead_counters['Pending'] +
                                        $lead_counters['Not-interested'] +
                                        $lead_counters['Follow Up'] +
                                        $lead_counters['Mature'] }}
                                </span>

                                <span style="color:#FB8C00">● Pending ({{ $lead_counters['Pending'] }})</span>
                                <span style="color:#E53935">● Not Interested
                                    ({{ $lead_counters['Not-interested'] }})</span>
                                <span style="color:#1E88E5">● Follow Up ({{ $lead_counters['Follow Up'] }})</span>
                                <span style="color:#43A047">● Mature ({{ $lead_counters['Mature'] }})</span>
                            </div>
                        </div>

                        <div class="card-body text-center">
                            <canvas id="leadStatusChart" style="height:300px;"></canvas>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <!-- TODAY LEADS -->
    <div class="tab-pane fade" id="today-leads">
        <h4 class="mb-2">Today Leads</h4>
        <table class="table table-striped table-bordered table-hover dashboard-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Info</th>
                    <th>Level</th>
                    <th>Comments</th>
                    <th>Assigned User</th>
                    <th>Follow Up Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($todayLeads as $k => $lead)
                    <tr>
                        <td>{{ $k + 1 }}</td>
                        <td>
                            @if ($lead->is_new == 0)
                                Party : {{ $lead->party?->name ?? '-' }}
                            @else
                                Name : {{ $lead->customer_name }}<br>
                                Contact : {{ $lead->customer_number }}
                            @endif
                        </td>
                        <td>{{ ucfirst($lead->level) }}</td>
                        <td>{{ $lead->latestFollowUp?->comments }}</td>
                                <td>{{ $lead->assignedUser?->name }}</td>
                        <td>{{ if_date($lead->latestFollowUp?->follow_up_date) }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary updateBtn"
                                data-id="{{ $lead->id }}" data-status="{{ $lead->status }}"
                                data-date="{{ $lead->latestFollowUp?->follow_up_date }}"
                                data-type="{{ $lead->latestFollowUp?->follow_up_type }}"
                                data-comment="{{ $lead->latestFollowUp?->comments }}">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>

                            <br><br>

                            <a href="{{ route('lead.edit', $lead->id) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No records</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- NEXT 7 DAYS -->
    <div class="tab-pane fade" id="next-leads">
        <h4 class="mb-2">Next 7 Days Leads</h4>
        <table class="table table-striped table-bordered table-hover dashboard-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Info</th>
                    <th>Level</th>
                    <th>Comments</th>
                    <th>Assigned User</th>
                    <th>Follow Up Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($nextDaysLeads as $k => $lead)
                    <tr>
                        <td>{{ $k + 1 }}</td>
                        <td>
                             @if ($lead->is_new == 0)
                                Party : {{ $lead->party?->name ?? '-' }}
                            @else
                                Name : {{ $lead->customer_name }}<br>
                                Contact : {{ $lead->customer_number }}
                            @endif
                        </td>
                        <td>{{ ucfirst($lead->level) }}</td>
                        <td>{{ $lead->latestFollowUp?->comments }}</td>
                                <td>{{ $lead->assignedUser?->name }}</td>
                        <td>{{ if_date($lead->latestFollowUp?->follow_up_date) }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary updateBtn"
                                data-id="{{ $lead->id }}" data-status="{{ $lead->status }}"
                                data-date="{{ $lead->latestFollowUp?->follow_up_date }}"
                                data-type="{{ $lead->latestFollowUp?->follow_up_type }}"
                                data-comment="{{ $lead->latestFollowUp?->comments }}">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>

                            <br><br>

                            <a href="{{ route('lead.edit', $lead->id) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No records</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MISSING LEADS -->
    <div class="tab-pane fade" id="missing-leads">
        <h4 class="mb-2">Missing Leads</h4>
        <table class="table table-striped table-bordered table-hover dashboard-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Info</th>
                    <th>Level</th>
                    <th>Comments</th>
                    <th>Assigned User</th>
                    <th>Follow Up Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($missingLeads as $k => $lead)
                    <tr>
                        <td>{{ $k + 1 }}</td>
                        <td>
                             @if ($lead->is_new == 0)
                                Party : {{ $lead->party?->name ?? '-' }}
                            @else
                                Name : {{ $lead->customer_name }}<br>
                                Contact : {{ $lead->customer_number }}
                            @endif
                        </td>
                        <td>{{ ucfirst($lead->level) }}</td>
                        <td>{{ $lead->latestFollowUp?->comments }}</td>
                                <td>{{ $lead->assignedUser?->name }}</td>
                        <td>
                            {{ if_date($lead->latestFollowUp?->follow_up_date) }}
                            <span class="badge bg-danger">Missed</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary updateBtn"
                                data-id="{{ $lead->id }}" data-status="{{ $lead->status }}"
                                data-date="{{ $lead->latestFollowUp?->follow_up_date }}"
                                data-type="{{ $lead->latestFollowUp?->follow_up_type }}"
                                data-comment="{{ $lead->latestFollowUp?->comments }}">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>

                            <br><br>

                            <a href="{{ route('lead.edit', $lead->id) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No records</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<span class="cpending d-none">{{ $complaint_counters['Pending'] }}</span>
<span class="cip d-none">{{ $complaint_counters['In-Progress'] }}</span>
<span class="chold d-none">{{ $complaint_counters['Hold'] }}</span>
<span class="cdone d-none">{{ $complaint_counters['Done'] }}</span>

<span class="hotComplaint d-none">{{ $complaint_counters['Hot'] }}</span>
<span class="coldComplaint d-none">{{ $complaint_counters['Cold'] }}</span>
<span class="warmComplaint d-none">{{ $complaint_counters['Warm'] }}</span>

{{-- leads --}}
<span class="hotLead d-none">{{ $lead_counters['Hot'] }}</span>
<span class="coldLead d-none">{{ $lead_counters['Cold'] }}</span>
<span class="warmLead d-none">{{ $lead_counters['Warm'] }}</span>

<span class="lPending d-none">{{ $lead_counters['Pending'] }}</span>
<span class="lNotInterested d-none">{{ $lead_counters['Not-interested'] }}</span>
<span class="lFollowUp d-none">{{ $lead_counters['Follow Up'] }}</span>
<span class="lMature d-none">{{ $lead_counters['Mature'] }}</span>

<div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateForm" action="{{ route('lead.updateMissed') }}" method="POST">
                @csrf
                {{-- @method('PUT') --}}
                <input type="hidden" name="id" id="lead_id">

                <div class="modal-header">
                    <h5 class="modal-title">Update Follow-Up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-12 mb-3">
                            <x-Inputs.drop-down name="status" id="lead_status" label="Missing Follow Up Report"
                                :list="$statusList" class="form-control select2" :value="old('status')" :mandatory="true" />
                        </div>

                        <div class="col-md-12 mb-3">
                            <x-Inputs.text-field name="follow_up_date" id="lead_date"
                                class="form-control date-picker" label="Follow Up Date" :value="old('follow_up_date')"
                                :mandatory="true" />
                        </div>

                        <div class="col-md-12 mb-3">
                            <x-Inputs.drop-down name="follow_up_type" id="lead_type" label="Follow Up Type"
                                :list="$followtypeList" :value="old('follow_up_type')" class="form-control select2" :mandatory="true" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <x-Inputs.text-area id="lead_comment" name="comments" label="Comments" />
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        // LEVEL WISE DATA
        const cpending = parseInt($('.cpending').text().trim());
        const cip = parseInt($('.cip').text().trim());
        const chold = parseInt($('.chold').text().trim());
        const cdone = parseInt($('.cdone').text().trim());

        const totalComplaint = cpending + cip + chold + cdone;

        // LEVEL CHART
        new Chart(document.getElementById("complaintPieChart"), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In-Progress', 'Hold', 'Done'],
                datasets: [{
                    data: [cpending, cip, chold, cdone],
                    backgroundColor: ['#E53935', '#FBC02D', '#1E88E5', '#43A047'],
                    hoverOffset: 10
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            padding: 15,
                            font: {
                                size: 13
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Complaints by Status',
                        font: {
                            size: 16
                        }
                    }
                }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw(chart) {
                    const ctx = chart.ctx;
                    ctx.restore();
                    ctx.font = "bold 18px Arial";
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    ctx.fillStyle = "#333";
                    ctx.fillText("Total: " + totalComplaint, chart.width / 2, chart.height / 2);
                    ctx.save();
                }
            }]
        });

        // Level WISE DATA
        const hotComplaint = parseInt($('.hotComplaint').text().trim());
        const coldComplaint = parseInt($('.coldComplaint').text().trim());
        const warmComplaint = parseInt($('.warmComplaint').text().trim());

        const totalStatus = hotComplaint + coldComplaint + warmComplaint;


        new Chart(document.getElementById("statusComplaintPieChart"), {
            type: 'bar',
            data: {
                labels: ['Hot', 'Cold', 'Warm'],
                datasets: [{
                    label: 'Complaints',
                    data: [hotComplaint, coldComplaint, warmComplaint],
                    backgroundColor: ['#E53935', '#43A047', '#FB8C00'],
                    borderRadius: 6,
                    barThickness: 40
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });


        // leads by level
        const hotLead = parseInt($('.hotLead').text().trim());
        const coldLead = parseInt($('.coldLead').text().trim());
        const warmLead = parseInt($('.warmLead').text().trim());

        const totalLeads = hotLead + coldLead + warmLead;

        new Chart(document.getElementById("leadLevelPieChart"), {
            type: 'doughnut',
            data: {
                labels: ['Hot', 'Cold', 'Warm'],
                datasets: [{
                    data: [hotLead, coldLead, warmLead],
                    backgroundColor: ['#FB8C00', '#FDD835', '#43A047'],
                    hoverOffset: 10
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            padding: 15
                        }
                    },
                    title: {
                        display: true,
                        text: 'Leads by Level',
                        font: {
                            size: 16
                        }
                    }
                }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw(chart) {
                    const ctx = chart.ctx;
                    ctx.restore();
                    ctx.font = "bold 18px Arial";
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    ctx.fillStyle = "#333";
                    ctx.fillText("Total: " + totalLeads, chart.width / 2, chart.height / 2);
                    ctx.save();
                }
            }]
        });

        const pending = parseInt($('.lPending').text().trim());
        const notInterested = parseInt($('.lNotInterested').text().trim());
        const followUp = parseInt($('.lFollowUp').text().trim());
        const mature = parseInt($('.lMature').text().trim());

        const statusTotalLeads = pending + notInterested + followUp + mature;

        new Chart(document.getElementById("leadStatusChart"), {
            type: 'bar',
            data: {
                labels: ['Pending', 'Not Interested', 'Follow Up', 'Mature'],
                datasets: [{
                    label: 'Leads',
                    data: [pending, notInterested, followUp, mature],
                    backgroundColor: ['#FF9F40', '#D32F2F', '#1E88E5', '#2E7D32'],
                    borderRadius: 6,
                    barThickness: 18
                }]
            },
            options: {
                indexAxis: 'y', // 🔥 horizontal bar
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });


    flatpickr(".date-picker", {
        dateFormat: "Y-m-d",
        allowInput: true
    });

    $(document).on("click", ".updateBtn", function() {
        // Set values from button data
        $("#lead_id").val($(this).data("id"));
        $("#lead_status").val($(this).data("status"));
        $("#lead_date").val($(this).data("date"));
        $("#lead_type").val($(this).data("type"));
        $("#lead_comment").val($(this).data("comment"));

        // Call toggle function after setting values
        toggleStatusFields(false);

        // Show modal
        $("#updateModal").modal("show");
    });

    function toggleStatusFields(clearValues = false) {
        let selectedStatus = $('#lead_status').val();

        // Hide all dependent fields first
        $('.status-dependent').hide().find('input, select, textarea')
            .removeAttr("required");

        // Clear values if requested
        if (clearValues) {
            $('.status-dependent').find('input, select, textarea').val('');
        }

        // Show fields based on status
        if (selectedStatus === 'not_interested') {
            $('#not-interested-reason').show()
                .find('input, select, textarea').attr("required", true);
        } else if (selectedStatus === 'follow_up') {
            $('#follow-up-fields').show()
                .find('input, select, textarea').attr("required", true);
        } else if (selectedStatus === 'mature') {
            $('#mature-fields').show()
                .find('input, select, textarea').attr("required", true);
        }
    }

    // Run once on page load (for edit mode if values already exist)
    toggleStatusFields(false);

    // On change of status dropdown
    $('#lead_status').change(function() {
        toggleStatusFields(true);
    });
</script>
