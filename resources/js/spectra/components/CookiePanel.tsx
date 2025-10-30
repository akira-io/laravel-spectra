import { useState, useEffect } from 'react';
import ky from 'ky';

interface Props {
  cookiesUrl: string;
}

export default function CookiePanel({ cookiesUrl }: Props) {
  const [cookies, setCookies] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    ky.get(cookiesUrl)
      .json()
      .then((data: any) => {
        setCookies(data.data || []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, [cookiesUrl]);

  if (loading) {
    return <div className="p-4">Loading cookies...</div>;
  }

  return (
    <div className="p-4">
      <h3 className="font-semibold mb-4">Cookies</h3>

      {cookies.length === 0 && (
        <p className="text-sm text-gray-500">No cookies found</p>
      )}

      {cookies.map((cookie, idx) => (
        <div
          key={idx}
          className="mb-4 p-3 border border-gray-300 dark:border-gray-600 rounded text-sm"
        >
          <div className="font-semibold mb-1">{cookie.name}</div>
          {cookie.encrypted && (
            <span className="inline-block px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs mb-2">
              🔒 Encrypted
            </span>
          )}
          <div className="font-mono text-xs text-gray-600 dark:text-gray-400 break-all">
            {cookie.value}
          </div>
        </div>
      ))}
    </div>
  );
}
