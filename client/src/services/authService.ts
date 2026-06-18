import api from '@/api/axios';

export interface RegisterData {
    role: 'customer' | 'driver';
    surname: string;
    name: string;
    patronymic?: string;
    phone: string;
    password: string;
    password_confirmation: string;
    license_number?: string;
    license_date?: string;
    license_categories?: string[];
    has_loaders?: boolean;
    loaders_count?: number;
}

export const register = async (data: RegisterData) => {
    try {
        const response = await api.post('/user/register', data);
        return response.data;
    } catch (error: any) {
        console.log('Ошибка регистрации:', error.response?.data);
        throw error;
    }
};

export const login = async (phone: string, password: string) => {
    const response = await api.post('/user/login', { phone, password });
    return response.data;
};

export const logout = async () => {
    const response = await api.post('/logout');
    return response.data;
};

