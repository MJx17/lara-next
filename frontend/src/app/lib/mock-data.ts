export const mockLoginRequests = [
    {
      id: 1,
      user: 'jdoe',
      reason: 'Needs access to server for backup checks',
      status: 'pending',
      created_at: '2025-07-16T09:30:00Z',
    },
    {
      id: 2,
      user: 'asmith',
      reason: 'Running maintenance scripts for audit',
      status: 'approved',
      created_at: '2025-07-15T13:10:00Z',
    },
    {
      id: 3,
      user: 'mjborja',
      reason: 'Remote login request from Linux box',
      status: 'declined',
      created_at: '2025-07-14T18:20:00Z',
    },
    {
      id: 4,
      user: 'rgarcia',
      reason: 'Investigating network slowdown',
      status: 'pending',
      created_at: '2025-07-16T07:15:00Z',
    },
  ];
  