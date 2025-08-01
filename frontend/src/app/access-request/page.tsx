'use client';

import React, { useEffect, useState } from 'react';
import {
  fetchActivePrivilegeAccessRequests,
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
import { api } from '@/lib/axios';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';

export default function AccessRequestPage() {
  const [requests, setRequests] = useState<PrivilegeAccessRequest[]>([]);
  const [filtered, setFiltered] = useState<PrivilegeAccessRequest[]>([]);
  const [loading, setLoading] = useState(false);

  const [typeFilter, setTypeFilter] = useState<string>('all');


  const checkAuth = async () => {
    try {
      const res = await api.get('/user');
      console.log('Logged in user:', res.data);
    } catch {
      toast.error('Not authenticated');
    }
  };

  const loadRequests = async () => {
    setLoading(true);
    try {
      const data = await fetchActivePrivilegeAccessRequests();
      setRequests(data);
      setFiltered(data);
    } catch {
      toast.error('Failed to fetch requests');
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (req: PrivilegeAccessRequest) => {
    try {
      await approvePrivilegeAccessRequest(req.request_uuid, {
        type: req.type,
        reason: req.reason,
        requestor_username: req.requestor_username,
        host: req.hostname,
        ip: req.ip_address,
        timestamp: req.created_at,
      });
      toast.success('Approved');
      loadRequests();
    } catch {
      toast.error('Approval failed');
    }
  };

  const handleDecline = async (req: PrivilegeAccessRequest) => {
    try {
      await declinePrivilegeAccessRequest(req.request_uuid, {
        type: req.type,
        reason: req.reason,
        requestor_username: req.requestor_username,
        host: req.hostname,
        ip: req.ip_address,
        timestamp: req.created_at,
      });
      toast.info('Declined');
      loadRequests();
    } catch {
      toast.error('Decline failed');
    }
  };



  const applyFilters = () => {
    let filteredData = [...requests];

    if (typeFilter !== 'all') {
      filteredData = filteredData.filter((req) => req.type === typeFilter);
    }


    setFiltered(filteredData);
  };

  useEffect(() => {
    checkAuth();
    loadRequests();
  }, []);

  useEffect(() => {
    applyFilters();
  }, [typeFilter, requests]);

  return (
    <Card className="mt-10 p-4">
      <CardContent>
        <div className="flex justify-between items-center mb-4 flex-wrap gap-4">
          <h1 className="text-2xl font-bold">Access Guarantor Requests</h1>
          <div className="flex gap-4">
            <Select value={typeFilter} onValueChange={setTypeFilter}>
              <SelectTrigger className="w-[120px]">
                <SelectValue placeholder="Type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Types</SelectItem>
                <SelectItem value="SSH">SSH</SelectItem>
                <SelectItem value="SFTP">SFTP</SelectItem>
              </SelectContent>
            </Select>


          </div>
        </div>

        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Guarantor</TableHead>
              <TableHead>Requestor</TableHead>
              <TableHead>Request UUID</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Reason</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Hostname</TableHead>
              <TableHead>IP</TableHead>
              <TableHead>Requested At</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell colSpan={9} className="text-center">
                  Loading...
                </TableCell>
              </TableRow>
            ) : filtered.length === 0 ? (
              <TableRow>
                <TableCell colSpan={9} className="text-center">
                  No requests found
                </TableCell>
              </TableRow>
            ) : (
              filtered.map((req) => (
                <TableRow key={req.id}>
                  <TableCell>
                    {req.user?.name ?? req.requestor_username ?? 'N/A'}
                  </TableCell>
                  <TableCell>{req.requestor_username}</TableCell>
                  <TableCell>{req.request_uuid}</TableCell>
                  <TableCell>{req.type}</TableCell>
                  <TableCell title={req.reason}>
                    <div className="truncate max-w-[150px]">{req.reason ?? 'N/A'}</div>
                  </TableCell>

                  <TableCell>{req.hostname ?? 'N/A'}</TableCell>
                  <TableCell>{req.ip_address ?? 'N/A'}</TableCell>
                  <TableCell>{new Date(req.created_at).toLocaleString()}</TableCell>
                  <TableCell className="space-x-2">
                    <Button
                      onClick={() => handleApprove(req)}
                      disabled={req.status !== 'pending'}
                    >
                      Approve
                    </Button>
                    <Button
                      variant="destructive"
                      onClick={() => handleDecline(req)}
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
