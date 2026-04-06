function updateClock() {
    const now = new Date();
    
    const hours = String(now.getHours() % 12 || 12).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const ampm = now.getHours() >= 12 ? 'PM' : 'AM';

    const hoursMinsEl = document.getElementById('time-hours-mins');
    const secondsEl = document.getElementById('time-seconds');
    const ampmEl = document.getElementById('time-ampm');

    if (hoursMinsEl && secondsEl && ampmEl) {
        hoursMinsEl.innerHTML = `${hours}<span class="blink">:</span>${minutes}`;
        secondsEl.innerHTML = `<span class="blink">:</span>${seconds}`; 
        ampmEl.textContent = ampm;
    }
}

// This ensures it starts even if the page is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setInterval(updateClock, 1000));
} else {
    updateClock();
    setInterval(updateClock, 1000);
}
