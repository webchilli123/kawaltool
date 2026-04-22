<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8%"><?= sortable_anchor('id', 'ID') ?></th>
                    <th>PI No.</th>
                    <th>Party</th>
                    <th>Date</th>
                    <th style="width: 15%">Amount Info</th>
                    <th style="width: 15%">Info</th>
                    <th style="width: 12%">Actions</th> 
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td class="text-center">{{ $record->id }}</td>
                    <td>{{ $record->pi_no }}</td>
                    <td>{{ $record->complaint->party->name }}</td>
                    <td>{{ $record->date->format(\App\Helpers\DateUtility::DATE_OUT_FORMAT)}}</td>
                    <td>
                        Amount : {{ $record->amount }}
                    </td>
                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />

                        <br/>
                        {{-- <br/><br/>
                        
                        <a href="{{ route('purchase-bill-item-movement.index', ['purchase_bill_id' => $record->id]) }}" 
                            class="btn btn-secondary btn-sm summary-action-button">
                            <i class="fas fa-suitcase"></i> Move Items To Warehouse
                        </a>
                        
                        <br/><br/>
                        
                        <a href="{{ route($routePrefix . '.return_items', ['id' => $record->id]) }}" 
                            class="btn btn-secondary btn-sm summary-action-button">
                            <i class="fas fa-suitcase"></i> Return
                        </a>
                        
                        <br/><br/> --}}
                        
                        <span class="btn btn-info btn-sm css-toggler mb-1">
                        Payment FollowUp
                    </span>
                    <br/>

                        <a href="{{ route($routePrefix . '.print', $record->id) }}"
   target="_blank"
   class="btn btn-warning summary-action-button css-toggler mb-1"
   title="Print">
    <i class="fa-solid fa-print"></i>
</a>

<span class="btn btn-info btn-sm css-toggler mb-1"
      title="View Details"
      data-sr-css-class-toggle-target="#record-{{ $record->id }}"
      data-sr-css-class-toggle-class="hidden">
    <i class="fa-solid fa-eye"></i>
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
                                @foreach($record->proformaInvoiceItem as $k => $purchase_bill_item)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $purchase_bill_item->product->item->getDisplayName() }}</td>
                                    <td>{{ $purchase_bill_item->qty }}</td>
                                    <td>{{ $purchase_bill_item->rate }}</td>
                                    <td>
                                        {{ $purchase_bill_item->igst }} ({{ $purchase_bill_item->igst_per }}%)
                                    </td>
                                    <td>
                                        {{ $purchase_bill_item->sgst }} ({{ $purchase_bill_item->sgst_per }}%)
                                    </td>
                                    <td>
                                        {{ $purchase_bill_item->cgst }} ({{ $purchase_bill_item->cgst_per }}%)
                                    </td>
                                    <td>{{ $purchase_bill_item->amount }}</td>
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