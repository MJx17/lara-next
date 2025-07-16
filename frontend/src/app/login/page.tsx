'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { login } from '@/lib/auth'; // ✅ Make sure lib is in src/lib
import type { LoginData } from '@/lib/types';

export default function LoginPage() {
  const [form, setForm] = useState<LoginData>({ email: '', password: '' });
  const [error, setError] = useState('');
  const router = useRouter();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await login(form);
      router.push('/dashboard');
    } catch (err: unknown) {
      if (typeof err === 'object' && err !== null && 'response' in err) {
        const axiosErr = err as any;
        setError(axiosErr.response?.data?.message || 'Login failed');
      } else {
        setError('Login failed');
      }
    }
  };

  return (
    <div className="max-w-md mx-auto mt-20 p-8 bg-white shadow-lg rounded-lg">
      <h2 className="text-2xl font-bold mb-6 text-center">Login</h2>
      {error && (
        <p className="mb-4 text-sm text-red-500 bg-red-100 p-2 rounded">
          {error}
        </p>
      )}
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label htmlFor="email" className="block mb-1 text-sm font-medium text-gray-700">
            Email
          </label>
          <input
            name="email"
            type="email"
            id="email"
            value={form.email}
            onChange={handleChange}
            required
            className="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>
        <div>
          <label htmlFor="password" className="block mb-1 text-sm font-medium text-gray-700">
            Password
          </label>
          <input
            name="password"
            type="password"
            id="password"
            value={form.password}
            onChange={handleChange}
            required
            className="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>
        <button
          type="submit"
          className="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors"
        >
          Login
        </button>
      </form>
    </div>
  );
}
