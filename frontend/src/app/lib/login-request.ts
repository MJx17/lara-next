import api from './axios';

export interface LoginRequest {
  id: number;
  email: string;
  status: 'pending' | 'approved' | 'declined';
  created_at: string;
}

export async function fetchLoginRequests(): Promise<LoginRequest[]> {
  const response = await api.get('/login-requests');
  return response.data;
}

export async function approveLoginRequest(id: number): Promise<void> {
  await api.post(`/login-requests/${id}/approve`);
}

export async function declineLoginRequest(id: number): Promise<void> {
  await api.post(`/login-requests/${id}/decline`);
}
