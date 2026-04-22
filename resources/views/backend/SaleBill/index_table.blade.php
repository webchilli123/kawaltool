<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8%"><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Party</th>
                    <th>Challan Date</th>
                    <th>Challan No.</th>
                    <th style="width: 15%">Amount Info</th>
                    <th style="width: 15%">Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td class="text-center">{{ $record->id }}</td>
                        <td>{{ $record->party->name ?? '-' }}</td>
                        <td>
                            {{ $record->bill_date }}
                        </td>
                        <td>
                            {{ $record->voucher_no }}

                            @if ($record->reference_no)
                                <br />
                                Reference No : {{ $record->reference_no }}
                                @endif
                                <br />
                                <br />
                                @if ($record->challan_type === 0)
        <span class="badge bg-success ms-1">Not Return Challan</span>
    @elseif ($record->challan_type === 1)
        <span class="badge bg-danger ms-1">Returnable Challan</span>
    @elseif ($record->challan_type === 2)
        <span class="badge bg-warning text-dark ms-1">From PI Challan</span>
    @endif
                        </td>

                        <td>
                            {{-- Amount : {{ $record->amount }}
                            <br />
                            Freight : {{ $record->freight }}
                            <br />
                            Discount : {{ $record->discount }}
                            <br />
                            IGST : {{ $record->igst }}
                            <br />
                            SGST : {{ $record->sgst }}
                            <br />
                            CGST : {{ $record->cgst }}
                            <br /> --}}
                            Receivable Amount : {{ $record->receivable_amount }}
                            <br />
                        </td>
                        <td>
                            <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                        </td>
                        <td>
                            <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />

                                <br/>

                        <a href="{{ route($routePrefix . '.print', $record->id) }}"
   target="_blank"
   class="btn btn-warning summary-action-button css-toggler mb-1"
   title="Print">
    <i class="fa-solid fa-print"></i>
</a>
                            {{-- <br/><br/>

                        <a href="{{ route('sale-bill-item-movement.index', ['sale_bill_id' => $record->id]) }}"
                            class="btn btn-secondary btn-sm summary-action-button">
                            <i class="fas fa-suitcase"></i> Shipments
                        </a>

                        <br/><br/>
                        
                        <a href="{{ route($routePrefix . '.return_items', ['id' => $record->id]) }}" 
                            class="btn btn-secondary btn-sm summary-action-button">
                            <i class="fas fa-suitcase"></i> Return
                        </a>
                        
                        <br/><br/> --}}

                            <span class="btn btn-info btn-sm css-toggler mb-1"
                                data-sr-css-class-toggle-target="#record-{{ $record->id }}"
                                data-sr-css-class-toggle-class="hidden">
                                Details
                            </span>
                        </td>
                    </tr>
                    <tr id="record-{{ $record->id }}" class="hidden">
                        <td></td>
                        <td colspan="6">
                            <h4>Items</h4>
                            <table class="table table-striped table-bordered table-hover mb-0 sub-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Rate</th>
                                        <th>IGST</th>
                                        <th>SGST</th>
                                        <th>CGST</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($record->saleBillItem as $k => $bill_item)
                                        <tr>
                                            <td><?= $k + 1 ?></td>
                                            <td>
                                                {{ $bill_item->product->getDisplayName() }}
                                            </td>
                                            <td>
                                                {{ $bill_item->qty }}
                                            </td>
                                            <td>{{ $bill_item->rate }}</td>
                                            <td>
                                                {{ $bill_item->igst }} ({{ $bill_item->igst_per }}%)
                                            </td>
                                            <td>
                                                {{ $bill_item->sgst }} ({{ $bill_item->sgst_per }}%)
                                            </td>
                                            <td>
                                                {{ $bill_item->cgst }} ({{ $bill_item->cgst_per }}%)
                                            </td>
                                            <td>{{ $bill_item->amount }}</td>
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
