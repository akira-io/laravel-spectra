import { Zap } from 'lucide-react';
import ResponseViewer from '../ResponseViewer';

interface ResponsePanelProps {
  response: any;
}

export default function ResponsePanel({ response }: ResponsePanelProps) {
  return (
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
  );
}