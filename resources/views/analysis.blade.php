<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Statistik & Analisis Perbelanjaan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
                <form method="GET" action="{{ route('analysis') }}" class="flex flex-wrap gap-6 items-end">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Bulan</label>
                        <select name="month" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-sm focus:ring-indigo-500 w-40">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Tahun</label>
                        <select name="year" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-sm focus:ring-indigo-500 w-32">
                            @foreach(range(now()->year - 2, now()->year) as $y)
                                <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="ml-auto">
                        <a href="{{ route('analysis') }}" class="text-sm text-gray-400 hover:text-gray-600 font-bold uppercase tracking-wider">Reset</a>
                    </div>
                </form>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 {{ $percentageChange <= 0 ? 'border-green-500' : 'border-red-500' }} hover:shadow-md transition-shadow">
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wider">Prestasi vs Bulan Lepas</div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-gray-900">RM {{ number_format($thisMonthTotal, 2) }}</span>
                        
                        @if($percentageChange <= 0)
                            <span class="inline-flex items-center px-2 py-1 rounded text-sm font-semibold bg-green-100 text-green-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ abs(round($percentageChange, 1)) }}%
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded text-sm font-semibold bg-red-100 text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                {{ round($percentageChange, 1) }}%
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mt-2 font-medium">
                        {{ $difference <= 0 ? '📉 Jimat' : '📈 Lebihan' }} <span class="text-gray-900 font-bold">RM {{ number_format(abs($difference), 2) }}</span> dari bulan lepas
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-yellow-500 hover:shadow-md transition-shadow">
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wider">Kategori Dominan</div>
                    <div class="mt-2">
                        <span class="text-2xl font-extrabold text-gray-900">{{ $categoryData->sortByDesc('total')->first()->name ?? 'N/A' }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Kategori perbelanjaan tertinggi</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-indigo-500 hover:shadow-md transition-shadow">
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wider">Bulan Paling Boros</div>
                    <div class="mt-2">
                        <span class="text-2xl font-extrabold text-gray-900">{{ $monthlyData->sortByDesc('total')->first()->month ?? 'N/A' }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Berdasarkan data 6 bulan terkini</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Pecahan Kategori</h3>
                        <span class="text-xs font-semibold bg-gray-100 px-3 py-1 rounded-full text-gray-500">Live Data</span>
                    </div>
                    <div class="relative">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Aliran Tunai (6 Bulan)</h3>
                        <span class="text-xs font-semibold bg-indigo-50 px-3 py-1 rounded-full text-indigo-600">Monthly Trend</span>
                    </div>
                    <div class="relative">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">Perincian Data Kategori</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase">Kategori</th>
                                <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase text-right">Jumlah (RM)</th>
                                <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase text-right">Peratusan (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($categoryData as $cat)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-8 py-4 flex items-center">
                                    <div class="w-4 h-4 rounded-full shadow-sm mr-4 group-hover:scale-125 transition-transform" style="background-color: {{ $cat->color }}"></div>
                                    <span class="font-semibold text-gray-700">{{ $cat->name }}</span>
                                </td>
                                <td class="px-8 py-4 text-right font-mono font-bold text-gray-900 text-lg">
                                    {{ number_format($cat->total, 2) }}
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <div class="flex items-center justify-end">
                                        <div class="w-24 bg-gray-100 rounded-full h-2 mr-3 overflow-hidden hidden md:block">
                                            <div class="h-full rounded-full" style="background-color: {{ $cat->color }}; width: {{ ($cat->total / $categoryData->sum('total')) * 100 }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-600">
                                            {{ round(($cat->total / $categoryData->sum('total')) * 100, 1) }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($categoryData->map(fn($item) => $item->name)) !!},
                datasets: [{
                    data: {!! json_encode($categoryData->pluck('total')) !!},
                    backgroundColor: {!! json_encode($categoryData->pluck('color')) !!},
                    borderWidth: 0,
                    hoverOffset: 30,
                    borderRadius: 10
                }]
            },
            options: {
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 30, font: { size: 13, weight: '600' } }
                    }
                }
            }
        });

        const monthCtx = document.getElementById('monthlyChart').getContext('2d');
        const gradient = monthCtx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.9)');
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.1)');

        new Chart(monthCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyData->pluck('month')) !!},
                datasets: [{
                    label: 'RM',
                    data: {!! json_encode($monthlyData->pluck('total')) !!},
                    backgroundColor: gradient,
                    borderRadius: 8,
                    barPercentage: 0.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>

    <style>
        canvas { max-height: 320px !important; }
    </style>
</x-app-layout>