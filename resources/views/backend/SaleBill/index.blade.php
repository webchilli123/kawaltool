@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Create", "url" => route($routePrefix . ".create")],
];
?>

{{-- @include($partial_path . ".page_header") --}}

<div class="page-title" style="padding:10px;">
    <div class="row">
        <div class="col-sm-6 col-12">
            <h2>Challan</h2>
        </div>
        <div class="col-sm-6 col-12">
            <?php if (isset($breadcums)): ?>
                <ol class="breadcrumb">
                    <?php foreach ($breadcums as $breadcum): ?>
                        <li class="breadcrumb-item">
                            <?= $breadcum['title']; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>

            <?php if (isset($page_header_links) && is_array($page_header_links)) : ?>
                <div style="text-align:right; padding:5px;">
                    <?php
                    foreach ($page_header_links as $k => $link):
                        if (!is_array($link)) {
                            die("page_header_links variable inner value should be array");
                        }

                        if (!isset($link['title'])) {
                            die("page_header_links -> $k title should be set");
                        }

                        if (!isset($link['url'])) {
                            die("page_header_links -> $k url should be set");
                        }

                        $css_class = isset($link['class']) ? $link['class'] : 'btn btn-secondary btn-sm';
                    ?>
                        <a class="<?= $css_class ?>" href="<?= $link['url'] ?>">
                            <?= $link['title']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="summary_search" action="{{ route($routePrefix . '.index') }}">
            <div class="row mb-2">
                <div class="col-md-3">
                    <x-Inputs.drop-down name="party_id" :value="$search['party_id']" :list="$partyList"                         
                        label="Party"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="challan_type" :value="$search['challan_type']" :list="[
                            0 => 'Non Returnable',
                            1 => 'Returnable',
                            2 => 'From PI',
                        ]"                         
                        label="Challan Type"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field name="voucher_no" :value="$search['voucher_no']"  label="Challan No."/>
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field name="reference_no" :value="$search['reference_no']" label="Reference No."  />
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <x-Inputs.text-field id="from_date" name="from_bill_date" :value="$search['from_bill_date']"
                        label="From Challan Date"
                        class="form-control date-picker"
                        autocomplete="off"
                        data-date-end="input#to_date" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field id="to_date" name="to_bill_date" :value="$search['to_bill_date']"
                        label="To Challan Date"
                        class="form-control date-picker"
                        autocomplete="off"
                        data-date-start="input#from_date" />
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
    @include($viewPrefix . ".index_table")
</div>

@endsection