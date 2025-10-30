import { useState } from 'react';
import ky from 'ky';

interface Props {
  endpoint: any;
  executeUrl: string;
  onResponse: (response: any) => void;
}

export default function RequestBuilder({ endpoint, executeUrl, onResponse }: Props) {
  const [method, setMethod] = useState(endpoint.methods.filter((m: string) => !['HEAD', 'OPTIONS'].includes(m))[0]);
  const [pathParams, setPathParams] = useState<Record<string, string>>({});
  const [query, setQuery] = useState<Record<string, string>>({});
  const [headers, setHeaders] = useState<Record<string, string>>({ 'Accept': 'application/json' });
  const [body, setBody] = useState('');
  const [loading, setLoading] = useState(false);

  const handleExecute = async () => {
    setLoading(true);

    try {
      const authMode = (window as any).spectraAuthMode || 'current';
      const authData = (window as any).spectraAuthData || {};

      const result = await ky
        .post(executeUrl, {
          json: {
            endpoint: endpoint.uri,
            method,
            path_params: pathParams,
            query,
            headers,
            body: body ? JSON.parse(body) : null,
            auth_mode: authMode,
            ...authData,
          },
        })
        .json();

      onResponse(result);
    } catch (error: any) {
      onResponse({
        status: error.response?.status || 500,
        body: { error: error.message },
        time_ms: 0,
        size_bytes: 0,
        headers: {},
      });
    } finally {
      setLoading(false);
    }
  };

  const parameterInputs = endpoint.parameters || [];

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-bold mb-2">{endpoint.uri}</h2>
        <select
          value={method}
          onChange={(e) => setMethod(e.target.value)}
          className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800"
        >
          {endpoint.methods
            .filter((m: string) => !['HEAD', 'OPTIONS'].includes(m))
            .map((m: string) => (
              <option key={m} value={m}>
                {m}
              </option>
            ))}
        </select>
      </div>

      {parameterInputs.length > 0 && (
        <div>
          <h3 className="font-semibold mb-2">Path Parameters</h3>
          {parameterInputs.map((param: any) => (
            <div key={param.name} className="mb-2">
              <label className="block text-sm mb-1">
                {param.name} {param.required && <span className="text-red-500">*</span>}
              </label>
              <input
                type="text"
                value={pathParams[param.name] || ''}
                onChange={(e) => setPathParams({ ...pathParams, [param.name]: e.target.value })}
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800"
              />
            </div>
          ))}
        </div>
      )}

      <div>
        <h3 className="font-semibold mb-2">Query Parameters</h3>
        <textarea
          value={JSON.stringify(query, null, 2)}
          onChange={(e) => {
            try {
              setQuery(JSON.parse(e.target.value));
            } catch {}
          }}
          className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 font-mono text-sm h-24"
          placeholder='{"key": "value"}'
        />
      </div>

      <div>
        <h3 className="font-semibold mb-2">Headers</h3>
        <textarea
          value={JSON.stringify(headers, null, 2)}
          onChange={(e) => {
            try {
              setHeaders(JSON.parse(e.target.value));
            } catch {}
          }}
          className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 font-mono text-sm h-24"
        />
      </div>

      {!['GET', 'HEAD'].includes(method) && (
        <div>
          <h3 className="font-semibold mb-2">Body</h3>
          <textarea
            value={body}
            onChange={(e) => setBody(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 font-mono text-sm h-48"
            placeholder='{"key": "value"}'
          />
        </div>
      )}

      <button
        onClick={handleExecute}
        disabled={loading}
        className="px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold"
      >
        {loading ? 'Executing...' : 'Execute Request'}
      </button>
    </div>
  );
}
