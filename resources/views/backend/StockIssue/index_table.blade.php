<div class="card summary-card">

    {{-- Pagination --}}
    <div class="card-header">
        <x-backend.pagination-links :records="$records" />
    </div>

    <div class="card-body p-0">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Stock Issue No</th>
                    <th>Complaint</th>
                    <th>Stock Receiver</th>
                    <th>Info</th>
                    <th style="width:12%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->id }}</td>

                        <td>
                            <strong>{{ $record->issue_no }}</strong><br>
                            <strong>Issue Date :</strong> {{ $record->issue_date ?? '-' }}
                        </td>

                        <td>
                            <strong>Complaint No :</strong> {{ $record->complaint?->complaint_no ?? '-' }} <br>
                            <strong>Item :</strong>
                            @forelse($record->complaint?->complaintItems as $item)
                                {{ $item->product?->getDisplayName() ?? '-' }} <br>
                            @empty
                                -
                            @endforelse
                        </td>
                        <td>{{ $record->stockReceiver?->name }}</td>
                        <td>
                            <x-backend.index-table-info :record="$record" :userList="$userListCache" />
                        </td>

                        {{-- Actions --}}
                        <td>
                            {{-- <x-backend.summary-comman-actions
                            :id="$record->id"
                            :routePrefix="$routePrefix"
                        />

                        <br><br> --}}

                            <span class="btn btn-info btn-sm css-toggler"
                                data-sr-css-class-toggle-target="#record-{{ $record->id }}"
                                data-sr-css-class-toggle-class="hidden">
                                Details
                            </span>
                        </td>
                    </tr>

                    {{-- RAW MATERIAL DETAILS --}}
                    <tr id="record-{{ $record->id }}" class="hidden">
                        <td></td>
                        <td colspan="5">

                            <h5 class="mb-2">Issue Items</h5>

                            <table class="table table-striped table-bordered table-hover mb-0 sub-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Qty / Unit</th>
                                        {{-- <th>Issued Qty</th>
                                    <th>Consumed Qty</th> --}}
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($record->issueItems as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $item->product?->getDisplayName() ?? '-' }}
                                            </td>
                                            <td>{{ $item->qty }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                No raw materials found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No job cards found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <x-backend.pagination-links :records="$records" />
    </div>

</div>
