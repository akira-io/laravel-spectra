import { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '../ui/dialog';
import { Button } from '../ui/button';
import { BarChart3, TrendingUp, AlertCircle, FileJson } from 'lucide-react';
import ResponseTimeMetrics from './metrics/ResponseTimeMetrics';
import StatusCodeMetrics from './metrics/StatusCodeMetrics';
import EndpointMetrics from './metrics/EndpointMetrics';

interface MetricsModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export default function MetricsModal({ open, onOpenChange }: MetricsModalProps) {
  const [activeMetric, setActiveMetric] = useState<'response-time' | 'status-codes' | 'endpoints'>('response-time');

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-5xl max-h-[90vh] flex flex-col">
        <DialogHeader>
          <div className="flex items-center gap-2">
            <BarChart3 className="h-5 w-5" />
            <DialogTitle>Development Metrics</DialogTitle>
          </div>
        </DialogHeader>

        {/* Metric Selector Tabs */}
        <div className="flex gap-2 border-b border-border/50 pb-4">
          <Button
            variant={activeMetric === 'response-time' ? 'default' : 'outline'}
            size="sm"
            onClick={() => setActiveMetric('response-time')}
            className="gap-2"
          >
            <TrendingUp className="h-4 w-4" />
            Response Time
          </Button>
          <Button
            variant={activeMetric === 'status-codes' ? 'default' : 'outline'}
            size="sm"
            onClick={() => setActiveMetric('status-codes')}
            className="gap-2"
          >
            <AlertCircle className="h-4 w-4" />
            Status Codes
          </Button>
          <Button
            variant={activeMetric === 'endpoints' ? 'default' : 'outline'}
            size="sm"
            onClick={() => setActiveMetric('endpoints')}
            className="gap-2"
          >
            <FileJson className="h-4 w-4" />
            Endpoints
          </Button>
        </div>

        {/* Metric Content */}
        <div className="flex-1 overflow-auto">
          {activeMetric === 'response-time' && <ResponseTimeMetrics />}
          {activeMetric === 'status-codes' && <StatusCodeMetrics />}
          {activeMetric === 'endpoints' && <EndpointMetrics />}
        </div>
      </DialogContent>
    </Dialog>
  );
}