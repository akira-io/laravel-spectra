import { useState } from 'react';

export default function AuthPanel() {
  const [mode, setMode] = useState('current');
  const [impersonateId, setImpersonateId] = useState('');
  const [bearerToken, setBearerToken] = useState('');
  const [basicUser, setBasicUser] = useState('');
  const [basicPass, setBasicPass] = useState('');

  const updateGlobalAuth = () => {
    (window as any).spectraAuthMode = mode;
    (window as any).spectraAuthData = {
      impersonate_id: mode === 'impersonate' ? parseInt(impersonateId) : undefined,
      bearer_token: mode === 'bearer' ? bearerToken : undefined,
      basic_user: mode === 'basic' ? basicUser : undefined,
      basic_pass: mode === 'basic' ? basicPass : undefined,
    };
  };

  const handleModeChange = (newMode: string) => {
    setMode(newMode);
    setTimeout(updateGlobalAuth, 0);
  };

  return (
    <div className="mb-6 p-4 border border-gray-300 dark:border-gray-600 rounded">
      <h3 className="font-semibold mb-3">Authentication</h3>

      <div className="flex gap-2 mb-4">
        {['current', 'impersonate', 'bearer', 'basic'].map((m) => (
          <button
            key={m}
            onClick={() => handleModeChange(m)}
            className={`px-3 py-1 rounded text-sm ${
              mode === m
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600'
            }`}
          >
            {m.charAt(0).toUpperCase() + m.slice(1)}
          </button>
        ))}
      </div>

      {mode === 'impersonate' && (
        <input
          type="number"
          value={impersonateId}
          onChange={(e) => {
            setImpersonateId(e.target.value);
            setTimeout(updateGlobalAuth, 0);
          }}
          placeholder="User ID"
          className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800"
        />
      )}

      {mode === 'bearer' && (
        <input
          type="text"
          value={bearerToken}
          onChange={(e) => {
            setBearerToken(e.target.value);
            setTimeout(updateGlobalAuth, 0);
          }}
          placeholder="Bearer Token"
          className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800"
        />
      )}

      {mode === 'basic' && (
        <div className="space-y-2">
          <input
            type="text"
            value={basicUser}
            onChange={(e) => {
              setBasicUser(e.target.value);
              setTimeout(updateGlobalAuth, 0);
            }}
            placeholder="Username"
            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800"
          />
          <input
            type="password"
            value={basicPass}
            onChange={(e) => {
              setBasicPass(e.target.value);
              setTimeout(updateGlobalAuth, 0);
            }}
            placeholder="Password"
            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800"
          />
        </div>
      )}
    </div>
  );
}
