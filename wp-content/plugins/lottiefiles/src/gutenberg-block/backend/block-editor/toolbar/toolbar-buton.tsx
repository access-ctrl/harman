/**
 * Copyright 2022 Design Barn Inc.
 */

import { Button } from '@wordpress/components';
import * as React from 'react';
import styled from 'styled-components';

import { color } from '../../../../assets/colors';
import { IToolbarButtonProps } from '../../../../interfaces';

const Wrapper = styled(Button)`
  text-decoration: none !important;
  color: ${color.black} !important;
`;

export const ToolbarButton: React.FC<IToolbarButtonProps> = ({
  children,
  icon,
  isOpen,
  onToggle,
}: IToolbarButtonProps) => (
  <Wrapper icon={icon} onClick={onToggle} aria-expanded={isOpen}>
    {children}
  </Wrapper>
);
