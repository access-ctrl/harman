/**
 * Copyright 2022 Design Barn Inc.
 */

import { Appearance, Size, TextColor } from '@lottiefiles/react-ui-kit';
import clsx from 'clsx';
import * as React from 'react';
import { useNavigate } from 'react-router-dom';

import { Button } from '../_components';

interface INoDataProps {
  children: React.ReactNode;
  lottieBy: string;
  noDataText: string;
}

export const NoData: React.FC<INoDataProps> = ({ children, lottieBy, noDataText }: INoDataProps) => {
  const navigate = useNavigate();

  const goToDiscover = (): void => {
    navigate('/discover');
  };

  return (
    <div className={clsx('lf-flex lf-justify-center lf-h-full lf-items-center lf-text-center')}>
      <div className={clsx('lf-flex lf-flex-col lf-items-center')}>
        {children}
        <p className={clsx('lf-text-xs')}>Animation by {lottieBy}</p>
        <h4 className={clsx('lf-font-bold lf-text-base lf-m-3.5')}>{noDataText}</h4>
        <Button appearance={Appearance.primary} size={Size.small} textColor={TextColor.white} onClick={goToDiscover}>
          Discover Animations
        </Button>
      </div>
    </div>
  );
};
