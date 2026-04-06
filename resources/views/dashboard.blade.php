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

    <div class="container mx-auto py-12 px-4">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow border border-slate-200">
            <h1 class="text-2xl font-bold text-slate-800 mb-4">Dashboard</h1>
            <p class="text-slate-600">This is the Dashboard view. Add widgets or summaries here.</p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm text-slate-500">Active Users</div>
                    <div class="text-xl font-bold text-slate-800">—</div>
                </div>
                <div class="p-4 bg-emerald-50 rounded-lg">
                    <div class="text-sm text-slate-500">This Month Hours</div>
                    <div class="text-xl font-bold text-slate-800">—</div>
                </div>
                <div class="p-4 bg-yellow-50 rounded-lg">
                    <div class="text-sm text-slate-500">Pending Approvals</div>
                    <div class="text-xl font-bold text-slate-800">—</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
