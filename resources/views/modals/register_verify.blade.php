<div id="modalRegisterVerify" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 mx-4">
        <div class="flex justify-between items-start">
            <h3 class="text-lg font-semibold">Choose verification method</h3>
            <button id="modalRegisterVerifyClose" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <div class="mt-4 text-sm text-slate-700">
            <form id="verifyChoiceForm">
                <div class="space-y-3">
                    <label class="flex items-start space-x-3">
                        <input type="radio" name="verify_method" value="email" {{ old('method') !== 'sms' ? 'checked' : '' }}>
                        <div>
                            <div class="font-medium">Email</div>
                            <div class="text-xs text-slate-500">Send the verification code to your registered email: <span id="verifyEmailText">—</span></div>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3">
                        <input type="radio" name="verify_method" value="sms" {{ old('method') === 'sms' ? 'checked' : '' }}>
                        <div>
                            <div class="font-medium">SMS</div>
                            <div class="text-xs text-slate-500">Send code to a mobile number. Enter the number below.</div>
                        </div>
                    </label>

                    <div id="verifyMobileGroup" class="mt-2 {{ old('method') === 'sms' ? '' : 'hidden' }}">
                        <input type="tel" id="verify_mobile" name="verify_mobile" placeholder="09xxxxxxxxx" value="{{ old('phone') }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                        <div id="verifyMobileError" class="text-xs text-red-600 mt-1 hidden">Please enter a valid mobile number.</div>
                    </div>
                </div>
            </form>
        </div>
        <div class="mt-6 text-right">
            <button id="modalRegisterVerifyCancel" class="px-4 py-2 bg-slate-200 rounded-xl mr-2">Cancel</button>
            <button id="modalRegisterVerifySend" class="px-4 py-2 bg-blue-600 text-white rounded-xl">Send & Continue</button>
        </div>
    </div>
</div>
