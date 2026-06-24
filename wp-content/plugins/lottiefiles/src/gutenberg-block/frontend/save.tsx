/**
 * Copyright 2022 Design Barn Inc.
 */

// import { DotLottiePlayer, Controls as DLControls } from '@lottiefiles/dotlottie-react-player';
import { useBlockProps } from '@wordpress/block-editor';
import clsx from 'clsx';
import * as React from 'react';

import { ILottieProps } from '../../interfaces';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 *
 * return WPElement Element to render.
 */
export const Save: React.FC<ILottieProps> = (attributes: ILottieProps) => {
  const {
    background,
    contentAlign,
    framesstart,
    height,
    id,
    interactivitymode,
    interactivitytype,
    loopHack,
    mode,
    speed,
    jsonSrc,
    totalFrames,
    visibilityend,
    visibilitystart,
    width,
  } = attributes;

  // For future reference:
  // This is actually the code that's used on the frontend/user-facing pages
  // It's rendered at the same time as the Edit component on the backend/back-office page, so you wont see any console logs here on the frontend page
  // Don't forget to click Update!

  const formattedWidth = () => {
    if (width.includes('px')) {
      return width;
    }

    return `${width}px`;
  };

  const formattedHeight = () => {
    if (height.includes('px')) {
      return height;
    }

    return `${height}px`;
  };

  return (
    <div {...useBlockProps.save()} style={{ backgroundColor: background as string }}>
      <lottie-player
        id={id}
        interactivitytype={interactivitytype}
        interactivitymode={interactivitymode}
        visibilitystart={visibilitystart}
        visibilityend={visibilityend}
        framesstart={framesstart}
        framesend={totalFrames}
        rest={loopHack}
        totalFrames={totalFrames}
        mode={mode}
        src={JSON.stringify(jsonSrc)}
        speed={speed}
        background={background}
        className={clsx(
          'lottie-player',
          contentAlign === 'center' ? 'mx-auto' : '',
          contentAlign === 'left' ? '' : '',
          contentAlign === 'right' ? 'mx-auto mr-0' : '',
        )}
        style={{ width: formattedWidth(), height: formattedHeight() }}
      ></lottie-player>
    </div>
  );
};
