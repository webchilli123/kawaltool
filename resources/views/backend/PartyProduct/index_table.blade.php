<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <table class="table table-bordered">
    <thead>
        <tr>
            <th>Party</th>
            <th>Products</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $party)
        <tr>
            <td>{{ $party->name }}</td>
            <td>
                <ul class="mb-0">
                    @foreach($party->partyProducts as $pp)
                        <li>
                            {{ strToupper($pp->product->sku) }}
                            <br>
                            <small class="text-muted">
                                {{ $pp->start_date }}
                                @if($pp->end_date)
                                    → {{ $pp->end_date }}
                                @endif
                            </small>
                        </li>
                    @endforeach
                </ul>
            </td>
            <td>
            <!-- <x-Backend.summary-comman-actions :id="$party->id" :routePrefix="$routePrefix" /> -->
                <a href="{{ route($routePrefix.'.edit', $party->id) }}"
       class="btn btn-sm btn-primary mt-1">
        <i class="icon-pencil-alt"></i>
    </a>
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