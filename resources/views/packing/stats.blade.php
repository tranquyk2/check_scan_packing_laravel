@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4 text-center">Biểu đồ Packing theo Line</h2>
    <canvas id="lineStatsChart" height="100"></canvas>

    <div class="my-8">
    <div class="my-8">
        <h3 class="text-lg font-bold mb-2 text-center">Top 5 Model Packing Nhiều Nhất</h3>
        <canvas id="topModelChart" height="120"></canvas>
    </div>
    <div class="my-8">
        <h3 class="text-lg font-bold mb-2 text-center">Biểu đồ số lượng Packing theo Model</h3>
        <canvas id="packingByModelChart" height="80"></canvas>
    </div>
        <h3 class="text-lg font-bold mb-2 text-center">Biểu đồ số lượng Packing theo ngày</h3>
        <canvas id="packingByDateChart" height="80"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

    // Biểu đồ top 5 model được packing nhiều nhất (cột ngang)
    fetch("{{ route('packing.stats.topmodel') }}")
        .then(res => res.json())
        .then(data => {
            const labels = data.map(item => item.packing_model || 'N/A');
            const values = data.map(item => item.total);
            const ctx = document.getElementById('topModelChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Số lượng Packing',
                        data: values,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: false }
                    },
                    scales: {
                        x: { title: { display: true, text: 'Số lượng' }, beginAtZero: true },
                        y: { title: { display: true, text: 'Model' } }
                    }
                }
            });
        });

    // Biểu đồ số lượng packing theo model
    fetch("{{ route('packing.stats.model') }}")
        .then(res => res.json())
        .then(data => {
            const labels = data.map(item => item.packing_model || 'N/A');
            const values = data.map(item => item.total);
            const ctx = document.getElementById('packingByModelChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Số lượng Packing',
                        data: values,
                        backgroundColor: 'rgba(255, 159, 64, 0.7)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: false }
                    },
                    scales: {
                        x: { title: { display: true, text: 'Model' } },
                        y: { title: { display: true, text: 'Số lượng' }, beginAtZero: true }
                    }
                }
            });
        });
    const stats = @json($stats);
    const labels = stats.map(item => item.line_name || 'N/A');
    const totalData = stats.map(item => item.total);
    const okData = stats.map(item => item.ok_count);
    const ngData = stats.map(item => item.ng_count);
    const ctx = document.getElementById('lineStatsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Tổng số',
                    data: totalData,
                    backgroundColor: 'rgba(33,163,102,0.7)',
                },
                {
                    label: 'OK',
                    data: okData,
                    backgroundColor: 'rgba(59,130,246,0.7)',
                },
                {
                    label: 'NG',
                    data: ngData,
                    backgroundColor: 'rgba(239,68,68,0.7)',
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Thống kê Packing theo Line (Tổng/OK/NG)' }
            },
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    // Biểu đồ số lượng packing theo ngày
    fetch("{{ route('packing.stats.daily') }}")
        .then(res => res.json())
        .then(data => {
            const labels = data.map(item => item.date);
            const values = data.map(item => item.total);
            const ctx = document.getElementById('packingByDateChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Số lượng Packing',
                        data: values,
                        backgroundColor: 'rgba(37, 99, 235, 0.7)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: false }
                    },
                    scales: {
                        x: { title: { display: true, text: 'Ngày' } },
                        y: { title: { display: true, text: 'Số lượng' }, beginAtZero: true }
                    }
                }
            });
        });
</script>
@endsection
