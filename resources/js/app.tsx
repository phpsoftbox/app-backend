import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { DebugProvider, ProfilerDebugPanel } from '@phpsoftbox/profiler-js';
import type { ProfilerSharedProps } from '@phpsoftbox/profiler-js';
import { initTheme } from '@phpsoftbox/react-softbox';
import type { ComponentType } from 'react';
import '@phpsoftbox/react-softbox/foundations/index.css';
import './styles.css';

initTheme({ defaultMode: 'light' });

const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true }) as Record<
  string,
  { default: ComponentType }
>;

type AppPageProps = {
  profiler?: ProfilerSharedProps;
};

createInertiaApp({
  resolve: (name) => {
    const page = pages[`./Pages/${name}.tsx`];
    if (!page) {
      throw new Error(`Page "${name}" not found.`);
    }
    return page.default;
  },
  setup({ el, App, props }) {
    const pageProps = props.initialPage.props as AppPageProps;

    createRoot(el).render(
      <DebugProvider profiler={pageProps.profiler}>
        <App {...props} />
        <ProfilerDebugPanel />
      </DebugProvider>,
    );
  },
});
