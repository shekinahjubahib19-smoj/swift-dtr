document.addEventListener("DOMContentLoaded", function () {
    const exportBtn = document.getElementById("exportExcelBtn");
    const printBtn = document.getElementById("printBtn");
    const tables = Array.from(document.querySelectorAll("table.w-full"));
    let isExporting = false;
    let isPrinting = false;

    function getTableRowsForCSV() {
        if (!tables || !tables.length) return [];
        const allRows = [];
        tables.forEach((tbl, idx) => {
            const rows = Array.from(tbl.querySelectorAll("thead tr, tbody tr"));
            if (idx > 0) allRows.push([]); // blank row between tables
            rows.forEach((row) => {
                const cells = Array.from(row.querySelectorAll("th, td")).map(
                    (td) => {
                        let text = (td.innerText || "")
                            .replace(/\u00A0/g, " ")
                            .trim();
                        text = text.replace(/"/g, '""');
                        return '"' + text + '"';
                    },
                );
                allRows.push(cells);
            });
        });
        return allRows.map((cells) => Array.isArray(cells) ? cells.join(",") : "");
    }

    function downloadCSV(filename, csvContent) {
        const blob = new Blob([csvContent], {
            type: "text/csv;charset=utf-8;",
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    function findFieldValue(label) {
        if (!label) return "";
        const lc = label.toLowerCase();
        const candidates = Array.from(
            document.querySelectorAll("span,div,td,th,label"),
        );
        for (const el of candidates) {
            const txt = (el.textContent || "").trim();
            if (!txt) continue;
            const normalized = txt.toLowerCase();
            if (
                normalized === lc ||
                normalized === lc + ":" ||
                normalized.startsWith(lc + ":") ||
                normalized === lc.replace(/\s+/g, " ")
            ) {
                let next = el.nextElementSibling;
                if (next && next.textContent && next.textContent.trim())
                    return next.textContent.trim();
                const parent = el.parentElement;
                if (parent) {
                    const others = Array.from(
                        parent.querySelectorAll("span,div"),
                    ).filter(
                        (x) =>
                            x !== el && x.textContent && x.textContent.trim(),
                    );
                    if (others.length) return others[0].textContent.trim();
                }
                const parts = txt.split(":");
                if (parts.length > 1) {
                    const after = parts.slice(1).join(":").trim();
                    if (after) return after;
                }
            }
        }
        return "";
    }

    function buildExcelHtml(headerHtml) {
        if (!tables || !tables.length) return headerHtml || "";
        const doc = document.implementation.createHTMLDocument("dtr");

        if (headerHtml) {
            const wrapper = doc.createElement("div");
            wrapper.innerHTML = headerHtml;
            const headerContainer = doc.createElement("div");
            headerContainer.appendChild(wrapper);
            doc.body.appendChild(headerContainer);
        }

        tables.forEach((tbl, idx) => {
            const clone = tbl.cloneNode(true);
            doc.body.appendChild(clone);
            if (idx < tables.length - 1) {
                const spacer = doc.createElement('div');
                spacer.style.height = '12px';
                doc.body.appendChild(spacer);
            }
        });

        const style =
            "table{border-collapse:collapse;}th,td{border:1px solid #e5e7eb;padding:4px;font-family:Calibri,Arial,Helvetica,sans-serif;font-size:11px;}thead th{background:#111827;color:#ffffff;}";
        return (
            '<!doctype html><html><head><meta charset="utf-8"><style>' +
            style +
            "</style></head><body>" +
            doc.body.innerHTML +
            "</body></html>"
        );
    }

    function downloadExcel(filename, html) {
        const blob = new Blob([html], { type: "application/vnd.ms-excel" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    if (exportBtn && !exportBtn.dataset.dtrExportAttached) {
        exportBtn.dataset.dtrExportAttached = "1";
        exportBtn.addEventListener("click", function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            if (isExporting) return;
            isExporting = true;
            const fullName =
                findFieldValue("Full Name") ||
                findFieldValue("FULL NAME") ||
                "";
            const division =
                findFieldValue("Division") || findFieldValue("DIVISION") || "";
            const position =
                findFieldValue("Position") || findFieldValue("POSITION") || "";
            const targetHours =
                findFieldValue("Target Hours") ||
                findFieldValue("TARGET HOURS") ||
                "";

            const headerHtml = `
                <table style="width:100%;border-collapse:collapse;margin-bottom:8px;">
                    <tr>
                        <td style="width:50%;padding:6px;border:1px solid #e5e7eb;vertical-align:top;">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase">Full Name</div>
                            <div style="font-weight:600">${fullName}</div>
                        </td>
                        <td style="width:50%;padding:6px;border:1px solid #e5e7eb;vertical-align:top;">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase">Division</div>
                            <div style="font-weight:600">${division}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:50%;padding:6px;border:1px solid #e5e7eb;vertical-align:top;">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase">Position</div>
                            <div style="font-weight:600">${position}</div>
                        </td>
                        <td style="width:50%;padding:6px;border:1px solid #e5e7eb;vertical-align:top;">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase">Target Hours</div>
                            <div style="font-weight:600">${targetHours}</div>
                        </td>
                    </tr>
                </table>
            `;

            const today = new Date().toISOString().slice(0, 10);
            const filename = "dtr-" + today + ".xls";
            try {
                const html = buildExcelHtml(headerHtml);
                downloadExcel(filename, html);
            } finally {
                setTimeout(() => {
                    isExporting = false;
                }, 600);
            }
        });
    }

    if (printBtn && !printBtn.dataset.dtrPrintAttached) {
        printBtn.dataset.dtrPrintAttached = "1";
        printBtn.addEventListener("click", function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            if (isPrinting) return;
            isPrinting = true;
            const fullName =
                findFieldValue("Full Name") ||
                findFieldValue("FULL NAME") ||
                "";
            const division =
                findFieldValue("Division") || findFieldValue("DIVISION") || "";
            const position =
                findFieldValue("Position") || findFieldValue("POSITION") || "";
            const targetHours =
                findFieldValue("Target Hours") ||
                findFieldValue("TARGET HOURS") ||
                "";

            const headerHtml = `
                <table style="width:100%;border-collapse:collapse;margin-bottom:8px;">
                    <tr>
                        <td style="width:50%;padding:6px;border:1px solid #e5e7eb;vertical-align:top;">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase">Full Name</div>
                            <div style="font-weight:600">${fullName}</div>
                        </td>
                        <td style="width:50%;padding:6px;border:1px solid #e5e7eb;vertical-align:top;">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase">Division</div>
                            <div style="font-weight:600">${division}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:50%;padding:6px;border:1px solid #e5e7eb;vertical-align:top;">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase">Position</div>
                            <div style="font-weight:600">${position}</div>
                        </td>
                        <td style="width:50%;padding:6px;border:1px solid #e5e7eb;vertical-align:top;">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase">Target Hours</div>
                            <div style="font-weight:600">${targetHours}</div>
                        </td>
                    </tr>
                </table>
            `;

                        let bodyHtml = "";
                        if (!tables || !tables.length) {
                                bodyHtml = "<div>No table found</div>";
                        } else {
                                bodyHtml = tables.map(t => t.outerHTML).join('<div style="height:12px"></div>');
                        }

                        const html = `
                                <html>
                                    <head>
                                        <title>Print DTR</title>
                                        <style>
                                            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #111827; }
                                            table { width: 100%; border-collapse: collapse; }
                                            th, td { padding: 4px; border: 1px solid #e5e7eb; text-align: left; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, 'Roboto Mono', 'Courier New', monospace; font-weight: normal; font-size: 0.875rem; }
                                            thead th { background: #111827; color: white; }
                                        </style>
                                    </head>
                                    <body>
                                        ${headerHtml}
                                        ${bodyHtml}
                                    </body>
                                </html>
                        `;

            try {
                const w = window.open("", "_blank");
                if (!w) {
                    alert("Unable to open print window — check popup blocker.");
                    return;
                }
                w.document.open();
                w.document.write(html);
                w.document.close();
                setTimeout(() => {
                    w.focus();
                    w.print();
                }, 300);
            } finally {
                setTimeout(() => {
                    isPrinting = false;
                }, 600);
            }
        });
    }
});
