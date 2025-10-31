import { useState } from 'react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { Badge } from './ui/badge';
import { Shield, User, Key, Lock } from 'lucide-react';

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

  const authModes = [
    { value: 'current', label: 'Current User', icon: User },
    { value: 'impersonate', label: 'Impersonate', icon: Shield },
    { value: 'bearer', label: 'Bearer Token', icon: Key },
    { value: 'basic', label: 'Basic Auth', icon: Lock },
  ];

  return (
    <div className="space-y-3">
      <div className="grid grid-cols-2 gap-1.5">
        {authModes.map((authMode) => {
          const Icon = authMode.icon;
          const isActive = mode === authMode.value;
          return (
            <Button
              key={authMode.value}
              onClick={() => handleModeChange(authMode.value)}
              variant={isActive ? "default" : "outline"}
              size="sm"
              className={`h-8 text-xs ${isActive ? 'gradient-primary' : ''}`}
            >
              <Icon className="h-3 w-3 mr-1.5" />
              {authMode.label.split(' ')[0]}
            </Button>
          );
        })}
      </div>

      {mode === 'impersonate' && (
        <div className="space-y-1.5">
          <label className="text-xs font-medium text-muted-foreground">User ID</label>
          <Input
            type="number"
            value={impersonateId}
            onChange={(e) => {
              setImpersonateId(e.target.value);
              setTimeout(updateGlobalAuth, 0);
            }}
            placeholder="Enter ID"
            className="h-8 text-xs"
          />
        </div>
      )}

      {mode === 'bearer' && (
        <div className="space-y-1.5">
          <label className="text-xs font-medium text-muted-foreground">Token</label>
          <Input
            type="text"
            value={bearerToken}
            onChange={(e) => {
              setBearerToken(e.target.value);
              setTimeout(updateGlobalAuth, 0);
            }}
            placeholder="Bearer token"
            className="h-8 text-xs font-mono"
          />
        </div>
      )}

      {mode === 'basic' && (
        <div className="space-y-2">
          <div className="space-y-1.5">
            <label className="text-xs font-medium text-muted-foreground">Username</label>
            <Input
              type="text"
              value={basicUser}
              onChange={(e) => {
                setBasicUser(e.target.value);
                setTimeout(updateGlobalAuth, 0);
              }}
              placeholder="Username"
              className="h-8 text-xs"
            />
          </div>
          <div className="space-y-1.5">
            <label className="text-xs font-medium text-muted-foreground">Password</label>
            <Input
              type="password"
              value={basicPass}
              onChange={(e) => {
                setBasicPass(e.target.value);
                setTimeout(updateGlobalAuth, 0);
              }}
              placeholder="Password"
              className="h-8 text-xs"
            />
          </div>
        </div>
      )}
    </div>
  );
}
