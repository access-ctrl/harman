/**
 * Copyright 2022 Design Barn Inc.
 */

import { Panel, PanelBody, SelectControl } from '@wordpress/components';
import * as React from 'react';

import { InputLabel } from '../../../components/input-label';
import { SwitchLabel } from '../../../components/switch-label';
import { IHostAppProps } from '../../../interfaces/index';
import { CURSOR_TYPES, Mode, SCROLL_TYPES } from '../../../utils/enums';

import { PanelRowWrapper } from './animation-settings';

export const InteractivitySettings: React.FC<IHostAppProps> = ({ attributes, setAttributes }: IHostAppProps) => {
  const onChangeMode = (val: string): void => {
    if (val === Mode.SCROLL) {
      setAttributes({ interactivitymode: val, interactivitytype: 'seek' });
    } else {
      setAttributes({ interactivitymode: val, interactivitytype: Mode.NONE });
    }
  };

  return (
    <Panel>
      <PanelBody title="Interactivity Settings" initialOpen={true}>
        <SelectControl
          label="Interactivity Mode"
          value={attributes.interactivitymode as string}
          onChange={(val: string): void => onChangeMode(val)}
          options={[
            { value: 'none', label: 'None' },
            { value: 'cursor', label: 'Cursor' },
            { value: 'scroll', label: 'Scroll' },
          ]}
        />
        <SelectControl
          label="Interactivity Action"
          value={attributes.interactivitytype as string}
          onChange={(val: string): void => setAttributes({ interactivitytype: val })}
          options={attributes.interactivitymode === Mode.SCROLL ? SCROLL_TYPES : CURSOR_TYPES}
        />
        {attributes.interactivitymode === Mode.SCROLL && (
          <>
            <PanelRowWrapper>
              <InputLabel
                label="Total Frames"
                type="number"
                value={attributes.totalFrames as string}
                disabled
                onChange={(): void => {
                  /** */
                }}
              />
            </PanelRowWrapper>
            <PanelRowWrapper>
              <InputLabel
                label="Visibility Start"
                type="number"
                min="0"
                value={attributes.visibilitystart as string}
                onChange={(value: string): void => {
                  if (value !== '') {
                    setAttributes({ visibilitystart: value });
                  }
                }}
              />
              <InputLabel
                label="Visibility End"
                type="number"
                max="1"
                value={attributes.visibilityend as string}
                onChange={(value: string): void => {
                  if (value !== '') {
                    setAttributes({ visibilityend: value });
                  }
                }}
              />
            </PanelRowWrapper>
            <PanelRowWrapper>
              <InputLabel
                label="Frames Start"
                type="number"
                min="0"
                value={attributes.framesstart as string}
                onChange={(value: string): void => {
                  if (value !== '') {
                    setAttributes({ framesstart: value });
                  }
                }}
              />
              <InputLabel
                label="Frames End"
                type="number"
                max={attributes.totalFrames as string}
                value={attributes.framesend as string}
                onChange={(value: string): void => {
                  if (value !== '') {
                    setAttributes({ framesend: value });
                  }
                }}
              />
            </PanelRowWrapper>
          </>
        )}
        <SwitchLabel
          title="Force Flag"
          subTitle="Play animation on load"
          value={attributes.forceflag as boolean}
          onChange={(value: boolean): void => setAttributes({ forceflag: value })}
        />
      </PanelBody>
    </Panel>
  );
};
