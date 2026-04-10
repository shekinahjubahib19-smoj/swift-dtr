<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-5 border border-slate-100">
        <div class="text-center mb-4">
            <h2 class="text-2xl font-bold text-slate-800">Create Intern Account</h2>
        </div>

        <form action="/register" method="POST" class="space-y-4" id="registerForm" data-turnstile-sitekey="{{ env('TURNSTILE_SITEKEY') }}">
            @csrf
            <!-- Honeypot field to catch simple bots -->
            <div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                <label>Leave this field empty</label>
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <!-- STEP 1: Basic Info -->
            <div id="step-basic" class="form-step">
                <div class="mb-4">
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1 ml-1">Full Name</label>
                    <input type="text" name="name" id="name" placeholder="Juan Dela Cruz" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition uppercase">
                </div>

                <div class="mb-1">
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1 ml-1">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="juan@ctu.edu.ph" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                </div>

                <div class="flex justify-end mt-2">
                    <button type="button" id="nextToPwd" class="px-4 py-2 bg-blue-600 text-white rounded-xl">Next</button>
                </div>
            </div>

            <!-- STEP 2: Password -->
            <div id="step-password" class="form-step hidden">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1 ml-1">Create Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                        <button type="button" id="togglePwd" class="absolute right-3 top-3 text-slate-400">Show</button>
                    </div>
                    <div class="text-xs text-slate-500 mt-2" id="pwdStrength">Password strength: <span id="pwdStrengthVal">—</span></div>
                </div>

                <div class="mt-3">
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1 ml-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                </div>

                <div class="flex justify-between mt-2">
                    <button type="button" id="backToBasic" class="px-4 py-2 bg-slate-200 rounded-xl">Back</button>
                    <button type="submit" id="registerBtn" class="px-4 py-2 bg-blue-600 text-white rounded-xl">Register</button>
                </div>
            </div>
            <!-- Removed STEP 3: contact/verification to simplify registration (server will default to email verification) -->
        </form>

        {{-- include modal --}}
        @include('modals.register_password')
        @include('modals.register_basic')

        <script src="/js/register.js" defer></script>

        <p class="text-center mt-8 text-sm text-slate-500">
            Already have an account? <a href="/login" class="text-blue-600 font-bold hover:underline">Login here</a>
        </p>
    </div>

</body>
</html>