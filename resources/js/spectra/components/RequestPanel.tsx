import AuthPanel from '../AuthPanel';
import RequestBuilder from '../RequestBuilder';
import { Layout, Zap, Shield, Cookie, FolderTree, Share2 } from 'lucide-react';

interface RequestPanelProps {
  selectedEndpoint: any;
  executeUrl: string;
  onResponse: (response: any) => void;
  cookiesUrl: string;
}

export default function RequestPanel({ selectedEndpoint, executeUrl, onResponse, cookiesUrl }: RequestPanelProps) {
  return (
    <div className="flex-1 flex flex-col border-r border-border/50 bg-background/50 overflow-hidden">
      <div className="flex-1 overflow-y-auto">
        <div className="p-4 space-y-4">
          {selectedEndpoint ? (
            <>
              <AuthPanel />
              <RequestBuilder
                key={`${selectedEndpoint.uri}-${selectedEndpoint.methods.join('-')}`}
                endpoint={selectedEndpoint}
                executeUrl={executeUrl}
                onResponse={onResponse}
                cookiesUrl={cookiesUrl}
              />
            </>
          ) : (
            <WelcomeScreen />
          )}
        </div>
      </div>
    </div>
  );
}

function WelcomeScreen() {
  return (
    <div className="flex flex-col items-center justify-center h-full p-8">
      <div className="max-w-2xl w-full space-y-8">
        {/* Hero Section */}
        <div className="text-center space-y-4">
          <div className="inline-flex p-4 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/10 ring-1 ring-primary/20">
            <Zap className="h-12 w-12 text-primary" />
          </div>
          <div className="space-y-2">
            <h2 className="text-3xl font-bold tracking-tight">Welcome to Spectra</h2>
            <p className="text-base text-muted-foreground max-w-lg mx-auto">
              Professional API testing and request building at your fingertips
            </p>
          </div>
        </div>

        {/* Quick Start Steps */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {/* Step 1 */}
          <div className="relative group">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-0 group-hover:opacity-100 rounded-xl transition-opacity" />
            <div className="relative p-4 rounded-xl border border-border/50 bg-card/30 hover:bg-card/60 transition-all h-full">
              <div className="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 mb-3 mx-auto">
                <span className="text-sm font-bold text-primary">1</span>
              </div>
              <h4 className="font-semibold text-sm text-center mb-2">Browse Endpoints</h4>
              <p className="text-xs text-muted-foreground text-center leading-relaxed">
                Explore all available API endpoints in the left sidebar
              </p>
            </div>
          </div>

          {/* Step 2 */}
          <div className="relative group">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-0 group-hover:opacity-100 rounded-xl transition-opacity" />
            <div className="relative p-4 rounded-xl border border-border/50 bg-card/30 hover:bg-card/60 transition-all h-full">
              <div className="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 mb-3 mx-auto">
                <span className="text-sm font-bold text-primary">2</span>
              </div>
              <h4 className="font-semibold text-sm text-center mb-2">Select & Configure</h4>
              <p className="text-xs text-muted-foreground text-center leading-relaxed">
                Click any endpoint to configure headers, body, and parameters
              </p>
            </div>
          </div>

          {/* Step 3 */}
          <div className="relative group">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-0 group-hover:opacity-100 rounded-xl transition-opacity" />
            <div className="relative p-4 rounded-xl border border-border/50 bg-card/30 hover:bg-card/60 transition-all h-full">
              <div className="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 mb-3 mx-auto">
                <span className="text-sm font-bold text-primary">3</span>
              </div>
              <h4 className="font-semibold text-sm text-center mb-2">Execute & View</h4>
              <p className="text-xs text-muted-foreground text-center leading-relaxed">
                Execute your request and see the response instantly
              </p>
            </div>
          </div>
        </div>

        {/* Features Section */}
        <div className="space-y-3 pt-4">
          <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Features</p>
          <div className="grid grid-cols-2 gap-3">
            <div className="flex items-start gap-3 p-3 rounded-lg border border-border/50 bg-card/20 hover:bg-card/40 transition-colors">
              <Shield className="h-4 w-4 text-primary flex-shrink-0 mt-1" />
              <div>
                <p className="text-xs font-medium">Authentication</p>
                <p className="text-[10px] text-muted-foreground mt-0.5">Bearer, Basic, & more</p>
              </div>
            </div>
            <div className="flex items-start gap-3 p-3 rounded-lg border border-border/50 bg-card/20 hover:bg-card/40 transition-colors">
              <Cookie className="h-4 w-4 text-primary flex-shrink-0 mt-1" />
              <div>
                <p className="text-xs font-medium">Cookies</p>
                <p className="text-[10px] text-muted-foreground mt-0.5">Manage session data</p>
              </div>
            </div>
            <div className="flex items-start gap-3 p-3 rounded-lg border border-border/50 bg-card/20 hover:bg-card/40 transition-colors">
              <FolderTree className="h-4 w-4 text-primary flex-shrink-0 mt-1" />
              <div>
                <p className="text-xs font-medium">Collections</p>
                <p className="text-[10px] text-muted-foreground mt-0.5">Save & organize</p>
              </div>
            </div>
            <div className="flex items-start gap-3 p-3 rounded-lg border border-border/50 bg-card/20 hover:bg-card/40 transition-colors">
              <Share2 className="h-4 w-4 text-primary flex-shrink-0 mt-1" />
              <div>
                <p className="text-xs font-medium">Real-time</p>
                <p className="text-[10px] text-muted-foreground mt-0.5">Instant responses</p>
              </div>
            </div>
          </div>
        </div>

        {/* CTA */}
        <div className="text-center pt-4">
          <p className="text-sm text-muted-foreground">
            👈 <span className="font-medium">Select an endpoint from the sidebar to begin</span>
          </p>
        </div>
      </div>
    </div>
  );
}