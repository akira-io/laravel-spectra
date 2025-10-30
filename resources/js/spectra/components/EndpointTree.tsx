import { useEffect, useState } from 'react';
import ky from 'ky';

interface Props {
  schemaUrl: string;
  onSelect: (endpoint: any) => void;
}

export default function EndpointTree({ schemaUrl, onSelect }: Props) {
  const [routes, setRoutes] = useState<any[]>([]);
  const [filter, setFilter] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    ky.get(schemaUrl)
      .json()
      .then((data: any) => {
        setRoutes(data.routes || []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, [schemaUrl]);

  const filteredRoutes = routes.filter(
    (route) =>
      route.uri.toLowerCase().includes(filter.toLowerCase()) ||
      route.name?.toLowerCase().includes(filter.toLowerCase())
  );

  const groupedRoutes = filteredRoutes.reduce((acc, route) => {
    const controller = route.action?.split('@')[0]?.split('\\').pop() || 'Other';
    if (!acc[controller]) acc[controller] = [];
    acc[controller].push(route);
    return acc;
  }, {} as Record<string, any[]>);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('endpoint-search')?.focus();
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  if (loading) {
    return <div className="p-4">Loading routes...</div>;
  }

  return (
    <div className="p-4">
      <input
        id="endpoint-search"
        type="text"
        value={filter}
        onChange={(e) => setFilter(e.target.value)}
        placeholder="Search endpoints (⌘K)"
        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 mb-4"
      />

      {Object.entries(groupedRoutes).map(([controller, routes]) => (
        <div key={controller} className="mb-4">
          <h3 className="font-semibold text-sm mb-2 text-gray-700 dark:text-gray-300">
            {controller}
          </h3>
          {routes.map((route, idx) => (
            <button
              key={idx}
              onClick={() => onSelect(route)}
              className="w-full text-left px-3 py-2 mb-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-sm"
            >
              <div className="flex gap-2 mb-1">
                {route.methods
                  .filter((m: string) => !['HEAD', 'OPTIONS'].includes(m))
                  .map((method: string) => (
                    <span
                      key={method}
                      className={`text-xs px-2 py-0.5 rounded font-mono ${
                        method === 'GET'
                          ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                          : method === 'POST'
                          ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                          : method === 'PUT' || method === 'PATCH'
                          ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                          : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                      }`}
                    >
                      {method}
                    </span>
                  ))}
              </div>
              <div className="font-mono text-xs text-gray-600 dark:text-gray-400">
                {route.uri}
              </div>
              {route.name && (
                <div className="text-xs text-gray-500 dark:text-gray-500">
                  {route.name}
                </div>
              )}
            </button>
          ))}
        </div>
      ))}
    </div>
  );
}
