/**
 * Copyright 2022 Design Barn Inc.
 */

import { Appearance, Size, TextColor } from '@lottiefiles/react-ui-kit';
import { CheckboxControl } from '@wordpress/components';
import { useReducer, useState } from '@wordpress/element';
import * as React from 'react';

import { Button } from '../../../../_components';
import { ISettingsProps } from '../../interfaces';
import { IHNResponseProps, ISettingReducerProps, IStateProps, onUpdateSettings, settingReducer } from '../../reducer';

export const Settings: React.FC<ISettingsProps> = ({ onDeleteSettings, settings, updateSettings }: ISettingsProps) => {
  const [state, dispatch] = useReducer<ISettingReducerProps, IStateProps>(
    settingReducer,
    {
      settings,
    },
    // eslint-disable-next-line no-undefined
    undefined,
  );
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const onChange = (newData: IHNResponseProps): void => {
    dispatch(onUpdateSettings({ settings: { ...state.settings, ...newData } }));
  };

  const onSave = async (): Promise<void> => {
    setIsLoading(() => true);

    await updateSettings({ ...state.settings });

    setIsLoading(() => false);
    window.location.reload();
  };

  return (
    <div className="setup-form shadow-sm">
      <h1 className="font-bold text-gray-700">Settings</h1>
      <p className="logged-in">
        Logged In as <strong className="name lf-text-teal-300 no-underline">{settings.userData?.name}</strong>{' '}
        <a className="logout" onClick={async (): Promise<boolean> => onDeleteSettings(state.settings?.userData.id)}>
          <strong>(Logout)</strong>
        </a>
      </p>
      <CheckboxControl
        label="Share LottieFiles account with other Wordpress users"
        name="share_with_others"
        checked={state.settings.shareWithOthers}
        onChange={(): void => onChange({ shareWithOthers: !state.settings.shareWithOthers })}
      />
      <CheckboxControl
        label="Copy animations to Wordpress Media Library"
        help=""
        name="copy_to_media"
        checked={state.settings?.copyLottieToMedia}
        onChange={(): void => onChange({ copyLottieToMedia: !state.settings.copyLottieToMedia })}
      />
      <CheckboxControl
        label="Use our Content Delivery Network (CDN) for JavaScript libraries like the Lottie Web Player"
        help=""
        name="use_our_cdn"
        checked={state.settings?.enableCdn}
        onChange={(): void => onChange({ enableCdn: !state.settings.enableCdn })}
      />
      <h2 className="usage-data-sharing">Usage data sharing</h2>
      <CheckboxControl
        className="checkbox"
        label="Help us improve by sharing your usage data."
        help=""
        name="share_user_data"
        checked={state.settings?.shareUserData}
        onChange={(): void => onChange({ shareUserData: !state.settings.shareUserData })}
      />
      <p className="privacy-policy lf-mt-1 lf-text-gray-400">
        All data collected will be treated in accordance with{' '}
        <a
          href="https://lottiefiles.com/page/privacy-policy"
          className="lf-text-teal-300 hover:lf-text-teal-400"
          target="_blank"
        >
          LottieFiles’ Privacy Policy.
        </a>
      </p>
      <Button
        appearance={Appearance.primary}
        size={Size.small}
        textColor={TextColor.white}
        className="lf-h-14 lf-px-9 lf-py-3 lf-text-base lf-font-bold lf-mt-8"
        onClick={onSave}
      >
        {isLoading ? 'Saving...' : 'Save Changes'}
      </Button>
    </div>
  );
};
