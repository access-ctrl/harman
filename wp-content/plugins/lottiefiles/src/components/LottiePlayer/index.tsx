/**
 * Copyright 2022 Design Barn Inc.
 */

import { Controls, IPlayerProps, Player } from '@lottiefiles/react-lottie-player';
import * as React from 'react';
// eslint-disable-next-line import/no-unassigned-import
import './lottieplayer.scss';
import { DotLottiePlayer, Controls as DLControls } from '@lottiefiles/dotlottie-react-player';

// import '@dotlottie/player-component';

interface ILottiePlayerProps extends IPlayerProps {
  bgColor?: string;
  isPreview: boolean;
  isSimple?: boolean;
  setBackground?: (value: string) => void;
  src: string | Record<string, unknown>;
}

interface IGenericPlayerProps extends IPlayerProps {
  bgColor: string;
  isDotLottie: boolean;
  setBackground?: (value: string) => void;
  src: string;
}

const PreviewPlayer: React.FC<IGenericPlayerProps> = ({ isDotLottie, bgColor, src, ...props }) => {
  if (isDotLottie) {
    return (
      <DotLottiePlayer
        background={bgColor}
        src={src}
        autoplay
        loop
        style={{ height: '160px', width: '165px', borderRadius: '0.5rem' }}
        {...props}
      />
    );
  }
  return (
    <Player
      background={bgColor}
      src={src}
      autoplay
      loop
      style={{ height: '160px', width: '165px', borderRadius: '0.5rem' }}
      {...props}
    />
  );
};

const LargePlayer: React.FC<IGenericPlayerProps> = ({ isDotLottie, bgColor, src, ...props }) => {
  if (isDotLottie) {
    return (
      <DotLottiePlayer
        background={bgColor}
        src={src}
        autoplay
        loop
        style={{ height: '300px', width: '300px', borderRadius: '0.5rem' }}
        {...props}
      />
    );
  }
  return (
    <Player
      background={bgColor}
      src={src}
      autoplay
      loop
      style={{ height: '300px', width: '300px', borderRadius: '0.5rem' }}
      {...props}
    />
  );
};

const SimplePlayer: React.FC<IGenericPlayerProps> = ({ isDotLottie, bgColor, setBackground, src, ...props }) => {
  if (isDotLottie) {
    return (
      <DotLottiePlayer
        src={src}
        autoplay
        loop
        background={bgColor}
        onBackgroundChange={async (color: string): Promise<void> => {
          setBackground?.(color);
        }}
        {...props}
      >
        <DLControls key={1} showLabels visible buttons={['play', 'repeat', 'frame']} />
      </DotLottiePlayer>
    );
  }

  return (
    <Player
      src={src}
      autoplay
      loop
      controls
      background={bgColor}
      onBackgroundChange={async (color: string): Promise<void> => {
        setBackground?.(color);
      }}
      {...props}
    >
      <Controls transparentTheme showLabels visible buttons={['play', 'repeat', 'frame']} />
    </Player>
  );
};

const checkDotLottie = (url: string) => {
  if (url.endsWith('.lottie')) return true;

  const basename = url.substring((url.lastIndexOf('/') as number) + 1, url.indexOf('?'));
  const extension = basename.split('.').pop();
  return extension === 'lottie';
};

export const LottiePlayer: React.FC<ILottiePlayerProps> = ({
  bgColor = '#fff',
  isPreview,
  isSimple = false,
  isLarge = false,
  setBackground,
  src,
  ...props
}) => {
  const isDotLottie = checkDotLottie(src);

  if (isPreview) {
    return <PreviewPlayer isDotLottie={isDotLottie} bgColor={bgColor} src={src} {...props} />;
  } else if (isSimple) {
    return (
      <SimplePlayer isDotLottie={isDotLottie} bgColor={bgColor} src={src} setBackground={setBackground} {...props} />
    );
  } else if (isLarge) {
    return <LargePlayer isDotLottie={isDotLottie} bgColor={bgColor} src={src} {...props} />;
  }
};
