<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - OJT ChronoLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
       @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
       .blink { animation: pulse 1s infinite; }
    </style>
</head>
<body class="bg-slate-50">

    @include('partials.header')

    @php
        $now = \Carbon\Carbon::now();
        $user = auth()->user();
        $settings = $user->dtrSetting;
        $todayRecord = $user->dailyRecords()->where('log_date', $now->toDateString())->first();
        
        // Status Check Logic
        $hasStarted = ($todayRecord && ($todayRecord->am_in || $todayRecord->pm_in));
        // Check if the shift is fully completed for the day
        $isFinished = ($todayRecord && $todayRecord->pm_out);
    @endphp

    <div class="mt-1 p-2">
        <div class="flex items-center justify-center min-h-[80vh] w-full px-4">
            <div class="w-full max-w-3xl bg-white p-10 rounded-2xl shadow-lg text-center border border-slate-200">
                <h2 class="text-2xl font-bold mb-2 text-slate-800">Daily Time Record</h2>
                
                <div class="my-4">
                    <span id="liveClock" class="font-mono font-bold text-blue-600 bg-blue-50 px-6 py-3 rounded-xl text-3xl border border-blue-100 shadow-sm inline-flex items-center gap-1">
                        {{ $now->format('h:i:s A') }}
                    </span>
                </div>

                <h2 class="text-2xl font-bold uppercase text-slate-700">Welcome, {{ $user->name }}!</h2>
                
                <div class="my-6">
                    <p class="text-slate-600">
                        Current Status: 
                        @if(!$hasStarted)
                            <span class="text-red-500 font-bold">Not Timed In Today</span>
                        @elseif($isFinished)
                            <span class="text-blue-600 font-bold">Shift Completed</span>
                        @else
                            <span class="text-green-500 font-bold">Active Session</span>
                        @endif
                    </p>
                </div>

                @if(!$isFinished)
                <form action="{{ route('dtr.clock') }}" method="POST">
                    @csrf
                    @php
                        if ($todayRecord && $todayRecord->pm_in) {
                            $action = 'pm_out';
                        } elseif ($todayRecord && $todayRecord->am_in && !$todayRecord->am_out && $now->hour < 12) {
                            $action = 'am_out';
                        } elseif ($now->hour >= 12 || ($todayRecord && $todayRecord->am_out)) {
                            $action = 'pm_in';
                        } else {
                            $action = 'am_in';
                        }
                    @endphp
                    <input type="hidden" name="action" value="{{ $action }}">
                    <button type="submit" 
                        class="px-10 py-4 rounded-full font-bold transition duration-300 shadow-lg uppercase bg-green-500 hover:bg-green-600 text-white active:scale-95">
                        
                        {{-- Logic updated to match Step 3 Controller rules --}}
                        
                        {{-- 1. Check if PM In is already recorded (User is currently in their afternoon shift) --}}
                        @if($todayRecord && $todayRecord->pm_in)
                            <i class="fas fa-door-open mr-2"></i> Afternoon Out

                        {{-- 2. Check if Morning shift is active and it is still before 12:00 PM --}}
                        @elseif($todayRecord && $todayRecord->am_in && !$todayRecord->am_out && $now->hour < 12)
                            <i class="fas fa-sun mr-2"></i> Morning Out

                        {{-- 3. Afternoon Gate: If it's 12:00 PM or later, OR morning shift was already closed --}}
                        @elseif($now->hour >= 12 || ($todayRecord && $todayRecord->am_out))
                            <i class="fas fa-moon mr-2"></i> Afternoon In

                        {{-- 4. Default: Morning In (Before 12:00 PM and nothing started yet) --}}
                        @else
                            <i class="fas fa-sun mr-2"></i> Morning In
                        @endif

                    </button>
                </form>
                @else
                    <div class="text-blue-700 p-4 rounded-xl border border-blue-100 inline-block">
                        <i class="fas fa-check-circle mr-2"></i>
                        Shift completed. See you tomorrow!
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Script to keep the clock ticking without refresh --}}
    <script>
        function updateClock() {
            const now = new Date();
            const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-US', options);
        }
        setInterval(updateClock, 1000);
    </script>
</body>
</html>