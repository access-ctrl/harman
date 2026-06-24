/**
 * Copyright 2022 Design Barn Inc.
 */

import Bugsnag from '@bugsnag/js';
import BugsnagPluginReact, { type BugsnagErrorBoundary } from '@bugsnag/plugin-react';
import { appDetails, isAllowTracking, token } from '@helpers/consts';
import { createTrackerBridge } from '@host';
import { DefaultIdentity } from '@lottiefiles/plugin-tracker';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import React from 'react';

import packageJson from '../../package.json';
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
/**
 * Internal dependencies
 */
import { IHNResponseProps } from '../admin/settings/reducer';
import { getSettings } from '../api';
import { Lottie } from '../assets/Icons/Lottie';
import { getEpoch } from '../utils';

import { edit as Edit } from './backend/edit';
import { Save } from './frontend/save';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */

const init = async (): Promise<void> => {
  const TRACKER_API_KEY = process.env.NODE_ENV === 'development' ? process.env.TRACKER_API_KEY_DEV : process.env.TRACKER_API_KEY;
  const TIME = getEpoch();
  const PLATFORM = appDetails.name;
  const APP_VERSION = appDetails.version;
  const DEVICE_ID = new DefaultIdentity().initializeDeviceId();
  const SOURCE_ID = appDetails.hitcountsource;
  const TOKEN = localStorage.getItem(token);
  const IS_ALLOW_TRACKING = localStorage.getItem(isAllowTracking) === 'true';

  // Initialize tracker host bridge
  const tracker = await createTrackerBridge(
    TRACKER_API_KEY,
    TIME,
    PLATFORM,
    APP_VERSION,
    DEVICE_ID,
    SOURCE_ID,
    TOKEN,
    IS_ALLOW_TRACKING,
  );

  Bugsnag.start({
    apiKey: process.env.BUGSNAG_API_KEY || '',
    appVersion: packageJson.version,
    releaseStage: process.env.NODE_ENV === 'development' ? 'development' : 'production',
    plugins: [new BugsnagPluginReact()],
  });

  // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
  const ErrorBoundary: BugsnagErrorBoundary = Bugsnag.getPlugin('react')?.createErrorBoundary(React)!;

  // Importing local lottie player bundle if not CDN allowed
  const importLottiePlayer = async () => {
    const { enableCdn }: IHNResponseProps | boolean = await getSettings();

    if (!enableCdn) {
      require('@lottiefiles/lottie-player');
    }
  };

  importLottiePlayer();

  registerBlockType('gb/lottiefiles', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __('Lottie'),
    description: __('Discover and use animations from world’s largest collection of free-to-use animations.'),
    icon: Lottie,
    category: 'widgets',
    keywords: [__('lottiefiles'), __('animation'), __('gif'), __('motion')],

    supports: {
      // Removes support for an HTML mode.
      align: ['wide', 'full'],
    },
    attributes: {
      src: {
        type: 'string',
        default: '',
      },
      jsonSrc: {
        type: 'object',
        default: {},
      },
      mode: {
        type: 'string',
        default: 'normal',
      },
      direction: {
        type: 'number',
        default: 1,
      },
      width: {
        type: 'string',
        default: '300px',
      },
      height: {
        type: 'string',
        default: '300px',
      },
      speed: {
        type: 'number',
        default: 1,
      },
      background: {
        type: 'string',
        default: 'transparent',
      },
      loop: {
        type: 'boolean',
        default: true,
      },
      autoplay: {
        type: 'boolean',
        default: true,
      },
      controls: {
        type: 'boolean',
        default: true,
      },
      align: {
        type: 'string',
        default: 'none',
      },
      alignment: {
        type: 'string',
        default: 'center',
      },
      contentAlign: {
        type: 'string',
        default: 'center',
      },
      blockSize: {
        type: 'string',
        default: 'none',
      },
      interactivitytype: {
        type: 'string',
        default: 'none',
      },
      interactivitymode: {
        type: 'string',
        default: 'none',
      },
      visibilitystart: {
        type: 'string',
        default: 0,
      },
      visibilityend: {
        type: 'string',
        default: 1,
      },
      framesstart: {
        type: 'string',
        default: 0,
      },
      framesend: {
        type: 'string',
        default: 25,
      },
      forceflag: {
        type: 'boolean',
        default: false,
      },
      id: {
        type: 'string',
        default: '',
      },
      totalFrames: {
        type: 'number',
        default: 25,
      },
      animationType: {
        type: 'string',
        default: 'none',
      },
      loopHack: {
        type: 'string',
        default: '{}',
      },
    },
    edit: ({ attributes, setAttributes }) => {
      return <Edit tracker={tracker} hostApp={{ setAttributes, attributes }} errorBoundary={ErrorBoundary} />;
    },
    save: ({ attributes }) => {
      return <Save {...attributes} />;
    },
  });
};

// eslint-disable-next-line promise/catch-or-return
Promise.resolve(init());
