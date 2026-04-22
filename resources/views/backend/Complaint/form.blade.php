@extends($layout)

@section('content')
<?php
$page_header_links = [['title' => 'Summary', 'url' => route($routePrefix . '.index')]];

?>

@include($partial_path . '.page_header')

<form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <input id="id" type="hidden" value="">
    <div class="row mt-2">
        <div class="col-xs-12">
            <div class="form-group mb-3">
                <h5>Complaint No. # <small class="text-muted">{{ $model->complaint_no }}</small></h5>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <x-Inputs.drop-down id="party_id" name="party_id" label="Party" :list="$partyList"
                            :value="$model->party_id ?? ''" class="form-control select2 cascade" :mandatory="true"
                            data-sr-cascade-target="#contact_number, #contact_person"
                            data-sr-cascade-url="/get-customer-details/{v}" />
                    </div>
                    <span class="partyAddress"></span>
                </div>

                <div class="col-md-2 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="complainant_mobile" id="complainant_mobile" label="Complainant Mobile"
                            :value="old('complainant_mobile', $model->complainant_mobile ?? '')" />
                    </div>
                </div>

                <div class="col-md-2 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="contact_number" id="contact_number" label="Contact Number"
                            :value="old('contact_number', $model->contact_number ?? '')" />
                    </div>
                </div>

                <div class="col-md-2 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="contact_person" id="contact_person" label="Contact Person"
                            :value="old('contact_person', $model->contact_person ?? '')" />
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="date" class="form-control date-picker" label="Date"
                            :mandatory="true" :value="old('date', if_date($model->date))" autocomplete="off" />
                    </div>
                </div>
            </div>

            {{-- product select --}}
            <table id="item_table" class="table table-striped table-bordered order-column template-table mb-2"
                data-sr-table-template-min-row="1" data-sr-last-id="0">
                <thead>
                    <tr>
                        <th class="text-center" style="width : 50px;">
                            <span class="sr-table-template-add">
                                <i class="fas fa-plus-circle text-success icon"></i>
                            </span>
                        </th>
                        <th style="width : 50%;">Item</th>
                        <th style="width : 20%;">Reading</th>
                        <th style="width : 30%;">Remarks</th>
                        {{-- <th style="width : 15%;">Rate</th> --}}
                    </tr>
                </thead>
                <tbody>
                    <tr class="sr-table-template-row hidden">
                        <td>
                            <div class="block">
                                <span class="sr-table-template-delete">
                                    <i class=" fas fa-times-circle text-danger icon"></i>
                                </span>
                                (sr-counter)
                            </div>
                        </td>
                        <td>
                            <input type="hidden" name="complaint_items[(sr-counter)][id]" />
                            <x-Inputs.drop-down name="complaint_items[(sr-counter)][product_id]" label=""
                                :list="[]" class="form-control item_id will-require" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="complaint_items[(sr-counter)][reading]" label=""
                            class="form-control reading" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="complaint_items[(sr-counter)][remarks]" label=""
                                class="form-control will-require remarks" />
                        </td>
                    </tr>
                    <?php
                    $complaint_items = old('complaint_items', $complaint_items ?? []);
                    ?>
                    @foreach ($complaint_items as $k => $purchase_item)
                    <?php $id = $purchase_item['id'] ?? $k; ?>
                    <tr class="" sr-id="{{ $id }}">
                        <td>
                            <input type="hidden" name="complaint_items[{{ $id }}][id]"
                                value="{{ $purchase_item['id'] }}" />
                            <span class="sr-table-template-delete">
                                <i class="fas fa-times-circle text-danger icon"></i>
                            </span>
                        </td>
                        <td>
                            <?php $value = $purchase_item['product_id'] ?? ''; ?>
                            <x-Inputs.drop-down name="complaint_items[{{ $id }}][product_id]"
                                errorName="complaint_items.{{ $id }}.product_id" label=""
                                :list="$itemList" :value="$value" class="form-control select2 item_id"
                                :mandatory="true" />
                        </td>
                        <td>
                            <?php $value = $purchase_item['reading'] ?? ''; ?>
                            <x-Inputs.text-field name="complaint_items[{{ $id }}][reading]"
                                errorName="complaint_items.{{ $id }}.reading" label=""
                                :value="$value" class="form-control reading" />
                        </td>
                        <td>
                            <?php $value = $purchase_item['remarks'] ?? ''; ?>
                            <x-Inputs.text-field name="complaint_items[{{ $id }}][remarks]"
                                errorName="complaint_items.{{ $id }}.remarks" label=""
                                :value="$value" class="form-control remarks"
                                :mandatory="true" />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row mb-2">
                <div class="col-md-4 mb-2">
                    <x-Inputs.drop-down name="status" id="status-dropdown" label="Status" :list="$complaintstatusList"
                        class="form-control select2" :value="old('status', $model->status ?? '')" :mandatory="true" />
                </div>
                <div class="col-md-4 mb-2">
                    <x-Inputs.drop-down name="level" id="level-dropdown" label="Level" :list="$levelList"
                        class="form-control select2" :value="old('level', $model->level ?? '')" :mandatory="true" />
                </div>
                <div class="col-md-4 mb-2">
                    <x-Inputs.drop-down name="assign_to" label="Assign to" :list="$userList"
                        class="form-control select2" :value="old('assign_to', $model->assign_to ?? '')" :mandatory="true" />
                </div>
                <div class="form-group mb-3">
                    <x-Inputs.checkbox name="is_under_warranty" label="Under Warranty" :value="$model->is_under_warranty" />
                 </div>
                <div class="col-md-4 mb-2">
                    <x-Inputs.drop-down name="payment_status" id="payment_status" label="Payment Status" :list="$paymentStatusList"
                        class="form-control select2" :value="old('payment_status', $model->payment_status ?? '')" :mandatory="true" />
                </div>
                <div class="col-md-4 mb-2">
                    <x-Inputs.drop-down name="payment_mode" id="payment_mode" label="Payment Mode" :list="$paymentModeList"
                        class="form-control select2" :value="old('payment_mode', $model->payment_mode ?? '')" :mandatory="true" />
                </div>

                <div class="col-md-4 mb-2">
                    <x-Inputs.text-field name="amount" id="amount" :value="old('amount', $model->amount ?? '')" label="Amount"
                        class="form-control validate-float" :mandatory="true" />
                </div>

                <!-- <div class="col-md-12 mb-2">
                    <x-Inputs.checkbox name="is_new_party" id="new-party" label="New" :value="old('is_new', $model->is_new_party ?? false)" />
                </div> -->

                <div class="col-md-12 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-area name="remarks" label="Remarks" :value="old('remarks', $model->remarks ?? '')" class="form-control"
                            rows="6" maxlength="500" :mandatory="true" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

