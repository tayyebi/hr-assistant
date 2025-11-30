
import React, { useState } from 'react';
import { authenticate } from '../services/storage';
import { User } from '../types';

interface LoginProps {
    onLogin: (user: User) => void;
}

const Login: React.FC<LoginProps> = ({ onLogin }) => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        const user = authenticate(email, password);
        if (user) {
            onLogin(user);
        } else {
            setError('Invalid email or password.');
        }
    };

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col justify-center items-center p-4">
            <div className="max-w-md w-full mx-auto">
                <h1 className="text-3xl font-bold text-center text-blue-600 mb-2">
                    HR Assistant
                </h1>
                <p className="text-center text-gray-500 mb-8">Administration Console</p>

                <div className="bg-white p-8 rounded-xl shadow-md border border-gray-100">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label className="text-sm font-bold text-gray-600 block mb-2">Email Address</label>
                            <input
                                type="email"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                className="w-full p-3 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>
                        <div>
                            <label className="text-sm font-bold text-gray-600 block mb-2">Password</label>
                            <input
                                type="password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                className="w-full p-3 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>
                        
                        {error && <p className="text-red-500 text-sm text-center">{error}</p>}

                        <div>
                            <button type="submit" className="w-full py-3 mt-4 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                                Sign In
                            </button>
                        </div>
                    </form>
                </div>
                <div className="text-center text-sm text-gray-400 mt-6 p-4 bg-gray-100 rounded-lg">
                    <p className="font-semibold">Demo Credentials:</p>
                    <p><b>Sys Admin:</b> sysadmin@corp.com / password</p>
                    <p><b>Tenant Admin:</b> admin@defaultcorp.com / password</p>
                </div>
            </div>
        </div>
    );
};

export default Login;
