/**
 * Copyright 2022 Design Barn Inc.
 */

import { createContext, useEffect, useState } from '@wordpress/element';
import * as React from 'react';
import { useNavigate } from 'react-router-dom';
import {
  createClient,
  Provider as UrqlProvider,
  // defaultExchanges,
  // subscriptionExchange
} from 'urql';

import packageJson from '../../package.json';
import { IHNResponseProps } from '../admin/settings/reducer';
import { getSettings, deleteSettings, updateSettings } from '../api';
import { api } from '../helpers/consts';
import { ILottieProps } from '../interfaces';

interface ILottieProviderProps {
  attributes: ILottieProps;
  children: React.ReactNode;
  closeModal(): void;
  isOpen: boolean;
  openModal(): void;
  setAttributes: React.Dispatch<React.SetStateAction<IPreviewFile>>;
}

export const defaultAttributeValue = {
  src: '',
  jsonSrc: null,
  direction: 1,
  width: '300px',
  height: '300px',
  speed: 1,
  background: 'white',
  loop: true,
  autoplay: true,
  hover: false,
};

interface IPreviewFile {
  file?: File;
  json?: Record<string, unknown>;
  path: string;
}

const defaultPreviewFileValues: IPreviewFile = {
  path: '',
};

export const LottieContext = createContext({
  attributes: defaultAttributeValue,
  previewFile: defaultPreviewFileValues,
  setPreviewFile: ((): void => {
    /** */
  }) as React.Dispatch<React.SetStateAction<IPreviewFile>>,
  setAttributes: (_value: unknown): void => {
    /** */
  },
});

export const LottieProvider: React.FC<ILottieProviderProps> = ({
  attributes,
  children,
  closeModal,
  isOpen,
  openModal,
  setAttributes,
}: ILottieProviderProps) => {
  const [settings, setSettings] = useState<IHNResponseProps | boolean>(false);
  const [isBlockLoggedIn, setIsBlockLoggedIn] = useState<IHNResponseProps | boolean>(false);
  const [isAppLoading, setIsAppLoading] = useState<IHNResponseProps | boolean>(false);
  const [selectedLottie, setSelectedLottie] = useState<string>(attributes ? attributes.src : '');
  const [previewFile, setPreviewFile] = useState<IPreviewFile>(defaultPreviewFileValues);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchSettings = async () => {
      setIsAppLoading(() => true);

      const response: IHNResponseProps | boolean = await getSettings();

      if (response && response.error) {
        setIsAppLoading(() => false);
        setIsBlockLoggedIn(() => false);
        setSettings(() => false);
      } else if (response && response.is_block_logged_in) {
        setIsBlockLoggedIn(true);
        setSettings(() => response);
        setIsAppLoading(() => false);
      } else {
        setSettings(() => false);
        setIsBlockLoggedIn(() => false);
        setIsAppLoading(() => false);
      }
    };

    fetchSettings();
  }, [isOpen]);

  const onLogin = async (_settings: IHNResponseProps): Promise<unknown> => {
    setIsAppLoading(() => true);
    const response = await updateSettings(_settings);

    setSettings(() => response);
    setIsAppLoading(() => false);

    return response;
  };

  const onLogout = async (): Promise<boolean> => {
    const response: boolean = await deleteSettings({});

    setSettings(() => false);
    navigate('/');

    return response;
  };

  const onSwitch = (): void => {
    setSettings(() => false);
    navigate('/');
  };

  const onChangeLottie = (uri: string): void => setSelectedLottie(() => uri);

  const client = createClient({
    url: api.graphql,
    fetchOptions: () => {
      return {
        headers: {
          Authorization: settings.userData?.accessToken ? `Bearer ${settings.userData?.accessToken}` : '',
          'client-name': packageJson.name,
          'client-version': packageJson.version,
        },
      };
    },
    // exchanges: [
    //   ...defaultExchanges,
    //   subscriptionExchange({
    //     forwardSubscription: operation => subscriptionClient.request(operation),
    //   }),
    // ],
  });

  return (
    <LottieContext.Provider
      value={{
        attributes,
        selectedLottie,
        isBlockLoggedIn,
        closeModal,
        openModal,
        onChangeLottie,
        isAppLoading,
        appData: settings,
        onLogout,
        onLogin,
        setAttributes,
        previewFile,
        setPreviewFile,
        onSwitch,
      }}
    >
      <UrqlProvider value={client}>{children}</UrqlProvider>
    </LottieContext.Provider>
  );
};
