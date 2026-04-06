<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - OJT ChronoLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 min-h-screen">

    @include('partials.header')

    @php
        use App\Models\MonthlyTotal;
        use App\Models\DtrSetting;
        use App\Models\DailyTimeRecord;
        use App\Models\DtrLog;

        // Dynamically build months from the user's first rendered-hours month
        // to the current month. This uses DailyTimeRecord (by user_id) and
        // DtrLog (by intern_profiles -> intern_id) and picks the earliest
        // date where total hours > 0. If none found, start at current month.
        $authUser = auth()->user();
        $userId = $authUser?->id ?? null;
        $totalsByMonth = [];
        $totalRendered = 0;

        $breakdown = [];
        // try to find an intern_profiles row that matches the current user
        $internProfile = null;
        if ($authUser) {
            $internProfile = \Illuminate\Support\Facades\DB::table('intern_profiles')
                ->where('name', $authUser->name)
                ->orWhere('name', $authUser->email)
                ->first();
        }
        $internId = $internProfile?->id ?? null;

        // find earliest rendered date from DailyTimeRecord or DtrLog
        $earliestDaily = null;
        if ($userId) {
            $earliestDaily = DailyTimeRecord::where('user_id', $userId)
                ->where('total_hours', '>', 0)
                ->orderBy('log_date', 'asc')
                ->value('log_date');
        }

        $earliestDtr = null;
        if ($internId) {
            $earliestDtr = DtrLog::where('intern_id', $internId)
                ->where('daily_total_hours', '>', 0)
                ->orderBy('log_date', 'asc')
                ->value('log_date');
        }

        $firstDate = null;
        if ($earliestDaily && $earliestDtr) {
            $firstDate = ($earliestDaily < $earliestDtr) ? $earliestDaily : $earliestDtr;
        } elseif ($earliestDaily) {
            $firstDate = $earliestDaily;
        } elseif ($earliestDtr) {
            $firstDate = $earliestDtr;
        }

        if (!$firstDate) {
            // no rendered hours yet — start at current month
            $start = \Carbon\Carbon::now()->startOfMonth();
        } else {
            $start = \Carbon\Carbon::parse($firstDate)->startOfMonth();
        }

        $end = \Carbon\Carbon::now()->startOfMonth();
        $months = [];
        $iter = $start->copy();
        while ($iter->lte($end)) {
            $months[$iter->format('Y-m')] = $iter->format('M');
            $iter->addMonth();
        }
        foreach ($months as $num => $label) {
            // $num is now a 'YYYY-MM' key
            list($y, $m) = explode('-', $num);

            // Sum from daily_time_records (if present)
            $sum1 = DailyTimeRecord::where('user_id', $userId)
                ->whereYear('log_date', $y)
                ->whereMonth('log_date', (int) $m)
                ->sum('total_hours');

            // Monthly totals (if present)
            $ym = $y . '-' . $m;
            $sum3 = MonthlyTotal::where('user_id', $userId)
                ->where('year_month', $ym)
                ->sum('total_hours');

            // DTR history (DtrLog) — prefer this if present so dashboard reflects
            // seeded DtrHistory data. We match by log_date month/year.
            $sumDtr = 0;
            // Only use DtrLog if we have an intern_profiles match for the
            // currently authenticated user; otherwise ignore DtrLog rows to
            // avoid showing other users' history.
            if ($internId) {
                $sumDtr = DtrLog::whereYear('log_date', $y)
                    ->whereMonth('log_date', (int) $m)
                    ->where('intern_id', $internId)
                    ->sum('daily_total_hours');
            }

            // If DtrLog has values, use it as authoritative to avoid double-counting
            if ((float)$sumDtr > 0) {
                $totalForMonth = (float) $sumDtr;
            } else {
                $totalForMonth = (float) $sum1 + (float) $sum3;
            }

            $totalsByMonth[$label] = $totalForMonth;
            $breakdown[$label] = [
                'daily_time_records' => (float) $sum1,
                'monthly_totals' => (float) $sum3,
                'dtr_logs' => (float) $sumDtr,
                'total' => $totalForMonth,
            ];
            $totalRendered += $totalForMonth;
        }

        // Current month total (if within our months range)
        $thisMonthLabel = date('M');
        $thisMonthTotal = $totalsByMonth[$thisMonthLabel] ?? 0;

        // Use the logged-in user's DTR settings when available.
        $setting = $userId ? DtrSetting::where('user_id', $userId)->first() : null;
        $targetHours = $setting ? (float) $setting->total_hours : 160; // fallback
        $remaining = $targetHours - $totalRendered;
        if ($remaining < 0) { $remaining = 0; }
    @endphp

    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Dashboard</h1>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 md:grid-rows-2 gap-4 min-h-[calc(80vh-64px)]">
            <!-- Left top -->
            <div name="div1" class="bg-white py-2 px-6 rounded shadow md:row-span-2 h-full flex flex-col">
                <div class="flex-1">
                    <div class="text-lg font-semibold text-slate-800 mt-2">Monthly Hours Rendered</div>

                    <div class="mt-4 h-90">
                        <canvas id="monthlyBar" aria-label="Monthly hours bar chart" 
                            data-labels='@json(array_keys($totalsByMonth))' 
                            data-values='@json(array_values($totalsByMonth))' class="w-full h-full"></canvas>
                    </div>

                </div>
            </div>

           

            <!-- Right column (spans both rows on md+) -->
            <div name="div2" class="bg-white py-2 px-6 rounded shadow md:row-span-2 h-full flex flex-col">
                <div class="flex-1">
                    <div class="text-lg font-semibold text-slate-800 mt-2">Hours Rendered</div>

                    <div class="mt-12 md:flex md:items-center md:gap-6">
                        <div class="md:flex-1">
                            <canvas id="hoursPie" aria-label="Hours pie chart" data-rendered="{{ $totalRendered }}" data-remaining="{{ $remaining }}"></canvas>
                        </div>

                        <div class="mt-4 md:mt-0 md:w-48">
                            <div class="font-semibold text-slate-700">Legend</div>
                            <div class="mt-2">
                                <div class="flex items-center justify-between py-2">
                                    <div class="flex items-center gap-3">
                                        <span class="w-3 h-3 bg-blue-500 inline-block rounded"></span>
                                        <div class="text-sm text-slate-600">Rendered</div>
                                    </div>
                                    <div class="text-sm font-medium text-slate-800">{{ number_format($totalRendered, 2) }}</div>
                                </div>

                                <div class="flex items-center justify-between py-2">
                                    <div class="flex items-center gap-3">
                                        <span class="w-3 h-3 bg-amber-400 inline-block rounded"></span>
                                        <div class="text-sm text-slate-600">Remaining</div>
                                    </div>
                                    <div class="text-sm font-medium text-slate-800">{{ number_format($remaining, 2) }}</div>
                                </div>

                                <div class="border-t border-slate-200 mt-3 pt-3 text-sm text-slate-600">
                                    Target: <span class="font-medium text-slate-800">{{ number_format($targetHours, 2) }}</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/dashboard.js') }}" defer></script>
</body>
</html>
