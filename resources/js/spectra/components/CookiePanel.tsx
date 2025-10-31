import { useState, useEffect } from 'react';
import ky from 'ky';
import { Badge } from './ui/badge';
import { Loader2, Lock } from 'lucide-react';

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
    return (
      <div className="p-3 flex items-center justify-center text-xs text-muted-foreground">
        <Loader2 className="h-3 w-3 animate-spin mr-2" />
        Loading...
      </div>
    );
  }

  return (
    <div className="p-2">
      {cookies.length === 0 && (
        <p className="text-xs text-muted-foreground text-center py-4">No cookies found</p>
      )}

      <div className="space-y-2">
        {cookies.map((cookie, idx) => (
          <div
            key={idx}
            className="p-2 border border-border/50 rounded-lg bg-card/50 hover:bg-card/80 transition-colors"
          >
            <div className="flex items-center justify-between mb-1">
              <div className="font-semibold text-xs truncate flex-1">{cookie.name}</div>
              {cookie.encrypted && (
                <Badge variant="secondary" className="h-4 text-[10px] px-1.5 py-0">
                  <Lock className="h-2.5 w-2.5 mr-0.5" />
                  Enc
                </Badge>
              )}
            </div>
            <div className="font-mono text-[10px] text-muted-foreground break-all line-clamp-2">
              {cookie.value}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
