<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">

        <div class="d-flex flex-wrap gap-2 action-buttons">
            <a class="btn btn-info waves-effect waves-light"
                href="{{ url(route($routePrefix . '.csv', $search)) }}">Export CSV</a>
        </div>

        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th><?= sortable_anchor('name', 'Name') ?></th>
                    <th>Item Info</th>
                    <th>Active / De-Active</th>
                    <th>Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td>{{ $record->id }}</td>
                        <td>
                            {{ $record->getDisplayName() }}
                        </td>
                        <td>
                            Sku : {{ $record->sku }}
                            <br />
                            Specification : {{ $record->specification }}
                            <br />
                            Brand : {{ $record->brand->name }}
                            @if ($record->batch)
                                <br />
                                Batch No. : {{ $record->batch }}
                            @endif
                            @if ($record->barcode)
                                <br />
                                Barcode : {{ $record->barcode }}
                                @if ($record->barcode_image)
                                    <br />
                                    <img src="{{ asset('storage/' . $record->barcode_image) }}"
                                        alt="Barcode {{ $record->barcode }}" style="max-width:200px; height:auto;">
                                @endif
                            @endif
                        </td>
                        <td>
                            <x-backend.active-deactive :isActive="$record->is_active" :routePrefix="$routePrefix" :id="$record->id" />
                        </td>
                        <td>
                            <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                            <br>
                            <br>
                            <strong>
                                @php
                                    $types = [
                                        0 => ['label' => 'Spare', 'class' => 'badge-warning'],
                                        1 => ['label' => 'Finished', 'class' => 'badge-success'],
                                        2 => ['label' => 'Part', 'class' => 'badge-info'],
                                    ];

                                    $type = $types[$record->product_type] ?? [
                                        'label' => 'Unknown',
                                        'class' => 'badge-secondary',
                                    ];
                                @endphp

                                <span class="badge {{ $type['class'] }}">
                                    {{ $type['label'] }}
                                </span>
                            </strong>
                        </td>
                        <td>
                            <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />
                            <br>
                            <a href="{{ route($routePrefix . '.print', $record->id) }}" target="_blank"
                                class="btn btn-warning summary-action-button css-toggler mb-1" title="Print">
                                <i class="fa-solid fa-print"></i>
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
