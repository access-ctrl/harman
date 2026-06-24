/**
 * Copyright 2020 Design Barn Inc.
 */

import { Client } from './client';

function createTrackerBridge(
  apiKey: string,
  time: string,
  platform: string,
  appVersion: string,
  deviceId: string,
  sourceId: number,
  token: string,
  notTracking: boolean,
) {
  try {
    return Client.getInstance(apiKey, time, platform, appVersion, deviceId, sourceId, token, notTracking);
  } catch (error) {
    console.error('Error creating tracker bridge:', error);
    throw error;
  }
}

export { Client, createTrackerBridge };
