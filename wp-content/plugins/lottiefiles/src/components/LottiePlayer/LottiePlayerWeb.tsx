/**
 * Copyright 2022 Design Barn Inc.
 */

import { createRef, useEffect, useMemo, useState } from '@wordpress/element';
import clsx from 'clsx';
import * as React from 'react';
import { DotLottieUtils } from "@lottiefiles/dotlottie-js";
import { DotLottiePlayer, Controls as DLControls } from '@lottiefiles/dotlottie-react-player';

import { useInteractivity } from '../../hooks';
import { IHostAppProps, ILottieProps } from '../../interfaces';
import { Mode } from '../../utils/enums';

export const LottiePlayer: React.FC<IHostAppProps> = ({ attributes, setAttributes }: IHostAppProps) => {
  const lottieRef = createRef<HTMLInputElement>();
  const [settings, setSettings] = useState<ILottieProps>(attributes);

  const { loopHack } = attributes;
  const { loop = true, controls = true, autoplay = true } = JSON.parse(loopHack);

  useInteractivity(lottieRef, attributes, setAttributes);
  useEffect(() => {
    setTimeout(() => {
      setSettings(attributes);
    }, 10);
  }, [attributes]);
  
  const formattedHeight = useMemo(() => {
    if (settings.height.includes('px')) {
      return settings.height;
    }

    return `${settings.height}px`;
  }, [settings.height]);

  const formattedWidth = useMemo(() => {
    if (settings.width.includes('px')) {
      return settings.width;
    }

    return `${settings.width}px`;
  }, [settings.width]);

  return (
    // Interactivity works fine with this one
    <lottie-player
      id={settings.id}
      ref={lottieRef}
      controls={controls === true ? '' : null}
      autoplay={attributes.interactivitymode === Mode.NONE ? '' : null}
      loop={loop === true ? '' : null}
      // hover={null}
      src={JSON.stringify(settings.jsonSrc)}
      speed={settings.speed}
      background={settings.background}
      className={clsx(
        settings.contentAlign === 'center' ? 'lf-mx-auto' : '',
        settings.contentAlign === 'left' ? '' : '',
        settings.contentAlign === 'right' ? 'lf-mx-auto lf-mr-0' : '',
      )}
      style={{ width: formattedWidth, height: formattedHeight }}
    ></lottie-player>
  );
};
