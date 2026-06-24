/**
 * Copyright 2022 Design Barn Inc.
 */

import { Size, Appearance, TextColor } from '@lottiefiles/react-ui-kit';
import { useContext } from '@wordpress/element';
import * as React from 'react';
import { useNavigate } from 'react-router-dom';

import { Button } from '../../../../_components';
import { LottiePlayer } from '../../../../components/LottiePlayer';
import { LottieContext } from '../../../../context/lottie-provider';
import { withBase } from '../../../../hoc';
import { IHostAppProps } from '../../../../interfaces';
import { saveToMediaLibrary, uploadToMediaLibrary } from '../../../../utility';
import { isDotLottie, extractDotLottie } from '../../../../utils';
import { ImportSidebar } from '../Import';

const Preview: React.FC<IHostAppProps> = ({ setAttributes }: IHostAppProps) => {
  const navigate = useNavigate();

  const { appData, closeModal, previewFile, setPreviewFile } = useContext(LottieContext);

  const { copyLottieToMedia } = appData;

  const isLocalUpload = previewFile.json && previewFile.file;

  const resetPreview = (): void => {
    setPreviewFile({ path: '' });
    navigate('/import');
  };

  const closePreview = (): void => {
    closeModal(false);
    resetPreview();
  };

  const insertAnimation = (): void => {
    if (isLocalUpload) {
      uploadToMediaLibrary({
        file: previewFile.file,
        onFileSave: (src: string, jsonSrc?: Record<string, unknown>) => {
          setAttributes({ src, jsonSrc });
          closePreview();
        },
        onError: () => {
          /** */
        },
      });
    } else if (copyLottieToMedia) {
      saveToMediaLibrary({
        url: previewFile.path,

        onFileSave: (src: string, jsonSrc?: Record<string, unknown>) => {
          setAttributes({ src, jsonSrc });
          closePreview();
        },
        onError: (error: unknown) => {
          console.log(error);
        },
      });
    } else {
      if (isDotLottie(previewFile.path)) {
        const getLottie = async () => {
          try {
            const jsonSrc = await extractDotLottie(previewFile.path);

            setAttributes({ src: previewFile.path, jsonSrc });
            closePreview();
          } catch (error) {
            console.log(error);
          }
        };

        getLottie();

        return;
      }
      setAttributes({ src: previewFile.path });
      closePreview();
    }
  };

  return (
    <div
      style={{
        display: 'flex',
        height: 'calc(100% - 80px)',
        minHeight: '600px',
      }}
    >
      <div className="lf-flex lf-flex-grow p-1 lf-bg-gray-50 lf-overflow-auto">
        <div className="lf-first-child-full-width lf-flex lf-flex-col lf-h-full lf-justify-center lf-w-full md:lf-w-8/12 lg:w-5/12 lf-items-center lf-m-auto">
          <LottiePlayer
            bgColor="transparent"
            src={previewFile.json ? previewFile.json : previewFile.path}
            isPreview={false}
            isSimple
            setBackground={(): null => null}
          />
          <div className="mt-4">
            <Button
              appearance={Appearance.primary}
              size={Size.small}
              textColor={TextColor.white}
              onClick={insertAnimation}
            >
              Insert animation
            </Button>
          </div>
          <button
            onClick={resetPreview}
            className="lf-font-semibold lf-text-teal-600 lf-cursor-pointer lf-text-sm lf-mt-12"
          >
            Import new animation
          </button>
        </div>
      </div>
    </div>
  );
};

export default withBase({ SidebarContent: ImportSidebar })(Preview);
