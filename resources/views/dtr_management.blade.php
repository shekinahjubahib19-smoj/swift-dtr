<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50">

    @include('partials.header')

    @php 
        // 1. Fetch settings via the relationship defined in User.php
        $settings = auth()->user()->dtrSetting; 
    @endphp

    <div class="p-8" x-data="{ 
        isEditing: {{ $settings ? 'false' : 'true' }} 
    }">
        <form action="/dtr-setup" method="POST" 
              class="space-y-8 {{ $settings ? 'pointer-events-none' : '' }}">
            @csrf

            <div class="flex justify-between items-center border-b pb-4">
                <h3 class="text-slate-800 font-bold text-xl">
                    {{ $settings ? 'Internship Profile' : 'Internship Configuration' }}
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Full Name</label>
                    <input type="text" name="full_name" :disabled="!isEditing"
                        value="{{ $settings->full_name ?? Auth::user()->name }}" 
                        class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none disabled:opacity-100 disabled:text-slate-700 disabled:cursor-default transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Hours to Render</label>
                    <input type="number" name="total_hours" :disabled="!isEditing"
                        value="{{ $settings->total_hours ?? '720' }}" 
                        class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none disabled:opacity-100 disabled:text-slate-700 disabled:cursor-default transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Department</label>
                    <input type="text" name="department" :disabled="!isEditing"
                        value="{{ $settings->department ?? '' }}" 
                        placeholder="e.g. IT Department"
                        class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none disabled:opacity-100 disabled:text-slate-700 disabled:cursor-default transition-all">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Position</label>
                    <input type="text" name="position" :disabled="!isEditing"
                        value="{{ $settings->position ?? '' }}" 
                        placeholder="e.g. IT Intern"
                        class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none disabled:opacity-100 disabled:text-slate-700 disabled:cursor-default transition-all">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Starting Date</label>
                    <input type="date" name="starting_date" :disabled="!isEditing"
                        value="{{ $settings->starting_date ?? now()->format('Y-m-d') }}"
                        class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none disabled:opacity-100 disabled:text-slate-700 disabled:cursor-default transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-blue-50 p-6 rounded-2xl border border-blue-100">
                <div class="space-y-3">
                    <p class="text-xs font-bold text-blue-600 uppercase">Morning Session</p>
                    <div class="flex gap-2">
                        <div class="w-full">
                            <span class="text-[10px] text-blue-400 font-bold uppercase">Time In</span>
                            <input type="time" name="am_in" :disabled="!isEditing" 
                                value="{{ $settings->am_in ?? '07:00' }}" 
                                class="w-full p-2 rounded-lg border border-blue-200 disabled:bg-white">
                        </div>
                        <div class="w-full">
                            <span class="text-[10px] text-blue-400 font-bold uppercase">Time Out</span>
                            <input type="time" name="am_out" :disabled="!isEditing" 
                                value="{{ $settings->am_out ?? '12:00' }}" 
                                class="w-full p-2 rounded-lg border border-blue-200 disabled:bg-white">
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-xs font-bold text-blue-600 uppercase">Afternoon Session</p>
                    <div class="flex gap-2">
                        <div class="w-full">
                            <span class="text-[10px] text-blue-400 font-bold uppercase">Time In</span>
                            <input type="time" name="pm_in" :disabled="!isEditing" 
                                value="{{ $settings->pm_in ?? '13:00' }}" 
                                class="w-full p-2 rounded-lg border border-blue-200 disabled:bg-white">
                        </div>
                        <div class="w-full">
                            <span class="text-[10px] text-blue-400 font-bold uppercase">Time Out</span>
                            <input type="time" name="pm_out" :disabled="!isEditing" 
                                value="{{ $settings->pm_out ?? '17:00' }}" 
                                class="w-full p-2 rounded-lg border border-blue-200 disabled:bg-white">
                        </div>
                    </div>
                </div>
            </div>

            @if(!$settings)
                <div class="flex justify-end gap-4" x-transition>
                    <button type="submit" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-black hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                        <i class="fas fa-save mr-2"></i> Save Configuration
                    </button>
                </div>
            @endif
        </form>
    </div>

</body>
</html>