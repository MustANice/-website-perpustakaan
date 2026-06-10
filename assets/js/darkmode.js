// darkmode.js — cukup include sekali di sidebar atau setiap halaman

// Apply dark mode SECEPATNYA saat halaman load (sebelum render)
(function() {
  if (localStorage.getItem('darkMode') === '1') {
    document.documentElement.classList.add('dark-mode');
    document.body && document.body.classList.add('dark-mode');
  }
})();

// Tunggu DOM siap
document.addEventListener('DOMContentLoaded', function() {
  // Apply ke body juga
  if (localStorage.getItem('darkMode') === '1') {
    document.body.classList.add('dark-mode');
    const icon = document.getElementById('darkIcon');
    if (icon) icon.textContent = '☀️';
  }
});

// Toggle dark mode
function toggleDarkMode() {
  const isDark = document.body.classList.toggle('dark-mode');
  const icon   = document.getElementById('darkIcon');
  if (icon) icon.textContent = isDark ? '☀️' : '🌙';
  localStorage.setItem('darkMode', isDark ? '1' : '0');
}

// Toggle notifikasi dropdown
function toggleNotif(e) {
  e.stopPropagation();
  const dd = document.getElementById('notifDropdown');
  if (dd) dd.classList.toggle('show');
}

// Tutup notifikasi kalau klik luar
document.addEventListener('click', function(e) {
  const dd = document.getElementById('notifDropdown');
  const wrap = document.querySelector('.notif-wrap');
  if (dd && wrap && !wrap.contains(e.target)) {
    dd.classList.remove('show');
  }
});

// Tandai semua dibaca
function tandaiDibaca() {
  fetch('tandai_dibaca.php', { method:'POST' })
    .then(() => {
      document.querySelectorAll('.notif-item.unread').forEach(el => {
        el.classList.remove('unread');
      });
      const badge = document.getElementById('notifBadge');
      if (badge) badge.remove();
    });
}
