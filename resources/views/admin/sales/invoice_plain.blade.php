<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>صيدلية العبادي</title>
    <style>
        /* Small thermal-friendly layout (approx 57mm width) */
        html, body {
            margin:0;
            padding:0;
            font-family: monospace, Arial, Helvetica, sans-serif;
            color:#000;
        }
        .invoice-wrapper {
            width: 57mm; /* target thermal width */
            padding:8px;
            box-sizing: border-box;
        }
        .center { text-align:center; }
        table { width:100%; border-collapse: collapse; font-size:12px; }
        th, td { padding:4px 0; }
        .right { text-align:right; }
        .small { font-size:11px; }
        hr { border: none; border-top: 1px dashed #000; margin:8px 0; }
        @media print {
            @page { size: 57mm auto; margin: 0; }
            body { margin:0; }
        }
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        <div class="center">
            <h3 style="margin:0;">صيدلية العبادي</h3>
            <div class="small">{{ settings('app_address','') }}</div>
            <div class="small">{{ date('d M Y H:i') }}</div>
        </div>
        <hr>
        <table>
            <thead>
                <tr>
                    <th style="text-align:left">الدواء</th>
                    <th style="width:50px; text-align:center">العدد</th>
                    <th style="width:60px; text-align:center">النوع</th>
                    <th style="width:60px; text-align:right">المجموع</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ optional($sale->product->purchase)->product ?? 'N/A' }}</td>
                    <td class="center">{{ $sale->quantity }}</td>
                    <td class="center">
                        @if($sale->unit_type === 'sheet')
                            شريط
                        @else
                            باكيت
                        @endif
                    </td>
                    <td class="right">{{ settings('app_currency','$') }} {{ number_format($sale->total_price,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        <div style="display:flex; justify-content:space-between; font-weight:bold;">
            <div>المجموع</div>
            <div>{{ settings('app_currency','$') }} {{ number_format($total,2) }}</div>
        </div>
        <div class="center small" style="margin-top:8px;">شكرا لكم</div>
        <div class="center small" style="margin-top:8px;">شكرا لكم</div>
    </div>

     <script>
        // auto-print when opened in a new window/tab, then close the window after print.
        window.onload = function(){
            try { window.print(); } catch(e){}

            function closeWin(){
                try { window.close(); } catch(e) { /* ignore */ }
            }

            // Modern browsers: onafterprint
            if (typeof window.onafterprint !== 'undefined') {
                window.onafterprint = closeWin;
            } else if (window.matchMedia) {
                // Some browsers support matchMedia for print events
                var mql = window.matchMedia('print');
                if (mql && typeof mql.addListener === 'function') {
                    mql.addListener(function(m) {
                        if (!m.matches) {
                            // printing finished
                            closeWin();
                        }
                    });
                }
            } else {
                // Fallback: close after a short timeout (3s)
                setTimeout(closeWin, 3000);
            }
        };
    </script>
</body>
</html>
