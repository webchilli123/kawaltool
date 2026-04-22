@extends($layout)

@section('content')

    <?php
    
    use App\Helpers\DateUtility;
    
    ?>

    @include($partial_path . '.page_header')


    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-6">
                        <x-Inputs.drop-down name="product_id" label="Product" :value="request('product_id')" :list="$item_list"
                            class="form-control select2" />
                    </div>

                    <div class="col-md-4">
                        <x-Inputs.drop-down name="warehouse_id" label="Warehouse" :value="request('warehouse_id')" :list="$warehouse_list"
                            class="form-control select2" />
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2 mb-4">
                        <button class="btn btn-primary w-100">Search</button>
                        <a href="{{ url()->current() }}" class="btn btn-secondary w-100">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    @if (isset($groupedRecords))
        @foreach ($groupedRecords as $groupKey => $rows)
            @php
                $first = $rows->first();
                $balance = 0;
            @endphp

            <div class="card mt-4">
                <div class="card-header bg-primary">
                    <strong>
                        {{ $item_list[$first->product_id] ?? 'Item' }}
                    </strong>
                    <span class="text-muted">
                        (Warehouse: {{ $warehouse_list[$first->warehouse_id] ?? '-' }})
                    </span>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Voucher</th>
                                <th>Type</th>
                                <th class="text-end">In (+)</th>
                                <th class="text-end">Out (-)</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    $balance += $row->qty_in;
                                    $balance -= $row->qty_out;
                                @endphp

                                <tr>
                                    <td>{{ date('d-m-Y', strtotime($row->movement_date)) }}</td>
                                    <td>
                                        @if ($row->module === 'purchase')
                                            <a href="{{ route('purchase-bill.index', ['voucher_no' => $row->voucher_no]) }}"
                                            target="_blank">
                                                {{ $row->voucher_no }}
                                            </a>

                                        @elseif ($row->module === 'sale')

                                            <a href="{{ route('sale-bill.index', ['voucher_no' => $row->voucher_no]) }}"
                                            target="_blank">
                                                {{ $row->voucher_no }}
                                            </a>

                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row->type == 'Opening Stock')
                                            <span class="badge badge-info">Opening</span>
                                        @elseif($row->type == 'Purchase')
                                            <span class="badge badge-success">Purchase</span>
                                        @else
                                            <span class="badge badge-danger">Sale</span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        {{ $row->qty_in > 0 ? $row->qty_in : '-' }}
                                    </td>

                                    <td class="text-end">
                                        {{ $row->qty_out > 0 ? $row->qty_out : '-' }}
                                    </td>

                                    <td class="text-end">
                                        <strong>{{ $balance }}</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="5" class="text-end">Final Stock</th>
                                <th class="text-end">{{ $balance }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    @endif


@endsection
