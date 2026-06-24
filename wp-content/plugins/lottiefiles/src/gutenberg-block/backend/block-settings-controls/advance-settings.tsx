/**
 * Copyright 2022 Design Barn Inc.
 */

import { InspectorAdvancedControls } from '@wordpress/block-editor';
import { SelectControl } from '@wordpress/components';
import * as React from 'react';

import { IHostAppProps } from '../../../interfaces/index';

export const AdvanceSettings: React.FC<IHostAppProps> = ({ attributes, setAttributes }: IHostAppProps) => {
  return (
    <InspectorAdvancedControls key="inspector">
      <SelectControl
        label="Play mode"
        value={attributes.mode as string}
        name="mode"
        onChange={(val: string): void => setAttributes({ mode: val })}
        options={[
          { value: 'normal', label: 'Normal' },
          { value: 'bounce', label: 'Bounce' },
        ]}
      />
      <SelectControl
        label="Direction"
        value={attributes.direction as string}
        onChange={(val: string): void => setAttributes({ direction: val })}
        options={[
          { value: '1', label: 'Forward' },
          { value: '-1', label: 'Backward' },
        ]}
      />
    </InspectorAdvancedControls>
  );
};
