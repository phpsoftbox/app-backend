import React from 'react';
import { Badge, Button, Card, Heading, Row, Stack, Text } from '@phpsoftbox/react-softbox';
import logoUrl from '../../../images/logo.svg';

type NavigationItem = {
  label: string;
  href: string;
};

type Props = {
  title: string;
  app?: {
    area?: string;
  };
  web?: {
    navigation?: NavigationItem[];
  };
};

export default function Home({ title, app, web }: Props) {
  const navigation = web?.navigation ?? [];

  return (
    <main className="shell">
      <Card className="home-card" aria-label={title}>
        <Card.Body>
          <Stack gap="28px">
            <Row gap="20px" align="center" wrap="wrap">
              <span className="logo-frame">
                <img src={logoUrl} alt="PhpSoftBox" className="logo" />
              </span>
              <Stack gap="10px" className="intro-copy">
                <Badge variant="primary" size="sm">{app?.area ?? 'web'}</Badge>
                <Heading level={1}>{title}</Heading>
                <Text as="p" size="lg" muted>
                  A quiet starting point for a PhpSoftBox backend application.
                </Text>
              </Stack>
            </Row>

            <Row gap="8px" wrap="wrap" aria-label="Application status">
              <Badge variant="info">Web</Badge>
              <Badge variant="success">Backend</Badge>
              <Badge variant="default">Ready</Badge>
            </Row>

            <Row gap="12px" wrap="wrap">
              {navigation.map((item) => (
                <Button
                  key={item.href}
                  type="button"
                  variant={item.href === '/' ? 'primary' : 'default'}
                  appearance={item.href === '/' ? 'solid' : 'outline'}
                  onClick={() => window.location.assign(item.href)}
                >
                  {item.label}
                </Button>
              ))}
            </Row>
          </Stack>
        </Card.Body>
      </Card>
    </main>
  );
}
