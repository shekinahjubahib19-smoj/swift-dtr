<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OJT ChronoLink | Smart DTR Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 font-sans text-slate-900">

    <nav class="flex justify-between items-center px-10 py-6 bg-white shadow-sm sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <div class="bg-blue-600 p-2 rounded-lg">
                <i class="fas fa-stopwatch text-white text-xl"></i>
            </div>
            <span class="text-2xl font-black tracking-tighter text-slate-800">ChronoLink</span>
        </div>
        <div class="space-x-8 font-medium">
            <a href="/login" class="text-slate-600 hover:text-blue-600 transition">Login</a>
            <a href="/signup" class="bg-blue-600 text-white px-6 py-2.5 rounded-full hover:bg-blue-700 transition shadow-md">Get Started</a>
        </div>
    </nav>

    <header class="container mx-auto px-10 py-20 flex flex-col md:flex-row items-center">
        <div class="md:w-1/2 space-y-6">
            <h1 class="text-6xl font-extrabold leading-tight text-slate-900">
                Track your OJT hours with <span class="text-blue-600">Precision.</span>
            </h1>
            <p class="text-lg text-slate-600 max-w-lg">
                The ultimate DTR system for interns. Manage your 720-hour or more requirement, track daily logs, and generate professional reports in one click.
            </p>
            <div class="flex gap-4">
                <a href="/login" class="bg-slate-900 text-white px-8 py-4 rounded-xl font-bold hover:bg-slate-800 transition">Start Tracking Now</a>
                <a href="#features" class="border border-slate-300 px-8 py-4 rounded-xl font-bold hover:bg-slate-100 transition">Learn More</a>
            </div>
        </div>
        <div class="md:w-1/2 mt-12 md:mt-0 relative">
            <div class="absolute -top-10 -left-10 w-64 h-64 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-pulse"></div>
            <div class="bg-white p-4 rounded-2xl shadow-2xl border border-slate-100 relative z-10">
                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=800" alt="Dashboard Preview" class="rounded-lg">
            </div>
        </div>
    </header>

    <section id="features" class="bg-white py-24">
        <div class="container mx-auto px-10">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold">Built for Modern Internships</h2>
                <p class="text-slate-500 mt-2">Everything you need to complete your 720 hours or more smoothly.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:shadow-lg transition">
                    <i class="fas fa-clock text-blue-600 text-3xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Smart Logging</h3>
                    <p class="text-slate-600 text-sm">One-click time in/out with automatic 1-hour delay protection to ensure accuracy.</p>
                </div>
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:shadow-lg transition">
                    <i class="fas fa-chart-pie text-blue-600 text-3xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Progress Tracking</h3>
                    <p class="text-slate-600 text-sm">Visual dashboard showing exactly how many hours you've rendered and how many are left.</p>
                </div>
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:shadow-lg transition">
                    <i class="fas fa-file-excel text-blue-600 text-3xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Instant Export</h3>
                    <p class="text-slate-600 text-sm">Generate and print your DTR card in Excel format whenever you need to submit to your supervisor.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-10 text-center text-slate-400 text-sm border-t border-slate-100">
        <p>&copy; 2026 OJT ChronoLink System. Designed for CTU Interns.</p>
    </footer>

</body>
</html>