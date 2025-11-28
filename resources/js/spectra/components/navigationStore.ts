import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface ResponseHistoryItem {
  id: string;
  timestamp: number;
  response: any;
}

interface NavigationState {
  selectedEndpoint: any | null;
  setSelectedEndpoint: (endpoint: any | null) => void;
  response: any | null;
  setResponse: (response: any | null) => void;
  expandedGroups: Set<string>;
  toggleGroup: (groupName: string) => void;
  bodyMode: 'json' | 'form';
  setBodyMode: (mode: 'json' | 'form') => void;
  responseHistory: Record<string, ResponseHistoryItem[]>; // keyed by endpoint URI
  addToHistory: (endpoint: any, response: any) => void;
  clearHistory: (endpointUri?: string) => void;
}

export const useNavigationStore = create<NavigationState>()(
  persist(
    (set) => ({
      selectedEndpoint: null,
      setSelectedEndpoint: (endpoint) => set({ selectedEndpoint: endpoint }),
      response: null,
      setResponse: (response) => set({ response }),
      expandedGroups: new Set<string>(),
      toggleGroup: (groupName) =>
        set((state) => {
          const newExpanded = new Set(state.expandedGroups);
          if (newExpanded.has(groupName)) {
            newExpanded.delete(groupName);
          } else {
            newExpanded.add(groupName);
          }
          return { expandedGroups: newExpanded };
        }),
      bodyMode: 'json',
      setBodyMode: (mode) => set({ bodyMode: mode }),
      responseHistory: {},
      addToHistory: (endpoint, response) =>
        set((state) => {
          const endpointKey = endpoint.uri;
          const newItem: ResponseHistoryItem = {
            id: `${endpoint.uri}-${Date.now()}`,
            timestamp: Date.now(),
            response,
          };
          
          const currentHistory = state.responseHistory[endpointKey] || [];
          // Keep last 10 items per endpoint
          const updatedHistory = [newItem, ...currentHistory].slice(0, 10);
          
          return {
            responseHistory: {
              ...state.responseHistory,
              [endpointKey]: updatedHistory,
            },
          };
        }),
      clearHistory: (endpointUri) =>
        set((state) => {
          if (endpointUri) {
            const { [endpointUri]: _, ...rest } = state.responseHistory;
            return { responseHistory: rest };
          }
          return { responseHistory: {} };
        }),
    }),
    {
      name: 'spectra-navigation',
      version: 2, // Increment version to clear old data
      storage: {
        getItem: (name) => {
          try {
            const item = localStorage.getItem(name);
            if (!item) return null;
            const parsed = JSON.parse(item);
            
            // Check version compatibility
            if (parsed.version !== 2) {
              localStorage.removeItem(name);
              return null;
            }
            
            return {
              state: {
                ...parsed.state,
                expandedGroups: new Set(parsed.state.expandedGroups || []),
                responseHistory: parsed.state.responseHistory || {},
              },
              version: parsed.version,
            };
          } catch (error) {
            console.error('Error loading state:', error);
            localStorage.removeItem(name);
            return null;
          }
        },
        setItem: (name, value) => {
          localStorage.setItem(
            name,
            JSON.stringify({
              state: {
                ...value.state,
                expandedGroups: Array.from(value.state.expandedGroups),
              },
              version: value.version,
            })
          );
        },
        removeItem: (name) => localStorage.removeItem(name),
      },
      partialize: (state) => ({
        selectedEndpoint: state.selectedEndpoint,
        expandedGroups: state.expandedGroups,
        bodyMode: state.bodyMode,
        responseHistory: state.responseHistory,
      }),
    }
  )
);
