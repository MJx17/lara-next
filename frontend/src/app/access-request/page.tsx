'use client';

import React, { useEffect, useState } from 'react';
import {
  fetchPrivilegeAccessRequests,
  approvePrivilegeAccessRequest,
  declinePrivilegeAccessRequest,
  type PrivilegeAccessRequest,
} from '@/lib/login-request';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableCell,
  TableHead,
} from '@/components/ui/table';
import { toast } from 'react-toastify';
import {api} from '@/lib/axios'; // your configured axios

export default function AccessRequestPage() {
  const [requests, setRequests] = useState<PrivilegeAccessRequest[]>([]);
  const [loading, setLoading] = useState(false);

  const checkAuth = async () => {
    try {
      const res = await api.get('/user');
      console.log('Logged in user:', res.data);
    } catch (err) {
      toast.error('Not authenticated');
    }
  };

  const loadRequests = async () => {
    setLoading(true);
    try {
      const data = await fetchPrivilegeAccessRequests();
      setRequests(data);
    } catch {
      toast.error('Failed to fetch requests');
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (id: number) => {
    try {
      await approvePrivilegeAccessRequest(id);
      toast.success('Approved');
      loadRequests();
    } catch {
      toast.error('Approval failed');
    }
  };

  const handleDecline = async (id: number) => {
    try {
      await declinePrivilegeAccessRequest(id);
      toast.info('Declined');
      loadRequests();
    } catch {
      toast.error('Decline failed');
    }
  };

  useEffect(() => {
    checkAuth();     // <-- check if authenticated
    loadRequests();  // <-- then load login requests
  }, []);

  return (
    <Card className="mt-10 p-4">
      <CardContent>
        <h1 className="text-2xl font-bold mb-6">User Access Requests</h1>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>User</TableHead>
              <TableHead>Reason</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Requested At</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {requests.length === 0 ? (
              <TableRow>
                <TableCell colSpan={5} className="text-center">
                  {loading ? 'Loading...' : 'No pending requests'}
                </TableCell>
              </TableRow>
            ) : (
              requests.map((req) => (
                <TableRow key={req.id}>
                  <TableCell className="font-medium">{req.email}</TableCell>
                  <TableCell>{req.reason ?? 'N/A'}</TableCell>
                  <TableCell className="capitalize">{req.status}</TableCell>
                  <TableCell>{new Date(req.created_at).toLocaleString()}</TableCell>
                  <TableCell className="space-x-2">
                    <Button
                      onClick={() => handleApprove(req.id)}
                      disabled={req.status !== 'pending'}
                    >
                      Approve
                    </Button>
                    <Button
                      variant="destructive"
                      onClick={() => handleDecline(req.id)}
                      disabled={req.status !== 'pending'}
                    >
                      Decline
                    </Button>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
}
