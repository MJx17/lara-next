import {backend, api} from './axios';
import type { LoginData, RegisterData, User } from './types';
import type { AxiosResponse } from 'axios';

export async function getCsrfCookie(): Promise<void> {
  await backend.get('/sanctum/csrf-cookie');
}

export async function login(data: LoginData): Promise<AxiosResponse> {
  await getCsrfCookie();
  return api.post('/login', data);
}

export async function register(data: RegisterData): Promise<AxiosResponse> {
  await getCsrfCookie();
  return api.post('/register', data);
}

export async function getUser(): Promise<AxiosResponse<User>> {
  return backend.get('/api/user');
}

export async function logout(): Promise<AxiosResponse> {
  return api.post('/logout');
}
