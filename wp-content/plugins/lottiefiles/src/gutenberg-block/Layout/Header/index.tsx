/**
 * Copyright 2022 Design Barn Inc.
 */

import * as React from 'react';

import { LottieFilesLogo } from '../../../assets/Logo';
import { IHeaderProps } from '../../../interfaces';

export const Header: React.FC<IHeaderProps> = ({ children }: IHeaderProps) => (
  <div className="lf-h-20 lf-shadow-sm lf-flex border-b lf-border-gray-200 lf-items-center bg-white header lf-justify-between lf-px-4">
    <div className="lf-flex w-72 lf-p-2 lf-items-center">
      <LottieFilesLogo className="lf-ml-1.5" />
    </div>
    {children}
  </div>
);
