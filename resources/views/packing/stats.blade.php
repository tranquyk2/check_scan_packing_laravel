@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4 text-center">Thống kê Packing theo Model (Tổng/OK/NG)</h2>
    <canvas id="modelStatsChart" height="100"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Biểu đồ thống kê Packing theo Model (Tổng/OK/NG) - chỉ top 10
    const stats = {!! json_encode($stats) !!};
    const top10Stats = stats.slice(0, 10);
    const modelLabels = top10Stats.map(item => item.packing_model || 'N/A');
    const totalData = top10Stats.map(item => item.total);
    const okData = top10Stats.map(item => item.ok_count);
    const ngData = top10Stats.map(item => item.ng_count);
    const modelStatsCtx = document.getElementById('modelStatsChart').getContext('2d');
    new Chart(modelStatsCtx, {
        type: 'bar',
        data: {
            labels: modelLabels,
            datasets: [
                { label: 'Tổng số', data: totalData, backgroundColor: 'rgba(33,163,102,0.7)' },
                { label: 'OK', data: okData, backgroundColor: 'rgba(59,130,246,0.7)' },
                { label: 'NG', data: ngData, backgroundColor: 'rgba(239,68,68,0.7)' }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Thống kê Packing theo Model (Tổng/OK/NG)' }
            },
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    // Biểu đồ số lượng packing theo ngày (chỉ lấy 30 ngày gần nhất)
    fetch("{{ route('packing.stats.daily') }}")
        .then(res => res.json())
        .then(data => {
            const last30 = data.slice(-30);
            const labels = last30.map(item => item.date);
            const values = last30.map(item => item.total);
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
