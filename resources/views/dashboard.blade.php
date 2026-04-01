<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50">

    @include('partials.header')

    <div class="container mx-auto mt-10 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500">
                <h3 class="text-gray-500 text-sm font-semibold uppercase">Time Remaining</h3>
                <p class="text-3xl font-bold text-gray-800">420 Hours</p>
            </div>

            <div class="md:col-span-2 bg-white p-8 rounded-xl shadow-md text-center">
                <h2 class="text-2xl font-bold mb-4">Daily Time Record</h2>
                <h2 class="text-2xl font-bold">Welcome, {{ Auth::user()->name }}!</h2>
                <p class="text-gray-600 mb-6">Current Status: <span class="text-red-500 font-bold">Not Timed In</span></p>
                
                <button class="bg-green-500 hover:bg-green-600 text-white px-10 py-4 rounded-full font-bold transition duration-300 shadow-lg">
                    MORNING TIME IN
                </button>
            </div>

        </div>
    </div>

</body>
</html>