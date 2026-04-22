@extends($layout)

@section('content')
<?php
$page_header_links = [['title' => 'Summary', 'url' => route($routePrefix . '.index')]];

?>

@include($partial_path . '.page_header')


<form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}

    {{-- PARTY BASIC DETAILS --}}
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <strong>Party Basic Details</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <x-Inputs.text-field name="name" label="Party Name" placeholder="Enter Party Name"
                        :value="$model->name ?? old('name')" :mandatory="true" />
                </div>

                <div class="col-md-6">
                    <x-Inputs.text-field name="email" label="Email" placeholder="Enter Email" :value="$model->email ?? old('email')" />
                </div>
            </div>
        </div>
    </div>

    {{-- CONTACT INFORMATION --}}
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <strong>Contact Information</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <x-Inputs.text-field name="mobile" label="Mobile No." placeholder="Enter Mobile No."
                        class="validate-float form-control" :mandatory="true" :value="$model->mobile ?? old('mobile')" />
                </div>

                <div class="col-md-4">
                    <x-Inputs.text-field name="phone" label="Phone No." placeholder="Enter Phone No."
                        class="validate-float form-control" :value="$model->phone ?? old('phone')" />
                </div>


                <div class="col-md-4">
                    <x-Inputs.text-field name="fax" label="Fax" placeholder="Enter Fax" :value="$model->fax ?? old('fax')" />
                </div>
            </div>
        </div>
    </div>

    {{-- ADDRESS & LOCATION --}}
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <strong>Address & Location</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <x-Inputs.drop-down id="state_id" name="state_id" label="State" :list="$state_list"
                        :value="$model->state_id" class="form-control select2 cascade" :mandatory="true"
                        data-sr-cascade-target="#city_id" data-sr-cascade-url="/cities-ajax_get_list/{v}" />
                </div>

                <div class="col-md-6 mb-3">
                    <x-Inputs.drop-down id="city_id" name="city_id" label="City" :list="[]"
                        data-value="{{ $model->city_id }}" class="form-control select2" :mandatory="true" />
                </div>

                <div class="col-md-12">
                    <x-Inputs.text-field name="address" label="Address" placeholder="Enter Full Address"
                        :value="$model->address ?? old('address')" />
                </div>
            </div>
        </div>
    </div>

    {{-- TAX & BUSINESS DETAILS --}}
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <strong>Tax & Business Details</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <x-Inputs.text-field name="tin_number" label="TIN No." placeholder="Enter TIN No."
                        class="validate-float form-control" :value="$model->tin_number ?? old('tin_number')" />
                </div>

                <div class="col-md-4">
                    <x-Inputs.text-field name="gstin" label="GSTIN" placeholder="Enter GSTIN"
                        class="form-control" :value="$model->gstin ?? old('gstin')" />
                </div>

                <div class="col-md-4">
                    <x-Inputs.text-field name="url" label="Website" placeholder="https://example.com"
                        :value="$model->url ?? old('url')" />
                </div>
            </div>
        </div>
    </div>

    {{-- PARTY TYPE & STATUS --}}
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <strong>Party Type & Status</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <x-Inputs.checkbox name="is_supplier" label="Supplier" :value="$model->is_supplier ?? false" />
                    <x-Inputs.checkbox name="is_customer" label="Customer" :value="$model->is_customer ?? false" />
                    <x-Inputs.checkbox name="is_active" label="Active" :value="$model->is_active ?? true" />
                </div>
            </div>
        </div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="form-buttons text-end">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function enableSelectize() {
        $('select').selectize({
            sortField: 'text'
        });
    }

    $(document).ready(() => {

        tinymce.init({
            selector: '[name=terms]',
            height: 420,
            branding: false,
            plugins: 'lists link image paste table fullscreen',
            toolbar: `undo redo | bold italic underline | alignleft
                    aligncenter alignright alignjustify | bullist numlist outdent indent
                    | table |link image | fullscreen`,
        });

        $(".cascade").cascade({
            onError: function(title, msg) {
                console.log([title, msg]);
                if (msg) {
                    $.events.onAjaxError(title, msg);
                }
            },
            beforeGet: function(src, url) {
                $.loader.init();
                $.loader.show();
                return url;
            },
            afterGet: function(src, dest, response) {
                $.loader.hide();
                return response;
            },
            afterValueSet: function(src, dest, val) {
                var value = dest.attr("data-value");
                if (value) {
                    var v = dest.attr("data-value").split(",");
                    dest.val(v);

                    if (dest.attr("id") == "purchase_order_ids") {
                        after_purchase_list_fetch();
                    }
                }
            },
        }).trigger("change", {
            "pageLoad": true
        });
    });
</script>
@endpush