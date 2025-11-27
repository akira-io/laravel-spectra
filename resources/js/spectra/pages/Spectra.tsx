import {useEffect, useState} from 'react';
import {Head} from '@inertiajs/react';
import EndpointTree from '../components/EndpointTree';
import RequestBuilder from '../components/RequestBuilder';
import AuthPanel from '../components/AuthPanel';
import ResponseViewer from '../components/ResponseViewer';
import Collections from '../components/Collections';
import {Button} from '../components/ui/button';
import {Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle} from '../components/ui/dialog';
import {Cookie, FolderOpen, FolderTree, Moon, Save, Share2, Shield, Sun, Zap} from 'lucide-react';

interface Props {
    schemaUrl: string;
    executeUrl: string;
    cookiesUrl: string;
}

export default function Spectra({schemaUrl, executeUrl, cookiesUrl}: Props) {
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
          <Head title='Spectra - API Inspector'/>
          <div className={darkMode ? 'dark' : ''}>
              {/* Gradient Background - Spectra Style */}
              <div
                className='pointer-events-none fixed inset-0'
                style={{
                    zIndex: -2,
                    background: `
              radial-gradient(ellipse 80% 50% at 50% -20%, oklch(50% 0.15 285 / 0.15), transparent),
              radial-gradient(ellipse 60% 50% at 50% 120%, oklch(60% 0.12 315 / 0.1), transparent)
            `
                }}
              />
              <div className='h-screen flex flex-col bg-gradient-to-br from-background via-background to-muted/20'>
                  {/* Fixed Header */}
                  <header className='flex-none flex justify-between items-center px-6 py-3 bg-card/80 backdrop-blur-xl border-b border-border/50 shadow-lg'>
                      <div className='flex items-center gap-4'>
                          <div className='flex items-center gap-3'>
                              <div className='flex h-10 w-10 items-center justify-center rounded-xl gradient-primary shadow-lg shadow-primary/25'>
                                  <Zap className='h-6 w-6 text-white'/>
                              </div>
                              <div>
                                  <h1 className='text-lg font-bold tracking-tight'>Spectra</h1>
                                  <p className='text-xs text-muted-foreground'>Professional API Inspector</p>
                              </div>
                          </div>
                      </div>
                      <div className='flex items-center gap-2'>
                          <Button
                            onClick={() => setDarkMode(!darkMode)}
                            variant='outline'
                            size='icon'
                            className='rounded-lg'
                          >
                              {darkMode ? (
                                <Sun className='h-4 w-4'/>
                              ) : (
                                <Moon className='h-4 w-4'/>
                              )}
                          </Button>
                      </div>
                  </header>
                  {/* Fixed Layout Container */}
                  <div className='flex-1 flex overflow-hidden'>
                      {/* Left Sidebar - Endpoints (Fixed with scrollable content) */}
                      <aside className='w-80 border-r border-border/50 bg-card/30 backdrop-blur-sm flex flex-col'>
                          <div className='flex-none p-3 border-b border-border/50 bg-card/50'>
                              <div className='flex items-center justify-between gap-2 mb-0.5'>
                                  <div className='flex items-center gap-2'>
                                      <FolderTree className='h-3.5 w-3.5 text-primary'/>
                                      <h2 className='text-xs font-semibold'>Endpoints</h2>
                                  </div>
                                  <Button
                                    onClick={() => setShowCollectionsModal(true)}
                                    variant='ghost'
                                    size='icon'
                                    className='h-6 w-6 text-primary hover:text-primary hover:bg-primary/10'
                                  >
                                      <Save className='h-3.5 w-3.5'/>
                                  </Button>
                              </div>
                              <p className='text-[10px] text-muted-foreground'>Browse routes</p>
                          </div>
                          <div className='flex-1 overflow-y-auto'>
                              <div className='p-2'>
                                  <EndpointTree
                                    schemaUrl={schemaUrl}
                                    onSelect={handleEndpointSelect}
                                    selectedEndpoint={selectedEndpoint}
                                  />
                              </div>
                          </div>
                      </aside>
                      {/* Main Content Area - Split Request/Response */}
                      <div className='flex-1 flex overflow-hidden'>
                          {/* Center - Request Builder (scrollable) */}
                          <div className='flex-1 flex flex-col border-r border-border/50 bg-background/50 overflow-hidden'>
                              <div className='flex-1 overflow-y-auto'>
                                  <div className='p-4 space-y-4'>
                                      {selectedEndpoint ? (
                                        <>
                                            {/* Auth Section Compact */}
                                            <AuthPanel/>
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
                                        <div className='flex flex-col items-center justify-center h-full p-8'>
                                            <div className='max-w-2xl w-full space-y-8'>
                                                {/* Hero Section */}
                                                <div className='text-center space-y-4'>
                                                    <div className='inline-flex p-4 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/10 ring-1 ring-primary/20'>
                                                        <Zap className='h-12 w-12 text-primary'/>
                                                    </div>
                                                    <div className='space-y-2'>
                                                        <h2 className='text-3xl font-bold tracking-tight'>Welcome to Spectra</h2>
                                                        <p className='text-base text-muted-foreground max-w-lg mx-auto'>
                                                            Professional API testing and request building at your fingertips
                                                        </p>
                                                    </div>
                                                </div>
                                                {/* Quick Start Steps */}
                                                <div className='grid grid-cols-1 md:grid-cols-3 gap-4'>
                                                    {/* Step 1 */}
                                                    <div className='relative group'>
                                                        <div className='absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-0 group-hover:opacity-100 rounded-xl transition-opacity'/>
                                                        <div className='relative p-4 rounded-xl border border-border/50 bg-card/30 hover:bg-card/60 transition-all h-full'>
                                                            <div className='flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 mb-3 mx-auto'>
                                                                <span className='text-sm font-bold text-primary'>1</span>
                                                            </div>
                                                            <h4 className='font-semibold text-sm text-center mb-2'>Browse Endpoints</h4>
                                                            <p className='text-xs text-muted-foreground text-center leading-relaxed'>
                                                                Explore all available API endpoints in the left sidebar
                                                            </p>
                                                        </div>
                                                    </div>
                                                    {/* Step 2 */}
                                                    <div className='relative group'>
                                                        <div className='absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-0 group-hover:opacity-100 rounded-xl transition-opacity'/>
                                                        <div className='relative p-4 rounded-xl border border-border/50 bg-card/30 hover:bg-card/60 transition-all h-full'>
                                                            <div className='flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 mb-3 mx-auto'>
                                                                <span className='text-sm font-bold text-primary'>2</span>
                                                            </div>
                                                            <h4 className='font-semibold text-sm text-center mb-2'>Select & Configure</h4>
                                                            <p className='text-xs text-muted-foreground text-center leading-relaxed'>
                                                                Click any endpoint to configure headers, body, and parameters
                                                            </p>
                                                        </div>
                                                    </div>
                                                    {/* Step 3 */}
                                                    <div className='relative group'>
                                                        <div className='absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-0 group-hover:opacity-100 rounded-xl transition-opacity'/>
                                                        <div className='relative p-4 rounded-xl border border-border/50 bg-card/30 hover:bg-card/60 transition-all h-full'>
                                                            <div className='flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 mb-3 mx-auto'>
                                                                <span className='text-sm font-bold text-primary'>3</span>
                                                            </div>
                                                            <h4 className='font-semibold text-sm text-center mb-2'>Execute & View</h4>
                                                            <p className='text-xs text-muted-foreground text-center leading-relaxed'>
                                                                Execute your request and see the response instantly
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                {/* Features Section */}
                                                <div className='space-y-3 pt-4'>
                                                    <p className='text-xs font-semibold text-muted-foreground uppercase tracking-wide'>Features</p>
                                                    <div className='grid grid-cols-2 gap-3'>
                                                        <div className='flex items-start gap-3 p-3 rounded-lg border border-border/50 bg-card/20 hover:bg-card/40 transition-colors'>
                                                            <Shield className='h-4 w-4 text-primary flex-shrink-0 mt-1'/>
                                                            <div>
                                                                <p className='text-xs font-medium'>Authentication</p>
                                                                <p className='text-[10px] text-muted-foreground mt-0.5'>Bearer, Basic, & more</p>
                                                            </div>
                                                        </div>
                                                        <div className='flex items-start gap-3 p-3 rounded-lg border border-border/50 bg-card/20 hover:bg-card/40 transition-colors'>
                                                            <Cookie className='h-4 w-4 text-primary flex-shrink-0 mt-1'/>
                                                            <div>
                                                                <p className='text-xs font-medium'>Cookies</p>
                                                                <p className='text-[10px] text-muted-foreground mt-0.5'>Manage session data</p>
                                                            </div>
                                                        </div>
                                                        <div className='flex items-start gap-3 p-3 rounded-lg border border-border/50 bg-card/20 hover:bg-card/40 transition-colors'>
                                                            <FolderTree className='h-4 w-4 text-primary flex-shrink-0 mt-1'/>
                                                            <div>
                                                                <p className='text-xs font-medium'>Collections</p>
                                                                <p className='text-[10px] text-muted-foreground mt-0.5'>Save & organize</p>
                                                            </div>
                                                        </div>
                                                        <div className='flex items-start gap-3 p-3 rounded-lg border border-border/50 bg-card/20 hover:bg-card/40 transition-colors'>
                                                            <Share2 className='h-4 w-4 text-primary flex-shrink-0 mt-1'/>
                                                            <div>
                                                                <p className='text-xs font-medium'>Real-time</p>
                                                                <p className='text-[10px] text-muted-foreground mt-0.5'>Instant responses</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {/* CTA */}
                                                <div className='text-center pt-4'>
                                                    <p className='text-sm text-muted-foreground'>
                                                        <span className='font-medium'>Select an endpoint from the sidebar to begin</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                      )}
                                  </div>
                              </div>
                          </div>
                          {/* Right Side - Response Viewer (scrollable) */}
                          <div className='flex-1 flex flex-col bg-card/30 backdrop-blur-sm overflow-hidden'>
                              {response ? (
                                <ResponseViewer response={response}/>
                              ) : (
                                <div className='flex flex-col items-center justify-center h-full p-8'>
                                    <div className='text-center'>
                                        <div className='inline-flex p-4 rounded-2xl bg-muted/50 mb-4'>
                                            <Zap className='h-12 w-12 text-muted-foreground'/>
                                        </div>
                                        <h3 className='text-sm font-semibold mb-2'>No Response Yet</h3>
                                        <p className='text-xs text-muted-foreground max-w-xs'>
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
                  <DialogContent className='max-w-2xl max-h-[80vh] overflow-y-auto'>
                      <DialogHeader>
                          <DialogTitle className='text-base'>
                              <FolderOpen className='h-5 w-5'/>
                              Collections
                          </DialogTitle>
                          <DialogDescription>
                              Manage and organize your API request collections
                          </DialogDescription>
                      </DialogHeader>
                      <div className='py-4'>
                          <Collections/>
                      </div>
                  </DialogContent>
              </Dialog>
          </div>
      </>
    );
}
