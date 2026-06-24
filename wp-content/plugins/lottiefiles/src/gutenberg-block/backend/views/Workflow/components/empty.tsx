/**
 * Copyright 2022 Design Barn Inc.
 */

import { Appearance, Button, Size } from '@lottiefiles/react-ui-kit';
import React from 'react';

import { EmptyImage } from '../../../../../assets/Icons';

interface Action {
  name: string;
  onAction: () => void;
}

interface EmptyProps {
  action?: Action;
  description?: string;
  fullscreen?: boolean;
  icon?: React.ReactNode;
  title?: string;
}

export const Empty: React.FC<EmptyProps> = ({
  action,
  description,
  icon = <EmptyImage />,
  title = 'Empty !',
}: EmptyProps) => {
  return (
    <div style={{ zIndex: -1 }} className="flex flex-col items-center justify-center pb-10">
      <div className="p-5">{icon}</div>
      <div className="p-5"></div>
      <h1 className="text-xl font-lf-semi-bold text-gray-700">{title}</h1>
      {description && <p className="text-sm font-lf-regular text-gray-600 text-center w-7/12 mt-1.5">{description}</p>}
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
