import {api, backend} from './axios';

export interface PrivilegeAccessRequest {
  id: number;
  email: string;
  status: 'pending' | 'approved' | 'declined';
  created_at: string;
  reason: string;
}

export interface PrivilegeAccessLog {
  id: number;
  privilege_access_request_id: number;
  actor_id: number;
  action: string;
  hostname: string;
  ip_address: string;
  status: string;
  created_at: string;
}

// GET /privilege-requests
export async function fetchPrivilegeAccessRequests(): Promise<PrivilegeAccessRequest[]> {
  const response = await backend.get('/privilege-requests');
  return response.data;
}

// POST /privilege-requests
export async function createPrivilegeAccessRequest(email: string, reason: string): Promise<PrivilegeAccessRequest> {
  const response = await backend.post('/privilege-requests', { email, reason });
  return response.data;
}

// POST /privilege-requests/{id}/approve
export async function approvePrivilegeAccessRequest(id: number): Promise<void> {
  await backend.post(`/privilege-requests/${id}/approve`);
}

// POST /privilege-requests/{id}/decline
export async function declinePrivilegeAccessRequest(id: number): Promise<void> {
  await backend.post(`/privilege-requests/${id}/decline`);
}

// GET /privilege-requests/latest
export async function fetchLatestPrivilegeAccessRequest(): Promise<PrivilegeAccessRequest | null> {
  const response = await backend.get('/privilege-requests/latest');
  return response.data;
}

// GET /privilege-requests/{id}/logs
export async function fetchPrivilegeAccessLogs(id: number): Promise<PrivilegeAccessLog[]> {
  const response = await backend.get(`/privilege-requests/${id}/logs`);
  return response.data;
}
