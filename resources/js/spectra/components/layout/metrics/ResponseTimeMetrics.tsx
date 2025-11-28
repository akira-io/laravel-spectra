import { useEffect, useState } from 'react';
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { useNavigationStore } from '../../../stores/navigationStore';

interface ChartDataPoint {
  timestamp: number;
  time: string;
  responseTime: number;
}

export default function ResponseTimeMetrics() {
  const metrics = useNavigationStore((state) => state.metrics);
  const [chartData, setChartData] = useState<ChartDataPoint[]>([]);

  useEffect(() => {
    const now = Date.now();
    const timelineData = metrics.responseTimes.map((time, index) => ({
      timestamp: now - (metrics.responseTimes.length - index) * 1000,
      time: `${metrics.responseTimes.length - index}s ago`,
      responseTime: time,
    })).slice(-20);

    setChartData(timelineData);
  }, [metrics]);

  const avgResponseTime = metrics.responseTimes.length > 0
    ? Math.round(metrics.responseTimes.reduce((a, b) => a + b, 0) / metrics.responseTimes.length)
    : 0;

  return (
    <div className="space-y-4 p-3">
      {/* Statistics Cards */}
      <div className="grid grid-cols-4 gap-4">
        <div className="p-4 rounded-lg bg-accent/5 border border-border/30">
          <p className="text-xs text-muted-foreground">Min</p>
          <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
            {metrics.responseTimes.length > 0 ? Math.min(...metrics.responseTimes) : 0}ms
          </p>
        </div>
        <div className="p-4 rounded-lg bg-accent/5 border border-border/30">
          <p className="text-xs text-muted-foreground">Max</p>
          <p className="text-2xl font-bold text-red-600 dark:text-red-400">
            {metrics.responseTimes.length > 0 ? Math.max(...metrics.responseTimes) : 0}ms
          </p>
        </div>
        <div className="p-4 rounded-lg bg-accent/5 border border-border/30">
          <p className="text-xs text-muted-foreground">Average</p>
          <p className="text-2xl font-bold text-amber-600 dark:text-amber-400">
            {avgResponseTime}ms
          </p>
        </div>
        <div className="p-4 rounded-lg bg-accent/5 border border-border/30">
          <p className="text-xs text-muted-foreground">Requests</p>
          <p className="text-2xl font-bold text-blue-600 dark:text-blue-400">
            {metrics.responseTimes.length}
          </p>
        </div>
      </div>

      {/* Line Chart */}
      <div>
        <h3 className="text-sm font-semibold mb-3">Response Time Trend</h3>
        <div className="w-full h-48">
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={chartData}>
              <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
              <XAxis dataKey="time" className="text-xs" />
              <YAxis label={{ value: 'ms', angle: -90, position: 'insideLeft' }} />
              <Tooltip
                contentStyle={{ backgroundColor: 'hsl(var(--background))', border: '1px solid hsl(var(--border))' }}
                formatter={(value) => `${value}ms`}
              />
              <Legend />
              <Line
                type="monotone"
                dataKey="responseTime"
                stroke="#3b82f6"
                strokeWidth={2}
                dot={{ fill: '#3b82f6', r: 4 }}
                activeDot={{ r: 6 }}
                name="Response Time"
              />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Bar Chart */}
      <div>
        <h3 className="text-sm font-semibold mb-3">Response Time Distribution</h3>
        <div className="w-full h-48">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={chartData}>
              <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
              <XAxis dataKey="time" className="text-xs" />
              <YAxis label={{ value: 'ms', angle: -90, position: 'insideLeft' }} />
              <Tooltip
                contentStyle={{ backgroundColor: 'hsl(var(--background))', border: '1px solid hsl(var(--border))' }}
                formatter={(value) => `${value}ms`}
              />
              <Bar dataKey="responseTime" fill="#3b82f6" name="Response Time" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  );
}
