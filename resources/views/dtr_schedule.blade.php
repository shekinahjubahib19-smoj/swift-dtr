<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR Schedule - OJT ChronoLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

    @include('partials.header')

    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Projected DTR Schedule</h1>

        <div class="bg-white p-4 rounded shadow mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">Starting Date</div>
                    <div class="font-bold">{{ \Carbon\Carbon::parse($start)->format('F d, Y') }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">Target Hours</div>
                    <div class="font-bold">{{ number_format($targetHours, 0) }} hrs</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">Hours / Day</div>
                    <div class="font-bold">{{ $hoursPerDay }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">Estimated End</div>
                    <div class="font-bold">{{ $endDate->format('F d, Y') }}</div>
                    <div class="text-sm text-slate-400">With 30-day extension: {{ $endWithExtension->format('F d, Y') }}</div>
                </div>
            </div>
        </div>

        @foreach($scheduleByMonth as $monthLabel => $rows)
            <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
                <div class="p-4 border-b">
                    <h2 class="font-bold">{{ $monthLabel }}</h2>
                </div>

                <table class="w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-right">Planned Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $cumulative = 0; @endphp
                        @foreach($rows as $r)
                            @php $cumulative += $r['hours']; @endphp
                            <tr class="border-t">
                                <td class="p-3">{{ \Carbon\parse($r['date'])->format('M d, Y (D)') }}</td>
                                <td class="p-3 text-right font-mono">{{ number_format($r['hours'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <div class="text-sm text-slate-600">Note: schedule assumes {{ $hoursPerDay }} hours/day and treats weekends as days off. You can change parameters in DTR Management.</div>

    </div>

</body>
</html>
