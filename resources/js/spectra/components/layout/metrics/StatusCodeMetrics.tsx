import { useEffect, useState } from 'react';
import { PieChart, Pie, Cell, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { useNavigationStore } from '../../../stores/navigationStore';

interface StatusCodeData {
  [key: string]: string | number;
  name: string;
  value: number;
  color: string;
}

const COLORS = {
  '2xx': '#10b981',
  '4xx': '#f59e0b',
  '5xx': '#ef4444',
};

export default function StatusCodeMetrics() {
  const metrics = useNavigationStore((state) => state.metrics);
  const [statusCodeData, setStatusCodeData] = useState<StatusCodeData[]>([]);

  useEffect(() => {
    const statusData = [
      {
        name: '2xx Success',
        value: metrics.successfulRequests,
        color: COLORS['2xx'],
      },
      {
        name: '4xx Client Errors',
        value: metrics.clientErrors,
        color: COLORS['4xx'],
      },
      {
        name: '5xx Server Errors',
        value: metrics.serverErrors,
        color: COLORS['5xx'],
      },
    ].filter((item) => item.value > 0);

    setStatusCodeData(statusData);
  }, [metrics]);

  const successRate = metrics.totalRequests > 0
    ? Math.round((metrics.successfulRequests / metrics.totalRequests) * 100)
    : 0;

  return (
    <div className="space-y-4 p-3">
      {/* Statistics Cards */}
      <div className="grid grid-cols-3 gap-4">
        <div className="p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
          <p className="text-xs text-muted-foreground">2xx Success</p>
          <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{metrics.successfulRequests}</p>
          <p className="text-xs text-muted-foreground mt-1">{successRate}%</p>
        </div>
        <div className="p-4 rounded-lg bg-amber-500/10 border border-amber-500/20">
          <p className="text-xs text-muted-foreground">4xx Client Errors</p>
          <p className="text-2xl font-bold text-amber-600 dark:text-amber-400">{metrics.clientErrors}</p>
        </div>
        <div className="p-4 rounded-lg bg-red-500/10 border border-red-500/20">
          <p className="text-xs text-muted-foreground">5xx Server Errors</p>
          <p className="text-2xl font-bold text-red-600 dark:text-red-400">{metrics.serverErrors}</p>
        </div>
      </div>

      {/* Pie Chart */}
      <div>
        <h3 className="text-sm font-semibold mb-3">Status Code Distribution</h3>
        <div className="w-full h-56 flex justify-center items-center">
          <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie
                data={statusCodeData}
                cx="50%"
                cy="50%"
                labelLine={false}
                label={({ name, value, percent = 0 }) => `${name}: ${value} (${((percent as number) * 100).toFixed(0)}%)`}
                outerRadius={80}
                fill="#8884d8"
                dataKey="value"
              >
                {statusCodeData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip formatter={(value) => `${value} requests`} />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Bar Chart */}
      <div>
        <h3 className="text-sm font-semibold mb-3">Status Code Breakdown</h3>
        <div className="w-full h-48">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={statusCodeData} layout="vertical" margin={{ top: 5, right: 30, left: 100, bottom: 5 }}>
              <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
              <XAxis type="number" />
              <YAxis dataKey="name" type="category" width={90} className="text-xs" />
              <Tooltip formatter={(value) => `${value} requests`} />
              <Bar dataKey="value" fill="#3b82f6" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  );
}
