@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4 text-center">📦️ Danh sách Packing</h2>
    <form method="GET" action="" class="mb-6 bg-white p-4 rounded shadow">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium">Barcode</label>
                <input type="text" name="barcode" value="{{ request('barcode') }}" class="border rounded px-2 py-1 w-full">
            </div>
            <div>
                <label class="block text-sm font-medium">Model</label>
                <select name="packing_model" class="border rounded px-2 py-1 w-full">
                    <option value="">-- Tất cả --</option>
                    @if(isset($modelOptions))
                        @foreach($modelOptions as $model)
                            <option value="{{ $model }}" {{ request('packing_model') == $model ? 'selected' : '' }}>{{ $model }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Packing Result</label>
                <select name="packing_result" class="border rounded px-2 py-1 w-full">
                    <option value="">-- Tất cả --</option>
                    @if(isset($packingResultOptions))
                        @foreach($packingResultOptions as $result)
                            <option value="{{ $result }}" {{ request('packing_result') == $result ? 'selected' : '' }}>{{ $result }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Line ID</label>
                <select name="line_id" class="border rounded px-2 py-1 w-full">
                    <option value="">-- Tất cả --</option>
                    @if(isset($lineIdOptions))
                        @foreach($lineIdOptions as $id)
                            <option value="{{ $id }}" {{ request('line_id') == $id ? 'selected' : '' }}>{{ $id }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Line Name</label>
                <select name="line_name" class="border rounded px-2 py-1 w-full">
                    <option value="">-- Tất cả --</option>
                    @if(isset($lineNameOptions))
                        @foreach($lineNameOptions as $name)
                            <option value="{{ $name }}" {{ request('line_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Packing Time từ</label>
                <input type="date" name="packing_time_from" value="{{ request('packing_time_from') }}" class="border rounded px-2 py-1 w-full">
            </div>
            <div>
                <label class="block text-sm font-medium">Packing Time đến</label>
                <input type="date" name="packing_time_to" value="{{ request('packing_time_to') }}" class="border rounded px-2 py-1 w-full">
            </div>
        </div>
        <div class="flex flex-wrap gap-2 items-center mt-4 w-full justify-between">
            <div class="flex gap-2 items-center">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-semibold shadow">Lọc</button>
                <a href="{{ route('packing.list') }}" class="text-gray-600 underline font-semibold">Reset</a>
            </div>
            <a
                id="export-excel-btn"
                href="{{ route('packing.export', array_merge(request()->all(), ['columns' => 'packing_id,barcode,packing_model,packing_result,packing_time,packing_updated,line_id,line_name'])) }}"
                class="flex items-center gap-1 bg-[#21a366] text-white px-3 py-1.5 rounded-md shadow font-semibold text-sm border border-[#1d8348] hover:bg-[#1d8348] transition-all"
                style="min-width: 0;"
                target="_blank"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 10.293a1 1 0 00-1.414 0L11 14.586V3a1 1 0 10-2 0v11.586l-4.293-4.293a1 1 0 00-1.414 1.414l6 6a1 1 0 001.414 0l6-6a1 1 0 000-1.414z"/></svg>
                Xuất Excel
            </a>
        </div>
    </form>
    <!-- Chọn cột hiển thị -->
    <!-- Hiển thị các trường cơ bản của bảng check_scan_packings -->
    <div class="mb-4 flex flex-wrap gap-4 items-center bg-gray-50 p-3 rounded shadow border border-gray-200">
        <span class="font-semibold mr-2">Chọn cột hiển thị:</span>
        <label><input type="checkbox" class="toggle-col" data-col="packing_id" checked> Packing ID</label>
        <label><input type="checkbox" class="toggle-col" data-col="barcode" checked> Barcode</label>
        <label><input type="checkbox" class="toggle-col" data-col="packing_model" checked> Packing Model</label>
        <label><input type="checkbox" class="toggle-col" data-col="packing_result" checked> Packing Result</label>
        <label><input type="checkbox" class="toggle-col" data-col="packing_time" checked> Packing Time</label>
        <label><input type="checkbox" class="toggle-col" data-col="packing_updated" checked> Updated At</label>
        <label><input type="checkbox" class="toggle-col" data-col="line_id" checked> Line ID</label>
        <label><input type="checkbox" class="toggle-col" data-col="line_name" checked> Line Name</label>
    </div>
    <script>
    // Ẩn/hiện cột theo checkbox và lưu cấu hình vào localStorage
    function updateColumnVisibility() {
        var checkedCols = Array.from(document.querySelectorAll('.toggle-col:checked')).map(cb => cb.getAttribute('data-col'));
        document.querySelectorAll('th, td').forEach(cell => {
            var col = cell.className.match(/col-([\w_]+)/);
            if (col && col[1]) {
                cell.style.display = checkedCols.includes(col[1]) ? '' : 'none';
            }
        });
        localStorage.setItem('packingListColumns', JSON.stringify(checkedCols));
    }
    document.querySelectorAll('.toggle-col').forEach(cb => {
        cb.addEventListener('change', updateColumnVisibility);
    });
    // Áp dụng cấu hình cột từ localStorage khi load trang
    document.addEventListener('DOMContentLoaded', function() {
        var savedCols = localStorage.getItem('packingListColumns');
        if (savedCols) {
            try {
                var arr = JSON.parse(savedCols);
                document.querySelectorAll('.toggle-col').forEach(function(cb) {
                    cb.checked = arr.includes(cb.getAttribute('data-col'));
                });
            } catch {}
        }
        updateColumnVisibility();
    });
    // Cập nhật link Xuất Excel theo cột đang chọn
    function updateExportLink() {
        var checkedCols = Array.from(document.querySelectorAll('.toggle-col:checked')).map(cb => cb.getAttribute('data-col'));
        var url = new URL(document.getElementById('export-excel-btn').href.split('?')[0], window.location.origin);
        var params = new URLSearchParams(window.location.search);
        checkedCols.length && params.set('columns', checkedCols.join(','));
        url.search = params.toString();
        document.getElementById('export-excel-btn').href = url.toString();
        localStorage.setItem('packingListColumns', JSON.stringify(checkedCols));
    }
    document.querySelectorAll('.toggle-col').forEach(cb => {
        cb.addEventListener('change', function() {
            updateColumnVisibility();
            updateExportLink();
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        updateExportLink();
    });
    </script>
    <div class="overflow-x-auto rounded shadow">
        <table class="table-auto w-full border border-gray-300 rounded-lg overflow-hidden" id="packing-table">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-3 py-2 border col-packing_id">Packing ID</th>
                    <th class="px-3 py-2 border col-barcode">Barcode</th>
                    <th class="px-3 py-2 border col-packing_model">Packing Model</th>
                    <th class="px-3 py-2 border col-packing_result">Packing Result</th>
                    <th class="px-3 py-2 border col-packing_time">Packing Time</th>
                    <th class="px-3 py-2 border col-packing_updated">Updated At</th>
                    <th class="px-3 py-2 border col-line_id">Line ID</th>
                    <th class="px-3 py-2 border col-line_name">Line Name</th>
                </tr>
            </thead>
            <tbody>
                @foreach($packings as $packing)
                <tr class="hover:bg-blue-50 text-center">
                    <td class="px-2 py-1 border col-packing_id">{{ $packing['packing_id'] }}</td>
                    <td class="px-2 py-1 border col-barcode">{{ $packing['barcode'] }}</td>
                    <td class="px-2 py-1 border col-packing_model">{{ $packing['packing_model'] }}</td>
                    <td class="px-2 py-1 border col-packing_result">{{ $packing['packing_result'] }}</td>
                    <td class="px-2 py-1 border col-packing_time">{{ $packing['packing_time'] }}</td>
                    <td class="px-2 py-1 border col-packing_updated">{{ $packing['packing_updated'] }}</td>
                    <td class="px-2 py-1 border col-line_id">{{ $packing['line_id'] }}</td>
                    <td class="px-2 py-1 border col-line_name">{{ $packing['line_name'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4 flex justify-center">
            {{ $packings->withQueryString()->links() }}
        </div>
    </div>
    <!-- ...existing code... -->
</div>
@endsection