<script>
    $(document).ready(function() {

        setTimeout(function(){
            if ($('#party_id').val()) {
                $('#party_id').trigger('change');
            }
        },200);

        $('#party_id').on('change', function() {
            var partyId = $(this).val();
            if (partyId) {
                $.ajax({
                    url: '/get-customer-details/' + partyId,
                    type: 'GET',
                    success: function(data) {
                        console.log("Address", data);
                        $('#contact_number').val(data.contact_number);
                        $('#contact_person').val(data.contact_person);
                        $('.partyAddress').text(data.address);
                    }
                });
            } else {
                $('#contact_number').val('');
                $('#contact_person').val('');
                $('.partyAddress').text('');
            }

            if (!partyId) {
                clearItemDropdowns();
                return;
            }

            $.ajax({
                url: '/get-party-products/' + partyId,
                type: 'GET',
                success: function(products) {
                    populateItemDropdowns(products);
                },
                error: function() {
                    alert('Failed to load products');
                }
            });


            function populateItemDropdowns(products) {
                $('.item_id').each(function() {
                    let select = $(this);
                    let currentValue = select.val(); // preserve selection if possible

                    select.empty().append('<option value="">Select Item</option>');

                    $.each(products, function(i, product) {
                        select.append(
                            `<option value="${product.id}">${product.sku}</option>`
                        );
                    });

                    // restore previous selection if still exists
                    if (currentValue) {
                        select.val(currentValue);
                    }

                    select.trigger('change.select2');
                });
            }

            function clearItemDropdowns() {
                $('.item_id').each(function() {
                    $(this)
                        .empty()
                        .append('<option value="">Select Item</option>')
                        .val('')
                        .trigger('change.select2');
                });
            }

        });

        var selectedValue = $('#status-dropdown').val();

        if (selectedValue === "done" || $('#new-party').prop("checked")) {
            $('#new-party').closest('.col-md-12').show();
        } else {
            $('#new-party').closest('.col-md-12').hide();
        }

        $('#status-dropdown').change(function() {
            var selectedValue = $(this).val();

            if (selectedValue === "done") {
                $('#new-party').closest('.col-md-12').show();
            } else {
                $('#new-party').prop('checked', false).closest('.col-md-12').hide();

                $("#items-table-container").hide().find("input, select").val("");

                $("#items-table-container tbody tr").not('.sr-table-template-row').remove();
            }
        });

        function toggleSaleBillFields() {
            if ($('#is_under_warranty').is(':checked')) {
                $('#payment_status').closest('.col-md-4').hide();
                $('#payment_mode').closest('.col-md-4').hide();
                $('#amount').closest('.col-md-4').hide();

                // remove required
                $('#payment_status').prop('required', false);
                $('#payment_mode').prop('required', false);
                $('#amount').prop('required', false);

                // clear values
                $('#payment_status').val('').trigger('change');
                $('#payment_mode').val('').trigger('change');
                $('#amount').val('');
            } else {
                $('#payment_status').closest('.col-md-4').show();
                $('#payment_mode').closest('.col-md-4').show();
                $('#amount').closest('.col-md-4').show();

                // add required again
                $('#payment_status').prop('required', true);
                $('#payment_mode').prop('required', true);
                $('#amount').prop('required', true);
            }
        }

        toggleSaleBillFields();

        $('#is_under_warranty').change(toggleSaleBillFields);

    });

    function toggleItemsTable() {
        if ($("#new-party").is(":checked")) {
            $("#items-table-container").slideDown();
            $("#items-table-container").find("input, select").prop('disabled', false);
        } else {
            $("#items-table-container").slideUp(() => {
                $("#items-table-container").find("input, select").prop('disabled', true);
                $(".will-require").val("");
            });
        }
    }



    $("#new-party").change(toggleItemsTable);
    toggleItemsTable();

    $(function() {
        $(".template-table").srTableTemplate({
            afterRowAdd: function(_table, last_id, _tr) {

                setTimeout(() => {
                    _tr.find("select").select2({
                        placeHolder: "Please Select",
                        theme: "bootstrap-5",
                    });
                }, 200);

                _tr.find(".will-require").attr("required", true);
            }
        });

        $("form").submit(function() {
            if (!form_check_unique_list(".item_id")) {
                $.events.onUserError("Duplicate Items");
                return false;
            }

            $(".sr-table-template-row select, .sr-table-template-row input").attr("disabled", true);
        });

        $('form').submit(function(event) {
            if ($('#items-table-container').is(':hidden')) {
                $('#items-table-container select, #items-table-container input').prop('disabled', true);
            }
        });
    });
</script>
@endsection