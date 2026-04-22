<!DOCTYPE html>
<html>
<head>
    <title>Print Barcode Sticker</title>

    <style>
        /* 🔥 EXACT LABEL SIZE */
        @page {
            size: 70mm 35mm; /* width height */
            margin: 0;
        }

        body {
            margin: 0;
            font-family: Arial;
        }

        .label {
            width: 70mm;
            height: 35mm;

            display: flex;
            flex-direction: column;
        }

        /* BARCODE IMAGE */
        .label img {
            width: 100%;
            height: 26mm;
            object-fit: contain;
        }

        /* TEXT (NO GAP) */
        .barcode-text,
        .batch-text {
            margin: 0;
            padding: 0;
            line-height: 1;
            text-align: center;
        }

        .barcode-text {
            font-size: 15px;
            font-weight: bold;
        }

        .batch-text {
            font-size: 13px;
        }
    </style>
</head>

<body onload="window.print()">

<div class="label">
    <img src="{{ asset('storage/' . $product->barcode_image) }}">
    <p class="barcode-text">{{ $product->barcode }}</p>
    <p class="batch-text">{{ $product->batch }}</p>
</div>

</body>
</html>