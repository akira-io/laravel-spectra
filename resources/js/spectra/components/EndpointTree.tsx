import { useEffect, useState } from 'react';
import ky from 'ky';

interface Props {
  schemaUrl: string;
  onSelect: (endpoint: any) => void;
  selectedEndpoint?: any;
}

export default function EndpointTree({ schemaUrl, onSelect, selectedEndpoint }: Props) {
  const [routes, setRoutes] = useState<any[]>([]);
  const [filter, setFilter] = useState('');
  const [loading, setLoading] = useState(true);
  const [expandedGroups, setExpandedGroups] = useState<Set<string>>(new Set());

  useEffect(() => {
    ky.get(schemaUrl)
      .json()
      .then((data: any) => {
        setRoutes(data.routes || []);
        setLoading(false);

        const allGroups = new Set<string>();
        (data.routes || []).forEach((route: any) => {
          let groupName = 'Other';

          // First, try to extract group from URI path (e.g., api/auth/login -> Auth)
          const uriParts = route.uri.split('/').filter(Boolean);

          // Look for the first meaningful segment that's not a common prefix or parameter
          for (let i = 0; i < uriParts.length; i++) {
            const part = uriParts[i];

            if (!part.startsWith('{') && !part.endsWith('}')) {
              // Skip common prefixes like 'api', 'admin', 'v1', etc
              if (!['api', 'admin', 'v1', 'v2', 'v3', 'web'].includes(part.toLowerCase())) {
                groupName = part
                  .split('-')
                  .map((word: string) => word.charAt(0).toUpperCase() + word.slice(1))
                  .join(' ');
                break;
              }
            }
          }

          // If still "Other" and we have a controller, try to use it as secondary fallback
          if (groupName === 'Other' && route.action && route.action !== 'Closure') {
            const controllerName = route.action.split('@')[0]?.split('\\').pop() || '';
            const cleanName = controllerName.replace(/Controller$/, '') || 'Other';
            // Only use controller name if it's not the same as a URI part
            if (!uriParts.some(part => part.toLowerCase() === cleanName.toLowerCase())) {
              groupName = cleanName;
            }
          }

          allGroups.add(groupName);
        });
        setExpandedGroups(allGroups);
      })
      .catch(() => setLoading(false));
  }, [schemaUrl]);

  const toggleGroup = (groupName: string) => {
    const newExpanded = new Set(expandedGroups);
    if (newExpanded.has(groupName)) {
      newExpanded.delete(groupName);
    } else {
      newExpanded.add(groupName);
    }
    setExpandedGroups(newExpanded);
  };

  const filteredRoutes = routes.filter(
    (route) =>
      route.uri.toLowerCase().includes(filter.toLowerCase()) ||
      route.name?.toLowerCase().includes(filter.toLowerCase())
  );

  const groupedRoutes = filteredRoutes.reduce((acc, route) => {
    let groupName = 'Other';

    // First, try to extract group from URI path (e.g., api/auth/login -> Auth)
    const uriParts = route.uri.split('/').filter(Boolean);

    // Look for the first meaningful segment that's not a common prefix or parameter
    for (let i = 0; i < uriParts.length; i++) {
      const part = uriParts[i];

      if (!part.startsWith('{') && !part.endsWith('}')) {
        // Skip common prefixes like 'api', 'admin', 'v1', etc
        if (!['api', 'admin', 'v1', 'v2', 'v3', 'web'].includes(part.toLowerCase())) {
          groupName = part
            .split('-')
            .map((word: string) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
          break;
        }
      }
    }

    // If still "Other" and we have a controller, try to use it as secondary fallback
    if (groupName === 'Other' && route.action && route.action !== 'Closure') {
      const controllerName = route.action.split('@')[0]?.split('\\').pop() || '';
      const cleanName = controllerName.replace(/Controller$/, '') || 'Other';
      // Only use controller name if it's not the same as a URI part
      if (!uriParts.some(part => part.toLowerCase() === cleanName.toLowerCase())) {
        groupName = cleanName;
      }
    }

    if (!acc[groupName]) acc[groupName] = [];
    acc[groupName].push(route);
    return acc;
  }, {} as Record<string, any[]>);


  const sortedGroups = Object.entries(groupedRoutes)
    .sort(([a], [b]) => {
      if (a === 'Other') return 1;
      if (b === 'Other') return -1;
      return a.localeCompare(b);
    })
    .map(([groupName, routes]) => {

      const sortedRoutes = routes.sort((a, b) => {
        // First by base path (without parameters)
        const aBase = a.uri.replace(/\/\{[^}]+\}/g, '');
        const bBase = b.uri.replace(/\/\{[^}]+\}/g, '');
        if (aBase !== bBase) return aBase.localeCompare(bBase);
        
        // Then by number of parameters (less parameters first)
        const aParams = (a.uri.match(/\{[^}]+\}/g) || []).length;
        const bParams = (b.uri.match(/\{[^}]+\}/g) || []).length;
        if (aParams !== bParams) return aParams - bParams;
        
        // Finally by URI
        return a.uri.localeCompare(b.uri);
      });
      
      return [groupName, sortedRoutes] as [string, any[]];
    });

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
    return (
      <div className="p-3 text-xs text-gray-400">
        <div className="flex items-center gap-2">
          <svg className="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Loading routes...
        </div>
      </div>
    );
  }

  return (
    <div className="p-3">
      <input
        id="endpoint-search"
        type="text"
        value={filter}
        onChange={(e) => setFilter(e.target.value)}
        placeholder="Search endpoints (⌘K)"
        className="w-full px-3 py-2 text-sm border border-white/10 rounded-md bg-[#1a1a1a] text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-3"
      />

      {sortedGroups.map(([groupName, routes]) => {
        const isExpanded = expandedGroups.has(groupName) || filter !== '';
        
        return (
          <div key={groupName} className="mb-3">
            <button
              onClick={() => toggleGroup(groupName)}
              className="w-full flex items-center justify-between mb-1.5 px-2 py-1.5 rounded-md hover:bg-white/5 transition-colors"
            >
              <div className="flex items-center gap-2">
                <span className="text-xs text-gray-500">
                  {isExpanded ? '▼' : '▶'}
                </span>
                <h3 className="font-semibold text-xs text-white">
                  {groupName}
                </h3>
              </div>
              <span className="text-xs text-gray-400 px-1.5 py-0.5 bg-white/5 rounded">
                {routes.length}
              </span>
            </button>
            
            {isExpanded && (
              <div className="space-y-0.5 ml-2">
                {routes.map((route, idx) => {
                  const isActive = selectedEndpoint && 
                    selectedEndpoint.uri === route.uri && 
                    selectedEndpoint.methods.join(',') === route.methods.join(',');
                  
                  return (
                    <button
                      key={idx}
                      onClick={() => onSelect(route)}
                      className={`w-full text-left px-2 py-2 rounded-md transition-colors group ${
                        isActive 
                          ? 'bg-primary/20 border border-primary/50 shadow-sm' 
                          : 'hover:bg-white/5'
                      }`}
                    >
                    <div className="flex gap-1.5 mb-1">
                      {route.methods
                        .filter((m: string) => !['HEAD', 'OPTIONS'].includes(m))
                        .map((method: string) => (
                          <span
                            key={method}
                            className={`text-xs px-1.5 py-0.5 rounded font-mono font-semibold ${
                              method === 'GET'
                                ? 'bg-blue-500/20 text-blue-400'
                                : method === 'POST'
                                ? 'bg-green-500/20 text-green-400'
                                : method === 'PUT' || method === 'PATCH'
                                ? 'bg-yellow-500/20 text-yellow-400'
                                : 'bg-red-500/20 text-red-400'
                            }`}
                          >
                            {method}
                          </span>
                        ))}
                    </div>
                    <div className="font-mono text-xs text-gray-400 group-hover:text-gray-300 transition-colors">
                      {route.uri}
                    </div>
                    {route.name && (
                      <div className="text-xs text-gray-500 mt-0.5">
                        {route.name}
                      </div>
                    )}
                  </button>
                );
              })}
              </div>
            )}
          </div>
        );
      })}
    </div>
  );
}
