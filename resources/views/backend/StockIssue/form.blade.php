@extends($layout)

@section('content')
    <?php
    $page_header_links = [['title' => 'Summary', 'url' => route($routePrefix . '.index')]];
    ?>

    @include($partial_path . '.page_header')

    <form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
        {!! csrf_field() !!}
        {{ method_field($form['method']) }}
        <input id="id" type="hidden" value="{{ $model->id }}">

        <div class="row mt-2">
            <div class="col-xs-12">
                <div class="form-group mb-3">
                    <h5>Stock Issue No. # <small class="text-muted"> {{ $model->issue_no }} </small></h5>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 mb-2">
                        <div class="form-group">
                            <x-inputs.drop-down id="complaint_id" name="complaint_id" label="Complaint No" :list="$complaintList"
                                :value="$model->complaint_id" class="form-control select2" :mandatory="true" />
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="form-group">
                            <x-inputs.drop-down id="receiver_id" name="receiver_id" label="Receiver User" :list="$userList"
                                :value="$model->receiver_id" class="form-control select2" :mandatory="true" />
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="form-group">
                            <x-inputs.drop-down id="warehouse_id" name="warehouse_id" label="Warehouse" :list="$warehouseList"
                                :value="$model->warehouse_id" class="form-control select2" :mandatory="true" />
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        @if ($model->issue_date)
                            <x-inputs.text-field id="issue_date" name="issue_date" class="form-control date-picker"
                                label="Issue Date" value="{{ if_date($model->issue_date) }}" :mandatory="true"
                                autocomplete="off" />
                        @else
                            <x-inputs.text-field id="issue_date" name="issue_date" class="form-control date-picker"
                                label="Issue Date" :mandatory="true" data-date-end="0" autocomplete="off" />
                        @endif
                    </div>
                </div>

                {{-- items --}}
                <div id="complaint-items-view" class="mt-2 mb-2"></div>

                <table id="item_table" class="table table-striped table-bordered order-column template-table"
                    data-sr-table-template-min-row="1" data-sr-last-id="0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width : 50px;">
                                <span class="sr-table-template-add">
                                    <i class="fas fa-plus-circle text-success icon"></i>
                                </span>
                            </th>
                            <th style="width : 70%;">Item</th>
                            <th style="width : 30%;">Qty</th>
                            {{-- <th style="width : 10%;">Rate</th>
                        <th style="width : 10%;">IGST %</th>
                        <th style="width : 10%;">SGST %</th>
                        <th style="width : 10%;">CGST %</th>
                        <th style="width : 10%;">Amount</th> --}}
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
                                <input type="hidden" name="items[(sr-counter)][id]" />
                                <x-Inputs.drop-down name="items[(sr-counter)][product_id]" label="" :list="$itemList"
                                    class="form-control item_id will-require" />
                            </td>
                            <td>
                                <x-Inputs.text-field name="items[(sr-counter)][qty]" label=""
                                    class="form-control will-require validate-float cal_amount qty" />
                            </td>
                            {{-- <td>
                            <x-Inputs.text-field name="sale_items[(sr-counter)][rate]" label=""
                                class="form-control will-require validate-float cal_amount rate" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-counter)][igst_per]" label=""
                                value="0"
                                class="form-control will-require validate-float validate-less-than-equal cal_amount igst_per"
                                data-less-than-equal-from="0" />
                            IGST :
                            <span class="igst"></span>
                            <input type="hidden" name="sale_items[(sr-counter)][igst]" class="igst" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-counter)][sgst_per]" label=""
                                value="0"
                                class="form-control will-require validate-float validate-less-than-equal cal_amount sgst_per"
                                data-less-than-equal-from="0" />
                            SGST :
                            <span class="sgst"></span>
                            <input type="hidden" name="sale_items[(sr-counter)][sgst]" class="sgst" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-counter)][cgst_per]" label=""
                                value="0"
                                class="form-control will-require validate-float validate-less-than-equal cal_amount cgst_per"
                                data-less-than-equal-from="0" />
                            CGST :
                            <span class="cgst"></span>
                            <input type="hidden" name="sale_items[(sr-counter)][cgst]" class="cgst" />
                        </td>
                        <td>
                            <span class="amount"></span>
                            <input type="hidden" name="sale_items[(sr-counter)][amount]" class="amount" />
                        </td> --}}
                        </tr>
                        <?php
                        $items = old('items', $items ?? []);
                        ?>
                        @foreach ($items as $k => $sale_item)
                            <?php $id = $sale_item['id'] ?? $k; ?>
                            <tr class="" sr-counter="{{ $id }}">
                                <td>
                                    <input type="hidden" name="items[{{ $id }}][id]"
                                        value="{{ $sale_item['id'] }}" />
                                    <span class="sr-table-template-delete">
                                        <i class="fas fa-times-circle text-danger icon"></i>
                                    </span>
                                </td>
                                <td>
                                    <?php $value = $sale_item['product_id'] ?? ''; ?>
                                    <x-Inputs.drop-down name="items[{{ $id }}][product_id]"
                                        errorName="items.{{ $id }}.product_id" label="" :list="$itemList"
                                        :value="$value" class="form-control select2 item_id" :mandatory="true" />
                                </td>
                                <td>
                                    <?php $value = $sale_item['qty'] ?? ''; ?>
                                    <x-Inputs.text-field name="items[{{ $id }}][qty]"
                                        errorName="items.{{ $id }}.qty" label="" :value="$value"
                                        class="form-control validate-float cal_amount qty" :mandatory="true" />
                                </td>
                                {{-- <td>
                            <?php $value = $sale_item['rate'] ?? ''; ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][rate]"
                                errorName="sale_items.{{ $id }}.rate"
                                label=""
                                :value="$value"
                                class="form-control validate-float cal_amount rate"
                                :mandatory="true" />
                        </td>
                        <td>
                            <?php
                            $value = $sale_item['igst_per'] ?? '';
                            // $max_gst_per = $itemMaxGSTList[$sale_item['item_id']];
                            ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][igst_per]"
                                errorName="sale_items.{{ $id }}.igst_per"
                                label=""
                                :value="$value"
                                class="form-control validate-float validate-less-than-equal cal_amount igst_per"
                                 />
                            IGST :
                            <?php
                            $value = $sale_item['igst'] ?? '';
                            ?>
                            <span class="igst">{{ $value }}</span>
                            <input type="hidden" name="sale_items[{{ $id }}][igst]" value="{{ $value }}" class="igst" />
                        </td>
                        <td>
                            <?php $value = $sale_item['sgst_per'] ?? ''; ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][sgst_per]"
                                errorName="sale_items.{{ $id }}.sgst_per"
                                label=""
                                :value="$value"
                                class="form-control validate-float validate-less-than-equal cal_amount sgst_per"
                                 />
                            SGST :
                            <?php
                            $value = $sale_item['sgst'] ?? '';
                            ?>
                            <span class="sgst">{{ $value }}</span>
                            <input type="hidden" name="sale_items[{{ $id }}][sgst]" value="{{ $value }}" class="sgst" />
                        </td>
                        <td>
                            <?php $value = $sale_item['cgst_per'] ?? ''; ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][cgst_per]"
                                errorName="sale_items.{{ $id }}.cgst_per"
                                label=""
                                :value="$value"
                                class="form-control validate-float validate-less-than-equal cal_amount cgst_per"
                                 />
                            CGST :
                            <?php
                            $value = $sale_item['cgst'] ?? '';
                            ?>
                            <span class="cgst">{{ $value }}</span>
                            <input type="hidden" name="sale_items[{{ $id }}][cgst]" value="{{ $value }}" class="cgst" />
                        </td>
                        <td>
                            <span class="amount">{{ number_format($model->amount, 2, '.', '') }}</span>
                            <input type="hidden" name="sale_items[{{ $id }}][amount]" value="{{ $model->amount }}" class="amount" />
                        </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <x-Inputs.text-area name="remarks" label="Remarks" :value="$model->remarks" />
                    </div>
                </div>

            </div>
        </div>

        <div class="form-buttons mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
    </form>

    <script type="text/javascript">
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

            $("#complaint_id").change(function(e, opt) {

                var v = $(this).val();

                if (v && v != "0") {

                    ajaxGetJson("/complaint-ajax_get/" + v, function(response) {

                        complaint = response['data'];
                        console.log(complaint);

                        if (complaint && complaint.assign_to) {

                            let receiver_id = complaint.assign_to;

                            if (receiver_id) {
                                $('#receiver_id').val(receiver_id).trigger('change');
                            }
                        }


                        let html = '';

                        if (complaint && complaint.complaint_items && complaint.complaint_items
                            .length > 0) {

                            html += `<h6 class="mb-1">Complaint Items</h6>`;
                            html += `<ul class="list-group">`;

                            $.each(complaint.complaint_items, function(i, item) {

                                let name = item.product?.display_name ?? 'N/A';
                                let remarks = item.remarks ?? '';

                                html += `
                        <li class="list-group-item">
                            <strong>${name}</strong>
                            ${remarks ? ' - <em>' + remarks + '</em>' : ''}
                        </li>
                    `;
                            });

                            html += `</ul>`;
                        } else {
                            html = `<span class="text-muted">No items found</span>`;
                        }

                        $("#complaint-items-view").html(html);

                        if (opt == "undefined") {
                            $(".item_id").trigger("change");
                        }
                    });

                } else {
                    complaint = null;
                    $("#complaint-items-view").html('');
                }
            });

            $("#complaint_id").trigger("change", {
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
