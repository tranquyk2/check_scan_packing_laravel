<table>
    <thead>
        <tr>
            @foreach($columns as $col)
                <th>
                    @switch($col)
                        @case('packing_id') Packing ID @break
                        @case('barcode') Barcode @break
                        @case('packing_model') Packing Model @break
                        @case('model_code') Model Code @break
                        @case('model_std') Model Std @break
                        @case('line_name') Line Name @break
                        @case('packing_result') Packing Result @break
                        @case('packing_time') Packing Time @break
                        @case('scan_result') Scan Result @break
                        @case('scan_time') Scan Time @break
                        @case('scan_file') Scan File @break
                        @case('box_count') Box Count @break
                        @case('box_date') Box Date @break
                        @default {{ $col }}
                    @endswitch
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($packings as $packing)
        <tr>
            @foreach($columns as $col)
                <td>{{ isset($packing->$col) ? $packing->$col : '' }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
