@extends($layout)

@section('content')
    <?php
    $page_header_links = [['title' => 'Summary', 'url' => route($routePrefix . '.index')]];
    ?>

    @include($partial_path . '.page_header')
    
    {{-- <div class="d-flex justify-content-end mt-3">
        <form action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="input-group" style="max-width: 350px;">
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                <button type="submit" class="btn btn-success">Import Excel</button>
            </div>
        </form>
    </div> --}}

    <form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
        {!! csrf_field() !!}
        {{ method_field($form['method']) }}
        <input id="id" type="hidden" value="{{ $model->id }}">
        <div class="row mt-2">
                <div class="row mb-2">
                    <div class="col-md-6 mb-2">
                        <div class="form-group">
                            <x-Inputs.text-field name="date" class="form-control date-picker" label="Date"
                                :value="old('date', $model->date ?? 'null')" :mandatory="true" autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-md-5 mb-2">
                        <x-Inputs.drop-down id="existing-customer-fields" name="party_id" label="Party"
                            class="form-control select2" :value="old('party_id', $model->party_id ?? '')" :list="$partyList" :mandatory="true" />
                    </div>
                    <div class="col-md-5 mb-2" id="new-customer-fields" style="display: none;">
                        <div class="row">
                            <div class="col-sm-6 mb-2">
                                <x-Inputs.text-field name="customer_name" placeholder="Enter Party Name" class="form-control" :value="old('customer_name', $model->customer_name ?? '')"
                                    label="Name *" />
                            </div>
                            <div class="col-sm-6 mb-2">
                                <x-Inputs.text-field name="firm_name" placeholder="Enter Firm Name" class="form-control" :value="old('firm_name', $model->firm_name ?? '')"
                                    label="Firm Name " />
                            </div>
                            <div class="col-sm-6 mb-2">
                                <x-Inputs.text-field name="customer_number" placeholder="Enter Party Mobile No." class="form-control" :value="old('customer_number', $model->customer_number ?? '')"
                                    label="Contact *" />
                            </div>
                            <div class="col-sm-6 mb-2">
                                <x-Inputs.text-field name="alternate_number" placeholder="Enter Party Alternate Mobile No." class="form-control" :value="old('customer_number', $model->customer_number ?? '')"
                                    label="Alternate Contact" />
                            </div>
                            <div class="col-sm-6 mb-2">
                                <x-Inputs.text-field name="customer_email" placeholder="Enter Party Email" class="form-control" :value="old('customer_email', $model->customer_email ?? '')"
                                    label="Email" />
                            </div>
                            <div class="col-sm-6 mb-2">
                                <x-Inputs.text-field name="customer_website" placeholder="Enter Party Website" class="form-control" :value="old('customer_website', $model->customer_website ?? '')"
                                    label="Website" />
                            </div>
                            <div class="col-sm-12 mb-2">
                                <x-Inputs.text-field name="customer_address" placeholder="Enter Party Address" class="form-control" :value="old('customer_address', $model->customer_address ?? '')"
                                    label="Address" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1" style="margin-top: 35px;">
                        <x-Inputs.checkbox name="is_new" id="new-customer" label="New Party" :value="old('is_new', $model->is_new ?? false)" />
                    </div>
                    <div class="col-md-6 mb-2">
                        <x-Inputs.drop-down name="source_id" label="Source" :list="$sourceList" :value="old('source_id', $model->source_id ?? '')"
                            class="form-control select2" :mandatory="true" />
                    </div>
                    <div class="col-md-6 mb-2">
                        <x-Inputs.drop-down name="level" label="Level" :list="$levelList" class="form-control select2"
                            :value="old('level', $model->level ?? 'null')" :mandatory="true" />
                    </div>

                    {{-- <div class="col-md-6 mb-2">
                        <x-Inputs.drop-down name="follow_up_user_id" label="Follow Up By *" :list="$userList"
                            :value="old('follow_up_user_id', $model->follow_up_user_id ?? '')" class="form-control select2" />
                    </div> --}}
                    
                </div>
                <div class="col-md-6 mb-2">
                    <x-Inputs.checkbox name="is_include_items" id="include-items" label="Include Items"
                        :value="old('is_include_items', $model->is_include_items ?? false)" />
                </div>
                <div id="items-table-container" class="mb-4" style="display: none;">
                    <table class="table table-striped table-bordered order-column template-table"
                        data-sr-table-template-min-row="0" data-sr-last-id="0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width : 80px;">
                                    <span class="sr-table-template-add">
                                        <i class="fas fa-plus-circle text-success icon"></i>
                                    </span>
                                </th>
                                <th style="width : 50%;">Item</th>
                                <th style="width : 50%;">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="sr-table-template-row hidden">
                                <td>
                                    <div class="block">
                                        <div class="left-block">

                                        </div>
                                        <div class="right-block">
                                            <span class="sr-table-template-delete">
                                                <i class="fas fa-times-circle text-danger icon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <x-Inputs.drop-down name="lead_items[product_id][]" label="" :list="$itemList"
                                        :value="old('lead_items.product_id', $model->product_id ?? '')" class="form-control will-require" />
                                </td>
                                <td>
                                    <x-Inputs.text-field name="lead_items[qty][]" :value="old('lead_items.qty', $model->qty ?? '')" label=""
                                        class="form-control will-require validate-float" style="margin-top: -23px" />
                                </td>
                            </tr>
                            <?php
                            $lead_items = old('lead_items', $lead_items ?? []);
                            ?>
                            @foreach ($lead_items as $k => $lead_item)
                                <?php $id = $lead_item['id'] ?? $k; ?>
                                <tr class="" sr-id="{{ $id }}">
                                    <td>
                                        <!-- <input type="hidden" name="lead_items[{{ $id }}][id]" value="{{ $id }}" /> -->
                                        <span class="sr-table-template-delete">
                                            <i class="fas fa-times-circle text-danger icon"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <?php $value = $lead_item['product_id'] ?? ''; ?>
                                        <x-Inputs.drop-down name="lead_items[product_id][]" label="" :list="$itemList"
                                            :value="$value" class="form-control select2 will-require"
                                            :mandatory="true" />
                                    </td>
                                    <td>
                                        <?php $value = $lead_item['qty'] ?? ''; ?>
                                        <x-Inputs.text-field name="lead_items[qty][]" label="" :value="$value"
                                            class="form-control validate-float will-require" style="margin-top: -23px" :mandatory="true" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6 mb-2">
                        <x-Inputs.drop-down name="assigned_user_id" id="assigned_user_id" label="Assigned User" :list="$userList"
                            class="form-control select2" :value="old('assigned_user_id', $model->assigned_user_id ?? '')" :mandatory="true" />
                    </div>
                    <div class="col-md-6 mb-2">
                        <x-Inputs.drop-down name="status" id="status-dropdown" label="Status" :list="$statusList"
                            class="form-control select2" :value="old('status', $model->status ?? '')" :mandatory="true" />
                    </div>
                </div>
                <div class="row status-dependent" id="not-interested-reason" style="display: none;">
                    <div class="col-md-6 mb-2">
                        <x-Inputs.text-field name="not_in_interested_reason" class="form-control" :value="old('not_in_interested_reason', $model->not_in_interested_reason ?? '')"
                            label="Not Interested Reason" />
                    </div>
                </div>
                <div class="row status-dependent" id="follow-up-fields" style="display: none;">
                    <div class="col-md-4 mb-2">
                        <x-Inputs.text-field name="follow_up_date" class="form-control date-picker"
                            label="Follow Up Date" :value="old('follow_up_date', $model->follow_up_date ?? '')" />
                    </div>
                    <div class="col-md-4 mb-2">
                        <x-Inputs.drop-down name="follow_up_type" label="Follow Up Type" :list="$followtypeList"
                            :value="old('follow_up_type', $model->follow_up_type ?? '')" class="form-control select2" />
                    </div>
                    <div class="col-md-4 mb-2">
                        <x-Inputs.drop-down name="follow_up_user_id" id="follow_up_user_id" label="Follow-Up User" :list="$userList"
                         class="form-control select2" :value="old('follow_up_user_id', $model->follow_up_user_id ?? '')" />
                    </div>
                </div>
                <div class="row status-dependent" id="mature-fields" style="display: none;">
                    <div class="col-md-6 mb-2">
                        <x-Inputs.drop-down name="mature_action_type" label="Action To Take *" :list="$maturefieldList"
                            :value="old('mature_action_type', $model->mature_action_type ?? '')" class="form-control select2" />
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-12 mb-3">
                        <x-Inputs.text-area name="comments" label="Comments" :value="$model->comments" />
                    </div>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </div>
    </form>

    <script type="text/javascript">
        $(document).ready(function() {

            function toggleCustomerFields() {
                if ($('#new-customer').is(':checked')) {
                    // Show new customer fields
                    $('#new-customer-fields').show();

                    // Make only name and number required
                    $('#new-customer-fields [name="customer_name"]').attr("required", true);
                    $('#new-customer-fields [name="customer_number"]').attr("required", true);

                    // Ensure others are not required
                    $('#new-customer-fields [name="customer_email"]').removeAttr("required");
                    $('#new-customer-fields [name="customer_website"]').removeAttr("required");

                    // Hide existing customer select
                    $('#existing-customer-fields').closest('.col-md-5')
                        .hide()
                        .find('select')
                        .removeAttr("required")
                        .val('');
                } else {
                    // Hide new customer fields
                    $('#new-customer-fields').hide();

                    // Remove required from all new-customer inputs
                    $('#new-customer-fields input, #new-customer-fields select, #new-customer-fields textarea')
                        .removeAttr("required");

                    // Show existing customer select and make it required
                    $('#existing-customer-fields').closest('.col-md-5')
                        .show()
                        .find('select')
                        .attr("required", true);
                }
            }


            $('#new-customer').change(toggleCustomerFields);
            toggleCustomerFields();

            function toggleStatusFields(clearValues = false) {
                let selectedStatus = $('#status-dropdown').val();

                $('.status-dependent').hide().find('input, select, textarea')
                    .removeAttr("required");

                if (clearValues) {
                    $('.status-dependent').find('input, select, textarea').val('');
                }

                if (selectedStatus === 'not_interested') {
                    $('#not-interested-reason').show().find('input, select, textarea').attr("required", false);
                } else if (selectedStatus === 'follow_up') {
                    $('#follow-up-fields').show().find('input, select, textarea').attr("required", true);
                } else if (selectedStatus === 'mature') {
                    $('#mature-fields').show().find('input, select, textarea').attr("required", true);
                }
            }

            toggleStatusFields(false);


            $('#status-dropdown').change(function() {
                toggleStatusFields(true);
            });



            function toggleItemsTable() {
                if ($("#include-items").is(":checked")) {
                    $("#items-table-container").slideDown();
                } else {
                    $("#items-table-container").slideUp(() => {
                        $(".will-require").removeAttr("required").val("");
                    });
                }
            }

            $("#include-items").change(toggleItemsTable);
            toggleItemsTable();


        });
        $(function() {
            $(".template-table").srTableTemplate({
                afterRowAdd: function(_table, last_id, _tr) {

                    _tr.find("select").select2({
                        placeHolder: "Please Select",
                        theme: "bootstrap-5",
                    });

                    _tr.find(".will-require").attr("required", true);
                }
            });


            $("form").submit(function() {
                if (!form_check_unique_list(".product_id")) {
                    $.events.onUserError("Duplicate Items");
                    return false;
                }

                $(".sr-table-template-row select, .sr-table-template-row input").attr("disabled", true);
            });
        });
    </script>
@endsection
