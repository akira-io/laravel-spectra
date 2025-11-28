import { Button } from '../ui/button';
import { FolderTree, Save } from 'lucide-react';
import EndpointTree from '../EndpointTree';

interface SidebarProps {
  schemaUrl: string;
  onEndpointSelect: (endpoint: any) => void;
  selectedEndpoint: any;
  onCollectionsClick: () => void;
}

export default function Sidebar({ schemaUrl, onEndpointSelect, selectedEndpoint, onCollectionsClick }: SidebarProps) {
  return (
    <aside className="w-80 border-r border-border/50 bg-card/30 backdrop-blur-sm flex flex-col">
      <div className="flex-none p-3 border-b border-border/50 bg-card/50">
        <div className="flex items-center justify-between gap-2 mb-0.5">
          <div className="flex items-center gap-2">
            <FolderTree className="h-3.5 w-3.5 text-primary" />
            <h2 className="text-xs font-semibold">Endpoints</h2>
          </div>
          <Button
            onClick={onCollectionsClick}
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
            onSelect={onEndpointSelect}
            selectedEndpoint={selectedEndpoint}
          />
        </div>
      </div>
    </aside>
  );
}