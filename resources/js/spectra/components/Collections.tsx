import { useState, useEffect } from 'react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Save, Upload, Download, Trash2, FolderOpen, Plus, X, BookOpen, Zap, Shield, Archive } from 'lucide-react';

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

  if (collections.length === 0 && !showSave) {
    return (
      <div className="space-y-6">
        {/* Welcome Section */}
        <div className="text-center space-y-3 pt-4">
          <div className="inline-flex p-3 rounded-xl bg-primary/10 mb-2">
            <Archive className="h-6 w-6 text-primary" />
          </div>
          <div>
            <h3 className="text-sm font-semibold mb-1">No Collections Yet</h3>
            <p className="text-xs text-muted-foreground">
              Create your first collection to save and organize your API request configurations
            </p>
          </div>
        </div>

        {/* Feature Cards */}
        <div className="grid grid-cols-1 gap-2.5">
          <div className="flex gap-3 p-3 rounded-lg bg-primary/5 border border-primary/10">
            <div className="flex-shrink-0">
              <Zap className="h-4 w-4 text-primary mt-0.5" />
            </div>
            <div>
              <p className="text-xs font-medium">Quick Save</p>
              <p className="text-[10px] text-muted-foreground mt-0.5">Save your current authentication and request setup</p>
            </div>
          </div>

          <div className="flex gap-3 p-3 rounded-lg bg-primary/5 border border-primary/10">
            <div className="flex-shrink-0">
              <Shield className="h-4 w-4 text-primary mt-0.5" />
            </div>
            <div>
              <p className="text-xs font-medium">Secure Storage</p>
              <p className="text-[10px] text-muted-foreground mt-0.5">Collections are stored locally in your browser</p>
            </div>
          </div>

          <div className="flex gap-3 p-3 rounded-lg bg-primary/5 border border-primary/10">
            <div className="flex-shrink-0">
              <BookOpen className="h-4 w-4 text-primary mt-0.5" />
            </div>
            <div>
              <p className="text-xs font-medium">Easy Access</p>
              <p className="text-[10px] text-muted-foreground mt-0.5">Load any collection with a single click</p>
            </div>
          </div>
        </div>

        {/* CTA Button */}
        <Button
          onClick={() => setShowSave(true)}
          size="sm"
          className="w-full h-9 text-sm gradient-primary font-semibold shine-effect"
        >
          <Plus className="h-4 w-4 mr-2" />
          Create First Collection
        </Button>

        {/* Import Section */}
        <div className="space-y-2 pt-2 border-t border-border/50">
          <p className="text-xs text-muted-foreground text-center font-medium">
            Or import existing collections
          </p>
          <label>
            <Button
              size="sm"
              variant="outline"
              className="w-full h-8 text-xs cursor-pointer"
              asChild
            >
              <span>
                <Upload className="h-3.5 w-3.5 mr-1.5" />
                Import Collections
              </span>
            </Button>
            <input type="file" onChange={importCollections} className="hidden" accept=".json" />
          </label>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {/* Save Collection Form */}
      <div>
        <Button
          onClick={() => setShowSave(!showSave)}
          size="sm"
          variant={showSave ? "default" : "outline"}
          className="w-full h-9 text-sm gradient-primary font-semibold"
        >
          <Plus className="h-4 w-4 mr-2" />
          {showSave ? 'Cancel' : 'Save New Collection'}
        </Button>
      </div>

      {showSave && (
        <div className="space-y-2.5 p-3 rounded-lg bg-primary/5 border border-primary/20">
          <div>
            <label className="text-xs font-medium text-foreground block mb-1.5">
              Collection Name
            </label>
            <Input
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="e.g., Production API Setup"
              className="h-9 text-sm"
              autoFocus
              onKeyDown={(e) => e.key === 'Enter' && saveCollection()}
            />
          </div>
          <div className="flex gap-2 pt-1">
            <Button
              onClick={saveCollection}
              disabled={!name.trim()}
              size="sm"
              className="flex-1 h-8 text-xs gradient-primary font-medium"
            >
              <Save className="h-3.5 w-3.5 mr-1" />
              Save Collection
            </Button>
            <Button
              onClick={() => {
                setShowSave(false);
                setName('');
              }}
              size="sm"
              variant="outline"
              className="h-8 text-xs"
            >
              <X className="h-3.5 w-3.5" />
            </Button>
          </div>
        </div>
      )}

      {/* Collections List */}
      {collections.length > 0 && (
        <div className="space-y-2">
          <p className="text-xs font-medium text-muted-foreground px-1">
            Saved Collections ({collections.length})
          </p>
          <div className="space-y-1.5">
            {collections.map((collection, idx) => (
              <button
                key={idx}
                onClick={() => loadCollection(collection)}
                className="w-full text-left p-3 rounded-lg border border-border/50 bg-card/30 hover:bg-primary/10 hover:border-primary/30 transition-all group"
              >
                <div className="flex items-start justify-between gap-2">
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-0.5">
                      <FolderOpen className="h-4 w-4 text-primary flex-shrink-0" />
                      <p className="text-sm font-medium truncate">{collection.name}</p>
                    </div>
                    <p className="text-[10px] text-muted-foreground">
                      {new Date(collection.timestamp).toLocaleDateString('pt-PT', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                      })}
                    </p>
                  </div>
                  <Button
                    onClick={(e) => {
                      e.stopPropagation();
                      deleteCollection(idx);
                    }}
                    size="sm"
                    variant="ghost"
                    className="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                  >
                    <Trash2 className="h-3.5 w-3.5 text-destructive" />
                  </Button>
                </div>
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Export/Import Actions */}
      {collections.length > 0 && (
        <div className="space-y-2 pt-2 border-t border-border/50">
          <p className="text-xs font-medium text-muted-foreground px-1">
            Import / Export
          </p>
          <div className="flex gap-2">
            <Button
              onClick={exportCollections}
              size="sm"
              variant="outline"
              className="flex-1 h-8 text-xs"
            >
              <Download className="h-3.5 w-3.5 mr-1" />
              Export
            </Button>
            <label className="flex-1">
              <Button
                size="sm"
                variant="outline"
                className="w-full h-8 text-xs cursor-pointer"
                asChild
              >
                <span>
                  <Upload className="h-3.5 w-3.5 mr-1" />
                  Import
                </span>
              </Button>
              <input type="file" onChange={importCollections} className="hidden" accept=".json" />
            </label>
          </div>
        </div>
      )}
    </div>
  );
}
