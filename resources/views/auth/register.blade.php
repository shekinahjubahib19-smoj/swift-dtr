<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-10 border border-slate-100">
        <div class="text-center mb-8">
            <div class="bg-blue-600 w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-200">
                <i class="fas fa-user-plus text-white"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Create Intern Account</h2>
            <p class="text-slate-500 text-sm">Join OJT ChronoLink to start tracking hours.</p>
        </div>

        <form action="/register" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1 ml-1">Full Name</label>
                <input type="text" name="name" placeholder="Juan Dela Cruz" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1 ml-1">Email Address</label>
                <input type="email" name="email" placeholder="juan@ctu.edu.ph" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1 ml-1">Create Password</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform active:scale-95">
                Register & Start OJT
            </button>
        </form>

        <p class="text-center mt-8 text-sm text-slate-500">
            Already have an account? <a href="/login" class="text-blue-600 font-bold hover:underline">Login here</a>
        </p>
    </div>

</body>
</html>