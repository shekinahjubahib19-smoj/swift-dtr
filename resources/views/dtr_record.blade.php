<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DTR Record - OJT ChronoLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="{{ asset('js/clock.js') }}" defer></script>
    <script src="{{ asset('js/dtr-export.js') }}" defer></script>
    <script src="{{ asset('js/dtr-save-month.js') }}" defer></script>
</head>
<body class="bg-slate-50 min-h-screen">

    @include('partials.header')

    @php
    $records = auth()->user()->dailyRecords()->latest()->take(10)->get();
    $settings = auth()->user()->dtrSetting; 
    @endphp

    <div class="container mx-auto py-10 px-4">
        
        <div class="flex flex-row gap-4 mb-4 -mt-6">
    
            <div class="w-70 flex-none bg-white px-20 py-4 rounded-2xl shadow-sm border border-slate-200">
                <h2 class="text-1xl text-center font-black text-slate-800 tracking-tight whitespace-nowrap">Daily Time Record</h2>
                <p class="text-slate-500 text-[15px] flex items-center justify-center gap-2 mt-2 whitespace-nowrap">
                    <i class="far fa-calendar-alt text-blue-500"></i>
                    Today is {{ date('F d, Y') }} 
                </p>
            </div>

            <div class="flex-1 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 h-full">
                    
                    <div class="flex flex-col justify-between gap-2">
                        <div class="flex items-center border-b border-slate-50 pb-1">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 w-24">Full Name</span>
                            <span class="text-sm font-bold text-left uppercase text-slate-700 ml-0">
                                {{ auth()->user()->name ?? ($settings->full_name ?? 'No Name Set') }}
                            </span>
                        </div>

                        <div class="flex items-center border-b border-slate-50 pb-1">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 w-24">Division</span>
                            <span class="text-sm font-bold text-left uppercase text-slate-600 ml-0">
                                {{ $settings->department ?? 'IT Division (ML)' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between gap-2">
                        <div class="flex items-center border-b border-slate-50 pb-1">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 w-24">Position</span>
                            <span class="text-sm font-bold text-left uppercase text-slate-600 ml-0">
                                {{ $settings->position ?? 'Intern' }}
                            </span>
                        </div>

                        <div class="flex items-center border-b border-slate-50 pb-1">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 w-24">Target Hours</span>
                            <div class="flex items-center gap-2 ml-0">
                                <span class="text-sm font-bold text-left uppercase text-slate-600">
                                    {{ $settings->total_hours ?? '720' }} hrs
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="flex">
                <div class="flex justify-center items-center mt-2">
                    <div class="flex flex-col gap-2 mb-0">
                        <button id="exportExcelBtn" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-xl border border-emerald-100 hover:bg-emerald-100 transition-colors text-xs font-bold">
                            <i class="fas fa-file-excel"></i>
                            Excel
                        </button>
                        
                        <button id="saveMonthBtn" class="flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-xl border border-blue-100 hover:bg-blue-100 transition-colors text-xs font-bold">
                            <i class="fas fa-save"></i>
                            Save Month Total
                        </button>
                        
                        <button id="printBtn" class="flex items-center gap-2 bg-slate-100 text-slate-700 px-3 py-1.5 rounded-xl border border-slate-100 hover:bg-slate-200 transition-colors text-xs font-bold">
                            <i class="fas fa-print"></i>
                            Print
                        </button>
                    </div>
                </div>
            </div>

        </div>

        @php
            // Ensure today's record exists
            $today = date('Y-m-d');
            auth()->user()->dailyRecords()->firstOrCreate(['log_date' => $today]);

            // Determine schedule end based on starting_date and target hours
            $hoursPerDay = 8; // default
            $targetHours = isset($settings->total_hours) ? (int)$settings->total_hours : 720;

            if (isset($settings->starting_date) && $settings->starting_date) {
                $cursor = \Carbon\Carbon::parse($settings->starting_date)->startOfDay()->copy();
            } else {
                $cursor = \Carbon\Carbon::now()->startOfMonth()->startOfDay();
            }

            // Estimate using calendar days by default (count weekends). To skip weekends set a flag elsewhere.
            $totalDays = (int) ceil($targetHours / max(1, $hoursPerDay));
            $daysAdded = 0;
            while ($daysAdded < $totalDays) {
                // calendar days count — if you want to skip weekends, add a condition here
                $daysAdded++;
                $cursor->addDay();
            }

            // cursor now points to the day after last scheduled day
            $lastWorking = $cursor->copy()->subDay();
            // Add 30-day extension and one extra month for safety as requested
            $endDate = $lastWorking->copy();
            $endWithExtension = $endDate->copy()->addDays(30)->addMonth();

            // Build month range from starting month to endWithExtension month
            $startMonth = isset($settings->starting_date) && $settings->starting_date ? \Carbon\Carbon::parse($settings->starting_date)->startOfMonth() : \Carbon\Carbon::now()->startOfMonth();
            $endMonth = $endWithExtension->copy()->startOfMonth();

            $months = [];
            $iter = $startMonth->copy();
            while ($iter->lte($endMonth)) {
                $months[] = $iter->copy();
                $iter->addMonth();
            }
        @endphp

        @foreach($months as $monthStart)
            @php
                $monthLabel = $monthStart->format('F Y');
                $monthBegin = $monthStart->copy();
                $monthEnd = $monthStart->copy()->endOfMonth();
                $periodStart = new DateTime($monthBegin->toDateString());
                $periodEnd = new DateTime($monthEnd->toDateString());
                $periodEnd->modify('+1 day');
                $period = new DatePeriod($periodStart, new DateInterval('P1D'), $periodEnd);
            @endphp

            <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden mb-6">
                <div class="p-4 border-b flex items-center justify-between">
                    <div class="font-bold">{{ $monthLabel }}</div>
                    <div class="text-sm text-slate-500">Month Total planned: {{ number_format($targetHours,0) }} hrs</div>
                </div>

                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-800 text-white">
                            <th class="px-2 py-1 text-left text-xs font-bold uppercase tracking-wider w-36 max-w-full whitespace-nowrap">Log Date</th>
                            <th class="px-4 py-1 text-center text-xs font-bold uppercase tracking-wider border-x border-slate-700">AM In</th>
                            <th class="px-4 py-1 text-center text-xs font-bold uppercase tracking-wider border-r border-slate-700">AM Out</th>
                            <th class="px-4 py-1 text-center text-xs font-bold uppercase tracking-wider border-r border-slate-700">PM In</th>
                            <th class="px-4 py-1 text-center text-xs font-bold uppercase tracking-wider border-r border-slate-700">PM Out</th>
                            <th class="px-4 py-1 text-center text-xs font-bold uppercase tracking-wider">Total Hours</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php $monthTotal = 0.0; @endphp
                        @foreach($period as $dt)
                            @php
                                $d = $dt->format('Y-m-d');
                                $record = auth()->user()->dailyRecords()->where('log_date', $d)->first();
                                $isToday = ($d === $today);
                                $isFuture = ($d > $today);
                            @endphp

                            <tr class="{{ $isToday ? 'bg-blue-50/30 hover:bg-blue-50' : 'hover:bg-slate-50' }} transition-all group">
                                <td class="px-2 py-1 {{ $isToday ? 'text-blue-700 font-mono' : 'text-slate-600 font-mono' }} whitespace-nowrap w-36">
                                    {{ \Carbon\Carbon::parse($d)->format('M d, Y') }}
                                    @if($isToday)
                                        <span class="ml-2 text-[9px] bg-blue-100 px-2 py-0.5 rounded-full uppercase">Today</span>
                                    @endif
                                </td>

                                <td class="px-4 py-1 text-center border-x border-slate-50 font-mono {{ $isToday ? 'text-slate-700 font-bold' : 'text-slate-500' }}">
                                    @if($isFuture)
                                        --:--
                                    @else
                                        {{ $record && $record->am_in ? date('h:i A', strtotime($record->am_in)) : '--:--' }}
                                    @endif
                                </td>

                                <td class="px-4 py-1 text-center border-r border-slate-50 font-mono text-slate-700">
                                   @if($isFuture)
                                       --:--
                                   @else
                                       {{ $record && !empty($record->am_out) && !in_array($record->am_out, ['00:00:00']) ? date('h:i A', strtotime($record->am_out)) : '--:--' }}
                                   @endif
                                </td>

                                <td class="px-4 py-1 text-center border-r border-slate-50 font-mono {{ $isToday ? 'text-slate-700' : 'text-slate-500' }}">
                                    @if($isFuture)
                                        --:--
                                    @else
                                        {{ $record && $record->pm_in ? date('h:i A', strtotime($record->pm_in)) : '--:--' }}
                                    @endif
                                </td>

                                <td class="px-4 py-1 text-center border-r border-slate-50 font-mono {{ $isToday ? 'text-slate-700' : 'text-slate-500' }}">
                                    @if($isFuture)
                                        --:--
                                    @else
                                        {{ $record && $record->pm_out ? date('h:i A', strtotime($record->pm_out)) : '--:--' }}
                                    @endif
                                </td>

                                @php
                                    $rowHours = 0.0;
                                    if (!$isFuture && $record) {
                                        if (!empty($record->am_in) && !empty($record->am_out) && !in_array($record->am_out, ['00:00:00'])) {
                                            $amIn = strtotime($record->am_in);
                                            $amOut = strtotime($record->am_out);
                                            if ($amOut > $amIn) {
                                                $rowHours += ($amOut - $amIn) / 3600;
                                            }
                                        }
                                        if (!empty($record->pm_in) && !empty($record->pm_out) && !in_array($record->pm_out, ['00:00:00'])) {
                                            $pmIn = strtotime($record->pm_in);
                                            $pmOut = strtotime($record->pm_out);
                                            if ($pmOut > $pmIn) {
                                                $rowHours += ($pmOut - $pmIn) / 3600;
                                            }
                                        }
                                        if ($rowHours <= 0 && isset($record->total_hours)) {
                                            $rowHours = max((float)$record->total_hours, 0);
                                        }
                                    }
                                    // accumulate month total only for past/today dates
                                    if (!$isFuture) { $monthTotal += $rowHours; }
                                @endphp

                                <td class="px-4 py-1 text-center font-mono text-blue-600 font-bold">
                                    @if($isFuture)
                                        --:--
                                    @else
                                        {{ number_format(max($rowHours, 0), 2) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tfoot>
                            <tr class="bg-slate-50">
                                <td class="px-4 py-1 font-semibold">Month Total</td>
                                <td colspan="4"></td>
                                <td class="px-4 py-1 text-center font-mono text-blue-600 font-bold">{{ number_format($monthTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

</body>
</html>