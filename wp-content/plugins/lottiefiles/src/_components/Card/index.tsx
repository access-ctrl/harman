/**
 * Copyright 2022 Design Barn Inc.
 */

import { Size } from '@lottiefiles/react-ui-kit';
import clsx from 'clsx';
import React, { ReactNode } from 'react';

export interface CardcaptionProps {
  children: ReactNode;
  hover: boolean;
}

export const Cardcaption: React.FC<CardcaptionProps> = (props: CardcaptionProps) => {
  const { children, hover } = props;

  return <div className={clsx('lf-card-caption', hover ? 'lf-hover' : '')}>{children}</div>;
};

export interface CardProps {
  children: ReactNode;
  size: Size;
}

export const Card: React.FC<CardProps> = (props: CardProps) => {
  const { children, size } = props;

  return <div className={clsx('lf-_lf-card lf-group', size ? `lf-${size}` : 'lf-tiny')}>{children}</div>;
};
