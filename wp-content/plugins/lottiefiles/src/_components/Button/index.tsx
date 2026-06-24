/**
 * Copyright 2022 Design Barn Inc.
 */

import { Size, TextColor, Shadow, OutlineColor } from '@lottiefiles/react-ui-kit';
import clsx from 'clsx';
import React, { ButtonHTMLAttributes, ReactNode } from 'react';

import { FontWeight, Appearance } from '../../_enums/modifiers';

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  appearance: Appearance;
  children: ReactNode;
  fontWeight?: FontWeight;
  outlineColor?: OutlineColor;
  shadow?: Shadow;
  size: Size;
  textColor?: TextColor;
}

export const Button: React.FC<ButtonProps> = (props: ButtonProps) => {
  const {
    appearance,
    children,
    fontWeight,
    outlineColor,
    shadow,
    size,
    textColor,
    className,
    ...ButtonHTMLAttributes
  } = props;

  return (
    <button
      className={clsx(
        'lf-_lf-btn',
        appearance !== 'standard' ? `lf-${appearance}` : '',
        size ? `lf-${size}` : '',
        textColor ? `lf-${textColor}` : '',
        shadow ? `lf-${shadow}` : '',
        outlineColor ? `${outlineColor}` : '',
        fontWeight ? `lf-${fontWeight}` : '',
        className,
      )}
      {...ButtonHTMLAttributes}
    >
      {children}
    </button>
  );
};
