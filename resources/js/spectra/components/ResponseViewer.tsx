import { useState, useEffect } from 'react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './ui/tabs';
import { Badge } from './ui/badge';
import { Button } from './ui/button';
import { Copy, CheckCheck, Clock, HardDrive } from 'lucide-react';
import { Prism as SyntaxHighlighter } from 'react-syntax-highlighter';
import { vscDarkPlus, vs } from 'react-syntax-highlighter/dist/esm/styles/prism';

interface Props {
  response: any;
}

export default function ResponseViewer({ response }: Props) {
  const [tab, setTab] = useState<'json' | 'raw' | 'headers'>('json');
  const [previousResponse, setPreviousResponse] = useState<any>(null);
  const [copied, setCopied] = useState(false);
  const [isDark, setIsDark] = useState(true);

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
        </div>
      </Tabs>
    </div>
  );
}
