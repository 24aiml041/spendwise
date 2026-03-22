// ============================================================
//  Spendwise — Frontend API Client
//  All calls go to the PHP backend. Update BASE_URL below.
// ============================================================

const BASE_URL = 'http://localhost/spendwise_php/backend/api';

// ---- Token helpers ----
const Auth = {
    getToken()       { return localStorage.getItem('sw_token'); },
    setToken(t)      { localStorage.setItem('sw_token', t); },
    getUser()        { return JSON.parse(localStorage.getItem('sw_user') || 'null'); },
    setUser(u)       { localStorage.setItem('sw_user', JSON.stringify(u)); },
    clear()          { localStorage.removeItem('sw_token'); localStorage.removeItem('sw_user'); },
    isLoggedIn()     { return !!this.getToken(); },
};

// ---- Core fetch wrapper ----
async function apiFetch(endpoint, options = {}) {
    const token = Auth.getToken();
    const headers = { 'Content-Type': 'application/json' };
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const res = await fetch(BASE_URL + endpoint, { ...options, headers });
    const data = await res.json();
    return data;
}

// ============================================================
//  AUTH
// ============================================================
const API = {
    // Register new user
    async register(name, email, password) {
        const data = await apiFetch('/auth.php?action=register', {
            method: 'POST',
            body: JSON.stringify({ name, email, password })
        });
        if (data.success) { Auth.setToken(data.token); Auth.setUser(data.user); }
        return data;
    },

    // Login
    async login(email, password) {
        const data = await apiFetch('/auth.php?action=login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
        if (data.success) { Auth.setToken(data.token); Auth.setUser(data.user); }
        return data;
    },

    // Get current user profile
    async getMe() {
        return apiFetch('/auth.php?action=me');
    },

    // Update profile
    async updateProfile(name, email) {
        const data = await apiFetch('/auth.php?action=update', {
            method: 'PUT',
            body: JSON.stringify({ name, email })
        });
        if (data.success) { Auth.setToken(data.token); Auth.setUser(data.user); }
        return data;
    },

    // Change password
    async changePassword(current_password, new_password) {
        return apiFetch('/auth.php?action=password', {
            method: 'PUT',
            body: JSON.stringify({ current_password, new_password })
        });
    },

    // Logout
    logout() { Auth.clear(); window.location.href = 'login.html'; },

    // ============================================================
    //  EXPENSES
    // ============================================================

    // Get expenses (optional filters: feeling, category, month)
    async getExpenses(filters = {}) {
        const params = new URLSearchParams(filters).toString();
        return apiFetch('/expenses.php' + (params ? '?' + params : ''));
    },

    // Add expense
    async addExpense(amount, category, description, feeling, date) {
        return apiFetch('/expenses.php', {
            method: 'POST',
            body: JSON.stringify({ amount, category, description, feeling, date })
        });
    },

    // Update expense
    async updateExpense(id, amount, category, description, feeling, date) {
        return apiFetch('/expenses.php?id=' + id, {
            method: 'PUT',
            body: JSON.stringify({ amount, category, description, feeling, date })
        });
    },

    // Delete expense
    async deleteExpense(id) {
        return apiFetch('/expenses.php?id=' + id, { method: 'DELETE' });
    },

    // ============================================================
    //  BUDGETS
    // ============================================================

    async getBudgets() {
        return apiFetch('/budgets.php');
    },

    async setBudget(category, amount) {
        return apiFetch('/budgets.php', {
            method: 'POST',
            body: JSON.stringify({ category, amount })
        });
    },

    async deleteBudget(category) {
        return apiFetch('/budgets.php?category=' + encodeURIComponent(category), { method: 'DELETE' });
    },

    // ============================================================
    //  STATS
    // ============================================================

    async getSummary() {
        return apiFetch('/stats.php?type=summary');
    },

    async getMonthlyStats() {
        return apiFetch('/stats.php?type=monthly');
    },

    async getCategoryStats(month = '') {
        return apiFetch('/stats.php?type=categories' + (month ? '&month=' + month : ''));
    },
};

// ---- Guard: redirect to login if not authenticated ----
function requireAuth() {
    if (!Auth.isLoggedIn()) { window.location.href = 'login.html'; return false; }
    return true;
}

// ---- Guard: redirect to dashboard if already logged in ----
function redirectIfLoggedIn() {
    if (Auth.isLoggedIn()) window.location.href = 'dashboard.html';
}
