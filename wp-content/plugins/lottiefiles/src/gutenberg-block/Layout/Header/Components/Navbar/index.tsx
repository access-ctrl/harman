/**
 * Copyright 2022 Design Barn Inc.
 */

import { Button } from '@wordpress/components';
import * as React from 'react';

import { INavbarProps } from '../../../../../interfaces';
import { Menu } from '../Menu';

export const Navbar: React.FC<INavbarProps> = ({ toggleModal }: INavbarProps) => (
  <div className="lf-flex lf-flex-grow lf-items-center lf-justify-between lf-h-full">
    <Menu />
    <Button icon="no-alt" onClick={(): void => toggleModal && toggleModal(false)} />
  </div>
);
