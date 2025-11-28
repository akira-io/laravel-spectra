import { useEffect, useState } from 'react';
import { Treemap, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { useNavigationStore } from '../../../stores/navigationStore';

interface EndpointData {
  endpoint: string;
  shortEndpoint: string;
  fullEndpoint: string;
  count: number;
  avgResponseTime: number;
}

export default function EndpointMetrics() {
  const metrics = useNavigationStore((state) => state.metrics);
  const [endpointData, setEndpointData] = useState<EndpointData[]>([]);

  useEffect(() => {
    const endpointStats: Record<string, { count: number; totalTime: number }> = {};

    if (metrics.requestHistory && Array.isArray(metrics.requestHistory)) {
      metrics.requestHistory.forEach((request: any) => {
        const key = `${request.method} ${request.url}`;
        if (!endpointStats[key]) {
          endpointStats[key] = { count: 0, totalTime: 0 };
        }
        endpointStats[key].count++;
        endpointStats[key].totalTime += request.responseTime || 0;
      });
    }

    const endpointChartData = Object.entries(endpointStats)
      .map(([endpoint, stats]) => {
        const shortEndpoint = endpoint.length > 30 ? endpoint.substring(0, 30) + '...' : endpoint;
        return {
          fullEndpoint: endpoint,
          endpoint: shortEndpoint,
          shortEndpoint: shortEndpoint.replace(/^(GET|POST|PUT|DELETE|PATCH) /, '').split('/')[1] || 'root',
          count: stats.count,
          avgResponseTime: Math.round(stats.totalTime / stats.count),
        };
      })
      .sort((a, b) => b.count - a.count)
      .slice(0, 10);

    setEndpointData(endpointChartData);
  }, [metrics]);

  return (
    <div className="space-y-4 p-3">
      {/* Treemap */}
      <div>
        <h3 className="text-sm font-semibold mb-3">Most Used Endpoints (Size = Requests, Color = Response Time)</h3>
        <div className="w-full h-56">
          <ResponsiveContainer width="100%" height="100%">
            <Treemap
              data={endpointData}
              dataKey="count"
              stroke="#fff"
              fill="#8884d8"
              content={({ x, y, width, height, name, count, avgResponseTime }) => {
                const getColor = (time: number) => {
                  if (time > 1000) return '#ef4444';
                  if (time > 500) return '#f59e0b';
                  if (time > 200) return '#eab308';
                  return '#10b981';
                };

                const isSmall = width < 80 || height < 60;

                return (
                  <g>
                    <rect
                      x={x}
                      y={y}
                      width={width}
                      height={height}
                      style={{
                        fill: getColor(avgResponseTime),
                        stroke: 'hsl(var(--border))',
                        strokeWidth: 2,
                        opacity: 0.9,
                      }}
                    />
                    {!isSmall && (
                      <>
                        <text
                          x={x + width / 2}
                          y={y + height / 2 - 15}
                          textAnchor="middle"
                          fill="#fff"
                          fontSize={12}
                          fontWeight="bold"
                          className="pointer-events-none"
                        >
                          {name}
                        </text>
                        <text
                          x={x + width / 2}
                          y={y + height / 2 + 5}
                          textAnchor="middle"
                          fill="#fff"
                          fontSize={11}
                          className="pointer-events-none"
                        >
                          {count} req
                        </text>
                        <text
                          x={x + width / 2}
                          y={y + height / 2 + 20}
                          textAnchor="middle"
                          fill="#fff"
                          fontSize={10}
                          className="pointer-events-none opacity-80"
                        >
                          {avgResponseTime}ms
                        </text>
                      </>
                    )}
                  </g>
                );
              }}
            />
          </ResponsiveContainer>
        </div>
        <div className="mt-6 flex items-center justify-center gap-6 text-sm flex-wrap">
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded" style={{ backgroundColor: '#10b981' }}></div>
            <span>Fast (&lt;200ms)</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded" style={{ backgroundColor: '#eab308' }}></div>
            <span>Normal (200-500ms)</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded" style={{ backgroundColor: '#f59e0b' }}></div>
            <span>Slow (500-1000ms)</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded" style={{ backgroundColor: '#ef4444' }}></div>
            <span>Very Slow (&gt;1000ms)</span>
          </div>
        </div>
      </div>

      {/* Bar Chart - Request Count */}
      <div>
        <h3 className="text-sm font-semibold mb-3">Requests per Endpoint</h3>
        <div className="w-full h-48">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={endpointData} margin={{ top: 20, right: 30, left: 20, bottom: 80 }}>
              <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
              <XAxis dataKey="endpoint" angle={-45} textAnchor="end" height={100} className="text-xs" />
              <YAxis label={{ value: 'Requests', angle: -90, position: 'insideLeft' }} />
              <Tooltip
                contentStyle={{ backgroundColor: 'hsl(var(--background))', border: '1px solid hsl(var(--border))' }}
                formatter={(value) => `${value} requests`}
              />
              <Bar dataKey="count" fill="#3b82f6" name="Requests" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Bar Chart - Response Time */}
      <div>
        <h3 className="text-sm font-semibold mb-3">Average Response Time per Endpoint</h3>
        <div className="w-full h-48">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={endpointData} margin={{ top: 20, right: 30, left: 20, bottom: 80 }}>
              <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
              <XAxis dataKey="endpoint" angle={-45} textAnchor="end" height={100} className="text-xs" />
              <YAxis label={{ value: 'Response Time (ms)', angle: -90, position: 'insideLeft' }} />
              <Tooltip
                contentStyle={{ backgroundColor: 'hsl(var(--background))', border: '1px solid hsl(var(--border))' }}
                formatter={(value) => `${value}ms`}
              />
              <Bar dataKey="avgResponseTime" fill="#f59e0b" name="Avg Response Time" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  );
}
