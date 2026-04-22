@extends($layout)

@section('content')
    <?php
    $page_header_links = [['title' => 'Summary', 'url' => route($routePrefix . '.index')]];
    
    ?>

    @include($partial_path . '.page_header')

    <form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
        {!! csrf_field() !!}
        {{ method_field($form['method']) }}
        <div class="d-flex justify-content-center">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">
                                Item Type <span class="text-danger">*</span>
                            </label>

                            <div class="d-flex gap-4 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="product_type" id="spare"
                                        value="0"
                                        {{ old('product_type', $model->product_type ?? 0) == 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="spare">
                                        Spare Part
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="product_type" id="finished"
                                        value="1"
                                        {{ old('product_type', $model->product_type ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="finished">
                                        Finished Product
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="product_type" id="part"
                                        value="2"
                                        {{ old('product_type', $model->product_type ?? 0) == 2 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="part">
                                        Part
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <x-Inputs.text-field id="name" name="name" label="Item Name" :value="$model->name"
                                placeholder="Enter Item Name" :mandatory="true" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <x-Inputs.text-area id="description" name="description" label="Description" :value="$model->description"
                                placeholder="Enter Description" :mandatory="false" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 d-flex align-items-center mt-2">
                        <div class="form-group mb-3">
                            <x-Inputs.checkbox name="is_active" label="Active" :value="$model->is_active" />
                        </div>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </div>
    </form>
@endsection
