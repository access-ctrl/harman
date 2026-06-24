/**
 * Copyright 2022 Design Barn Inc.
 */

import { DotLottieUtils, ExtractionType } from '@lottiefiles/dotlottie-js';

export const getEpoch = (): string => {
  return Date.now().toString();
};

export const capitalizeFirstLetter = (string: string): string => {
  return string.charAt(0).toUpperCase() + string.slice(1);
};

export async function extractDotLottie(dotLottieUrl: string): Promise<Record<string, unknown> | null> {
  // extract dotlottie contents and save to state
  const dotlottie = await DotLottieUtils.build(dotLottieUrl);

  try {
    const dotLottieId = dotlottie.getManifest().animations[0].id;
    const dLottie = dotlottie.extract(dotLottieId, ExtractionType.String, true);

    if (dLottie && typeof dLottie === 'string') {
      return JSON.parse(dLottie);
    }
  } catch (ex) {
    console.error(ex);
  }

  return null;
}

export const isDotLottie = (url: string) => {
  if (url.endsWith('.lottie')) return true;

  const basename = url.substring((url.lastIndexOf('/') as number) + 1, url.indexOf('?'));
  const extension = basename.split('.').pop();

  return extension === 'lottie';
};

export const toHumanDate = (date: Date, withYear = false) => {
  // eslint-disable-next-line no-restricted-globals
  if (date instanceof Date && !isNaN(date as unknown)) {
    const options: Intl.DateTimeFormatOptions = {
      month: 'short',
      day: 'numeric',
    };

    if (withYear) {
      options.year = 'numeric';
    }

    return date.toLocaleDateString('en-US', options);
  }

  return date;
};

interface Thumbnail {
  thumbnails?: {
    png?: {
      small?: {
        url?: string;
      };
    };
    webp?: {
      small?: {
        url?: string;
      };
    };
  };
}

export const getThumbnailUrls = (thumbnails: Thumbnail[]): string[] =>
  thumbnails.map(
    thumbnail =>
      (thumbnail.thumbnails &&
        thumbnail.thumbnails.webp &&
        thumbnail.thumbnails.webp.small &&
        thumbnail.thumbnails.webp.small.url) ||
      (thumbnail.thumbnails &&
        thumbnail.thumbnails.png &&
        thumbnail.thumbnails.png.small &&
        thumbnail.thumbnails.png.small.url) ||
      '',
  );
