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
    })();
});
