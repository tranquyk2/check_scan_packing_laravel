<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PackingExport implements FromView
{
    protected $packings;
    protected $columns;

    public function __construct($packings, $columns)
    {
        $this->packings = $packings;
        $this->columns = $columns;
    }

    public function view(): View
    {
        return view('packing.export', [
            'packings' => $this->packings,
            'columns' => $this->columns
        ]);
    }
}
