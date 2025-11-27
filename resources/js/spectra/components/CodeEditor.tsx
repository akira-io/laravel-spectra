import { useState, useEffect } from 'react';
import { Prism as SyntaxHighlighter } from 'react-syntax-highlighter';
import { vscDarkPlus } from 'react-syntax-highlighter/dist/esm/styles/prism';

interface CodeEditorProps {
  value: string;
  onChange: (value: string) => void;
  language?: string;
  placeholder?: string;
  className?: string;
  minHeight?: string;
}

export default function CodeEditor({
  value,
  onChange,
  language = 'json',
  placeholder = '',
  className = '',
  minHeight = '200px',
}: CodeEditorProps) {
  const [isFocused, setIsFocused] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    onChange(e.target.value);
  };

  // Format JSON on blur
  const handleBlur = () => {
    setIsFocused(false);
    if (language === 'json' && value) {
      try {
        const formatted = JSON.stringify(JSON.parse(value), null, 2);
        onChange(formatted);
      } catch {
        // Keep original if invalid JSON
      }
    }
  };

  return (
    <div className={`relative ${className}`}>
      {/* Editor textarea - only show when focused */}
      {isFocused ? (
        <textarea
          value={value}
          onChange={handleChange}
          onFocus={() => setIsFocused(true)}
          onBlur={handleBlur}
          placeholder={placeholder}
          autoFocus
          className="w-full px-3 py-2 text-xs border border-input rounded-md bg-background text-foreground font-mono focus:outline-none focus:ring-2 focus:ring-ring resize-none"
          style={{ minHeight }}
          spellCheck={false}
        />
      ) : (
        /* Syntax highlighted preview - clickable to edit */
        <div 
          onClick={() => setIsFocused(true)}
          className="w-full cursor-text border border-input rounded-md bg-background/50 hover:border-ring/50 transition-colors overflow-auto"
          style={{ minHeight }}
        >
          {value ? (
            <SyntaxHighlighter
              language={language}
              style={vscDarkPlus}
              customStyle={{
                margin: 0,
                padding: '8px 12px',
                background: 'transparent',
                fontSize: '12px',
                minHeight,
              }}
              wrapLines={true}
              showLineNumbers={false}
            >
              {value}
            </SyntaxHighlighter>
          ) : (
            <div className="px-3 py-2 text-xs text-muted-foreground font-mono">
              {placeholder}
            </div>
          )}
        </div>
      )}
    </div>
  );
}
