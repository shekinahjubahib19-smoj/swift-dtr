<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
       @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .blink { 
            animation: pulse 1s infinite; 
        }
    </style>
</head>
<body class="bg-slate-50">

    @include('partials.header')

    @php
        // 1. Capture the exact current time from the server
        $now = \Carbon\Carbon::now();
        $user = auth()->user();
        
        // 2. Fetch User Settings & Today's Record
        $settings = $user->dtrSetting;
        $todayRecord = $user->dailyRecords()->where('log_date', $now->toDateString())->first();

        // 3. Setup Thresholds
        // If settings aren't configured, we use defaults (8AM and 1PM)
        $officialAmIn = $settings && $settings->am_in ? \Carbon\Carbon::parse($settings->am_in) : \Carbon\Carbon::parse('08:00:00');
        $pmInThreshold = $settings && $settings->pm_in ? \Carbon\Carbon::parse($settings->pm_in) : \Carbon\Carbon::parse('13:00:00');

        $isCompleted = ($todayRecord && $todayRecord->pm_out);
    @endphp

    <div class="mt-1 p-2">
        <div class="flex items-center justify-center min-h-[80vh] w-full px-4">
            <div class="w-full max-w-3xl bg-white p-10 rounded-2xl shadow-lg text-center">
            <h2 class="text-2xl font-bold mb-2">Daily Time Record</h2>
            
           <div class="my-4">
                <span id="liveClock" class="font-mono font-bold text-blue-600 bg-blue-50 px-6 py-3 rounded-xl text-3xl border border-blue-100 shadow-sm inline-flex items-center gap-1">
                    
                    <span id="time-hours-mins">
                        {{ $now->format('h') }}<span class="blink">:</span>{{ $now->format('i') }}
                    </span>

                    <span id="time-seconds">
                        <span class="blink">:</span>{{ $now->format('s') }}
                    </span>

                    <span id="time-ampm">{{ $now->format('A') }}</span>
                </span>
            </div>

            <h2 class="text-2xl font-bold">Welcome, {{ $user->name }}!</h2>
  
                    
                    <div class="my-6">
                        <p class="text-gray-600">
                            Current Status: 
                            @if(!$todayRecord)
                                <span class="text-red-500 font-bold">Not Timed In Today</span>
                            @elseif($todayRecord->pm_out)
                                <span class="text-blue-600 font-bold">Shift Completed</span>
                            @else
                                <span class="text-green-500 font-bold">Active Session</span>
                            @endif
                        </p>
                    </div>

                    <form action="{{ route('dtr.clock') }}" method="POST">
                        @csrf
                        <button type="submit" 
                            class="px-10 py-4 rounded-full font-bold transition duration-300 shadow-lg uppercase bg-green-500 hover:bg-green-600 text-white active:scale-95">
                            {{-- This text will still reflect the server's logic on load --}}
                            @if($now->lt($pmInThreshold))
                                <i class="fas fa-sun mr-2"></i> Morning Action
                            @else
                                <i class="fas fa-moon mr-2"></i> Afternoon Action
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>

<script src="{{ asset('js/clock.js') }}"></script>
</body>
</html>