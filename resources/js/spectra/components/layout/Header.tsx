import { Button } from '../ui/button';
import { Sun, Moon, Zap } from 'lucide-react';

interface HeaderProps {
  darkMode: boolean;
  onDarkModeToggle: () => void;
  onCollectionsClick: () => void;
}

export default function Header({ darkMode, onDarkModeToggle, onCollectionsClick }: HeaderProps) {
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
      </div>
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