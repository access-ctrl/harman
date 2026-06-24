/**
 * Copyright 2020 Design Barn Inc.
 */

import * as React from 'react';
import { invariant } from 'ts-invariant';

import { Client } from '../host';

export interface ITrackerContextValue {
  instance?: Client;
}

let trackerContext: React.Context<ITrackerContextValue>;

export function getTrackerContext(): React.Context<ITrackerContextValue> {
  if (!trackerContext) {
    trackerContext = React.createContext<ITrackerContextValue>({});
  }

  return trackerContext;
}

// Create use hook for consuming the default Tracker context
export function useTracker(): Client {
  const { instance } = React.useContext(getTrackerContext());

  invariant(
    instance,
    'No Tracker Client instance can be found. Please ensure that you ' +
      'have called `TrackerProvider` higher up in your tree.',
  );

  return instance as Client;
}

export interface ITrackerProviderProps {
  instance?: Client;
  // eslint-disable-next-line typescript-sort-keys/interface
  children: React.ReactNode | React.ReactNode[] | null;
}

// Create context provider component for the Tracker context
export const TrackerProvider: React.FC<ITrackerProviderProps> = ({ children, instance }) => {
  const TrackerContext = getTrackerContext();

  return (
    <TrackerContext.Consumer>
      {(context = {}): React.ReactElement<ITrackerContextValue> => {
        if (instance && context.instance !== instance) {
          // eslint-disable-next-line no-param-reassign
          context = { ...context, instance };
        }

        invariant(context.instance, 'TrackerProvider was not passed an application instance.');

        return <TrackerContext.Provider value={context}>{children}</TrackerContext.Provider>;
      }}
    </TrackerContext.Consumer>
  );
};
