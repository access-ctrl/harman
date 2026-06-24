/**
 * Copyright 2022 Design Barn Inc.
 */

import { FormToggle, PanelRow } from '@wordpress/components';
import * as React from 'react';

import { Label } from './Label';

interface ISwitchWithLabelProps {
  onChange: (val: boolean) => void;
  subTitle: string;
  title: string;
  value: boolean;
}

export const SwitchLabel: React.FC<ISwitchWithLabelProps> = ({
  onChange,
  subTitle,
  title,
  value,
}: ISwitchWithLabelProps) => {
  return (
    <PanelRow>
      <Label>
        {title}
        <div>{subTitle}</div>
      </Label>
      <FormToggle checked={value} onChange={(): void => onChange(!value)} />
    </PanelRow>
  );
};
