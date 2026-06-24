/**
 * Copyright 2022 Design Barn Inc.
 */

import { Player } from '@lottiefiles/react-lottie-player';
import { Appearance, Size } from '@lottiefiles/react-ui-kit';
import React, { ReactNode } from 'react';

import { Button } from '../../../../../_components';
import errorJson from '../../../../../assets/json/error-cone.json';

interface Action {
  name: string;
  onAction: () => void;
}

interface ErrorViewProps {
  action?: Action;
  description?: ReactNode;
  title?: string;
}

export const ErrorView: React.FC<ErrorViewProps> = ({ action, description, title = 'Error !' }: ErrorViewProps) => {
  return (
    <div className="flex flex-col items-center justify-center ">
      <div className="w-52 h-52">
        <Player
          background={''}
          src={JSON.stringify(errorJson)}
          autoplay
          loop
          style={{ height: '100%', width: '100%', borderRadius: '0.5rem' }}
        />
      </div>
      <h1 className="text-xl font-lf-semi-bold text-gray-500">{title}</h1>
      {description && (
        <p className="text-sm font-lf-regular text-center lf-text-gray-500 w-7/12 mt-1.5">{description}</p>
      )}
      {action && (
        <div className="mt-6">
          <Button onClick={action.onAction} appearance={Appearance.primary} size={Size.small}>
            {action.name}
          </Button>
        </div>
      )}
    </div>
  );
};

export const GlobalErrorView: React.FC = () => {
  return (
    <ErrorView title="Something went wrong" description="Please try again, or contact our customer support team." />
  );
};
