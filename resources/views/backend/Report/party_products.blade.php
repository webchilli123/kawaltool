@extends($layout)

@section('content')

<?php

use App\Helpers\DateUtility;

?>

@include($partial_path . ".page_header")


<div class="card">
    {{-- <div class="card-body">
        <form method="GET">
            <div class="row mb-4">
                <div class="col-md-3">
                    <x-Inputs.drop-down name="warehouse_id" label="Warehouse"
                        :value="$search['warehouse_id']"
                        :list="$warehouse_list"
                        class="form-control select2" />
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-4">
                    <div>
                        <button type="submit" class="btn btn-primary w-md">Search</button>
                        <span class="btn btn-secondary clear_form_search_conditions">Clear</span>
                    </div>
                </div>
            </div>
        </form>
    </div> --}}
</div>
@if (isset($records))
<div class="card mt-3">
    <div class="card-body">
        {{-- <div class="mt-2 mb-2">
            <span class="btn btn-secondary table-export-csv"
                data-sr-table-csv-export-target="#report"
                data-sr-table-csv-export-filename="Party-Products">
                Export CSV (JS)
            </span>
        </div> --}}

        <table class="table table-striped table-bordered table-hover mb-0" id="report">
            <thead>
                <tr>
                    {{-- <th>#</th> --}}
                    <th>Party</th>
                    <th>Product</th>
                    <th>SKU</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp

                @foreach ($records as $party)
                    @foreach ($party->products as $product)
                      @php $counter++; @endphp
                        <tr>
                            {{-- <td>{{ $counter++ }}</td> --}}
                            {{-- <td>{{ $party->name }}</td> --}}
                               <td>
                    {{ $loop->first ? $party->name : '' }}
                </td>
                            <td>{{ $product->item->name }}</td>
                            <td>{{ $product->sku }}</td>
                        </tr>
                    @endforeach
                @endforeach

                @if($counter === 1)
                <tr>
                    <td colspan="4" class="text-center">No records found</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endif



@endsection