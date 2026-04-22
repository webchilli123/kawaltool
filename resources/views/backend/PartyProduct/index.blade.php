@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Create", "url" => route($routePrefix . ".create")],
    // ["title" => "Create With PO", "url" => route($routePrefix . ".create_with_po")],
];
?>

@include($partial_path . ".page_header")

<div class="card">
    <div class="card-body">
        <form method="GET" class="summary_search" action="{{ route($routePrefix . '.index') }}">
            <div class="row mb-2">
                <div class="col-md-10">
                    <x-Inputs.drop-down name="party_id" :value="$search['party_id']" :list="$partyList"
                        label="Party"
                        class="form-control select2" />
                </div>
                <div class="col-md-2" style="margin-top:30px">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <span class="btn btn-secondary clear_form_search_conditions">Clear</span>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-4">
                </div>
            </div>
        </form>
    </div>
</div>

<div id="index_table">
    @include($viewPrefix . ".index_table")
</div>

@endsection