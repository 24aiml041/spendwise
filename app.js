// Spendwise — Shared Utilities (no localStorage DB — backend handles data)

const CATEGORIES = [
  { name: '🍕 Food & Dining',  color: '#f07b7b' },
  { name: '🚗 Transport',      color: '#f5c26b' },
  { name: '🛍️ Shopping',      color: '#7c6fcd' },
  { name: '💊 Health',         color: '#6ecfb2' },
  { name: '🎬 Entertainment',  color: '#f093fb' },
  { name: '📚 Education',      color: '#4fc3f7' },
  { name: '🏠 Housing',        color: '#a5d6a7' },
  { name: '💻 Technology',     color: '#90caf9' },
  { name: '✈️ Travel',         color: '#ffb74d' },
  { name: '🎁 Gifts',          color: '#ef9a9a' },
  { name: '🏋️ Fitness',       color: '#80cbc4' },
  { name: '📦 Other',          color: '#bcaaa4' }
];
const CAT_COLOR = {};
CATEGORIES.forEach(c => CAT_COLOR[c.name] = c.color);

const fmt = v => '₹' + Math.round(v).toLocaleString('en-IN');

// Toast
let toastTimer;
function toast(msg, type = '') {
  const el = document.getElementById('toast');
  if (!el) return;
  el.textContent = msg;
  el.className = 'toast show' + (type ? ' ' + type : '');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.className = 'toast', 3200);
}

// Dark mode
function toggleDark() { setDark(document.documentElement.getAttribute('data-dark') !== 'true'); }
function setDark(on) {
  document.documentElement.setAttribute('data-dark', on ? 'true' : 'false');
  const pill = document.getElementById('darkPill'), label = document.getElementById('darkLabel');
  if (pill)  pill.className = 'toggle-pill' + (on ? ' on' : '');
  if (label) label.textContent = on ? '☀️ Light Mode' : '🌙 Dark Mode';
  localStorage.setItem('sw_dark', on ? '1' : '0');
}
function applyStoredDark() { if (localStorage.getItem('sw_dark') === '1') setDark(true); }

// Sidebar toggle
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
}

function doLogout() { API.logout(); }

// Sidebar init
function initSidebar(user, activePage) {
  const avatar = document.getElementById('sidebarAvatar');
  const name   = document.getElementById('sidebarName');
  const email  = document.getElementById('sidebarEmail');
  if (avatar) avatar.textContent = user.name[0].toUpperCase();
  if (name)   name.textContent   = user.name;
  if (email)  email.textContent  = user.email;
  document.querySelectorAll('.nav-item').forEach(el => el.classList.toggle('active', el.dataset.page === activePage));
  applyStoredDark();
}

// Category dropdown
function populateCategorySelect(selectId) {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.innerHTML = '<option value="">Select category</option>' + CATEGORIES.map(c => `<option>${c.name}</option>`).join('');
}

// Pie chart
function drawPie(canvasId, data, total) {
  const canvas = document.getElementById(canvasId); if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height, cx = W/2, cy = H/2, r = Math.min(W,H)/2 - 6;
  ctx.clearRect(0, 0, W, H);
  if (!data.length || total === 0) {
    ctx.beginPath(); ctx.arc(cx,cy,r,0,Math.PI*2);
    ctx.fillStyle = document.documentElement.getAttribute('data-dark')==='true' ? '#342852' : '#e8e0f5';
    ctx.fill(); return;
  }
  let startAngle = -Math.PI/2;
  data.forEach(d => {
    const slice = (d.value/total)*Math.PI*2;
    ctx.beginPath(); ctx.moveTo(cx,cy);
    ctx.arc(cx,cy,r,startAngle+0.04,startAngle+slice-0.04);
    ctx.closePath(); ctx.fillStyle = d.color; ctx.fill();
    startAngle += slice;
  });
  ctx.beginPath(); ctx.arc(cx,cy,r*0.58,0,Math.PI*2);
  ctx.fillStyle = document.documentElement.getAttribute('data-dark')==='true' ? '#1e1830' : '#ffffff';
  ctx.fill();
}

// Expense table HTML
function expenseTable(expenses, showAll) {
  const fmtDate = d => d ? new Date(d+'T00:00:00').toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'}) : '—';
  const feelEmoji = { happy:'😊', neutral:'😐', regret:'😔' };
  return `<table><thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Feeling</th><th>Amount</th>${showAll?'<th>Actions</th>':''}</tr></thead><tbody>
  ${expenses.map(e => `<tr>
    <td style="color:var(--text-muted);font-size:.83rem">${fmtDate(e.date)}</td>
    <td>${e.description||'<span style="color:var(--text-muted);font-style:italic">No description</span>'}</td>
    <td><span class="category-badge" style="background:${(CAT_COLOR[e.category]||'#ccc')}22;color:${CAT_COLOR[e.category]||'var(--lavender-dark)'}">${e.category||'—'}</span></td>
    <td><span class="badge badge-${e.feeling}">${feelEmoji[e.feeling]||''} ${e.feeling?e.feeling[0].toUpperCase()+e.feeling.slice(1):''}</span></td>
    <td class="amount-cell">${fmt(e.amount)}</td>
    ${showAll?`<td class="actions-cell">
      <button class="btn btn-sm btn-ghost" style="padding:6px 12px" onclick="openEditModal(${e.id})">✏️</button>
      <button class="btn btn-danger" onclick="deleteExpense(${e.id})">🗑️</button>
    </td>`:''}
  </tr>`).join('')}
  </tbody></table>`;
}
