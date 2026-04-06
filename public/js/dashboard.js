(function(){
    // dashboard.js — initializes the hours pie chart
    function initHoursPie(){
        const canvas = document.getElementById('hoursPie');
        if(!canvas) return;

        const rendered = Number(canvas.dataset.rendered || 0);
        const remaining = Number(canvas.dataset.remaining || 0);

        const ctx = canvas.getContext('2d');
        // ensure canvas parent has a height so chart can render
        const parent = canvas.parentElement;
        if(parent && !parent.style.height){
            parent.style.minHeight = '320px';
        }

        const data = {
            labels: ['Rendered','Remaining'],
            datasets: [{
                data: [rendered, remaining],
                backgroundColor: ['#3b82f6', '#f59e0b'],
                hoverOffset: 6
            }]
        };

        // Wait until Chart is available
        function createChart(){
            if(typeof Chart === 'undefined'){
                // try again shortly
                setTimeout(createChart, 50);
                return;
            }

            // destroy existing chart instance on canvas if present
            if(canvas._chartInstance){
                try{ canvas._chartInstance.destroy(); }catch(e){}
            }

            canvas._chartInstance = new Chart(ctx, {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });
        }

        createChart();
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', initHoursPie);
    } else {
        initHoursPie();
    }
    
    // initialize monthly horizontal bar chart
    function initMonthlyBar(){
        const canvas = document.getElementById('monthlyBar');
        if(!canvas) return;

        const labels = (()=>{ try{ return JSON.parse(canvas.dataset.labels || '[]'); }catch(e){ return []; } })();
        const values = (()=>{ try{ return JSON.parse(canvas.dataset.values || '[]'); }catch(e){ return []; } })();

        const ctx = canvas.getContext('2d');
        const parent = canvas.parentElement;
        if(parent && !parent.style.height){ parent.style.minHeight = '400px'; }

        function create(){
            if(typeof Chart === 'undefined'){ setTimeout(create, 50); return; }

            if(canvas._chartInstance){ try{ canvas._chartInstance.destroy(); }catch(e){} }

            canvas._chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hours',
                        data: values,
                        backgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    // vertical bars: months on the x-axis, hours on the y-axis
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { title: { display: true, text: 'Month' } },
                        y: { title: { display: true, text: 'Hours' }, beginAtZero: true }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        create();
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', initMonthlyBar);
    } else {
        initMonthlyBar();
    }
})();
