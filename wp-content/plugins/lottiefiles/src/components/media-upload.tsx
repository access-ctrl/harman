/**
 * Copyright 2022 Design Barn Inc.
 */

import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import * as React from 'react';

import { IMyMediaUploaderProps } from '../interfaces';
import { isDotLottie, extractDotLottie } from '../utils';

const ALLOWED_MEDIA_TYPES = ['application/json', 'text/plain'];

export const MyMediaUploader: React.FC<IMyMediaUploaderProps> = ({
  gallery = false,
  onSelect,
  render,
}: IMyMediaUploaderProps) => {
  return (
    <MediaUploadCheck>
      <MediaUpload
        gallery={gallery}
        onSelect={(media): void => {
          if (['application/json', 'text/plain'].includes(media.mime) && media.url) {
            if (isDotLottie(media.url)) {
              const getLottie = async () => {
                try {
                  const jsonSrc = await extractDotLottie(media.url);

                  onSelect(media.url, jsonSrc);
                } catch (error) {
                  console.log(error);
                }
              };

              getLottie();

              return;
            }
            const getLottie = async () => {
              try {
                const jsonSrc = await fetch(media.url as string).then(async (res: Response) => res.json());

                onSelect(media.url, jsonSrc);
              } catch (error) {
                console.log(error);
              }
            };

            getLottie();
          }
        }}
        render={render}
        allowedTypes={ALLOWED_MEDIA_TYPES}
      />
    </MediaUploadCheck>
  );
};
