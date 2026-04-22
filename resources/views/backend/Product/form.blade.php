@extends($layout)

@section('content')
    <?php
    $page_header_links = [['title' => 'Summary', 'url' => route($routePrefix . '.index')]];
    ?>

    @include($partial_path . '.page_header')

    <form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
        {!! csrf_field() !!}
        {{ method_field($form['method']) }}
        <div class="col-md-12">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">
                    Product Type <span class="text-danger">*</span>
                </label>

                <div class="d-flex gap-4 mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="product_type" id="spare_product" value="0"
                            {{ old('product_type', $model->product_type ?? 0) == 0 ? 'checked' : '' }}>
                        <label class="form-check-label" for="spare_product">
                            Spare Part
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="product_type" id="finished_product"
                            value="1" {{ old('product_type', $model->product_type ?? 0) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="finished_product">
                            Finished Product
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="product_type" id="part_product" value="2"
                            {{ old('product_type', $model->product_type ?? 0) == 2 ? 'checked' : '' }}>
                        <label class="form-check-label" for="part_product">
                            Part
                        </label>
                    </div>
                </div>
            </div>
        </div>
        {{-- PRODUCT DETAILS --}}
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <strong>Product Details</strong>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addItemModal">
                            +
                        </button>
                        <x-Inputs.drop-down id="item_id" name="item_id" label="Item" :value="$model->item_id" :list="$finishedItemList"
                            class="form-control select2" :mandatory="true" />
                    </div>

                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addBrandModal">
                            +
                        </button>
                        <x-Inputs.drop-down id="brand_id" name="brand_id" label="Brand" :value="$model->brand_id"
                            :list="$brandList" class="form-control select2" :mandatory="true" />
                    </div>

                    <div class="col-md-3">
                        <x-Inputs.text-field id="specification" name="specification" label="KVA Rating" :value="$model->specification"
                            placeholder="Enter Specification" />
                    </div>

                    <div class="col-md-3">
                        <x-Inputs.text-field id="sku" name="sku" label="SKU" :value="$model->sku"
                            readonly="readonly" />
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-3">
                        <x-Inputs.text-field id="capacity" name="capacity" label="Capacity" :value="$model->capacity"
                            placeholder="Enter Capacity" />
                    </div>

                    <div class="col-md-3">
                        <x-Inputs.text-field id="material_type" name="material_type" label="Material Type" :value="$model->material_type"
                            placeholder="Enter Material Type" />
                    </div>

                    <div class="col-md-3">
                        <x-Inputs.text-field id="batch" name="batch" label="Batch No" :value="$model->batch"
                            placeholder="Enter Batch No" />
                    </div>
                </div>

            </div>
        </div>

        {{-- STOCK DETAILS --}}
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <strong>Inventory / Stock Details</strong>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-2">
                        <x-Inputs.text-field id="opening_stock" name="opening_stock" label="Opening Stock"
                            :value="$model->opening_stock" placeholder="Enter Opening Stock" />
                    </div>
                    <div class="col-md-2">
                        <button type="button" 
                        class="btn btn-primary btn-sm"
                        data-bs-toggle="modal" 
                        data-bs-target="#addWarehouseModal">
                        +
                        </button>
                        <x-Inputs.drop-down id="warehouse_id" name="warehouse_id" label="Warehouse" :value="$model->warehouse_id"
                            :list="$warehouseList" class="form-control select2" />
                    </div>
                    <div class="col-md-4">
                        <x-Inputs.text-field id="min_stock" name="min_stock" label="Minimum Stock" :value="$model->min_stock"
                            placeholder="Enter Minimum Stock" />
                    </div>

                    <div class="col-md-4">
                        <x-Inputs.text-field id="max_stock" name="max_stock" label="Maximum Stock" :value="$model->max_stock"
                            placeholder="Enter Maximum Stock" />
                    </div>
                </div>

            </div>
        </div>


        {{-- PRICING DETAILS --}}
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <strong>Pricing & Taxes</strong>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-3">
                        <x-Inputs.text-field name="purchase_price" label="Purchase Price (Default)" :value="$model->purchase_price"
                            placeholder="Enter Price" class="form-control validate-float validate-postive-only" />
                    </div>

                    <div class="col-md-3">
                        <x-Inputs.text-field name="selling_price" label="Selling Price (Default)" :value="$model->selling_price"
                            placeholder="Enter Price" class="form-control validate-float validate-postive-only" />
                    </div>

                    <div class="col-md-3">
                        <x-Inputs.text-field name="gst" label="GST % (Default)" :value="$model->gst"
                            placeholder="Enter GST %"
                            class="form-control validate-float validate-postive-only validate-less-than"
                            data-less-than-from="50" />
                    </div>

                    <div class="col-md-3">
                        <x-Inputs.text-field name="discount" label="Discount % (Default)" :value="$model->discount"
                            placeholder="Enter Discount %" class="form-control" />
                    </div>
                </div>

            </div>
        </div>

        {{-- ADDITIONAL INFO --}}
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <strong>Additional Information</strong>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-3">
                        <x-Inputs.text-field name="rack_location" label="Rack Location" :value="$model->rack_location"
                            placeholder="Rack Location" />
                    </div>

                    <div class="col-md-3">
                        <x-Inputs.text-field name="barcode" label="Barcode" :value="$model->barcode" placeholder="Barcode"
                            readonly />
                    </div>

                    <div class="col-md-6" style="padding-top: 35px;">
                        {{-- <x-Inputs.checkbox name="product_type" label="Is Finished Product" :value="$model->product_type" /> --}}
                        <x-Inputs.checkbox name="is_returnable" label="Is Returnable" :value="$model->is_returnable" />
                        <x-Inputs.checkbox name="is_active" label="Active" :value="$model->is_active" class="ml-3" />
                    </div>
                </div>

            </div>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">Submit</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
    </form>

    

{{-- modal for item create pop up in product create page --}}

<div class="modal fade" id="addItemModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Add Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="addItemForm">
                    <label><input type="radio" name="product_type" id="spare" value="0">Spare
                        Part</label>
                    <label><input type="radio" name="product_type" id="finished" value="1">Finished
                        Product</label>
                    <label><input type="radio" name="product_type" id="part" value="2">Part</label><br>
                    <input type="text" name="name" placeholder="Enter item name" class="form-control">
                    <button type="submit" class="btn btn-success mt-2">Save</button>
                </form>

            </div>

        </div>
    </div>
</div>


{{-- modal for brand create pop up in product create page --}}

<div class="modal fade" id="addBrandModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Add Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="addBrandForm">
                    <label>Name
                    <input type="text" id="name" name="name" placeholder="Enter Name" class="form-control"
                    :value="$model->name" :mandatory="true"></label><br>
                    <label>Short Name
                    <input type="text" id="short_name" name="short_name" placeholder="Enter Short Name" class="form-control"
                    :value="$model->short_name" :mandatory="true"></label><br>
                    <button type="submit" class="btn btn-success mt-2">Save</button>
                </form>

            </div>

        </div>
    </div>
</div>


{{-- modal for warehouse create pop up in product create page --}} 

<div class="modal fade" id="addWarehouseModal">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5>Add Warehouse</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <form id="addWarehouseForm">
            <label>Warehouse Name </label>
            <input type="text" name="name" placeholder="Enter Warehouse Name" class="form-control" required>
            <button type="submit" class="btn btn-success mt-2">Save</button>
        </form>

      </div>

    </div>
  </div>
</div>

    @push('scripts')
        <script>
            $(function() {

                var item_name, brand, pattern = '<?= $item_sku_pattern ?>';

                function create_sku() {
                    var code = pattern;

                    if (item_name) {
                        code = code.replace("{item_name}", item_name['name']);
                    }

                    if (brand) {
                        code = code.replace("{brand}", brand['short_name']);
                    }

                    var specification = $("#specification").val();
                    if (specification && typeof specification == "string") {
                        code = code.replace("{specification}", specification);
                    } else {
                        code = code.replace("{specification}", "");
                    }

                    code = str_convert_space_to_hyphine(code);
                    code = str_trim_hyphine(code);
                    code = code.toLowerCase();

                    $("#sku").val(code);
                }

                $("#item_id").change(function() {
                    var v = $(this).val();

                    if (v) {
                        ajaxGetJson("/item_ajax_get/" + v, function(response) {
                            item_name = response["data"];
                            create_sku();
                        });
                    }
                }).trigger("change", {
                    pageLoad: true
                });

                $("#brand_id").change(function() {
                    var v = $(this).val();

                    if (v) {
                        ajaxGetJson("/brand_ajax_get/" + v, function(response) {
                            brand = response["data"];
                            create_sku();
                        });
                    }
                }).trigger("change", {
                    pageLoad: true
                });

                $("#name, #specification").keyup(function() {
                    create_sku();
                })

                $('#opening_stock').on('input', function() {
                    if ($(this).val() !== '') {
                        $('#warehouse_id').attr('required', true);
                    } else {
                        $('#warehouse_id').removeAttr('required');
                    }

                    // trigger change for select2 validation
                    $('#warehouse_id').trigger('change');
                });


                // Item lists from backend
                // const finishedItems = @json($finishedItemList);
                // const spareItems = @json($spareItemList);
                // const partItems = @json($partItemList);

                // function loadItems(items) {
                //     const $item = $('#item_id');

                //     $item.empty();
                //     $item.append('<option value="">Select Item</option>');

                //     $.each(items, function(id, name) {
                //         $item.append(
                //             $('<option></option>').val(id).text(name)
                //         );
                //     });

                //     // Refresh select2
                //     $item.trigger('change.select2');
                // }

                // // On product type change
                // $('input[name="product_type"]').on('change', function() {
                //     if ($(this).val() == '1') {
                //         loadItems(finishedItems);
                //     } elseif ($(this).val() == '0'){
                //         loadItems(spareItems);
                //     }else{
                //         loadItems(partItems);
                //     }
                // });

                // // Page load (edit case)
                // const selectedType = $('input[name="product_type"]:checked').val();

                // if (selectedType == '1') {
                //     loadItems(finishedItems);
                // }elseif ($(this).val() == '0'){
                //     loadItems(spareItems);
                // }else {
                //     loadItems(partItems);
                // }

                // // Restore selected item on edit
                // @if ($model && $model->item_id)
                //     $('#item_id').val('{{ $model->item_id }}').trigger('change');
                // @endif

                const finishedItems = @json($finishedItemList);
                const spareItems = @json($spareItemList);
                const partItems = @json($partItemList);

                function loadItems(items, selectedItem = null) {
                    const $item = $('#item_id');

                    $item.empty();
                    $item.append('<option value="">Select Item</option>');

                    $.each(items, function(id, name) {
                        $item.append(
                            $('<option></option>').val(id).text(name)
                        );
                    });


                    if (selectedItem) {
                        $item.val(selectedItem).trigger('change');
                    }
                }

                $('input[name="product_type"]').on('change', function() {
                    const type = $(this).val();

                    if (type == '1') {
                        loadItems(finishedItems);
                    } else if (type == '0') {
                        loadItems(spareItems);
                    } else if (type == '2') {
                        loadItems(partItems);
                    }
                });

                const selectedType = $('input[name="product_type"]:checked').val();
                const selectedItemId = '{{ $model->item_id ?? '' }}';

                if (selectedType == '1') {
                    loadItems(finishedItems, selectedItemId);
                } else if (selectedType == '0') {
                    loadItems(spareItems, selectedItemId);
                } else if (selectedType == '2') {
                    loadItems(partItems, selectedItemId);
                }

            });

            

            // jquery for item create pop up in product create page

                $(document).on('submit', '#addItemForm', function(e) {

                    e.preventDefault(); // stop page refresh

                    let form = this;
                    let formData = new FormData(form);

                    $.ajax({
                        url: "{{ route('items.store.ajax') }}",
                        type: "POST",
                        data: formData,
                        processData: false, // important for FormData
                        contentType: false, // important for FormData
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(data) {

                            if (data.success) {

                                let dropdown = $('#item_id');

                                let option = new Option(data.item.name, data.item.id, true, true);
                                dropdown.append(option);

                                // select2 fix
                                if (dropdown.hasClass('select2')) {
                                    dropdown.trigger('change');
                                }

                                // close modal
                                let modal = bootstrap.Modal.getInstance(document.getElementById('addItemModal'));
                                modal.hide();

                                form.reset();
                            }

                        },
                        error: function(err) {
                            console.log(err);
                    }
                });

            });


            

            // jquery for brand create pop up in product create page 


            $(document).on('submit', '#addBrandForm', function(e) {

                    e.preventDefault(); // stop page refresh

                    let form = this;
                    let formData = new FormData(form);

                    $.ajax({
                        url: "{{ route('brands.store.ajax') }}",
                        type: "POST",
                        data: formData,
                        processData: false, // important for FormData
                        contentType: false, // important for FormData
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(data) {

                            if (data.success) {

                                let dropdown = $('#brand_id');

                                let option = new Option(data.brand.name, data.brand.id, true, true);
                                dropdown.append(option);

                                // select2 fix
                                if (dropdown.hasClass('select2')) {
                                    dropdown.trigger('change');
                                }

                                // close modal
                                let modal = bootstrap.Modal.getInstance(document.getElementById('addBrandModal'));
                                modal.hide();

                                form.reset();
                            }

                        },
                        error: function(err) {
                            console.log(err);
                    }
                });

            });

       
        // jquery for warehouse create pop up in prodcut create page 

        $(document).on('submit', '#addWarehouseForm', function(e) {

                    e.preventDefault(); // stop page refresh

                    let form = this;
                    let formData = new FormData(form);

                    $.ajax({
                        url: "{{ route('warehouses.store.ajax') }}",
                        type: "POST",
                        data: formData,
                        processData: false, // important for FormData
                        contentType: false, // important for FormData
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(data) {

                            if (data.success) {

                                let dropdown = $('#warehouse_id');

                                let option = new Option(data.warehouse.name, data.warehouse.id, true, true);
                                dropdown.append(option);

                                // select2 fix
                                if (dropdown.hasClass('select2')) {
                                    dropdown.trigger('change');
                                }

                                // close modal
                                let modal = bootstrap.Modal.getInstance(document.getElementById('addWarehouseModal'));
                                modal.hide();

                                form.reset();
                            }

                        },
                        error: function(err) {
                            console.log(err);
                    }
                });

            });



        </script>

     
    @endpush
@endsection
