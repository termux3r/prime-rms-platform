const API_BASE = '/api/v1';

let accessToken = null;
let refreshToken = null;
let isRefreshing = false;
let refreshPromise = null;

function getAccessToken() {
  return accessToken || localStorage.getItem('rms_token');
}

function getRefreshToken() {
  return refreshToken || localStorage.getItem('rms_refresh_token');
}

function setTokens(access, refresh) {
  accessToken = access;
  refreshToken = refresh;
  if (access) localStorage.setItem('rms_token', access);
  if (refresh) localStorage.setItem('rms_refresh_token', refresh);
}

function clearTokens() {
  accessToken = null;
  refreshToken = null;
  localStorage.removeItem('rms_token');
  localStorage.removeItem('rms_refresh_token');
}

async function refreshAccessToken() {
  if (isRefreshing) return refreshPromise;

  const rt = getRefreshToken();
  if (!rt) throw new Error('No refresh token');

  isRefreshing = true;
  refreshPromise = (async () => {
    try {
      const res = await fetch(`${API_BASE}/auth/refresh`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ refresh_token: rt })
      });

      if (!res.ok) throw new Error('Refresh failed');

      const data = await res.json();
      const tokens = data.data || data;
      setTokens(tokens.access_token || tokens.token, tokens.refresh_token);
      return tokens.access_token || tokens.token;
    } finally {
      isRefreshing = false;
      refreshPromise = null;
    }
  })();

  return refreshPromise;
}

async function request(path, options = {}) {
  const token = getAccessToken();
  const headers = {
    'Accept': 'application/json',
    ...(options.headers || {})
  };

  if (token) headers['Authorization'] = `Bearer ${token}`;

  if (options.body && !(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
  }

  const config = {
    ...options,
    headers,
    credentials: 'same-origin'
  };

  let response = await fetch(`${API_BASE}${path}`, config);

  if (response.status === 401 && token) {
    try {
      const newToken = await refreshAccessToken();
      config.headers['Authorization'] = `Bearer ${newToken}`;
      response = await fetch(`${API_BASE}${path}`, config);
    } catch (e) {
      clearTokens();
      if (window.toast) window.toast.error('Session expired', 'Please sign in again');
      setTimeout(() => window.location.href = '/login', 1500);
      throw e;
    }
  }

  let data;
  const contentType = response.headers.get('content-type') || '';
  if (contentType.includes('application/json')) {
    data = await response.json().catch(() => ({}));
  } else {
    data = await response.text().catch(() => '');
  }

  if (!response.ok) {
    const message = data?.message || data?.error || `HTTP ${response.status}`;
    const error = new Error(message);
    error.status = response.status;
    error.data = data;
    throw error;
  }

  return data;
}

const api = {
  get: (path, params) => {
    const url = params ? `${path}?${new URLSearchParams(params).toString()}` : path;
    return request(url, { method: 'GET' });
  },

  post: (path, body) => request(path, {
    method: 'POST',
    body: body instanceof FormData ? body : JSON.stringify(body)
  }),

  put: (path, body) => {
    // PHP doesn't populate $_POST/$_FILES for a true PUT with multipart/form-data,
    // so for FormData payloads use CI4's supported POST + _method spoofing.
    if (body instanceof FormData) {
      body.append('_method', 'PUT');
      return request(path, { method: 'POST', body });
    }
    return request(path, {
      method: 'PUT',
      body: JSON.stringify(body)
    });
  },

  patch: (path, body) => request(path, {
    method: 'PATCH',
    body: body instanceof FormData ? body : JSON.stringify(body)
  }),

  delete: (path) => request(path, { method: 'DELETE' }),

  setTokens,
  getAccessToken,
  getRefreshToken,
  clearTokens,

  login: (username, password) => request('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ username, password })
  }),

  logout: () => request('/auth/logout', { method: 'POST' }).finally(clearTokens),

  me: () => request('/auth/me', { method: 'GET' })
};

window.api = api;

if (typeof module !== 'undefined' && module.exports) {
  module.exports = api;
}