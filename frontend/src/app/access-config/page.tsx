'use client'

import { useEffect, useState } from 'react'
import { Button } from '@/components/ui/button'
import axios from 'axios'

type AccessRequest = {
  id: number
  user: { name: string }
  ip_address: string
  hostname: string
  reason: string
  status: string
  created_at: string
}

export default function PrivilegeAccessPage() {
  const [requests, setRequests] = useState<AccessRequest[]>([])

  const fetchRequests = async () => {
    const res = await axios.get('/api/privilege-access')
    setRequests(res.data)
  }

  const handleApprove = async (id: number) => {
    await axios.post(`/api/privilege-access/${id}/approve`)
    fetchRequests()
  }

  const handleDecline = async (id: number) => {
    await axios.post(`/api/privilege-access/${id}/decline`)
    fetchRequests()
  }

  useEffect(() => {
    fetchRequests()
  }, [])

  return (
    <div className="p-6">
      <h1 className="text-xl font-bold mb-4">Privilege Access Requests</h1>
      <div className="space-y-4">
        {requests.map((req) => (
          <div
            key={req.id}
            className="border p-4 rounded-xl shadow-md bg-white space-y-2"
          >
            <div><strong>User:</strong> {req.user?.name}</div>
            <div><strong>IP:</strong> {req.ip_address}</div>
            <div><strong>Host:</strong> {req.hostname}</div>
            <div><strong>Reason:</strong> {req.reason}</div>
            <div><strong>Status:</strong> {req.status}</div>
            <div className="flex gap-2">
              <Button onClick={() => handleApprove(req.id)} variant="outline">
                Approve
              </Button>
              <Button onClick={() => handleDecline(req.id)} variant="destructive">
                Decline
              </Button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
