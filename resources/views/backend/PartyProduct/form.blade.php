@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];
?>

@include($partial_path . ".page_header")

<form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <div class="row mt-2">
        <div class="col-xs-12">

            <div class="row mb-3">
                <div class="col-md-12 mb-2">
                    <div class="form-group">
                        <x-Inputs.drop-down id="party_id" name="party_id" label="Party"
                            :list="$partyList" :value="$model->party_id"
                            class="form-control select2" :mandatory="true" />
                    </div>
                </div>
            </div>

            <table id="item_table" class="table table-striped table-bordered order-column template-table"
                data-sr-table-template-min-row="1" data-sr-last-id="0">
                <thead>
                    <tr>
                        <th class="text-center" style="width : 50px;">
                            <span class="sr-table-template-add">
                                <i class="fas fa-plus-circle text-success icon"></i>
                            </span>
                        </th>
                        <th style="width : 40%;">Item</th>
                        <th style="width : 17%;">Start Date</th>
                        <th style="width : 17%;">End Date</th>
                        <th style="width : 26%;">Remarks</th>
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
                            <input type="hidden" name="party_products[(sr-counter)][id]" />
                            <x-Inputs.drop-down name="party_products[(sr-counter)][product_id]" label=""
                                :list="$itemList"
                                class="form-control item_id will-require" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="party_products[(sr-counter)][start_date]" class="form-control date-picker will-require start_date"
                                label="" data-date-end="0" autocomplete="off" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="party_products[(sr-counter)][end_date]" class="form-control date-picker will-require end_date"
                                label="" data-date-end="0" autocomplete="off" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="party_products[(sr-counter)][remarks]" label=""
                                class="form-control will-require remarks" />
                        </td>
                    </tr>
                    <?php
                    $party_products = old("party_products", $party_products ?? []);
                    ?>
                    @foreach($party_products as $k => $purchase_item)
                    <?php $id = $purchase_item['id'] ?? $k; ?>
                    <tr class="" sr-counter="{{ $id }}">
                        <td>
                            <input type="hidden" name="party_products[{{ $id }}][id]" value="{{ $purchase_item['id'] }}" />
                            <span class="sr-table-template-delete">
                                <i class="fas fa-times-circle text-danger icon"></i>
                            </span>
                        </td>
                        <td>
                            <?php $value = $purchase_item['product_id'] ?? ""; ?>
                            <x-Inputs.drop-down name="party_products[{{ $id }}][product_id]"
                                errorName="party_products.{{ $id }}.product_id"
                                label=""
                                :list="$itemList" :value="$value"
                                class="form-control select2 item_id" :mandatory="true" />
                        </td>
                        <td>
                            <?php $value = $purchase_item['start_date'] ?? ""; ?>
                            <x-Inputs.text-field name="party_products[{{ $id }}][start_date]" errorName="party_products.{{ $id }}.start_date" :value="$value" class="form-control date-picker start_date"
                                label="" data-date-end="0" autocomplete="off" :mandatory="true" />
                        </td>
                        <td>
                            <?php $value = $purchase_item['end_date'] ?? ""; ?>
                            <x-Inputs.text-field name="party_products[{{ $id }}][end_date]" errorName="party_products.{{ $id }}.end_date" :value="$value" class="form-control date-picker end_date"
                                label="" data-date-end="0" autocomplete="off" :mandatory="true" />
                        </td>
                        <td>
                            <?php $value = $purchase_item['remarks'] ?? ""; ?>
                            <x-Inputs.text-field name="party_products[{{ $id }}][remarks]" :value="$value" :mandatory="true" label=""
                                class="form-control remarks" />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

<script type="text/javascript">
    var party = null;
    $(function() {
        $(".template-table").srTableTemplate({
            afterRowAdd: function(_table, last_id, _tr) {

                setTimeout(() => {
                    _tr.find("select").select2({
                        placeHolder: "Please Select",
                        theme: "bootstrap-5",
                    });
                }, 200);

                setTimeout(() => {
                    _tr.find(".date-picker").flatpickr({
                        dateFormat: "d-M-Y",
                    });
                }, 200);

                _tr.find(".will-require").attr("required", true);
            }
        });

        $("#party_id").change(function(e, opt) {

            var v = $(this).val();
            if (v && v != "0") {
                ajaxGetJson("/party-ajax_get/" + v, function(response) {
                    party = response['data'];
                    console.log(party);
                    if (opt == "undefined") {
                        $(".item_id").trigger("change");
                    }
                });
            } else {
                party = null;
            }
        });

        $("#party_id").trigger("change", {
            pageLoad: true
        });

        $("form").submit(function() {
            if (!form_check_unique_list(".item_id")) {
                $.events.onUserError("Duplicate Items");
                return false;
            }

            $(".sr-table-template-row input, .sr-table-template-row select").attr("disabled", true);
        });
    });
</script>

@endsection