/**
 * Copyright 2022 Design Barn Inc.
 */

import { useTracker } from '@context/tracker-provider';
import { URLS } from '@helpers/consts';
import { eventsConst } from '@lottiefiles/plugin-tracker';
import { CheckboxControl } from '@wordpress/components';
import { useReducer, useState } from '@wordpress/element';
import * as React from 'react';

import { LoginAuto } from '../../../../components/login-auto';
import { LottieProvider } from '../../../../context/lottie-provider';
import { ISetupProps } from '../../interfaces';
import {
  IHNResponseProps,
  ISettingReducerProps,
  IStateProps,
  IUserDataProps,
  onUpdateSettings,
  settingReducer,
} from '../../reducer';

export const Setup: React.FC<ISetupProps> = ({ onSaveSettings, settings }: ISetupProps) => {
  const [state, dispatch] = useReducer<ISettingReducerProps, IStateProps>(
    settingReducer,
    {
      isLoading: false,
      settings,
    },
    // eslint-disable-next-line no-undefined
    undefined,
  );
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const tracker = useTracker();

  const onChange = (newData: IHNResponseProps): void => {
    dispatch(onUpdateSettings({ settings: { ...state.settings, ...newData } }));
  };

  const onSuccess = async (data: IUserDataProps): void => {
    setIsLoading(() => true);
    onChange({ userData: data });
    await onSaveSettings({ ...state.settings, userData: { ...data } });

    setIsLoading(() => false);
  };

  return (
    <LottieProvider>
      <div className="setup-form lf-shadow-sm">
        <h1 className="lf-font-bold lf-text-gray-700">Set up your LottieFiles plugin</h1>
        <p className="lf-font-semibold lf-text-gray-700 lf-max-w-xl">
          Log in with your LottieFiles account to access the world’s largest collection of free-to-use animations on
          your website.
        </p>
        <CheckboxControl
          label="Share LottieFiles account with other Wordpress users"
          name="share_with_others"
          checked={state.settings.shareWithOthers}
          onChange={(): void => onChange({ shareWithOthers: !state.settings.shareWithOthers })}
        />
        <p className="privacy-policy lf-text-gray-400 lf--mt-1 lf-mb-5">
          Allow other WordPress users to explore LottieFiles using your account.
        </p>
        <CheckboxControl
          label="Use our Content Delivery Network (CDN) for JavaScript libraries like the Lottie Web Player"
          name="use_our_cdn"
          checked={state.settings.enableCdn}
          onChange={(): void => onChange({ enableCdn: !state.settings.enableCdn })}
        />
        <CheckboxControl
          label="Help us improve by sharing your usage data."
          help=""
          name="share_user_data"
          checked={state.settings.shareUserData}
          onChange={(): void => onChange({ shareUserData: !state.settings.shareUserData })}
        />
        <p className="privacy-policy lf--mt-1 lf-mb-5 lf-text-gray-400">
          Your data will be kept safely under LottieFiles’{' '}
          <a href={URLS.privacy} className=" lf-text-teal-300 hover:lf-text-teal-400" target="_blank">
            Privacy Policy.
          </a>
        </p>
        <div className="mt-8">
          <LoginAuto
            label="Log in with your LottieFiles account"
            className="lf-h-14 lf-px-9 lf-py-3 lf-text-base lf-font-bold"
            onClick={(): void => {
              setIsLoading({ isLoading: true });
            }}
            onSuccess={onSuccess}
            onError={(): void => {
              /** */
              console.log('error');
            }}
          />
          {!isLoading && (
            <a
              onClick={(): void => {
                tracker.pluginTracking({
                  eventType: eventsConst.click.signup,
                });
                window.open(URLS.register, '_blank');
              }}
              className="create-account lf-m-2 lf-cursor-pointer"
            >
              <span style={{ cursor: 'cursor' }} className="lf-font-bold lf-text-teal-300 hover:lf-text-teal-400">
                Create an account for free
              </span>
            </a>
          )}
        </div>
      </div>
    </LottieProvider>
  );
};
