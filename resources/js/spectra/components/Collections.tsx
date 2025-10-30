import { useState, useEffect } from 'react';

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
    <div className="p-4 border-t border-gray-200 dark:border-gray-700">
      <div className="flex items-center justify-between mb-3">
        <h3 className="font-semibold text-sm">Collections</h3>
        <button
          onClick={() => setShowSave(!showSave)}
          className="text-sm px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          + Save
        </button>
      </div>

      {showSave && (
        <div className="mb-3 p-2 border border-gray-300 dark:border-gray-600 rounded">
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Collection name"
            className="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 mb-2"
          />
          <button
            onClick={saveCollection}
            className="w-full text-sm px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700"
          >
            Save
          </button>
        </div>
      )}

      <div className="space-y-2 mb-3">
        {collections.map((collection, idx) => (
          <div
            key={idx}
            className="flex items-center justify-between p-2 border border-gray-300 dark:border-gray-600 rounded text-sm"
          >
            <button onClick={() => loadCollection(collection)} className="flex-1 text-left">
              {collection.name}
            </button>
            <button
              onClick={() => deleteCollection(idx)}
              className="text-red-600 hover:text-red-700 ml-2"
            >
              ×
            </button>
          </div>
        ))}
      </div>

      <div className="flex gap-2">
        <button
          onClick={exportCollections}
          className="flex-1 text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
        >
          Export
        </button>
        <label className="flex-1 text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 text-center cursor-pointer">
          Import
          <input type="file" onChange={importCollections} className="hidden" accept=".json" />
        </label>
      </div>
    </div>
  );
}
