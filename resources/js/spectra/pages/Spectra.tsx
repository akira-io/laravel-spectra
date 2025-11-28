import {useEffect, useState} from 'react';
import {Head} from '@inertiajs/react';
import {useNavigationStore} from '../stores/navigationStore';
import Header from '../components/layout/Header';
import Sidebar from '../components/layout/Sidebar';
import RequestPanel from '../components/layout/RequestPanel';
import ResponsePanel from '../components/layout/ResponsePanel';
import CollectionsModal from '../components/layout/CollectionsModal';

interface Props {
    schemaUrl: string;
    executeUrl: string;
    cookiesUrl: string;
}

export default function Spectra({schemaUrl, executeUrl, cookiesUrl}: Props) {
    const {selectedEndpoint, setSelectedEndpoint, response, setResponse} = useNavigationStore();
    const [darkMode, setDarkMode] = useState(true);
    const [showCollectionsModal, setShowCollectionsModal] = useState(false);

    const handleEndpointSelect = (endpoint: any) => {
        setSelectedEndpoint(endpoint);
        setResponse(null); // Clear response when changing endpoint
    };

    useEffect(() => {
        document.body.classList.add('spectra-theme');
        document.documentElement.classList.add('dark');
        return () => {
            document.body.classList.remove('spectra-theme');
            document.documentElement.classList.remove('dark');
        };
    }, []);

    return (
      <>
          <Head title='Spectra - API Inspector'/>
          <div className={darkMode ? 'dark' : ''}>
              {/* Gradient Background - Spectra Style */}
              <div
                className='pointer-events-none fixed inset-0'
                style={{
                    zIndex: -2,
                    background: `
              radial-gradient(ellipse 80% 50% at 50% -20%, oklch(50% 0.15 285 / 0.15), transparent),
              radial-gradient(ellipse 60% 50% at 50% 120%, oklch(60% 0.12 315 / 0.1), transparent)
            `
                }}
              />
              <div className='h-screen flex flex-col bg-gradient-to-br from-background via-background to-muted/20'>
                  <Header
                      darkMode={darkMode}
                      onDarkModeToggle={() => setDarkMode(!darkMode)}
                      onCollectionsClick={() => setShowCollectionsModal(true)}
                  />

                  <div className='flex-1 flex overflow-hidden'>
                      <Sidebar
                          schemaUrl={schemaUrl}
                          onEndpointSelect={handleEndpointSelect}
                          selectedEndpoint={selectedEndpoint}
                          onCollectionsClick={() => setShowCollectionsModal(true)}
                      />

                      <div className='flex-1 flex overflow-hidden'>
                          <RequestPanel
                              selectedEndpoint={selectedEndpoint}
                              executeUrl={executeUrl}
                              onResponse={setResponse}
                              cookiesUrl={cookiesUrl}
                          />

                          <ResponsePanel response={response} />
                      </div>
                  </div>
              </div>

              <CollectionsModal
                  open={showCollectionsModal}
                  onOpenChange={setShowCollectionsModal}
              />
          </div>
      </>
    );
}
