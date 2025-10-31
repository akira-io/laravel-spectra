import { useState } from 'react';
import { Head } from '@inertiajs/react';
import EndpointTree from '../components/EndpointTree';
import RequestBuilder from '../components/RequestBuilder';
import AuthPanel from '../components/AuthPanel';
import ResponseViewer from '../components/ResponseViewer';
import CookiePanel from '../components/CookiePanel';
import Collections from '../components/Collections';
import { Button } from '../components/ui/button';
import { ScrollArea } from '../components/ui/scroll-area';
import { Sun, Moon, Zap, Cookie, FolderTree, Layout, Shield, FolderOpen } from 'lucide-react';

interface Props {
  schemaUrl: string;
  executeUrl: string;
  cookiesUrl: string;
}

export default function Spectra({ schemaUrl, executeUrl, cookiesUrl }: Props) {
  const [selectedEndpoint, setSelectedEndpoint] = useState<any>(null);
  const [response, setResponse] = useState<any>(null);
  const [darkMode, setDarkMode] = useState(true);

  const handleEndpointSelect = (endpoint: any) => {
    setSelectedEndpoint(endpoint);
    setResponse(null); // Clear response when changing endpoint
  };

  return (
    <>
      <Head title="Spectra - API Inspector" />
      <div className={darkMode ? 'dark' : ''}>
        <div className="min-h-screen flex flex-col bg-gradient-to-br from-background via-background to-muted/20">
          {/* Modern Professional Header */}
          <header className="sticky top-0 z-50 flex justify-between items-center px-6 py-3 bg-card/80 backdrop-blur-xl border-b border-border/50 shadow-lg">
            <div className="flex items-center gap-4">
              <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl gradient-primary shadow-lg shadow-primary/25">
                  <Zap className="h-6 w-6 text-white" />
                </div>
                <div>
                  <h1 className="text-lg font-bold tracking-tight">Spectra</h1>
                  <p className="text-xs text-muted-foreground">Professional API Inspector</p>
                </div>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <Button
                onClick={() => setDarkMode(!darkMode)}
                variant="outline"
                size="icon"
                className="rounded-lg"
              >
                {darkMode ? (
                  <Sun className="h-4 w-4" />
                ) : (
                  <Moon className="h-4 w-4" />
                )}
              </Button>
            </div>
          </header>

          <div className="flex flex-1 overflow-hidden">
            {/* Left Sidebar - Endpoints */}
            <aside className="w-64 border-r border-border/50 bg-card/30 backdrop-blur-sm flex flex-col">
              <div className="p-3 border-b border-border/50 bg-card/50">
                <div className="flex items-center gap-2 mb-0.5">
                  <FolderTree className="h-3.5 w-3.5 text-primary" />
                  <h2 className="text-xs font-semibold">Endpoints</h2>
                </div>
                <p className="text-[10px] text-muted-foreground">Browse routes</p>
              </div>
              <ScrollArea className="flex-1">
                <div className="p-2">
                  <EndpointTree
                    schemaUrl={schemaUrl}
                    onSelect={handleEndpointSelect}
                  />
                </div>
              </ScrollArea>
            </aside>

            {/* Main Content Area - Split Request/Response */}
            <div className="flex-1 flex overflow-hidden">
              {/* Left Side - Request Builder + Auth */}
              <div className="flex-1 flex flex-col border-r border-border/50 bg-background/50 overflow-hidden">
                <ScrollArea className="flex-1">
                  <div className="p-4 space-y-4">
                    {selectedEndpoint ? (
                      <>
                        {/* Auth Section Compact */}
                        <div className="glass-card rounded-lg border border-border/50 p-3">
                          <AuthPanel />
                        </div>
                        
                        {/* Request Builder */}
                        <RequestBuilder
                          key={`${selectedEndpoint.uri}-${selectedEndpoint.methods.join('-')}`}
                          endpoint={selectedEndpoint}
                          executeUrl={executeUrl}
                          onResponse={setResponse}
                        />
                      </>
                    ) : (
                      <div className="flex flex-col items-center justify-center h-full">
                        <div className="glass-card p-8 rounded-2xl text-center max-w-md border border-border/50">
                          <div className="inline-flex p-3 rounded-2xl bg-primary/10 mb-3">
                            <Layout className="h-10 w-10 text-primary" />
                          </div>
                          <h3 className="text-lg font-bold mb-2">Welcome to Spectra</h3>
                          <p className="text-xs text-muted-foreground leading-relaxed">
                            Select an API endpoint from the sidebar to start building and testing your requests
                          </p>
                        </div>
                      </div>
                    )}
                  </div>
                </ScrollArea>
              </div>

              {/* Right Side - Response Viewer */}
              <div className="flex-1 flex flex-col bg-card/30 backdrop-blur-sm overflow-hidden">
                {response ? (
                  <ResponseViewer response={response} />
                ) : (
                  <div className="flex flex-col items-center justify-center h-full p-8">
                    <div className="text-center">
                      <div className="inline-flex p-4 rounded-2xl bg-muted/50 mb-4">
                        <Zap className="h-12 w-12 text-muted-foreground" />
                      </div>
                      <h3 className="text-sm font-semibold mb-2">No Response Yet</h3>
                      <p className="text-xs text-muted-foreground max-w-xs">
                        Execute a request to see the response here
                      </p>
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Right Sidebar - Cookies & Collections */}
            <aside className="w-64 border-l border-border/50 bg-card/30 backdrop-blur-sm flex flex-col">
              <ScrollArea className="flex-1">
                {/* Cookies Section */}
                <div className="border-b border-border/50">
                  <div className="p-3 border-b border-border/50 bg-card/50">
                    <div className="flex items-center gap-2 mb-0.5">
                      <Cookie className="h-3.5 w-3.5 text-primary" />
                      <h2 className="text-xs font-semibold">Cookies</h2>
                    </div>
                    <p className="text-[10px] text-muted-foreground">Request cookies</p>
                  </div>
                  <CookiePanel cookiesUrl={cookiesUrl} />
                </div>

                {/* Collections Section */}
                <div>
                  <div className="p-3 border-b border-border/50 bg-card/50">
                    <div className="flex items-center gap-2 mb-0.5">
                      <FolderOpen className="h-3.5 w-3.5 text-primary" />
                      <h2 className="text-xs font-semibold">Collections</h2>
                    </div>
                    <p className="text-[10px] text-muted-foreground">Save & load configs</p>
                  </div>
                  <div className="p-3">
                    <Collections />
                  </div>
                </div>
              </ScrollArea>
            </aside>
          </div>
        </div>
      </div>
    </>
  );
}
