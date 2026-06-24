/**
 * Copyright 2022 Design Barn Inc.
 */

import { IInteractivityProps } from '../../interfaces';
import { create } from '@lottiefiles/lottie-interactivity';

enum Mode {
  CURSOR = 'cursor',
  NONE = 'none',
  SCROLL = 'scroll',
}

window.addEventListener('DOMContentLoaded', () => {
  // Find all lottiePlayers
  const player = document.querySelectorAll('lottie-player');

  const getActions = (attributes: unknown): IInteractivityProps[] => {
    const { interactivitymode, interactivitytype, totalframes } = attributes;

    if (interactivitymode.value === Mode.SCROLL) {
      return [
        {
          visibility: [0, 1],
          type: interactivitytype.value,
          frames: [0, parseFloat(totalframes.value)],
        },
      ];
    }

    return [
      {
        type: interactivitytype.value,
      },
    ];
  };

  // Apply Interactivity
  const doInteractivity = (attributes: unknown): void => {
    if (attributes.id.value !== '') {
      const actions = getActions(attributes);

      create({
        player: `#${attributes.id.value}`,
        mode: attributes.interactivitymode.value,
        actions,
      });
    }
  };

  // Loop all Player items. Initialise LottieInteractivity on each item based on attributes.
  player.forEach(({ attributes }) => {
    const restAttributed = JSON.parse(attributes.rest.value);
    const lottieAnimation = document.getElementById(attributes.id.value);
    const { controls = true, loop = true } = restAttributed;

    lottieAnimation.__autoplay = false;
    lottieAnimation.__controls = controls === true;
    lottieAnimation.setLooping(loop === true);

    if (attributes.interactivitymode.value === Mode.NONE) {
      lottieAnimation.__autoplay = true;
      lottieAnimation.play();
    } else {
      doInteractivity(attributes);
    }
  });
});
