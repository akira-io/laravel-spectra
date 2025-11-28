import { useState } from 'react';
import ky from 'ky';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './ui/tabs';
import { Badge } from './ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { PlayIcon, Loader2, RefreshCw } from 'lucide-react';
import CodeEditor from './CodeEditor';
import CookiePanel from './CookiePanel';
import { useNavigationStore } from '../stores/navigationStore';

interface Props {
  endpoint: any;
  executeUrl: string;
  onResponse: (response: any) => void;
  cookiesUrl?: string;
}

export default function RequestBuilder({ endpoint, executeUrl, onResponse, cookiesUrl }: Props) {
  const { bodyMode, setBodyMode, addToHistory } = useNavigationStore();
  const [method, setMethod] = useState(endpoint.methods.filter((m: string) => !['HEAD', 'OPTIONS'].includes(m))[0]);
  const [pathParams, setPathParams] = useState<Record<string, string>>({});
  const [query, setQuery] = useState<Record<string, string>>({});
  const [headers, setHeaders] = useState<Record<string, string>>({ 'Accept': 'application/json' });

  const generateRandomValue = (key: string, meta: any) => {
    // Generate random values based on field name patterns
    const lowerKey = key.toLowerCase();
    
    if (lowerKey.includes('email')) {
      const randomNum = Math.floor(Math.random() * 10000);
      return `user${randomNum}@example.com`;
    }
    
    if (lowerKey.includes('name')) {
      const names = ['John Doe', 'Jane Smith', 'Alice Johnson', 'Bob Wilson', 'Emma Brown', 'Michael Davis'];
      return names[Math.floor(Math.random() * names.length)];
    }
    
    if (lowerKey.includes('password')) {
      return `password${Math.floor(Math.random() * 1000)}`;
    }
    
    if (lowerKey.includes('phone')) {
      return `+1${Math.floor(Math.random() * 9000000000 + 1000000000)}`;
    }
    
    if (lowerKey.includes('url') || lowerKey.includes('website')) {
      return `https://example${Math.floor(Math.random() * 100)}.com`;
    }
    
    // Use meta.example if available, otherwise generate based on type
    if (meta.example !== undefined && meta.example !== null) {
      // Modify the example to make it unique
      if (typeof meta.example === 'string') {
        const randomNum = Math.floor(Math.random() * 10000);
        return meta.example.includes('@') ? 
          meta.example.replace(/\d+/, randomNum.toString()) : 
          `${meta.example}${randomNum}`;
      }
      return meta.example;
    }
    
    if (meta.type === 'integer') {
      return Math.floor(Math.random() * 1000);
    }
    if (meta.type === 'boolean') {
      return Math.random() > 0.5;
    }
    if (meta.type === 'array') {
      return [];
    }
    
    return `test_${Math.floor(Math.random() * 10000)}`;
  };

  const initializeBody = () => {
    if (endpoint.body_parameters && Object.keys(endpoint.body_parameters).length > 0) {
      const bodyObj: Record<string, any> = {};
      let passwordValue: string | null = null;
      
      // First pass: generate all values and capture password
      Object.entries(endpoint.body_parameters).forEach(([key, meta]: [string, any]) => {
        const value = generateRandomValue(key, meta);
        bodyObj[key] = value;
        
        // Capture password value for confirmation
        if (key.toLowerCase() === 'password') {
          passwordValue = value as string;
        }
      });
      
      // Second pass: set password_confirmation to match password
      Object.keys(bodyObj).forEach(key => {
        const lowerKey = key.toLowerCase();
        if ((lowerKey === 'password_confirmation' || lowerKey === 'passwordconfirmation') && passwordValue) {
          bodyObj[key] = passwordValue;
        }
      });
      
      return JSON.stringify(bodyObj, null, 2);
    }
    return '';
  };
  
  const regenerateBody = () => {
    setBody(initializeBody());
  };
  
  const [body, setBody] = useState(initializeBody());
  const [loading, setLoading] = useState(false);

  const handleExecute = async () => {
    setLoading(true);

    try {
      const authMode = (window as any).spectraAuthMode || 'current';
      const authData = (window as any).spectraAuthData || {};

      // Auto-login for Basic Auth to get token and cookies
      if (authMode === 'basic' && authData.basic_user && authData.basic_pass) {
        try {
          const loginResponse = await ky.post('/api/auth/spectra-login', {
            json: {
              email: authData.basic_user,
              password: authData.basic_pass,
            },
            credentials: 'include',
          });

          const loginResult = await loginResponse.json<any>();

          if (loginResult.success && loginResult.token) {
            // Auto-set Bearer token in headers
            const updatedHeaders = {
              ...headers,
              'Authorization': `Bearer ${loginResult.token}`,
            };
            setHeaders(updatedHeaders);
            
            // Store token for future requests
            (window as any).spectraToken = loginResult.token;
            
            // Show success message
            console.log('✅ Auto-authenticated:', loginResult.user.name);
            
            // Trigger cookie panel refresh
            window.dispatchEvent(new CustomEvent('spectra-cookies-updated'));
          }
        } catch (loginError) {
          console.warn('Auto-login failed, continuing with Basic Auth');
        }
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      const result = await ky
        .post(executeUrl, {
          credentials: 'include', // Include cookies
          headers: {
            'X-CSRF-TOKEN': csrfToken || '',
          },
          json: {
            endpoint: endpoint.uri,
            method,
            path_params: pathParams,
            query,
            headers,
            body: body ? JSON.parse(body) : null,
            auth_mode: authMode,
            ...authData,
          },
        })
        .json();

      onResponse(result);
      
      // Add to history
      addToHistory(endpoint, result);
      
      // Auto-regenerate body for POST/PUT/PATCH on success
      if (['POST', 'PUT', 'PATCH'].includes(method) && result.status >= 200 && result.status < 300) {
        setTimeout(() => {
          regenerateBody();
        }, 300); // Small delay to see the success first
      }
    } catch (error: any) {
      onResponse({
        status: error.response?.status || 500,
        body: { error: error.message },
        time_ms: 0,
        size_bytes: 0,
        headers: {},
      });
    } finally {
      setLoading(false);
    }
  };

  const parameterInputs = endpoint.parameters || [];
  
  const getMethodColor = (m: string) => {
    const colors: Record<string, string> = {
      'GET': 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
      'POST': 'bg-blue-500/10 text-blue-500 border-blue-500/20',
      'PUT': 'bg-amber-500/10 text-amber-500 border-amber-500/20',
      'PATCH': 'bg-orange-500/10 text-orange-500 border-orange-500/20',
      'DELETE': 'bg-red-500/10 text-red-500 border-red-500/20',
    };
    return colors[m] || 'bg-gray-500/10 text-gray-500 border-gray-500/20';
  };

  // Extract action label from route name
  const getActionLabel = () => {
    if (!endpoint.name) return null;
    
    // Check URI first for special cases
    if (endpoint.uri.includes('password')) {
      if (endpoint.uri.includes('reset')) {
        return 'Reset Password';
      }
      if (endpoint.uri.includes('forgot')) {
        return 'Forgot Password';
      }
      if (endpoint.uri.includes('password') && !endpoint.uri.includes('reset') && !endpoint.uri.includes('forgot')) {
        return 'Update Password';
      }
    }
    
    const parts = endpoint.name.split('.');
    const action = parts[parts.length - 1];
    
    const labels: Record<string, string> = {
      'index': 'List',
      'show': 'Show',
      'store': 'Create',
      'update': 'Update',
      'destroy': 'Delete',
      'login': 'Login',
      'logout': 'Logout',
      'register': 'Register',
      'email': 'Forgot Password',
      'reset': 'Reset Password',
      'verify': 'Verify Email',
      'resend': 'Resend Verification',
    };
    
    return labels[action] || action
      .split(/[-_]/)
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  };

  const actionLabel = getActionLabel();

  return (
    <Card className="border-border/50 bg-card/50 backdrop-blur-sm">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <div className="space-y-1">
            {actionLabel && (
              <CardTitle className="text-lg font-semibold">{actionLabel}</CardTitle>
            )}
            <CardDescription className="text-sm font-mono text-muted-foreground">{endpoint.uri}</CardDescription>
          </div>
          <Badge className={`${getMethodColor(method)} font-mono font-bold px-2.5 py-1 pointer-events-none`}>
            {method}
          </Badge>
        </div>
      </CardHeader>
      <CardContent className="space-y-3 pt-0">
        <Tabs defaultValue="body" className="w-full">
          <TabsList className="grid w-full grid-cols-4 h-8">
            <TabsTrigger value="body" disabled={['GET', 'HEAD'].includes(method)} className="text-xs">Body</TabsTrigger>
            <TabsTrigger value="params" className="text-xs">Params</TabsTrigger>
            <TabsTrigger value="headers" className="text-xs">Headers</TabsTrigger>
            <TabsTrigger value="cookies" className="text-xs">Cookies</TabsTrigger>
          </TabsList>

          <TabsContent value="body" className="space-y-1.5 mt-3">
            <div className="flex items-center justify-between mb-2">
              <div className="flex items-center gap-2">
                <h3 className="text-xs font-semibold">Request Body</h3>
                <div className="inline-flex rounded-md bg-muted p-0.5 gap-0.5">
                  <button
                    type="button"
                    onClick={() => setBodyMode('json')}
                    className={`px-2.5 py-0.5 text-xs font-medium rounded transition-all cursor-pointer ${
                      bodyMode === 'json'
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground'
                    }`}
                  >
                    JSON
                  </button>
                  <button
                    type="button"
                    onClick={() => setBodyMode('form')}
                    className={`px-2.5 py-0.5 text-xs font-medium rounded transition-all cursor-pointer ${
                      bodyMode === 'form'
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground'
                    }`}
                  >
                    Form
                  </button>
                </div>
              </div>
              {endpoint.body_parameters && Object.keys(endpoint.body_parameters).length > 0 && (
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={regenerateBody}
                  className="h-6 text-xs gap-1 hover:bg-transparent hover:text-current"
                >
                  <RefreshCw className="h-3 w-3" />
                  Regenerate
                </Button>
              )}
            </div>
            
            {bodyMode === 'json' ? (
              <CodeEditor
                value={body}
                onChange={setBody}
                language="json"
                placeholder='{"key": "value"}'
                minHeight="300px"
              />
            ) : (
              <div className="space-y-2">
                {endpoint.body_parameters && Object.keys(endpoint.body_parameters).length > 0 ? (
                  Object.entries(endpoint.body_parameters).map(([key, meta]: [string, any]) => {
                    const currentValue = (() => {
                      try {
                        const parsed = JSON.parse(body);
                        return parsed[key] || '';
                      } catch {
                        return '';
                      }
                    })();
                    
                    return (
                      <div key={key} className="space-y-1">
                        <label className="text-xs font-medium text-muted-foreground">
                          {key} {meta.required && <span className="text-destructive">*</span>}
                        </label>
                        <Input
                          value={currentValue}
                          onChange={(e) => {
                            try {
                              const parsed = JSON.parse(body || '{}');
                              parsed[key] = e.target.value;
                              setBody(JSON.stringify(parsed, null, 2));
                            } catch {
                              setBody(JSON.stringify({ [key]: e.target.value }, null, 2));
                            }
                          }}
                          placeholder={meta.description || key}
                          className="h-8 text-xs"
                        />
                      </div>
                    );
                  })
                ) : (
                  <p className="text-xs text-muted-foreground">No body parameters defined</p>
                )}
              </div>
            )}
          </TabsContent>

          <TabsContent value="params" className="space-y-3 mt-3">
            {parameterInputs.length > 0 ? (
              <div className="space-y-2.5">
                <h3 className="text-xs font-semibold">Path Parameters</h3>
                {parameterInputs.map((param: any) => (
                  <div key={param.name} className="space-y-1.5">
                    <label className="text-xs font-medium text-muted-foreground">
                      {param.name} {param.required && <span className="text-destructive">*</span>}
                    </label>
                    <Input
                      type="text"
                      value={pathParams[param.name] || ''}
                      onChange={(e) => setPathParams({ ...pathParams, [param.name]: e.target.value })}
                      placeholder={`Enter ${param.name}`}
                      className="font-mono h-8 text-xs"
                    />
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-6 text-muted-foreground text-xs">
                No path parameters required
              </div>
            )}
            
            <div className="space-y-1.5">
              <h3 className="text-xs font-semibold">Query Parameters</h3>
              <textarea
                value={JSON.stringify(query, null, 2)}
                onChange={(e) => {
                  try {
                    setQuery(JSON.parse(e.target.value));
                  } catch {}
                }}
                className="w-full px-2.5 py-2 text-xs border border-input rounded-md bg-background text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-ring h-20 resize-none"
                placeholder='{"key": "value"}'
              />
            </div>
          </TabsContent>

          <TabsContent value="headers" className="space-y-1.5 mt-3">
            <h3 className="text-xs font-semibold">Request Headers</h3>
            <textarea
              value={JSON.stringify(headers, null, 2)}
              onChange={(e) => {
                try {
                  setHeaders(JSON.parse(e.target.value));
                } catch {}
              }}
              className="w-full px-2.5 py-2 text-xs border border-input rounded-md bg-background text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-ring h-32 resize-none"
            />
          </TabsContent>

          <TabsContent value="cookies" className="space-y-1.5 mt-3">
            {cookiesUrl ? (
              <CookiePanel cookiesUrl={cookiesUrl} />
            ) : (
              <div className="text-center py-6 text-muted-foreground text-xs">
                Cookies URL not configured
              </div>
            )}
          </TabsContent>
        </Tabs>

        {/* Method selector outside tabs if multiple methods available */}
        {endpoint.methods.filter((m: string) => !['HEAD', 'OPTIONS'].includes(m)).length > 1 && (
          <div className="space-y-2">
            <h3 className="text-xs font-semibold">HTTP Method</h3>
            <div className="grid grid-cols-2 gap-1.5">
              {endpoint.methods
                .filter((m: string) => !['HEAD', 'OPTIONS'].includes(m))
                .map((m: string) => (
                  <Button
                    key={m}
                    variant={method === m ? "default" : "outline"}
                    size="sm"
                    className={`font-mono font-bold text-xs h-8 ${method === m ? getMethodColor(m) : ''}`}
                    onClick={() => setMethod(m)}
                  >
                    {m}
                  </Button>
                ))}
            </div>
          </div>
        )}

        <Button
          onClick={handleExecute}
          disabled={loading}
          className="w-full gradient-primary text-white font-semibold shine-effect h-9"
        >
          {loading ? (
            <>
              <Loader2 className="mr-2 h-3.5 w-3.5 animate-spin" />
              Executing...
            </>
          ) : (
            <>
              <PlayIcon className="mr-2 h-3.5 w-3.5" />
              Execute Request
            </>
          )}
        </Button>
      </CardContent>
    </Card>
  );
}
