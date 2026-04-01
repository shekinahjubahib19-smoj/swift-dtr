<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR Record - OJT ChronoLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="{{ asset('js/clock.js') }}" defer></script>
</head>
<body class="bg-slate-50 min-h-screen">

    @include('partials.header')

    @php
    $records = auth()->user()->dailyRecords()->latest()->take(10)->get();
    // Add this line to get the user's internship profile
    $settings = auth()->user()->dtrSetting; 
    @endphp

    <div class="container mx-auto py-10 px-4">
        
        <div class="flex flex-row gap-4 mb-4 -mt-6">
    
            <div class="flex-1 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
                <h2 class="text-1xl text-center font-black text-slate-800 tracking-tight whitespace-nowrap">Daily Time Record</h2>
                <p class="text-slate-500 text-[15px] flex items-center justify-center gap-2 mt-2 whitespace-nowrap">
                    <i class="far fa-calendar-alt text-blue-500"></i>
                    Today is {{ date('F d, Y') }} 
                    <span class="text-slate-300 mx-2">|</span>
                    <span id="liveClock" class="font-mono font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-lg">00:00:00 AM</span>
                </p>
            </div>

            <div class="flex-1 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 h-full">
                    
                    <div class="flex flex-col justify-between gap-2">
                        <div class="flex items-center border-b border-slate-50 pb-1">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 w-24">Full Name</span>
                            <span class="text-sm font-bold uppercase text-slate-700 ml-5">
                                {{ auth()->user()->name ?? ($settings->full_name ?? 'No Name Set') }}
                            </span>
                        </div>

                        <div class="flex items-center border-b border-slate-50 pb-1">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 w-24">Division</span>
                            <span class="text-sm font-bold uppercase text-slate-600 ml-5">
                                {{ $settings->department ?? 'IT Division (ML)' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between gap-2">
                        <div class="flex items-center border-b border-slate-50 pb-1">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 w-24">Position</span>
                            <span class="text-sm font-bold uppercase text-slate-600 ml-5">
                                {{ $settings->position ?? 'Intern' }}
                            </span>
                        </div>

                        <div class="flex items-center">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 w-24">Target Hours</span>
                            <div class="flex items-center gap-2 ml-5">
                                <span class="text-sm font-bold uppercase text-slate-600">
                                    {{ $settings->total_hours ?? '720' }} hrs
                                </span>
                                <i class="fas fa-bullseye text-xs text-blue-300"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="flex">
                <div class="flex justify-center items-center mt-2">
                    <div class="flex flex-col gap-2 mb-0">
                        <button class="flex items-center gap-2 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-xl border border-emerald-100 hover:bg-emerald-100 transition-colors text-xs font-bold">
                            <i class="fas fa-file-excel"></i>
                            Excel
                        </button>
                        
                        <button class="flex items-center gap-2 bg-slate-100 text-slate-700 px-3 py-1.5 rounded-xl border border-slate-100 hover:bg-slate-200 transition-colors text-xs font-bold">
                            <i class="fas fa-print"></i>
                            Print
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-800 text-white">
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-wider">Log Date</th>
                        <th class="px-4 py-2 text-center text-xs font-bold uppercase tracking-wider border-x border-slate-700">AM In</th>
                        <th class="px-4 py-2 text-center text-xs font-bold uppercase tracking-wider border-r border-slate-700">AM Out</th>
                        <th class="px-4 py-2 text-center text-xs font-bold uppercase tracking-wider border-r border-slate-700">PM In</th>
                        <th class="px-4 py-2 text-center text-xs font-bold uppercase tracking-wider">PM Out</th>
                        <th class="px-4 py-2 text-center text-xs font-bold uppercase tracking-wider">Total Hours</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $records = auth()->user()->dailyRecords()->latest()->take(10)->get();
                    @endphp

                    @foreach($records as $log)
                    <tr class="hover:bg-blue-50/50 transition-all group">
                        <td class="px-4 py-1 text-slate-700 font-semibold group-hover:text-blue-600">
                            {{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-1 text-center border-x border-slate-50 font-mono text-slate-600">
                            {{ $log->am_in ? date('h:i A', strtotime($log->am_in)) : '--:--' }}
                        </td>
                        <td class="px-4 py-1 text-center border-r border-slate-50 font-mono text-slate-600">
                            {{ $log->am_out ? date('h:i A', strtotime($log->am_out)) : '--:--' }}
                        </td>
                        <td class="px-4 py-1 text-center border-r border-slate-50 font-mono text-slate-600">
                            {{ $log->pm_in ? date('h:i A', strtotime($log->pm_in)) : '--:--' }}
                        </td>
                        <td class="px-4 py-1 text-center border-r border-slate-50 font-mono text-slate-600">
                            {{ $log->pm_out ? date('h:i A', strtotime($log->pm_out)) : '--:--' }}
                        </td>
                        <td class="px-4 py-1 text-center font-mono text-slate-600">
                            {{ $log->total_hours ?? '--:--' }}
                        </td>
                    </tr>
                    @endforeach

                    {{-- Fill remaining rows up to 10 for consistency --}}
                    @for ($i = count($records); $i < 10; $i++)
                    <tr class="opacity-40">
                        <td class="px-4 py-1 text-slate-300 text-sm italic">Pending Log...</td>
                        <td class="px-4 py-1 text-center border-x border-slate-50 text-slate-200 font-mono">--:--</td>
                        <td class="px-4 py-1 text-center border-r border-slate-50 text-slate-200 font-mono">--:--</td>
                        <td class="px-4 py-1 text-center border-r border-slate-50 text-slate-200 font-mono">--:--</td>
                        <td class="px-4 py-1 text-center text-slate-200 font-mono">--:--</td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>