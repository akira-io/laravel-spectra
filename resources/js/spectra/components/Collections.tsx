import { useState, useEffect } from 'react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Badge } from './ui/badge';
import { Save, Upload, Download, Trash2, FolderOpen, Plus, X } from 'lucide-react';

export default function Collections() {
  const [collections, setCollections] = useState<any[]>([]);
  const [showSave, setShowSave] = useState(false);
  const [name, setName] = useState('');

  useEffect(() => {
    const saved = localStorage.getItem('spectra-collections');
    if (saved) {
      setCollections(JSON.parse(saved));
    }
  }, []);

  const saveCollection = () => {
    if (!name.trim()) return;

    const newCollection = {
      name,
      timestamp: Date.now(),
      data: {
        authMode: (window as any).spectraAuthMode,
        authData: (window as any).spectraAuthData,
      },
    };

    const updated = [...collections, newCollection];
    setCollections(updated);
    localStorage.setItem('spectra-collections', JSON.stringify(updated));
    setName('');
    setShowSave(false);
  };

  const loadCollection = (collection: any) => {
    (window as any).spectraAuthMode = collection.data.authMode;
    (window as any).spectraAuthData = collection.data.authData;
  };

  const deleteCollection = (index: number) => {
    const updated = collections.filter((_, i) => i !== index);
    setCollections(updated);
    localStorage.setItem('spectra-collections', JSON.stringify(updated));
  };

  const exportCollections = () => {
    const dataStr = JSON.stringify(collections, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
    const link = document.createElement('a');
    link.setAttribute('href', dataUri);
    link.setAttribute('download', 'spectra-collections.json');
    link.click();
  };

  const importCollections = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      try {
        const imported = JSON.parse(event.target?.result as string);
        const updated = [...collections, ...imported];
        setCollections(updated);
        localStorage.setItem('spectra-collections', JSON.stringify(updated));
      } catch {}
    };
    reader.readAsText(file);
  };

  return (
    <div className="space-y-3">
      <div className="flex items-center justify-between">
        <Button
          onClick={() => setShowSave(!showSave)}
          size="sm"
          variant={showSave ? "default" : "outline"}
          className="w-full h-8 text-xs gradient-primary"
        >
          <Plus className="h-3 w-3 mr-1.5" />
          Save Collection
        </Button>
      </div>

      {showSave && (
        <div className="space-y-2 p-2 border border-border/50 rounded-lg bg-card/50">
          <Input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Collection name"
            className="h-8 text-xs"
            onKeyDown={(e) => e.key === 'Enter' && saveCollection()}
          />
          <div className="flex gap-1.5">
            <Button
              onClick={saveCollection}
              size="sm"
              className="flex-1 h-7 text-xs gradient-primary"
            >
              <Save className="h-3 w-3 mr-1" />
              Save
            </Button>
            <Button
              onClick={() => setShowSave(false)}
              size="sm"
              variant="outline"
              className="h-7 text-xs"
            >
              <X className="h-3 w-3" />
            </Button>
          </div>
        </div>
      )}

      {collections.length > 0 && (
        <div className="space-y-1.5">
          {collections.map((collection, idx) => (
            <div
              key={idx}
              className="flex items-center gap-1.5 p-2 border border-border/50 rounded-lg bg-card/50 hover:bg-card/80 transition-colors group"
            >
              <button
                onClick={() => loadCollection(collection)}
                className="flex-1 text-left text-xs font-medium truncate hover:text-primary transition-colors"
              >
                <FolderOpen className="h-3 w-3 inline mr-1.5" />
                {collection.name}
              </button>
              <Button
                onClick={() => deleteCollection(idx)}
                size="sm"
                variant="ghost"
                className="h-6 w-6 p-0 opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <Trash2 className="h-3 w-3 text-destructive" />
              </Button>
            </div>
          ))}
        </div>
      )}

      <div className="flex gap-1.5 pt-2 border-t border-border/50">
        <Button
          onClick={exportCollections}
          size="sm"
          variant="outline"
          disabled={collections.length === 0}
          className="flex-1 h-7 text-xs"
        >
          <Download className="h-3 w-3 mr-1" />
          Export
        </Button>
        <label className="flex-1">
          <Button
            size="sm"
            variant="outline"
            className="w-full h-7 text-xs cursor-pointer"
            asChild
          >
            <span>
              <Upload className="h-3 w-3 mr-1" />
              Import
            </span>
          </Button>
          <input type="file" onChange={importCollections} className="hidden" accept=".json" />
        </label>
      </div>
    </div>
  );
}
