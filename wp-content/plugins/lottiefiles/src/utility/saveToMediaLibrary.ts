/**
 * Copyright 2022 Design Barn Inc.
 */

import { DotLottieUtils, ExtractionType } from '@lottiefiles/dotlottie-js';
import { MediaItem, uploadMedia } from '@wordpress/media-utils';

interface IErrorProps {
  error: string;
}

interface IsaveToMediaLibrary {
  file?: File;
  onError?(error: IErrorProps): void;
  onFileSave(url: string, jsonSrc?: Record<string, unknown> | null): void;
  url: string;
}

export const uploadToMediaLibrary = ({ file, onError, onFileSave }: IsaveToMediaLibrary): void => {
  uploadMedia({
    filesList: [file as File],
    onFileChange: ([fileObj]: MediaItem[]) => {
      if (fileObj && fileObj.id) {
        onFileSave(fileObj.url);
      }
    },
    onError: (error): void => {
      onError?.({ error: 'File upload error', ...error });
    },
    maxUploadFileSize: 5000000,
  });
};

export const createJsonFile = (blobPart: Blob): File => {
  return new File([blobPart], `${Math.floor(Date.now() / 1000)}.json`, { type: 'application/json' });
};

export const checkDotLottie = (url: string): boolean => {
  if (url.endsWith('.lottie')) return true;

  const basename = url.substring((url.lastIndexOf('/') as number) + 1, url.indexOf('?'));
  const extension = basename.split('.').pop();

  return extension === 'lottie';
};

export const saveToMediaLibrary = async ({ onError, onFileSave, url }: IsaveToMediaLibrary): Promise<void> => {
  // 1. Check if dotlottie
  const isDotLottie = checkDotLottie(url);

  // 2. If yes, build dotlottie url
  if (isDotLottie) {
    const dotlottie = await DotLottieUtils.build(url);

    try {
      const dotLottieId = dotlottie.getManifest()['animations'][0].id;
      const dLottie = dotlottie.extract(dotLottieId, ExtractionType.String, true);

      if (dLottie && typeof dLottie === 'string') {
        const blobPart = new Blob([dLottie], { type: 'application/json' });
        const newFile = createJsonFile(blobPart);

        // TODO: If reusing the code, newFile is always null
        // uploadToMediaLibrary({ newFile, onFileSave, onError });
        uploadMedia({
          filesList: [newFile as File],
          onFileChange: ([fileObj]: MediaItem[]) => {
            if (fileObj && fileObj.id) {
              onFileSave(fileObj.url, JSON.parse(dLottie));
            }
          },
          onError: (error): void => {
            onError?.({ error: 'File upload error', ...error });
          },
          maxUploadFileSize: 500000,
        });
      }
    } catch (ex) {
      onError?.({ error: 'Something went wrong!' });
    }
  } else {
    try {
      const file: File = await fetch(url as string)
        .then(async (r: Response) => r.blob())
        .then((blobPart: Blob) => createJsonFile(blobPart));

      uploadToMediaLibrary({ file, onFileSave, onError });
    } catch (error) {
      onError?.({ error: 'Something went wrong!' });
    }
  }
};
