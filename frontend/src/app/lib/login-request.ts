import { api, backend } from './axios';

export interface PrivilegeAccessUser {
  id: number;
  name: string;
  email: string;
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
  request_uuid: string;
}

export interface PrivilegeAccessRequest {
  id: number;
  request_uuid: string;
  user_id: number;
  user: PrivilegeAccessUser;
  type: string;
  reason: string;
  hostname: string;
  ip_address: string;
  requestor_username: string;
  status: 'pending' | 'approved' | 'declined' | 'expired';
  created_at: string;
  updated_at: string;
  logs: PrivilegeAccessLog[];
}



// GET /privilege-requests
export async function fetchPrivilegeAccessRequests(): Promise<PrivilegeAccessRequest[]> {
  const response = await api.get('/privilege-requests');
  return response.data;
}

// POST /privilege-requests
export async function createPrivilegeAccessRequest(email: string, reason: string): Promise<PrivilegeAccessRequest> {
  const response = await api.post('/privilege-requests', { email, reason });
  return response.data;
}


// POST /privilege-requests/{id}/approve
export async function approvePrivilegeAccessRequest(
  request_uuid: string,
  payload: {
    type: string;
    reason: string;
    requestor_username: string;
    host: string;
    ip: string;
    timestamp: string;
  }
): Promise<void> {
  const uuid = request_uuid;
  await api.post(`/privilege-requests/${uuid}/approve`, payload);
}

export async function declinePrivilegeAccessRequest(
  request_uuid: string,
  payload: {
    type: string;
    reason: string;
    requestor_username: string;
    host: string;
    ip: string;
    timestamp: string;
  }
): Promise<void> {
  const uuid = request_uuid;
  await api.post(`/privilege-requests/${uuid}/decline`, payload);
}



// GET /privilege-requests/latest
export async function fetchLatestPrivilegeAccessRequest(): Promise<PrivilegeAccessRequest | null> {
  const response = await api.get('/privilege-requests/latest');
  return response.data;
}

// GET /privilege-requests/{id}/logs
export async function fetchPrivilegeAccessLogs(id: number): Promise<PrivilegeAccessLog[]> {
  const response = await api.get(`/privilege-requests/${id}/logs`);
  return response.data;
}

// GET /privilege-requests/active
export async function fetchActivePrivilegeAccessRequests(): Promise<PrivilegeAccessRequest[]> {
  const response = await api.get('/privilege-requests/active');
  return response.data;
}

