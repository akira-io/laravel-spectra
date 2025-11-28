import { useState, useEffect } from 'react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './ui/tabs';
import { Badge } from './ui/badge';
import { Button } from './ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from './ui/dialog';
import { Copy, CheckCheck, Clock, HardDrive, History as HistoryIcon, Trash2 } from 'lucide-react';
import { Prism as SyntaxHighlighter } from 'react-syntax-highlighter';
import { vscDarkPlus, vs } from 'react-syntax-highlighter/dist/esm/styles/prism';
import { useNavigationStore } from '../stores/navigationStore';

interface Props {
  response: any;
  endpoint?: any;
}

export default function ResponseViewer({ response, endpoint }: Props) {
  const allHistory = useNavigationStore((state) => state.responseHistory);
  const clearHistory = useNavigationStore((state) => state.clearHistory);
  const responseHistory = endpoint && allHistory[endpoint.uri] ? allHistory[endpoint.uri] : [];
  const [tab, setTab] = useState<'json' | 'raw' | 'headers' | 'history'>('json');
  const [previousResponse, setPreviousResponse] = useState<any>(null);
  const [copied, setCopied] = useState(false);
  const [isDark, setIsDark] = useState(true);
  const [showClearDialog, setShowClearDialog] = useState(false);

  useEffect(() => {
    if (response) {
      setPreviousResponse(response);
    }
  }, [response]);

  useEffect(() => {
    // Detect dark mode
    const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const rootHasDarkClass = document.documentElement.classList.contains('dark');
    setIsDark(rootHasDarkClass || darkModeMediaQuery.matches);
    
    const observer = new MutationObserver(() => {
      setIsDark(document.documentElement.classList.contains('dark'));
    });
    
    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['class']
    });
    
    return () => observer.disconnect();
  }, []);

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const getStatusColor = (status: number) => {
    if (status >= 200 && status < 300) return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
    if (status >= 300 && status < 400) return 'bg-blue-500/10 text-blue-500 border-blue-500/20';
    if (status >= 400 && status < 500) return 'bg-amber-500/10 text-amber-500 border-amber-500/20';
    return 'bg-red-500/10 text-red-500 border-red-500/20';
  };

  const getCurrentContent = () => {
    if (tab === 'json') {
      // JSON tab shows only the body content
      return JSON.stringify(response.body?.data || response.body || response.data || response, null, 2);
    }
    if (tab === 'headers') return JSON.stringify(response.headers, null, 2);
    return JSON.stringify(response, null, 2);
  };

  return (
    <div className="flex flex-col h-full">
      <div className="flex items-center justify-between p-4 border-b border-border/50 bg-card/50 backdrop-blur-sm">
        <div className="flex items-center gap-3">
          <Badge className={`${getStatusColor(response.status)} font-bold px-3 py-1`}>
            {response.status}
          </Badge>
          <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
            <Clock className="h-3.5 w-3.5" />
            <span className="font-medium">{response.time_ms}ms</span>
          </div>
          <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
            <HardDrive className="h-3.5 w-3.5" />
            <span className="font-medium">{(response.size_bytes / 1024).toFixed(2)}KB</span>
          </div>
        </div>

        <Button
          onClick={() => copyToClipboard(getCurrentContent())}
          variant="outline"
          size="sm"
          className="gap-2"
        >
          {copied ? (
            <>
              <CheckCheck className="h-4 w-4" />
              Copied!
            </>
          ) : (
            <>
              <Copy className="h-4 w-4" />
              Copy
            </>
          )}
        </Button>
      </div>

      <Tabs value={tab} onValueChange={(v) => setTab(v as any)} className="flex-1 flex flex-col overflow-hidden">
        <TabsList className="mx-4 mt-4">
          <TabsTrigger value="json">JSON</TabsTrigger>
          <TabsTrigger value="raw">Raw</TabsTrigger>
          <TabsTrigger value="headers">Headers</TabsTrigger>
          <TabsTrigger value="history" className="gap-1.5">
            <HistoryIcon className="h-3 w-3" />
            History {responseHistory.length > 0 && `(${responseHistory.length})`}
          </TabsTrigger>
        </TabsList>

        <div className="flex-1 overflow-auto">
          <TabsContent value="json" className="m-4 mt-2" forceMount hidden={tab !== 'json'}>
            <div className="rounded-lg border border-border/50 overflow-hidden">
              <SyntaxHighlighter
                language="json"
                style={isDark ? vscDarkPlus : vs}
                customStyle={{
                  margin: 0,
                  borderRadius: 0,
                  fontSize: '0.75rem',
                  background: isDark ? 'rgb(30, 30, 30)' : 'rgb(250, 250, 250)',
                }}
                showLineNumbers={false}
                wrapLines={true}
              >
                {JSON.stringify(response.body?.data || response.body || response.data || response, null, 2)}
              </SyntaxHighlighter>
            </div>
          </TabsContent>
          <TabsContent value="raw" className="m-4 mt-2" forceMount hidden={tab !== 'raw'}>
            <div className="rounded-lg border border-border/50 overflow-hidden">
              <SyntaxHighlighter
                language="json"
                style={isDark ? vscDarkPlus : vs}
                customStyle={{
                  margin: 0,
                  borderRadius: 0,
                  fontSize: '0.75rem',
                  background: isDark ? 'rgb(30, 30, 30)' : 'rgb(250, 250, 250)',
                }}
                showLineNumbers={false}
                wrapLines={true}
              >
                {JSON.stringify(response, null, 2)}
              </SyntaxHighlighter>
            </div>
          </TabsContent>
          <TabsContent value="headers" className="m-4 mt-2" forceMount hidden={tab !== 'headers'}>
            <div className="rounded-lg border border-border/50 overflow-hidden">
              <SyntaxHighlighter
                language="json"
                style={isDark ? vscDarkPlus : vs}
                customStyle={{
                  margin: 0,
                  borderRadius: 0,
                  fontSize: '0.75rem',
                  background: isDark ? 'rgb(30, 30, 30)' : 'rgb(250, 250, 250)',
                }}
                showLineNumbers={true}
                wrapLines={true}
              >
                {JSON.stringify(response.headers || {}, null, 2)}
              </SyntaxHighlighter>
            </div>
          </TabsContent>
          <TabsContent value="history" className="m-4 mt-2" forceMount hidden={tab !== 'history'}>
            {responseHistory.length > 0 ? (
              <div className="space-y-3">
                <div className="flex items-center justify-between pb-2 border-b border-border/50">
                  <p className="text-xs text-muted-foreground">
                    {responseHistory.length} request{responseHistory.length !== 1 ? 's' : ''} in history
                  </p>
                  <Button
                    onClick={() => setShowClearDialog(true)}
                    variant="outline"
                    size="sm"
                    className="h-7 text-xs gap-1.5 text-destructive hover:text-destructive hover:bg-destructive/10"
                  >
                    <Trash2 className="h-3 w-3" />
                    Clear History
                  </Button>
                </div>

                <Dialog open={showClearDialog} onOpenChange={setShowClearDialog}>
                  <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                      <DialogTitle className="flex items-center gap-2">
                        <Trash2 className="h-5 w-5 text-destructive" />
                        Clear History
                      </DialogTitle>
                      <DialogDescription className="pt-2">
                        Are you sure you want to clear the history for this endpoint?
                        <div className="mt-3 p-3 rounded-lg bg-muted/50 border border-border/50">
                          <p className="text-xs font-mono text-foreground">{endpoint?.uri}</p>
                        </div>
                        <p className="mt-3 text-xs">
                          This will permanently delete <strong>{responseHistory.length} request{responseHistory.length !== 1 ? 's' : ''}</strong> from history. This action cannot be undone.
                        </p>
                      </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="flex-row gap-2 sm:justify-end">
                      <Button
                        type="button"
                        variant="outline"
                        onClick={() => setShowClearDialog(false)}
                        className="flex-1 sm:flex-none"
                      >
                        Cancel
                      </Button>
                      <Button
                        type="button"
                        variant="destructive"
                        onClick={() => {
                          if (endpoint) {
                            clearHistory(endpoint.uri);
                            setShowClearDialog(false);
                          }
                        }}
                        className="flex-1 sm:flex-none gap-2"
                      >
                        <Trash2 className="h-4 w-4" />
                        Clear History
                      </Button>
                    </DialogFooter>
                  </DialogContent>
                </Dialog>
                
                {responseHistory.map((item, index) => {
                  const date = new Date(item.timestamp);
                  const isLatest = index === 0;
                  
                  return (
                    <div
                      key={item.id}
                      className={`rounded-lg border p-4 ${
                        isLatest
                          ? 'border-primary/50 bg-primary/5'
                          : 'border-border/50 bg-card/30'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-3">
                        <div className="flex items-center gap-2">
                          {isLatest && (
                            <Badge variant="outline" className="text-xs bg-primary/10 text-primary border-primary/20">
                              Latest
                            </Badge>
                          )}
                          <Badge className={`${getStatusColor(item.response.status)} text-xs`}>
                            {item.response.status}
                          </Badge>
                        </div>
                        <div className="flex items-center gap-3 text-xs text-muted-foreground">
                          <div className="flex items-center gap-1">
                            <Clock className="h-3 w-3" />
                            {item.response.time_ms}ms
                          </div>
                          <div className="flex items-center gap-1">
                            <HardDrive className="h-3 w-3" />
                            {(item.response.size_bytes / 1024).toFixed(2)}KB
                          </div>
                        </div>
                      </div>
                      
                      <div className="text-xs text-muted-foreground mb-3">
                        <div className="flex items-center gap-1.5">
                          <Clock className="h-3 w-3" />
                          <span>
                            {date.toLocaleDateString()} at {date.toLocaleTimeString()}
                          </span>
                        </div>
                      </div>
                      
                      <div className="rounded border border-border/50 overflow-hidden">
                        <SyntaxHighlighter
                          language="json"
                          style={isDark ? vscDarkPlus : vs}
                          customStyle={{
                            margin: 0,
                            borderRadius: 0,
                            fontSize: '0.7rem',
                            background: isDark ? 'rgb(30, 30, 30)' : 'rgb(250, 250, 250)',
                            maxHeight: '200px',
                          }}
                          showLineNumbers={false}
                          wrapLines={true}
                        >
                          {JSON.stringify(item.response.body?.data || item.response.body || item.response, null, 2)}
                        </SyntaxHighlighter>
                      </div>
                    </div>
                  );
                })}
              </div>
            ) : (
              <div className="flex flex-col items-center justify-center py-12 text-center">
                <HistoryIcon className="h-12 w-12 text-muted-foreground/50 mb-3" />
                <p className="text-sm font-medium text-muted-foreground">No history yet</p>
                <p className="text-xs text-muted-foreground/70 mt-1">Execute requests to see history</p>
              </div>
            )}
          </TabsContent>
        </div>
      </Tabs>
    </div>
  );
}
