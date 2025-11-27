# Keyboard Shortcuts

Spectra provides keyboard shortcuts for efficient navigation and operation.

## Global Shortcuts

### Search

| Shortcut | Action | Description |
|----------|--------|-------------|
| `Cmd/Ctrl + K` | Quick search | Open endpoint search |
| `/` | Focus search | Focus search input |
| `Esc` | Close search | Close search dialog |

### Request Actions

| Shortcut | Action | Description |
|----------|--------|-------------|
| `Cmd/Ctrl + Enter` | Send request | Execute current request |
| `Cmd/Ctrl + S` | Save request | Save to collections |
| `Cmd/Ctrl + R` | Refresh | Reload endpoint list |

### Navigation

| Shortcut | Action | Description |
|----------|--------|-------------|
| `Tab` | Next field | Move to next input |
| `Shift + Tab` | Previous field | Move to previous input |
| `Esc` | Close modal | Close open dialogs |

### View Actions

| Shortcut | Action | Description |
|----------|--------|-------------|
| `Cmd/Ctrl + B` | Toggle sidebar | Show/hide endpoint tree |
| `Cmd/Ctrl + .` | Toggle theme | Switch dark/light mode |
| `Cmd/Ctrl + /` | Show help | Display keyboard shortcuts |

## Search Shortcuts

### In Search Dialog

| Shortcut | Action |
|----------|--------|
| `↑` | Previous result |
| `↓` | Next result |
| `Enter` | Select result |
| `Esc` | Close dialog |

### Search Filters

| Shortcut | Filter |
|----------|--------|
| `Cmd/Ctrl + G` | Filter GET routes |
| `Cmd/Ctrl + P` | Filter POST routes |
| `Cmd/Ctrl + U` | Filter PUT routes |
| `Cmd/Ctrl + D` | Filter DELETE routes |

## Editor Shortcuts

### Code Editor

| Shortcut | Action |
|----------|--------|
| `Cmd/Ctrl + A` | Select all |
| `Cmd/Ctrl + C` | Copy |
| `Cmd/Ctrl + V` | Paste |
| `Cmd/Ctrl + Z` | Undo |
| `Cmd/Ctrl + Shift + Z` | Redo |
| `Cmd/Ctrl + F` | Find in editor |

### JSON Formatting

| Shortcut | Action |
|----------|--------|
| `Cmd/Ctrl + Shift + F` | Format JSON |
| `Cmd/Ctrl + Shift + C` | Copy formatted |

## Response Viewer

| Shortcut | Action |
|----------|--------|
| `Cmd/Ctrl + C` | Copy response |
| `Cmd/Ctrl + Shift + C` | Copy pretty JSON |
| `1` | JSON tab |
| `2` | Raw tab |
| `3` | Headers tab |
| `4` | Cookies tab |

## Collections Panel

| Shortcut | Action |
|----------|--------|
| `Cmd/Ctrl + N` | New collection |
| `Cmd/Ctrl + E` | Export collections |
| `Cmd/Ctrl + I` | Import collections |
| `Delete` | Delete selected |

## Form Inputs

### Text Inputs

| Shortcut | Action |
|----------|--------|
| `Cmd/Ctrl + A` | Select all |
| `Cmd/Ctrl + C` | Copy |
| `Cmd/Ctrl + V` | Paste |
| `Cmd/Ctrl + X` | Cut |

### Dropdowns

| Shortcut | Action |
|----------|--------|
| `Space` | Open dropdown |
| `↑` | Previous option |
| `↓` | Next option |
| `Enter` | Select option |
| `Esc` | Close dropdown |

## Browser Shortcuts

Standard browser shortcuts still work:

| Shortcut | Action |
|----------|--------|
| `Cmd/Ctrl + R` | Reload page |
| `Cmd/Ctrl + Shift + R` | Hard reload |
| `Cmd/Ctrl + W` | Close tab |
| `Cmd/Ctrl + T` | New tab |
| `Cmd/Ctrl + L` | Focus address bar |
| `F12` | Open DevTools |

## Customization

### Disable Shortcuts

To disable Spectra shortcuts:

```typescript
// In resources/js/spectra/main.tsx
const disableShortcuts = true;

if (!disableShortcuts) {
    // Register shortcuts
}
```

### Custom Shortcuts

Add custom shortcuts:

```typescript
import { useEffect } from 'react';

function useCustomShortcuts() {
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            // Custom shortcut: Ctrl+Q for custom action
            if (e.ctrlKey && e.key === 'q') {
                e.preventDefault();
                customAction();
            }
        };
        
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);
}
```

## Accessibility

### Screen Readers

All shortcuts have aria-labels:

```tsx
<button aria-label="Send request (Cmd+Enter)">
    Send
</button>
```

### Focus Management

Keyboard navigation respects tab order:
1. Search input
2. Endpoint tree
3. Request builder
4. Send button
5. Response viewer

### Focus Indicators

Clear focus indicators for keyboard navigation.

## Platform Differences

### macOS

- Uses `Cmd` key
- `Cmd + K` for search
- `Cmd + Enter` for send

### Windows/Linux

- Uses `Ctrl` key
- `Ctrl + K` for search
- `Ctrl + Enter` for send

### Detection

Shortcuts automatically detect platform:

```typescript
const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
const modKey = isMac ? 'Cmd' : 'Ctrl';
```

## Tips

1. **Learn gradually**: Start with `Cmd/Ctrl + K` and `Cmd/Ctrl + Enter`
2. **Display shortcuts**: Hover over buttons to see shortcuts
3. **Practice**: Shortcuts become muscle memory
4. **Customize**: Add shortcuts that match your workflow
5. **Accessibility**: Use Tab for keyboard-only navigation

## Cheat Sheet

Print-friendly quick reference:

```
SEARCH
Cmd/Ctrl + K    Quick search
/               Focus search

REQUEST
Cmd/Ctrl+Enter  Send request
Cmd/Ctrl + S    Save request

VIEW
Cmd/Ctrl + B    Toggle sidebar
Cmd/Ctrl + .    Toggle theme

NAVIGATION
Tab             Next field
Shift + Tab     Previous field
Esc             Close modal
```

## Next Steps

- [Quick Start](../quick-start.md) - Learn the basics
- [Interface Overview](../ui/overview.md) - UI components
