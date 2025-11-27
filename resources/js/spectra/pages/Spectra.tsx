import { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import EndpointTree from '../components/EndpointTree';
import RequestBuilder from '../components/RequestBuilder';
import AuthPanel from '../components/AuthPanel';
import ResponseViewer from '../components/ResponseViewer';
import CookiePanel from '../components/CookiePanel';
import Collections from '../components/Collections';
import { Button } from '../components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '../components/ui/dialog';
import { Sun, Moon, Zap, Cookie, FolderTree, Layout, Shield, FolderOpen, Save, Share2 } from 'lucide-react';

interface Props {
  schemaUrl: string;
  executeUrl: string;
  cookiesUrl: string;
}

export default function Spectra({ schemaUrl, executeUrl, cookiesUrl }: Props) {
  const [selectedEndpoint, setSelectedEndpoint] = useState<any>(null);
  const [response, setResponse] = useState<any>(null);
  const [darkMode, setDarkMode] = useState(true);
  const [showCollectionsModal, setShowCollectionsModal] = useState(false);

  const handleEndpointSelect = (endpoint: any) => {
    setSelectedEndpoint(endpoint);
    setResponse(null); // Clear response when changing endpoint
  };

  useEffect(() => {
    document.body.classList.add('spectra-theme');
    document.documentElement.classList.add('dark');

    return () => {
      document.body.classList.remove('spectra-theme');
      document.documentElement.classList.remove('dark');
    };
  }, []);

  return (
    <>
      <Head title="Spectra - API Inspector" />
      <div className={darkMode ? 'dark' : ''}>
        {/* Gradient Background - Spectra Style */}
        <div
          className="pointer-events-none fixed inset-0"
          style={{
            zIndex: -2,
            background: `
              radial-gradient(ellipse 80% 50% at 50% -20%, oklch(50% 0.15 285 / 0.15), transparent),
              radial-gradient(ellipse 60% 50% at 50% 120%, oklch(60% 0.12 315 / 0.1), transparent)
            `
          }}
        />

        <div className="h-screen flex flex-col bg-gradient-to-br from-background via-background to-muted/20">
          {/* Fixed Header */}
          <header className="flex-none flex justify-between items-center px-6 py-3 bg-card/80 backdrop-blur-xl border-b border-border/50 shadow-lg">
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

          {/* Fixed Layout Container */}
          <div className="flex-1 flex overflow-hidden">
            {/* Left Sidebar - Endpoints (Fixed with scrollable content) */}
            <aside className="w-80 border-r border-border/50 bg-card/30 backdrop-blur-sm flex flex-col">
              <div className="flex-none p-3 border-b border-border/50 bg-card/50">
                <div className="flex items-center justify-between gap-2 mb-0.5">
                  <div className="flex items-center gap-2">
                    <FolderTree className="h-3.5 w-3.5 text-primary" />
                    <h2 className="text-xs font-semibold">Endpoints</h2>
                  </div>
                  <Button
                    onClick={() => setShowCollectionsModal(true)}
                    variant="ghost"
                    size="icon"
                    className="h-6 w-6 text-primary hover:text-primary hover:bg-primary/10"
                  >
                    <Save className="h-3.5 w-3.5" />
                  </Button>
                </div>
                <p className="text-[10px] text-muted-foreground">Browse routes</p>
              </div>
              <div className="flex-1 overflow-y-auto">
                <div className="p-2">
                  <EndpointTree
                    schemaUrl={schemaUrl}
                    onSelect={handleEndpointSelect}
                    selectedEndpoint={selectedEndpoint}
                  />
                </div>
              </div>
            </aside>

            {/* Main Content Area - Split Request/Response */}
            <div className="flex-1 flex overflow-hidden">
              {/* Center - Request Builder (scrollable) */}
              <div className="flex-1 flex flex-col border-r border-border/50 bg-background/50 overflow-hidden">
                <div className="flex-1 overflow-y-auto">
                  <div className="p-4 space-y-4">
                    {selectedEndpoint ? (
                      <>
                        {/* Auth Section Compact */}
                        <AuthPanel />

                        {/* Request Builder */}
                        <RequestBuilder
                          key={`${selectedEndpoint.uri}-${selectedEndpoint.methods.join('-')}`}
                          endpoint={selectedEndpoint}
                          executeUrl={executeUrl}
                          onResponse={setResponse}
                          cookiesUrl={cookiesUrl}
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
                </div>
              </div>

              {/* Right Side - Response Viewer (scrollable) */}
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
          </div>
        </div>

        {/* Collections Modal */}
        <Dialog open={showCollectionsModal} onOpenChange={setShowCollectionsModal}>
          <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle className="text-base">
                <FolderOpen className="h-5 w-5" />
                Collections
              </DialogTitle>
              <DialogDescription>
                Manage and organize your API request collections
              </DialogDescription>
            </DialogHeader>
            <div className="py-4">
              <Collections />
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </>
  );
}
