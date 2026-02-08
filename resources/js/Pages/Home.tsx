import React from 'react';

type Props = {
  title: string;
};

export default function Home({ title }: Props) {
  return (
    <main style={{ padding: '32px', fontFamily: 'system-ui, sans-serif' }}>
      <h1>{title}</h1>
      <p>Inertia + Vite готовы к работе.</p>
    </main>
  );
}
