import React from 'react';
import { Badge, Button, Card, Heading, Row, Stack, Text } from '@phpsoftbox/react-softbox';

type NavigationItem = {
  label: string;
  href: string;
};

type Props = {
  title: string;
  app?: {
    area?: string;
  };
  admin?: {
    navigation?: NavigationItem[];
  };
};

export default function Dashboard({ title, app, admin }: Props) {
  const navigation = admin?.navigation ?? [];

  return (
    <main className="shell admin-shell">
      <Card className="home-card admin-card" aria-label={title}>
        <Card.Body>
          <Stack gap="24px">
            <Row gap="12px" align="center" wrap="wrap">
              <Badge variant="warning" size="sm">{app?.area ?? 'admin'}</Badge>
              <Heading level={1}>{title}</Heading>
            </Row>

            <Text as="p" size="lg" muted>
              Workspace for application management.
            </Text>

            <Row gap="8px" wrap="wrap" aria-label="Admin status">
              <Badge variant="success">Overview</Badge>
              <Badge variant="info">Users</Badge>
              <Badge variant="default">Settings</Badge>
            </Row>

            <Row gap="12px" wrap="wrap">
              {navigation.map((item) => (
                <Button
                  key={item.href}
                  type="button"
                  variant="primary"
                  appearance="outline"
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
