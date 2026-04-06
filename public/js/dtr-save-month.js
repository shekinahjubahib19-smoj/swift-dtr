document.addEventListener("DOMContentLoaded", function () {
    const saveBtn = document.getElementById("saveMonthBtn");
    const monthTotalCell = document.getElementById("monthTotalCell");

    function computeMonthTotalFromTable() {
        const rows = Array.from(
            document.querySelectorAll("table.w-full tbody tr"),
        );
        let total = 0;
        rows.forEach((r) => {
            const cell = r.querySelector("td:last-child");
            if (!cell) return;
            const text = cell.innerText.trim();
            if (!text || text.includes("--")) return;
            const num = parseFloat(text.replace(/[^0-9.\-]+/g, ""));
            if (!isNaN(num)) total += num;
        });
        return total;
    }

    function formatHours(n) {
        return n.toFixed(2);
    }

    if (monthTotalCell) {
        const total = computeMonthTotalFromTable();
        monthTotalCell.innerText = isNaN(total) ? "--:--" : formatHours(total);
    }

    if (saveBtn) {
        saveBtn.addEventListener("click", function () {
            const total = computeMonthTotalFromTable();
            const yearMonth = new Date().toISOString().slice(0, 7);

            fetch("/dtr/save-monthly-total", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    )
                        ? document
                              .querySelector('meta[name="csrf-token"]')
                              .getAttribute("content")
                        : "",
                },
                body: JSON.stringify({ year_month: yearMonth }),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (data && data.status === "ok") {
                        alert(
                            "Monthly total saved: " + data.total_hours + " hrs",
                        );
                    } else {
                        alert("Failed to save monthly total");
                    }
                })
                .catch((err) => {
                    console.error(err);
                    alert("Error saving monthly total");
                });
        });
    }
});
