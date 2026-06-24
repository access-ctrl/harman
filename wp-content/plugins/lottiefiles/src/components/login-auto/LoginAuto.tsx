/**
 * Copyright 2020 Design Barn Inc.
 */

/* eslint-disable promise/catch-or-return */

import { useTracker } from '@context/tracker-provider';
import { eventEnums, eventsConst } from '@lottiefiles/plugin-tracker';
import { Appearance, Size, TextColor } from '@lottiefiles/react-ui-kit';
import { useState, useEffect } from '@wordpress/element';
import * as React from 'react';
import { useMutation, useQuery, useClient } from 'urql';

import packageJson from '../../../package.json';
import { Button } from '../../_components';
import { appDetails } from '../../helpers/consts';
import { mutation, queries } from '../../helpers/query-strings';

interface ILoginAutoProps {
  LoggingInMessage?: string;
  className?: string;
  isBrowserLogin?: boolean;
  label: string;
  onClick(): void;
  onError(): void;
  onSuccess(val: unknown): void;
}

export const LoginAuto: React.FC<ILoginAutoProps> = ({
  LoggingInMessage = 'Logging In...',
  className,
  isBrowserLogin = false,
  label,
  onSuccess,
}: ILoginAutoProps) => {
  // const [loginSiteUrl, setLoginSiteUrl] = useState<string>();
  // const [loginToken, setLoginToken] = useState<string>();
  // const [isEnabled, setIsEnabled] = useState<boolean>(false);
  // const [queryInterval, setQueryInterval] = useState<number>(0);
  // const [accessToken, setAccessToken] = useState<number>(0);
  // const [isLoggingIn, setIsLoggingIn] = useState<boolean>(false);

  const [isLoggingIn, setIsLoggingIn] = useState(false);
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [_, createLoginToken] = useMutation(mutation.createLoginToken);
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [__, tokenLogin] = useMutation(mutation.tokenLogin);
  const [accessToken, setAccessToken] = useState('');
  const [{ data: viewerData }, fetchViewer] = useQuery({
    query: queries.viewer,
    pause: true,
  });
  const tracker = useTracker();
  const client = useClient();

  useEffect(() => {
    if (viewerData && viewerData.viewer && accessToken) {
      const viewer = viewerData.viewer;

      tracker.pluginTracking({
        eventType: eventsConst.click.login,
        userId: viewer.id,
        method: appDetails.hitcountsource,
        eventProperties: { type: eventEnums.loginIntentType.completed },
      });

      onSuccess({
        accessToken,
        ...viewer,
      });

      setIsLoggingIn(false);
    }
  }, viewerData);

  async function pollLoginComplete(token, pollStartTime): Promise<void> {
    tokenLogin({ token }).then(async result => {
      // eslint-disable-next-line no-negated-condition
      if (!result.data) {
        const currentTime = new Date();

        if ((currentTime.getTime() - pollStartTime.getTime()) / 1000 / 60 < 10) {
          // eslint-disable-next-line @typescript-eslint/no-misused-promises
          setTimeout(async () => pollLoginComplete(token, pollStartTime), 2000);
        } else {
          setIsLoggingIn(false);
        }
      } else {
        const newToken = result.data.tokenLogin.accessToken;

        setAccessToken(newToken);

        client.fetchOptions = {
          headers: {
            authorization: newToken ? `Bearer ${newToken}` : '',
            'client-name': packageJson.name,
            'client-version': packageJson.version,
          },
        };
        // Needed at this point, because client that was init-ed at App level doesnt have any auth headers.
        // We can replace this with an auth exchange eventually?
        fetchViewer({
          fetchOptions: {
            headers: {
              authorization: newToken ? `Bearer ${newToken}` : '',
              'client-name': packageJson.name,
              'client-version': packageJson.version,
            },
          },
        });
      }
    });
  }

  function login(): void {
    setIsLoggingIn(true);
    tracker.pluginTracking({
      eventType: eventsConst.click.login,
      eventProperties: { type: eventEnums.loginIntentType.clicked },
    });

    createLoginToken({ appKey: 'wordpress-plugin' }).then(async result => {
      if (result.data && result.data.createLoginToken) {
        window.open(result.data.createLoginToken.loginUrl, '_blank');
        await pollLoginComplete(result.data.createLoginToken.token, new Date());
      }
    });
  }

  // useEffect(() => {
  //   if (loginSiteUrl) {
  //     window.open(loginSiteUrl, '_blank');
  //   }
  // }, [loginSiteUrl]);

  if (isBrowserLogin && isLoggingIn) {
    return <div className="mt-5">{LoggingInMessage}</div>;
  }

  return (
    <Button
      appearance={Appearance.primary}
      size={Size.small}
      disabled={isLoggingIn}
      className={className}
      textColor={TextColor.white}
      onClick={() => login()}
    >
      {isLoggingIn ? LoggingInMessage : label}
    </Button>
  );
};
