<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Verify account</title>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-xl shadow p-8">
        <h2 class="text-lg font-bold mb-4">Verify your account</h2>
        <p class="text-sm text-slate-600 mb-4">Enter the 6-digit code we sent to your selected method.</p>
        @if(session('error'))
            <div class="mb-3 text-sm text-red-600">{{ session('error') }}</div>
        @endif
        @if($errors->has('resend'))
            <div class="mb-3 text-sm text-red-600">{{ $errors->first('resend') }}</div>
        @endif
        @if(session('success'))
            <div class="mb-3 text-sm text-green-600">{{ session('success') }}</div>
        @endif

        <form method="POST" action="/verify">
            @csrf
            <div>
                <label class="block text-xs uppercase text-slate-500 mb-1">Code</label>
                <input name="code" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required class="w-full px-4 py-3 rounded border border-slate-200">
            </div>
            <div class="mt-4">
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded">Verify</button>
            </div>
        </form>
        <form method="POST" action="/verify/resend" class="mt-3">
            @csrf
            <button type="submit" class="w-full bg-gray-200 text-slate-800 py-2 rounded">Resend code</button>
        </form>
    </div>
</body>
</html>
