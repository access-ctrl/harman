/**
 * Copyright 2020 Design Barn Inc.
 */

import { Button, Size, Appearance } from '@lottiefiles/react-ui-kit';
import * as React from 'react';

import { LoadingLottie } from './loading-lottie';

interface Action {
  name: string;
  onAction: () => void;
}

interface ILoadingProps {
  action?: Action;
  content?: React.ReactNode;
}

export const Loading: React.FC<ILoadingProps> = ({ action, content }) => {
  return (
    <div className="flex flex-col items-center justify-center pb-8 ">
      <div style={{ zIndex: 10, paddingBottom: content ? '1.5rem' : '2rem' }}>
        <LoadingLottie />
      </div>
      {content && (
        <p className="text-gray-300" style={{ paddingBottom: action ? '2rem' : '', fontSize: '.8rem' }}>
          {content}
        </p>
      )}
      {action && (
        <div>
          <Button onClick={action.onAction} appearance={Appearance.primary} size={Size.tiny}>
            {action.name}
          </Button>
        </div>
      )}
    </div>
  );
};
