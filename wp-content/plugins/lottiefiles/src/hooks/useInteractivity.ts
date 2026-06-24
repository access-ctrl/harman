/**
 * Copyright 2023 Design Barn Inc.
 */

// @ts-nocheck

import { useEffect } from '@wordpress/element';

import { ILottieProps, IInteractivityProps } from '../interfaces';
import { randomHtmlId } from '../utility';
import { Mode } from '../utils/enums';

export const useInteractivity = (
  lottieRef: React.RefObject<HTMLInputElement>,
  attributes: ILottieProps,
  setAttributes: unknown,
): void => {
  if (attributes.id === '') {
    const id = randomHtmlId();

    if (setAttributes instanceof Function) {
      setAttributes({ id });
    }
  }

  const getActions = (): IInteractivityProps[] => {
    const { animationstate, id, interactivitymode, interactivitytype }: unknown = attributes;
    const item = document.getElementById(id as string);
    const totalFrames = item?.getLottie().totalFrames;

    if (totalFrames !== attributes.totalFrames) {
      setAttributes({ totalFrames });
    }

    if (interactivitymode === Mode.SCROLL) {
      return [
        {
          visibility: [0, 1],
          type: interactivitytype as string,
          frames: [0, parseFloat(totalFrames as string)],
        },
      ];
    }

    return [
      {
        type: interactivitytype as string,
        state: animationstate,
      },
    ];
  };

  const doInteractivity = (): void => {
    if (document.getElementById(attributes.id as string) !== null) {
      const { id, interactivitymode }: ILottieProps = attributes;

      if (interactivitymode !== Mode.NONE) {
        const actions = getActions();

        const interactivityConfig = {
          player: `#${id}`,
          mode: interactivitymode,
          actions,
        };

        LottieInteractivity.create(interactivityConfig);
      }
    }
  };

  useEffect(() => {
    if (lottieRef.current !== null) {
      lottieRef.current.addEventListener('ready', doInteractivity);
    }

    return (): void => {
      if (lottieRef.current !== null) {
        lottieRef.current.removeEventListener('ready', doInteractivity);
      }
    };
  }, [lottieRef]);
};
