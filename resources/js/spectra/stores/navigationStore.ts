import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface NavigationState {
  selectedEndpoint: any | null;
  setSelectedEndpoint: (endpoint: any | null) => void;
  response: any | null;
  setResponse: (response: any | null) => void;
  expandedGroups: Set<string>;
  toggleGroup: (groupName: string) => void;
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
    }),
    {
      name: 'spectra-navigation',
      storage: {
        getItem: (name) => {
          const item = localStorage.getItem(name);
          if (!item) return null;
          const parsed = JSON.parse(item);
          return {
            state: {
              ...parsed.state,
              expandedGroups: new Set(parsed.state.expandedGroups || []),
            },
            version: parsed.version,
          };
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
      }),
    }
  )
);
