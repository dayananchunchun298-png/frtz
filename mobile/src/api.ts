import AsyncStorage from '@react-native-async-storage/async-storage';

const TOKEN_KEY = 'pawcare_jwt';

/** Android emulator → host machine; iOS simulator / web → localhost */
export const DEFAULT_API_URL = 'http://127.0.0.1:8000';

export type ApiError = { code: string; message: string; violations?: { field: string; message: string }[] };

let apiBaseUrl = DEFAULT_API_URL;

export function setApiBaseUrl(url: string): void {
  apiBaseUrl = url.replace(/\/$/, '');
}

export function getApiBaseUrl(): string {
  return apiBaseUrl;
}

export async function getToken(): Promise<string | null> {
  return AsyncStorage.getItem(TOKEN_KEY);
}

export async function setToken(token: string | null): Promise<void> {
  if (token) {
    await AsyncStorage.setItem(TOKEN_KEY, token);
  } else {
    await AsyncStorage.removeItem(TOKEN_KEY);
  }
}

export async function apiRequest<T>(
  path: string,
  options: { method?: string; body?: unknown; auth?: boolean } = {},
): Promise<{ success: true; data: T } | { success: false; error: ApiError }> {
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  };

  if (options.auth !== false) {
    const token = await getToken();
    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }
  }

  let response: Response;
  try {
    response = await fetch(`${apiBaseUrl}${path}`, {
      method: options.method ?? 'GET',
      headers,
      body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
    });
  } catch {
    return {
      success: false,
      error: {
        code: 'network_error',
        message: `Cannot reach API at ${apiBaseUrl}. Start Symfony and check the API URL in Profile.`,
      },
    };
  }

  const json = await response.json().catch(() => null);

  if (json && typeof json === 'object' && json.success === true) {
    return { success: true, data: json.data as T };
  }

  const err = json?.error ?? {};
  return {
    success: false,
    error: {
      code: err.code ?? 'request_failed',
      message: err.message ?? `Request failed (${response.status})`,
      violations: err.violations,
    },
  };
}

export async function login(email: string, password: string): Promise<{ ok: true } | { ok: false; error: ApiError }> {
  let response: Response;
  try {
    response = await fetch(`${apiBaseUrl}/api/login`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    });
  } catch {
    return {
      ok: false,
      error: { code: 'network_error', message: `Cannot reach API at ${apiBaseUrl}` },
    };
  }

  const json = await response.json().catch(() => ({}));

  if (response.ok && typeof json.token === 'string') {
    await setToken(json.token);
    return { ok: true };
  }

  const message =
    json.message ?? json.error?.message ?? (response.status === 401 ? 'Invalid email or password.' : 'Login failed.');

  return { ok: false, error: { code: json.code ?? 'login_failed', message: String(message) } };
}

export async function register(email: string, password: string): Promise<{ ok: true } | { ok: false; error: ApiError }> {
  const res = await apiRequest<{ message?: string }>('/api/register', {
    method: 'POST',
    body: { email, password },
    auth: false,
  });

  if (!res.success) {
    return { ok: false, error: res.error };
  }

  return { ok: true };
}

export async function logout(): Promise<void> {
  await setToken(null);
}

export type Product = {
  id: number;
  name: string;
  description?: string;
  price: string;
  stock: number;
  image?: string | null;
};

export type SyncData = {
  syncedAt: string;
  user: { id: number; email: string; isVerified: boolean };
  products: Product[];
  orders: { items: { id: number; status: string; total: string }[] };
  appointments: { items: { id: number; name: string; petType: string; appointmentDate: string }[] };
};
