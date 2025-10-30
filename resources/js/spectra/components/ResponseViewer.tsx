import { useState, useEffect } from 'react';

interface Props {
  response: any;
}

export default function ResponseViewer({ response }: Props) {
  const [tab, setTab] = useState<'json' | 'raw' | 'headers'>('json');
  const [previousResponse, setPreviousResponse] = useState<any>(null);

  useEffect(() => {
    if (response) {
      setPreviousResponse(response);
    }
  }, [response]);

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
  };

  const getStatusColor = (status: number) => {
    if (status >= 200 && status < 300) return 'text-green-600 dark:text-green-400';
    if (status >= 300 && status < 400) return 'text-blue-600 dark:text-blue-400';
    if (status >= 400 && status < 500) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
  };

  return (
    <div className="flex flex-col h-full">
      <div className="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <div className="flex items-center gap-4">
          <span className={`font-bold ${getStatusColor(response.status)}`}>
            {response.status}
          </span>
          <span className="text-sm text-gray-600 dark:text-gray-400">
            {response.time_ms}ms
          </span>
          <span className="text-sm text-gray-600 dark:text-gray-400">
            {(response.size_bytes / 1024).toFixed(2)}KB
          </span>
        </div>

        <div className="flex gap-2">
          {['json', 'raw', 'headers'].map((t) => (
            <button
              key={t}
              onClick={() => setTab(t as any)}
              className={`px-3 py-1 rounded text-sm ${
                tab === t
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600'
              }`}
            >
              {t.charAt(0).toUpperCase() + t.slice(1)}
            </button>
          ))}
          <button
            onClick={() => {
              const content =
                tab === 'json'
                  ? JSON.stringify(response.body, null, 2)
                  : tab === 'headers'
                  ? JSON.stringify(response.headers, null, 2)
                  : JSON.stringify(response, null, 2);
              copyToClipboard(content);
            }}
            className="px-3 py-1 rounded text-sm bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600"
          >
            📋 Copy
          </button>
        </div>
      </div>

      <div className="flex-1 overflow-auto p-4">
        <pre className="font-mono text-sm whitespace-pre-wrap">
          {tab === 'json' && JSON.stringify(response.body, null, 2)}
          {tab === 'raw' && JSON.stringify(response, null, 2)}
          {tab === 'headers' && JSON.stringify(response.headers, null, 2)}
        </pre>
      </div>
    </div>
  );
}
