<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | OJT ChronoLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-10 border border-slate-100">
        <div class="text-center mb-8">
            <div class="bg-blue-600 w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-200">
                <i class="fas fa-lock text-white"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Welcome Back</h2>
            <p class="text-slate-500 text-sm">Log in to continue tracking your OJT hours.</p>
        </div>

        <form action="/login" method="POST" class="space-y-5">
            @csrf <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1 ml-1">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required placeholder="name@email.com" 
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                </div>
            </div>

            <div>
                <div class="flex justify-between mb-1 ml-1">
                    <label class="block text-xs font-bold uppercase text-slate-500">Password</label>
                    <a href="#" class="text-xs font-bold text-blue-600 hover:underline">Forgot?</a>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fas fa-key"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••" 
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                </div>
            </div>

            <div class="flex items-center ml-1">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                <label for="remember" class="ml-2 text-sm text-slate-600">Remember this device</label>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform active:scale-95">
                Sign In
            </button>
        </form>

        <p class="text-center mt-8 text-sm text-slate-500">
            First time using ChronoLink? <a href="/register" class="text-blue-600 font-bold hover:underline">Create an account</a>
        </p>
    </div>

</body>
</html>