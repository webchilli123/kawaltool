<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 action-buttons">
            <a class="btn btn-info waves-effect waves-light" href="{{ url(route($routePrefix . '.csv', $search)) }}">Export CSV</a>
        </div>
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Customer Info</th>
                    <th>Level</th>
                    <th>Satus</th>
                    <th>Comments</th>
                    <th style="width: 15%">Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $i => $record)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>
                        @if($record->is_new == 0)
                        Party : {{ $record->party->name ?? '-' }}
                        <br />
                        @else
                        Name : {{ $record->customer_name }}
                        @if($record->customer_number)
                        <br />
                        Contact : {{ $record->customer_number }}
                        @endif
                        @if($record->customer_email)
                        <br />
                        Email : {{ $record->customer_email }}
                        @endif
                        @if($record->customer_website)
                        <br />
                        Website : {{ $record->customer_website }}
                        @endif
                        @if($record->customer_address)
                        <br />
                        Address : {{ $record->customer_address }}
                        @endif
                        @endif
                    </td>
                    <td>
                        {{ $record->level }}
                    </td>
                    <td>
                        @if($record->status == 'pending')
                        Pending
                        @elseif($record->status == 'follow_up')
                        Follow Up By: {{ $record->user->name ?? 'N/A' }}
                        <br />
                        Follow Up Date: {{ $record->follow_up_date ?? 'N/A' }}
                        <br />
                        Follow Up Type: {{ $record->follow_up_type ?? 'N/A' }}
                        @elseif($record->status == 'not_interested')
                        Not Interested
                        {{-- Not Interested Reason: {{ $record->not_in_interested_reason ?? 'N/A' }} --}}
                        @elseif($record->status == 'mature')
                        Mature
                        @else
                        {{ $record->status }}
                        @endif
                    </td>
                    <td>
                        {{ $record->comments }}
                    </td>
                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />

                        <br><br>

                        <strong>Assigned To:</strong>
@if($record->assignedUser)
    <span class="badge bg-primary">
        {{ $record->assignedUser->name }}
    </span>
@else
    <span class="badge bg-secondary">Unassigned</span>
@endif
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />

                        <br /><br />
                        
                        @if($record->is_include_items==0)
                        @else
                        <span class="btn btn-info btn-sm css-toggler mb-1"
                        data-sr-css-class-toggle-target="#record-{{ $record->id }}" data-sr-css-class-toggle-class="hidden">
                            Details Items
                        </span>
                        @endif
                        <br /><br />
                        <span class="btn btn-info btn-sm css-toggler mb-1"
                        data-sr-css-class-toggle-target="#records-{{ $record->id }}" data-sr-css-class-toggle-class="hidden">
                            Detail
                        </span>
                    </td>
                </tr>
                <tr id="record-{{ $record->id }}" class="hidden">
                    <td></td>
                    <td colspan="5">
                        <h4>Items</h4>
                        <table class="table table-striped table-bordered table-hover mb-0 sub-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->leadItem as $k => $lead_item)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $lead_item->product->item->name ?? null }} - {{ $lead_item->product->sku ?? null }} </td>
                                    <td>{{ $lead_item->qty }}</td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr id="records-{{ $record->id }}" class="hidden">
                    <td></td>
                    <td colspan="5">
                        <h4>Follow-Up Detail</h4>
                        <table class="table table-striped table-bordered table-hover mb-0 sub-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Follow-Up Type</th>
                                    <th>Follow-Up Date</th>
                                    <th>Folllow By</th>
                                    <th>Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->followups as $k => $followup)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $followup->follow_up_type ?? Null}}</td>
                                    <td>{{ $followup->follow_up_date }}</td>
                                    <td>{{ $followup->followupUser->name }}</td>
                                    <td>{{ $followup->comments }}</td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <x-Backend.pagination-links :records="$records" />
    </div>
</div>