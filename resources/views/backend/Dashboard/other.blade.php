@extends($layout)
@section('content')
    {{-- <style>
        .blue {
            background-color: #19bbd2;
            color: #FFF;
        }

        .purple {
            background-color: #8f70e7;
            color: #FFF;
        }

        .magenta {
            background-color: #ae379b;
            color: #FFF;
        }

        .yellow {
            background-color: #fecb4b;
            color: #FFF;
        }


        .counter {
            padding: 0;
            box-shadow: 4px 4px 10px 0px rgba(0, 0, 0, 0.5);
        }

        .counter ol {
            list-style: none;
            padding-left: 1px;
        }

        .counter ol li {}

        .counter .card-title {
            padding: 6px;
            text-align: center;
        }

        .counter .card-body {
            padding: 4px;
        }

        .dashboard-table thead {
            position: sticky;
            background-color: #f2f2f2;
        }

        .dashboard-table tbody {
            height: 200px;
            /* Adjust to your desired height */
            overflow-y: scroll;
            /* Enables vertical scrolling for the tbody */
        }
    </style> --}}

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

        .loan-card {
            border-left-color: #6F42C1;
        }

        .partner-card {
            border-left-color: #D63384;
        }

        .staff-card {
            border-left-color: #FFC107;
        }
    </style>

    <div class="row">
        <div class="col-12 mt-4">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4>{{ isset($page_title) ? $page_title : 'Please set page_title variable' }}</h4>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">

        <!-- Complaints -->
        <div class="col-xl-6 col-lg-4 col-md-6">
            <div class="dashboard-card border-danger">
                <div class="card-icon text-danger">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="card-content">
                    <h6>Complaints</h6>
                    <h2>{{ $tComplaints }}</h2>
                    <p>Total Complaints</p>
                </div>
            </div>
        </div>

        <!-- Leads -->
        <div class="col-xl-6 col-lg-4 col-md-6">
            <div class="dashboard-card border-primary">
                <div class="card-icon text-primary">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <div class="card-content">
                    <h6>Leads</h6>
                    <h2>{{ $tLeads }}</h2>
                    <p>Total Leads</p>
                </div>
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

    </div>

    <div class="row mt-4">
        <!-- Today Leads -->
        <div class="col-xs-12 col-md-12 col-lg-6">
            <h4>Today Leads</h4>
            <table class="table table-striped table-bordered table-hover mb-0 dashboard-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer Info</th>
                        <th>Level</th>
                        <th>Comments</th>
                        <th>Follow Up Date/Satus</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($todayLeads as $k => $lead)
                        <tr>
                            <td>{{ $k + 1 }}</td>
                            <td>
                                @if ($lead->is_new == 0)
                                    Party : {{ $lead->party?->name ?? '-' }}
                                    <br />
                                @else
                                    Name : {{ $lead->customer_name ?? '-' }}
                                    <br />
                                    Contact : {{ $lead->customer_number ?? '-' }}
                                    <br />
                                    Email : {{ $lead->customer_email ?? '-' }}
                                    <br />
                                    Website : {{ $lead->customer_website ?? '-' }}
                                    <br />
                                @endif
                            </td>
                            <td>{{ $lead->level }}</td>
                            <td>{{ $lead->comments }}</td>
                            <td>{{ if_date($lead->follow_up_date) }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary updateBtn"
                                    data-id="{{ $lead->id }}" data-status="{{ $lead->status }}"
                                    data-date="{{ $lead->latestFollowUp?->follow_up_date }}"
                                    data-type="{{ $lead->latestFollowUp?->follow_up_type }}"
                                    data-comment="{{ $lead->latestFollowUp?->comments }}">
                                    <i class="bi-arrow-repeat"></i>
                                </button>
                                <br><br>
                                <a href="{{ route('lead.edit', $lead->id) }}" class="btn btn-info btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


        <div class="col-xs-12 col-md-12 col-lg-6">
            <h4>Next 7 Days Leads</h4>
            <table class="table table-striped table-bordered table-hover mb-0 dashboard-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer Info</th>
                        <th>Level</th>
                        <th>Comments</th>
                        <th>Follow Up Date/Satus</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nextDaysLeads as $k => $lead)
                        <tr>
                            <td>{{ $k + 1 }}</td>
                            <td>
                                @if ($lead->is_new == 0)
                                    Party : {{ $lead->party?->name ?? '-' }}
                                    <br />
                                @else
                                    Name : {{ $lead->customer_name ?? '-' }}
                                    <br />
                                    Contact : {{ $lead->customer_number ?? '-' }}
                                    <br />
                                    Email : {{ $lead->customer_email ?? '-' }}
                                    <br />
                                    Website : {{ $lead->customer_website ?? '-' }}
                                    <br />
                                @endif
                            </td>
                            <td>{{ $lead->level }}</td>

                            <td>{{ $lead->comments }}</td>
                            <td>{{ if_date($lead->follow_up_date) }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary updateBtn"
                                    data-id="{{ $lead->id }}" data-status="{{ $lead->status }}"
                                    data-date="{{ $lead->latestFollowUp?->follow_up_date }}"
                                    data-type="{{ $lead->latestFollowUp?->follow_up_type }}"
                                    data-comment="{{ $lead->latestFollowUp?->comments }}">
                                    <i class="bi-arrow-repeat"></i>
                                </button>
                                <br><br>
                                <a href="{{ route('lead.edit', $lead->id) }}" class="btn btn-info btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <!-- Missing Leads -->
    <div class="row mt-3">
        <div class="col-xs-12 col-md-12 col-lg-12">
            <h4>Missing Leads</h4>
            <table class="table table-striped table-bordered table-hover mb-0 dashboard-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer Info</th>
                        <th>Level</th>
                        <th>Comments</th>
                        <th>Follow Up Date/Satus</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($missingLeads as $k => $lead)
                        <tr>
                            <td>{{ $k + 1 }}</td>
                            <td>
                                @if ($lead->is_new == 0)
                                    Party : {{ $lead->party?->name ?? '-' }}
                                    <br />
                                @else
                                    Name : {{ $lead->customer_name ?? '-' }}
                                    <br />
                                    Contact : {{ $lead->customer_number ?? '-' }}
                                    <br />
                                    Email : {{ $lead->customer_email ?? '-' }}
                                    <br />
                                    Website : {{ $lead->customer_website ?? '-' }}
                                    <br />
                                @endif
                            </td>
                            <td>{{ $lead->level }}</td>

                            <td>{{ $lead->comments }}</td>
                            <td>
                                {{ if_date($lead->follow_up_date) }}
                                <span class="badge rounded-pill bg-danger text-white">Missed</span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary updateBtn"
                                    data-id="{{ $lead->id }}" data-status="{{ $lead->status }}"
                                    data-date="{{ $lead->latestFollowUp?->follow_up_date }}"
                                    data-type="{{ $lead->latestFollowUp?->follow_up_type }}"
                                    data-comment="{{ $lead->latestFollowUp?->comments }}">
                                    <i class="bi-arrow-repeat"></i>
                                </button>
                                <br><br>
                                <a href="{{ route('lead.edit', $lead->id) }}" class="btn btn-info btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

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
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            flatpickr(".date-picker", {
                dateFormat: "Y-m-d",
                allowInput: true
            });

            $(document).on("click", ".updateBtn", function () {

        let id      = $(this).data("id");
        let status  = $(this).data("status");
        let date    = $(this).data("date");
        let type    = $(this).data("type");
        let comment = $(this).data("comment");

        $("#lead_id").val(id);

        // 🔥 IMPORTANT: trigger change for select2
        $("#lead_status").val(status).trigger('change');
        $("#lead_type").val(type).trigger('change');

        $("#lead_date").val(date);
        $("#lead_comment").val(comment);

        // Show modal AFTER setting values
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
        });
    </script>
@endpush
