import { Zap } from 'lucide-react';
import ResponseViewer from '../ResponseViewer';
import { useNavigationStore } from '../../stores/navigationStore';

interface ResponsePanelProps {
  response: any;
}

export default function ResponsePanel({ response }: ResponsePanelProps) {
  const selectedEndpoint = useNavigationStore((state) => state.selectedEndpoint);
  const allHistory = useNavigationStore((state) => state.responseHistory);
  const responseHistory = selectedEndpoint && allHistory[selectedEndpoint.uri] ? allHistory[selectedEndpoint.uri] : [];
  
  // If no current response but we have history, show the last one
  const displayResponse = response || (responseHistory.length > 0 ? responseHistory[0].response : null);
  
  return (
    <div className="flex-1 flex flex-col bg-card/30 backdrop-blur-sm overflow-hidden">
      {displayResponse ? (
        <ResponseViewer response={displayResponse} endpoint={selectedEndpoint} />
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
  );
}