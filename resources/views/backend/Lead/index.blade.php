@extends($layout)

@section('content')
    <?php
    $page_header_links = [['title' => 'Create', 'url' => route($routePrefix . '.create')]];
    ?>

    @include($partial_path . '.page_header')

    <div class="card">
        <div class="card-body">
            <form method="GET" class="summary_search" action="{{ route($routePrefix . '.index') }}">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <x-Inputs.text-field name="customer_name" label="Customer Name" />
                    </div>
                    <div class="col-md-3">
                        <x-Inputs.text-field name="customer_number" label="Customer Number" />
                    </div>
                    <div class="col-md-3">
                        <x-Inputs.text-field name="customer_address" label="Customer Address" />
                    </div>
                    <div class="col-md-3">
                        <x-Inputs.drop-down name="is_new" label="New" :list="$yes_no_list" :value="$search['is_new']"
                            class="form-control select2" />
                    </div>
                    <div class="col-md-3">
                        <x-Inputs.drop-down name="source_id " label="Lead Source" :list="$sourceList ?? []" :value="$search['source_id']"
                            class="form-control select2" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <x-Inputs.drop-down name="party_id" label="Party" :list="$partyList" :value="$search['party_id']"
                            class="form-control select2" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <x-Inputs.drop-down name="level" label="Level" :list="$levelList ?? []" :value="$search['level']"
                            class="form-control select2" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <x-Inputs.drop-down name="missed_followup" label="Missed Follow Up" :list="['1' => 'Yes']"
                            :value="$search['missed_followup'] ?? ''" class="form-control select2" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <x-Inputs.drop-down name="status" label="Status" :list="$statusList ?? []" :value="$search['status']"
                            class="form-control select2" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <x-Inputs.drop-down name="follow_up_user_id" label="Follow Up By" :list="$userList"
                            :value="$search['follow_up_user_id']" class="form-control select2" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <x-Inputs.drop-down name="assigned_user_id" label="Assigned User" :list="$userList"
                            :value="$search['assigned_user_id']" class="form-control select2" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <x-Inputs.text-field id="follow_up_date" name="follow_up_date" label="Follow Up Date"
                            class="form-control date-picker" autocomplete="off" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <x-Inputs.drop-down name="follow_up_type" label="Follow Up Type" :list="$followtypeList ?? []"
                            :value="$search['follow_up_type']" class="form-control select2" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <x-Inputs.text-field name="comments" label="Comments" />
                    </div>
                    <div class="col-md-2 mb-3">
                        <x-Inputs.text-field id="date" name="date" label="Created Date"
                            class="form-control date-picker" autocomplete="off" />
                    </div>
                    <div class="col-md-2">
                        <x-Inputs.text-field id="from_date" name="from_date" :value="$search['from_date']" label="From Date"
                            class="form-control date-picker" autocomplete="off" data-date-end="input#to_date" />
                    </div>
                    <div class="col-md-2">
                        <x-Inputs.text-field id="to_date" name="to_date" :value="$search['to_date']" label="To Date"
                            class="form-control date-picker" autocomplete="off" data-date-start="input#from_date" />
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-sm-6 col-md-4">
                        <div>
                            <button type="submit" class="btn btn-primary">Search</button>
                            <span class="btn btn-secondary clear_form_search_conditions">Clear</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="index_table">
        @include($viewPrefix . '.index_table')
    </div>
@endsection
