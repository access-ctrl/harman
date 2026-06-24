/**
 * Copyright 2020 Design Barn Inc.
 */

import { LottiePlayer } from '@lottiefiles/react-ui-kit';
import * as React from 'react';

import loaderJSON from '../../../../../assets/json/tointoin.json';

export const LoadingLottie: React.FC = () => {
  return (
    <div className="h-20 w-20">
      <LottiePlayer bgColor="none" isPreview src={JSON.stringify(loaderJSON)} />
    </div>
  );
};
