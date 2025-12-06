@extends('admin.layouts.app')

@section('content')
<div id="invoice" style="width:350px; font-family:monospace;">
    <div style="text-align:center;">
        <h3 style="margin:0;">mohammed</h3>
        <p style="margin:0; font-size:12px;">{{ date('d M Y H:i') }}</p>
        <hr>
    </div>
    <table style="width:100%; font-size:12px;">
        <thead>
            <tr>
                <th style="text-align:left">Item</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->product->purchase->product ?? 'N/A' }}</td>
                <td style="text-align:center">{{ $sale->quantity }}</td>
                <td style="text-align:right">{{ settings('app_currency','$') }} {{ number_format($sale->total_price,2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <div style="text-align:right; font-size:14px;">
        <strong>Total: {{ settings('app_currency','$') }} {{ number_format($total,2) }}</strong>
    </div>
    <div style="text-align:center; margin-top:10px; font-size:11px;">
        <p>Thank you for your purchase!</p>
    </div>
</div>

<script>
    // auto-print when this view is returned in a new window/tab, then close the window after print.
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
@endsection
