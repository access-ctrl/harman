/**
 * Copyright 2022 Design Barn Inc.
 */

import { api, amplitudeAPIDomain } from '@helpers/consts';

// eslint-disable-next-line @typescript-eslint/ban-types
export const tracker = (trackerApiKey: string, events: unknown[]): object => {
  return fetch(amplitudeAPIDomain, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: '*/*',
    },
    body: JSON.stringify({
      // eslint-disable-next-line @typescript-eslint/naming-convention
      api_key: trackerApiKey,
      events,
    }),
  });
};

export async function gqlFetch(
  query: string,
  hcToken?: string,
  variables?: Record<string, unknown>
): Promise<unknown> {
  try {
    const response = await fetch(api.graphql, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: hcToken ? `Bearer ${hcToken}` : '',
      },
      body: JSON.stringify({ query, variables }),
    });

    if (!response.ok) {
      console.error('GraphQL request failed:', {
        status: response.status,
        statusText: response.statusText
      });
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    
    if (data.errors) {
      console.error('GraphQL errors:', data.errors);
      throw new Error('GraphQL request failed');
    }

    return data;
  } catch (error) {
    console.error('Error in gqlFetch:', error);
    throw error;
  }
};
