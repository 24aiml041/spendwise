/* ================================================
   sidebar.js — Injects the sidebar into app pages
   ================================================ */

function buildSidebar(activePage) {
  const sidebarHTML = `
    <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-logo">Spendwise<span class="logo-dot"></span></div>
      <div class="sidebar-user">
        <div class="user-avatar" id="sidebarAvatar">J</div>
        <div>
          <div class="user-name"  id="sidebarName">Loading…</div>
          <div class="user-email" id="sidebarEmail"></div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a class="nav-item" data-page="dashboard"   href="dashboard.html"><span class="nav-icon">📊</span>Dashboard</a>
        <a class="nav-item" data-page="add-expense"  href="add-expense.html"><span class="nav-icon">➕</span>Add Expense</a>
        <a class="nav-item" data-page="history"      href="history.html"><span class="nav-icon">📋</span>Expense History</a>
        <div class="nav-section">Analytics</div>
        <a class="nav-item" data-page="reports"     href="reports.html"><span class="nav-icon">📈</span>Monthly Reports</a>
        <a class="nav-item" data-page="analytics"   href="analytics.html"><span class="nav-icon">🍩</span>Category Analysis</a>
        <div class="nav-section">Tools</div>
        <a class="nav-item" data-page="budget"      href="budget.html"><span class="nav-icon">🔔</span>Budget Alerts</a>
        <a class="nav-item" data-page="ai"          href="ai.html"><span class="nav-icon">🤖</span>AI Suggestions</a>
        <div class="nav-section">Account</div>
        <a class="nav-item" data-page="profile"     href="profile.html"><span class="nav-icon">👤</span>Profile</a>
      </nav>
      <div class="sidebar-footer">
        <div class="dark-toggle" onclick="toggleDark()">
          <div class="toggle-pill" id="darkPill"></div>
          <span id="darkLabel">🌙 Dark Mode</span>
        </div>
        <button class="btn btn-ghost" style="width:100%;margin-top:10px" onclick="doLogout()">← Sign Out</button>
      </div>
    </aside>
  `;

  // Insert before <main>
  const main = document.querySelector('main');
  if (main) main.insertAdjacentHTML('beforebegin', sidebarHTML);

  // Insert toast
  if (!document.getElementById('toast')) {
    document.body.insertAdjacentHTML('beforeend', '<div class="toast" id="toast"></div>');
  }
}
