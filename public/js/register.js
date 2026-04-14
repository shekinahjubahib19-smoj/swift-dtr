// Registration form behavior moved from inline Blade.
document.addEventListener('DOMContentLoaded', function () {
    // Show/hide password
    const pwd = document.getElementById('password');
    const toggle = document.getElementById('togglePwd');
    const strengthVal = document.getElementById('pwdStrengthVal');
    const confirmInput = document.getElementById('password_confirmation');
    const confirmStatusVal = (function(){
        let el = document.getElementById('confirmStatusVal');
        if (!el) {
            const container = document.createElement('div');
            container.className = 'text-xs text-slate-500 mt-2';
            container.innerHTML = 'Confirm password: <span id="confirmStatusVal">—</span>';
            const ref = document.getElementById('password_confirmation');
            if (ref && ref.parentNode) ref.parentNode.appendChild(container);
            el = document.getElementById('confirmStatusVal');
        }
        return el;
    })();
    const registerBtn = document.getElementById('registerBtn');
    function strength(s){
        let score = 0;
        if (s.length >= 8) score++;
        if (/[A-Z]/.test(s)) score++;
        if (/[0-9]/.test(s)) score++;
        if (/[^A-Za-z0-9]/.test(s)) score++;
        return score;
    }
    if(toggle && pwd){
        toggle.addEventListener('click', function(){
            if(pwd.type === 'password'){ pwd.type = 'text'; toggle.textContent = 'Hide'; }
            else { pwd.type = 'password'; toggle.textContent = 'Show'; }
        });
        pwd.addEventListener('input', function(){
            const s = strength(pwd.value);
            const labels = ['Very weak','Weak','Okay','Good','Strong'];
            if (strengthVal) strengthVal.textContent = labels[s];
            updateConfirmStatus();
        });
    }

    // contact/verification removed from UI; server will use email by default
    function updateConfirmStatus(){
        if (!confirmInput || !confirmStatusVal || !registerBtn) return;
        const a = pwd ? (pwd.value || '') : '';
        const b = confirmInput.value || '';
        const matched = a.length > 0 && a === b;
        confirmStatusVal.textContent = matched ? 'Matched' : (b.length ? 'Unmatched' : '—');
        confirmStatusVal.style.color = matched ? '#059669' : '#dc2626';
        registerBtn.disabled = !matched;
        if (registerBtn.disabled) registerBtn.classList.add('opacity-60', 'pointer-events-none');
        else registerBtn.classList.remove('opacity-60', 'pointer-events-none');
    }
    if (confirmInput) {
        confirmInput.addEventListener('input', updateConfirmStatus);
        updateConfirmStatus();
    }

    // Optional: attach Turnstile if site key configured
    (function(){
        const formEl = document.getElementById('registerForm');
        const TURNSTILE_SITEKEY = formEl ? formEl.dataset.turnstileSitekey : null;
        if (!TURNSTILE_SITEKEY) return;
        // insert hidden input for token
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'cf-turnstile-response';
        input.id = 'cf-turnstile-response';
        formEl.appendChild(input);
        // Load Turnstile script and call render onload
        const s = document.createElement('script');
        s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=turnstileOnLoad';
        s.async = true; s.defer = true;
        document.head.appendChild(s);
        window.turnstileOnLoad = function(){
            if (typeof turnstile === 'undefined') return;
            turnstile.render('#turnstile', {
                sitekey: TURNSTILE_SITEKEY,
                callback: function(token){ var el = document.getElementById('cf-turnstile-response'); if(el) el.value = token; }
            });
        };
    })();

    // Multi-step navigation logic
    (function(){
        const steps = ['step-basic','step-password','step-contact'];
        let current = 0;
        function showStep(i){
            current = i;
            for (let idx=0; idx<steps.length; idx++){
                const el = document.getElementById(steps[idx]);
                if (!el) continue;
                if (idx===i) el.classList.remove('hidden'); else el.classList.add('hidden');
            }
        }

        function validBasic(){
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            if (!name || !email) return false;
            const n = name.value.trim();
            const e = email.value.trim();
            const emailOK = /\S+@\S+\.\S+/.test(e);
            return n.length>0 && emailOK;
        }

        function validPassword(){
            const pwd = document.getElementById('password');
            const conf = document.getElementById('password_confirmation');
            if (!pwd || !conf) return false;
            return pwd.value.length >= 8 && pwd.value === conf.value;
        }

        document.getElementById('nextToPwd')?.addEventListener('click', function(){
            if (validBasic()) showStep(1);
            else {
                const modal = document.getElementById('modalRegisterBasic');
                const msg = document.getElementById('modalRegisterBasicMessage');
                if (msg) msg.textContent = 'Please fill name and a valid email to continue.';
                if (modal) modal.classList.remove('hidden');
            }
        });
        document.getElementById('backToBasic')?.addEventListener('click', function(){ showStep(0); });

        // Validate on submit: ensure basic info & password step are valid before sending to server
        const registerForm = document.getElementById('registerForm');
        registerForm.addEventListener('submit', function(e){
            if (!validBasic()){
                e.preventDefault();
                const modal = document.getElementById('modalRegisterBasic');
                const msg = document.getElementById('modalRegisterBasicMessage');
                if (msg) msg.textContent = 'Please fill name and a valid email.';
                if (modal) modal.classList.remove('hidden');
                showStep(0);
                return;
            }
            if (!validPassword()){
                e.preventDefault();
                // show modal instead of alert for password warnings
                const modal = document.getElementById('modalRegisterPwd');
                const msg = document.getElementById('modalRegisterPwdMessage');
                if (msg) msg.textContent = 'Passwords must match and be at least 8 characters.';
                if (modal) modal.classList.remove('hidden');
                showStep(1);
                return;
            }
            // If verification choice hasn't been confirmed yet, open the verify modal
            if (!registerForm.dataset.verificationConfirmed || registerForm.dataset.verificationConfirmed !== '1'){
                e.preventDefault();
                // open modal to choose verification method (email or sms)
                openVerifyModal();
                return;
            }
        });

        // initialize
        showStep(0);
        // modal handlers (close)
        document.getElementById('modalRegisterPwdClose')?.addEventListener('click', function(){
            document.getElementById('modalRegisterPwd')?.classList.add('hidden');
        });
        document.getElementById('modalRegisterPwdOk')?.addEventListener('click', function(){
            document.getElementById('modalRegisterPwd')?.classList.add('hidden');
        });
        // basic modal handlers
        document.getElementById('modalRegisterBasicClose')?.addEventListener('click', function(){
            document.getElementById('modalRegisterBasic')?.classList.add('hidden');
        });
        document.getElementById('modalRegisterBasicOk')?.addEventListener('click', function(){
            document.getElementById('modalRegisterBasic')?.classList.add('hidden');
        });

        // verification modal handlers
        const modalVerify = document.getElementById('modalRegisterVerify');
        const verifyEmailText = document.getElementById('verifyEmailText');
        const verifyMobileGroup = document.getElementById('verifyMobileGroup');
        const verifyMobile = document.getElementById('verify_mobile');
        const verifyMobileError = document.getElementById('verifyMobileError');

        // If server returned validation errors previously (page reload), automatically open verify modal
        try {
            const formEl = document.getElementById('registerForm');
            let srvErrs = [];
            let oldMethod = null;
            let oldPhone = null;
            if (formEl) {
                try { srvErrs = JSON.parse(formEl.dataset.serverRegisterErrors || '[]'); } catch(e){ srvErrs = []; }
                try { oldMethod = JSON.parse(formEl.dataset.serverOldMethod || 'null'); } catch(e){ oldMethod = formEl.dataset.serverOldMethod || null; }
                try { oldPhone = JSON.parse(formEl.dataset.serverOldPhone || 'null'); } catch(e){ oldPhone = formEl.dataset.serverOldPhone || null; }
            }
            const shouldOpen = (oldMethod === 'sms') || srvErrs.some(function(e){ if (!e) return false; const s = e.toLowerCase(); return s.includes('phone') || s.includes('sms'); });
            if (shouldOpen && modalVerify) {
                if (verifyMobile && oldPhone) verifyMobile.value = oldPhone;
                const smsRadio = document.querySelector('input[name="verify_method"][value="sms"]');
                if (smsRadio) smsRadio.checked = true;
                if (verifyMobileGroup) verifyMobileGroup.classList.remove('hidden');
                modalVerify.classList.remove('hidden');
            }
        } catch (err) {
            // ignore
        }

        function openVerifyModal(){
            if (!modalVerify) return;
            // populate email text from form
            const emailEl = document.getElementById('email');
            verifyEmailText.textContent = emailEl ? emailEl.value || '—' : '—';
            // default state: email selected
            const checked = document.querySelector('input[name="verify_method"]:checked');
            if (checked && checked.value === 'sms') { verifyMobileGroup.classList.remove('hidden'); }
            else { verifyMobileGroup.classList.add('hidden'); }
            verifyMobileError.classList.add('hidden');
            modalVerify.classList.remove('hidden');
        }

        document.querySelectorAll('input[name="verify_method"]').forEach(function(r){
            r.addEventListener('change', function(){
                if (r.value === 'sms') verifyMobileGroup.classList.remove('hidden');
                else verifyMobileGroup.classList.add('hidden');
            });
        });

        document.getElementById('modalRegisterVerifyClose')?.addEventListener('click', function(){ if (modalVerify) modalVerify.classList.add('hidden'); });
        document.getElementById('modalRegisterVerifyCancel')?.addEventListener('click', function(){ if (modalVerify) modalVerify.classList.add('hidden'); });

        document.getElementById('modalRegisterVerifySend')?.addEventListener('click', function(){
            if (!registerForm) return;
            const methodEl = document.querySelector('input[name="verify_method"]:checked');
            const method = methodEl ? methodEl.value : 'email';
            // if sms, validate mobile
            if (method === 'sms'){
                const m = verifyMobile ? verifyMobile.value.trim() : '';
                // very simple mobile validation: starts with 09 and 11 digits OR +63...
                const ok = /^09\d{9}$/.test(m) || /^\+63\d{10}$/.test(m);
                if (!ok){ verifyMobileError.classList.remove('hidden'); return; }
            }
            // remove any existing hidden inputs we added earlier
            const oldMethod = registerForm.querySelector('input[name="method"]');
            if (oldMethod) oldMethod.remove();
            const oldPhone = registerForm.querySelector('input[name="phone"]');
            if (oldPhone) oldPhone.remove();

            // append hidden inputs using server-expected names: `method` and `phone`
            const hm = document.createElement('input'); hm.type = 'hidden'; hm.name = 'method'; hm.value = method;
            registerForm.appendChild(hm);
            if (method === 'sms'){
                const hp = document.createElement('input'); hp.type = 'hidden'; hp.name = 'phone'; hp.value = verifyMobile.value.trim();
                registerForm.appendChild(hp);
            }
            // mark confirmed to avoid modal loop
            registerForm.dataset.verificationConfirmed = '1';
            modalVerify.classList.add('hidden');
            // submit the form programmatically now that choices have been recorded
            registerForm.submit();
        });
    })();
});
