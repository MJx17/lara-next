export interface LoginData {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
  // add more if needed based on your Laravel User model
}
