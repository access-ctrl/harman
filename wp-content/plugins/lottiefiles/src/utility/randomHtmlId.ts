/**
 * Copyright 2022 Design Barn Inc.
 */

// Create Random Html Ids for Lottie Players for frontend
export const randomHtmlId = (): string => {
  let text = '';
  // eslint-disable-next-line no-secrets/no-secrets
  const possible = 'abcdefghijklmnopqrstuvwxyz';

  // eslint-disable-next-line no-plusplus
  for (let i = 0; i < 8; i++) text += possible.charAt(Math.floor(Math.random() * possible.length));

  return text;
};
