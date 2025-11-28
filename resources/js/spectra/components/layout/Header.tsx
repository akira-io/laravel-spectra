import { Button } from '../ui/button';
import { Sun, Moon, Zap, Activity, CheckCircle2, Zap as ZapIcon } from 'lucide-react';
import { useNavigationStore } from '../../stores/navigationStore';

interface HeaderProps {
  darkMode: boolean;
  onDarkModeToggle: () => void;
  onCollectionsClick: () => void;
}

export default function Header({ darkMode, onDarkModeToggle, onCollectionsClick }: HeaderProps) {
  const config = (window as any).spectraConfig || {};
  const appName = config.appName || 'Laravel';
  const appEnv = config.appEnv || 'local';
  const phpVersion = config.phpVersion;
  const laravelVersion = config.laravelVersion;
  const appUrl = window.location.origin;
  
  const metrics = useNavigationStore((state) => state.metrics);
  
  // Calculate metrics
  const successRate = metrics.totalRequests > 0 
    ? Math.round((metrics.successfulRequests / metrics.totalRequests) * 100) 
    : 0;
  const avgResponseTime = metrics.responseTimes.length > 0
    ? Math.round(metrics.responseTimes.reduce((a, b) => a + b, 0) / metrics.responseTimes.length)
    : 0;
  
  const getEnvBadgeColor = (env: string) => {
    const colors: Record<string, string> = {
      'production': 'bg-red-500/10 text-red-500 border-red-500/20',
      'staging': 'bg-amber-500/10 text-amber-500 border-amber-500/20',
      'local': 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
      'development': 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    };
    return colors[env.toLowerCase()] || 'bg-gray-500/10 text-gray-500 border-gray-500/20';
  };

  return (
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
        
        <div className="hidden md:flex items-center gap-3 pl-4 border-l border-border/50">
          <div className="flex flex-col gap-0.5">
            <div className="flex items-center gap-2">
              <span className="text-sm font-semibold">{appName}</span>
              <span className={`text-[10px] px-2 py-0.5 rounded-full border font-medium uppercase tracking-wider ${getEnvBadgeColor(appEnv)}`}>
                {appEnv}
              </span>
            </div>
            <div className="flex items-center gap-2 text-[10px] text-muted-foreground font-mono">
              <span>{appUrl}</span>
              {phpVersion && (
                <>
                  <span className="text-border">•</span>
                  <span>PHP {phpVersion}</span>
                </>
              )}
              {laravelVersion && (
                <>
                  <span className="text-border">•</span>
                  <span>Laravel {laravelVersion}</span>
                </>
              )}
            </div>
          </div>
        </div>
      </div>
      
      {metrics.totalRequests > 0 && (
        <div className="hidden lg:flex items-center gap-4 px-4 py-1.5 rounded-lg bg-muted/30 border border-border/50">
          <div className="flex items-center gap-1.5">
            <Activity className="h-3.5 w-3.5 text-blue-500" />
            <span className="text-xs font-medium">{metrics.totalRequests}</span>
            <span className="text-[10px] text-muted-foreground">requests</span>
          </div>
          
          <div className="h-3 w-px bg-border" />
          
          <div className="flex items-center gap-1.5">
            <CheckCircle2 className="h-3.5 w-3.5 text-emerald-500" />
            <span className="text-xs font-medium">{successRate}%</span>
            <span className="text-[10px] text-muted-foreground">success</span>
          </div>
          
          <div className="h-3 w-px bg-border" />
          
          <div className="flex items-center gap-1.5">
            <ZapIcon className="h-3.5 w-3.5 text-amber-500" />
            <span className="text-xs font-medium">{avgResponseTime}ms</span>
            <span className="text-[10px] text-muted-foreground">avg</span>
          </div>
        </div>
      )}
      
      <div className="flex items-center gap-2">
        <Button
          onClick={onDarkModeToggle}
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
  );
}