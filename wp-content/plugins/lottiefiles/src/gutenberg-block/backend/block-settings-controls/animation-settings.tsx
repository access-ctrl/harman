/**
 * Copyright 2022 Design Barn Inc.
 */

import { Panel, PanelBody, PanelRow, SelectControl } from '@wordpress/components';
import * as React from 'react';
import styled from 'styled-components';

import { InputLabel } from '../../../components/input-label';
import { SwitchLabel } from '../../../components/switch-label';
import { IHostAppProps } from '../../../interfaces/index';
import { Mode } from '../../../utils/enums';

export const PanelRowWrapper = styled(PanelRow)`
  gap: 10px;
`;

export const AnimationSettings: React.FC<IHostAppProps> = ({ attributes, setAttributes }: IHostAppProps) => {
  const { loopHack } = attributes;
  const { autoplay = true, controls = true, loop = true } = JSON.parse(loopHack);

  const onChangeReflect = (val: unknown): void => {
    const newLoopHack = JSON.stringify({ autoplay, loop, controls, ...val });

    setAttributes({ loopHack: newLoopHack });
  };

  const onChangeMode = (val: string): void => {
    if (val === Mode.SCROLL) {
      setAttributes({
        animationType: val,
        interactivitymode: Mode.SCROLL,
        interactivitytype: 'seek',
        hover: false,
        state: 'none',
        loop: false,
        autoplay: false,
      });
      onChangeReflect({ loop: false, autoplay: false });
    } else if (val === Mode.NONE) {
      setAttributes({
        animationType: val,
        interactivitymode: Mode.NONE,
        interactivitytype: Mode.NONE,
        state: 'autoplay',
        loop: true,
        autoplay: true,
      });
      onChangeReflect({ loop: true, autoplay: true });
    } else {
      setAttributes({
        animationType: val,
        interactivitymode: Mode.CURSOR,
        interactivitytype: val,
        state: 'none',
        hover: false,
        loop: false,
        autoplay: false,
      });
    }
    onChangeReflect({ loop: false, autoplay: false });
  };

  return (
    <Panel>
      <PanelBody title="Animation Settings" initialOpen={true}>
        <SelectControl
          label="Play animation on"
          value={attributes.animationType as string}
          onChange={(val: string): void => onChangeMode(val)}
          options={[
            { value: 'none', label: 'Page Load' },
            { value: 'hold', label: 'Hover' },
            { value: 'click', label: 'Click' },
            { value: 'scroll', label: 'Scroll' },
          ]}
        />
        <SwitchLabel
          title="Loop"
          subTitle="Repeat animation"
          value={loop as boolean}
          onChange={(value: boolean): void => {
            const newValue = value ? value : null;

            setAttributes({ loop: newValue });
            onChangeReflect({ loop: newValue });
          }}
        />
        <SwitchLabel
          title="Controls"
          subTitle="Display animation Control"
          value={controls as boolean}
          onChange={(value: boolean): void => {
            setAttributes({ controls: value ? value : null });
            onChangeReflect({ controls: value ? value : null });
          }}
        />
        <PanelRowWrapper>
          <InputLabel
            label="Speed"
            type="number"
            min="0"
            max="5"
            value={attributes.speed as string}
            onChange={(value: string): void => setAttributes({ speed: value })}
          />
          <input
            type="range"
            value={attributes.speed as string}
            max="5"
            onChange={(event: React.ChangeEvent<HTMLInputElement>): void =>
              setAttributes({ speed: event.target.value })
            }
          />
        </PanelRowWrapper>
        <PanelRowWrapper>
          <InputLabel
            label="Width"
            value={attributes.width as string}
            onChange={(value: string): void => setAttributes({ width: value })}
          />
          <InputLabel
            label="Height"
            value={attributes.height as string}
            onChange={(value: string): void => setAttributes({ height: value })}
          />
        </PanelRowWrapper>
      </PanelBody>
    </Panel>
  );
};
