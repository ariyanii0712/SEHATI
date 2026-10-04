// Guest Book Tab Session Control
(function() {
    var path = window.location.pathname;
    // Only apply to visitor pages
    if (path.indexOf('/visitor/') !== -1) {
        var isTamuPage = path.indexOf('/tamu.php') !== -1;
        var isGetMember = path.indexOf('/get_member.php') !== -1;
        var isLogout = path.indexOf('/logout.php') !== -1;
        
        if (!isTamuPage && !isGetMember && !isLogout) {
            if (!sessionStorage.getItem('tamu_session_active')) {
                window.location.href = 'logout.php';
            }
        } else if (isTamuPage) {
            if (sessionStorage.getItem('tamu_session_active') === 'true') {
                window.location.href = 'index.php';
            }
        }
    }
})();

$(document).ready(function () {
    // Highlighting active nav and sidebar menu items
    var currentUrl = window.location.href;
    $(".navmenu, .nav-links a").each(function () {
        var href = $(this).attr("href");
        if (href && currentUrl.indexOf(href) !== -1 && href !== "#" && href !== "../visitor/index.php") {
            $(this).addClass("active");
        }
    });

    // Make table rows clickable safely
    $(document).on('click', '.clickable-row', function (e) {
        // Only trigger if click is not on a link or button inside the row
        if (!$(e.target).closest("a, button, select, input, .action-link").length) {
            var targetUrl = $(this).find(".row-link").attr("href");
            if (targetUrl) {
                window.location = targetUrl;
            }
        }
    });

    // Handle view toggle for catalog (Grid vs Table)
    if ($('#view-grid-btn').length && $('#view-table-btn').length) {
        // Set default view or read from localStorage
        var activeView = localStorage.getItem('catalogView') || 'grid';
        setView(activeView);

        $('#view-grid-btn').click(function () {
            setView('grid');
        });

        $('#view-table-btn').click(function () {
            setView('table');
        });
    }

    function setView(view) {
        localStorage.setItem('catalogView', view);
        $('.view-toggle-btn').removeClass('active');

        if (view === 'grid') {
            $('#view-grid-btn').addClass('active');
            $('#catalog-table-wrapper').hide();
            $('#catalog-grid-wrapper').show();
        } else {
            $('#view-table-btn').addClass('active');
            $('#catalog-grid-wrapper').hide();
            $('#catalog-table-wrapper').show();
        }
    }

    // Clear guest book session storage on logout click
    $(document).on('click', 'a[href="logout.php"]', function() {
        sessionStorage.removeItem('tamu_session_active');
    });

    // Initialize Dark Mode state on page load
    if (localStorage.getItem('darkMode') === 'true') {
        $('body').addClass('dark-mode');
        if ($('#mode-toggle').length) {
            $('#mode-toggle').prop('checked', true);
        }
    }

    // Toggle Dark Mode
    $(document).on('change', '#mode-toggle', function() {
        if ($(this).is(':checked')) {
            $('body').addClass('dark-mode');
            localStorage.setItem('darkMode', 'true');
        } else {
            $('body').removeClass('dark-mode');
            localStorage.setItem('darkMode', 'false');
        }
    });
});

// --- SEHATI Registration Flow Logic (book.php) ---
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('dateContainer') && typeof CFG_FASKES_ID !== 'undefined') {
        initRegistrationCalendar();
    }
});

function initRegistrationCalendar() {
    const dateContainer = document.getElementById('dateContainer');
    
    // Generate next 14 days
    const today = new Date();
    const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    let dateHtml = '';
    
    for (let i = 0; i < 14; i++) {
        const d = new Date(today);
        d.setDate(d.getDate() + i);
        
        const dayStr = days[d.getDay()];
        const dateNum = String(d.getDate()).padStart(2, '0');
        const monthNum = String(d.getMonth() + 1).padStart(2, '0');
        const yearNum = d.getFullYear();
        const fullDate = `${yearNum}-${monthNum}-${dateNum}`;
        
        // Sunday is closed
        const isSunday = d.getDay() === 0;
        
        if (isSunday) {
            dateHtml += `
                <div class="date-option disabled">
                    <p class="date-option-day">${dayStr}</p>
                    <p class="date-option-date">${dateNum}</p>
                    <p class="date-option-status">Tutup</p>
                </div>
            `;
        } else {
            dateHtml += `
                <div class="date-option" data-date="${fullDate}" onclick="selectDate(this)">
                    <p class="date-option-day">${dayStr}</p>
                    <p class="date-option-date">${dateNum}</p>
                    <p class="date-option-status">Pilih</p>
                </div>
            `;
        }
    }
    
    dateContainer.innerHTML = dateHtml;
}

window.selectDate = function(element) {
    const allDates = document.querySelectorAll('.date-option');
    allDates.forEach(el => el.classList.remove('selected'));
    
    element.classList.add('selected');
    const date = element.getAttribute('data-date');
    fetchSlots(date);
};

function fetchSlots(date) {
    const timeContainer = document.getElementById('timeContainer');
    timeContainer.innerHTML = '<div style="grid-column: 1 / -1; padding: 1rem; text-align: center; color: #64748b;">Memuat jadwal...</div>';
    
    fetch(`/sehati/ajax/get_slots.php?faskes_id=${CFG_FASKES_ID}&poli_id=${CFG_POLI_ID}&date=${date}`)
        .then(response => response.json())
        .then(slots => {
            if (slots.length === 0) {
                timeContainer.innerHTML = '<div style="grid-column: 1 / -1; padding: 1rem; text-align: center; color: #ef4444; font-weight: bold;">Tutup / Tidak ada slot tersedia di tanggal ini.</div>';
                return;
            }
            
            let html = '';
            slots.forEach((s, idx) => {
                if (s.available) {
                    const isAlmostFull = s.remaining <= 3;
                    const statusText = isAlmostFull ? `Sisa ${s.remaining} Slot` : 'Tersedia';
                    const statusClass = isAlmostFull ? 'hampir-penuh' : '';
                    
                    html += `
                        <label class="time-option">
                            <input type="radio" name="time" value="${s.time}">
                            <div class="time-option-card">
                                <span class="time-option-time">${s.time}</span>
                                <span class="time-option-status ${statusClass}">${statusText}</span>
                            </div>
                        </label>
                    `;
                } else {
                    html += `
                        <label class="time-option disabled">
                            <input type="radio" name="time" value="${s.time}" disabled>
                            <div class="time-option-card disabled">
                                <span class="time-option-time">${s.time}</span>
                                <span class="time-option-status">Penuh</span>
                            </div>
                        </label>
                    `;
                }
            });
            timeContainer.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            timeContainer.innerHTML = '<div style="grid-column: 1 / -1; padding: 1rem; text-align: center; color: #ef4444;">Gagal mengambil data jadwal. Silakan coba lagi.</div>';
        });
}
