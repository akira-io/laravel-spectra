import { useState } from 'react';
import { Head } from '@inertiajs/react';
import EndpointTree from '../components/EndpointTree';
import RequestBuilder from '../components/RequestBuilder';
import AuthPanel from '../components/AuthPanel';
import ResponseViewer from '../components/ResponseViewer';
import CookiePanel from '../components/CookiePanel';
import Collections from '../components/Collections';

interface Props {
  schemaUrl: string;
  executeUrl: string;
  cookiesUrl: string;
}

export default function Spectra({ schemaUrl, executeUrl, cookiesUrl }: Props) {
  const [selectedEndpoint, setSelectedEndpoint] = useState<any>(null);
  const [response, setResponse] = useState<any>(null);
  const [darkMode, setDarkMode] = useState(false);

  return (
    <>
      <Head title="Spectra API Inspector" />
      <div className={darkMode ? 'dark' : ''}>
        <div className="min-h-screen bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
          <header className="border-b border-gray-200 dark:border-gray-700 p-4">
            <div className="flex items-center justify-between">
              <h1 className="text-2xl font-bold">Spectra API Inspector</h1>
              <button
                onClick={() => setDarkMode(!darkMode)}
                className="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600"
              >
                {darkMode ? '☀️' : '🌙'}
              </button>
            </div>
          </header>

          <div className="flex h-[calc(100vh-73px)]">
            <aside className="w-80 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
              <EndpointTree
                schemaUrl={schemaUrl}
                onSelect={setSelectedEndpoint}
              />
              <Collections />
            </aside>

            <main className="flex-1 flex flex-col">
              <div className="flex-1 overflow-y-auto p-6">
                {selectedEndpoint ? (
                  <>
                    <AuthPanel />
                    <RequestBuilder
                      endpoint={selectedEndpoint}
                      executeUrl={executeUrl}
                      onResponse={setResponse}
                    />
                  </>
                ) : (
                  <div className="flex items-center justify-center h-full text-gray-500">
                    Select an endpoint to get started
                  </div>
                )}
              </div>

              {response && (
                <div className="border-t border-gray-200 dark:border-gray-700 h-1/2 overflow-hidden">
                  <ResponseViewer response={response} />
                </div>
              )}
            </main>

            <aside className="w-80 border-l border-gray-200 dark:border-gray-700 overflow-y-auto">
              <CookiePanel cookiesUrl={cookiesUrl} />
            </aside>
          </div>
        </div>
      </div>
    </>
  );
}
