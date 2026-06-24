/**
 * Copyright 2022 Design Barn Inc.
 */

import { useTracker } from '@context/tracker-provider';
import { Transition } from '@headlessui/react';
import { eventEnums, eventsConst } from '@lottiefiles/plugin-tracker';
import { Appearance, Input, InputAppearance, Size } from '@lottiefiles/react-ui-kit';
import { useEffect, useState, useContext } from '@wordpress/element';
import * as React from 'react';
import { useNavigate } from 'react-router-dom';

import { DragDrop, Button } from '../../../../_components';
import { LottieContext } from '../../../../context/lottie-provider';
import { withBase } from '../../../../hoc';
import { createJsonFile } from '../../../../utility';
import { DotLottieUtils, ExtractionType } from '@lottiefiles/dotlottie-js';

export const ImportSidebar: React.FC = () => {
  const navigate = useNavigate();

  const { previewFile, setPreviewFile } = useContext(LottieContext);

  const isLocalUpload = Boolean(previewFile.json && previewFile.file);

  const handleSubmit = (event: React.FormEvent): void => {
    event.preventDefault();
    navigate('/preview');
  };

  // returns true if invalid link
  const isInvalidLink = (link: string): boolean => !link;
  const isDisabled = isInvalidLink(previewFile.path) || isLocalUpload;

  return (
    <form onSubmit={handleSubmit}>
      <label className="lf-text-sm lf-font-semibold	lf-text-gray-400">Animation URL</label>
      <div className="lf-mt-2">
        <Input
          onChange={(event: React.ChangeEvent<HTMLInputElement>): void =>
            setPreviewFile({ ...previewFile, path: event.target.value })
          }
          disabled={isLocalUpload}
          inputSize={Size.medium}
          appearance={InputAppearance.standard}
          placeholder="Paste .lottie/.json URL here"
          style={{ width: '100%' }}
          value={previewFile.path}
        />
      </div>
      <div className="lf-mt-3">
        <Button
          disabled={isDisabled}
          style={{ opacity: isDisabled ? 0.5 : 1 }}
          type="submit"
          appearance={Appearance.primary}
          size={Size.small}
        >
          Open
        </Button>
      </div>
    </form>
  );
};

const Import: React.FC = () => {
  const tracker = useTracker();
  const navigate = useNavigate();

  const { appData, setPreviewFile } = useContext(LottieContext);
  const [show, setShow] = useState(false);

  useEffect(() => {
    setShow(true);
    // setPreviewFile({ path: '' });

    return (): void => setShow(false);
  }, []);

  const handleFileChosen = (file: File): void => {
    const fileReader = new FileReader();

    fileReader.onloadend = async (): Promise<void> => {
      // eslint-disable-next-line node/no-unsupported-features/node-builtins
      const decoder = new TextDecoder();

      const fileReaderResult = fileReader.result;

      if (file && file.name.endsWith('.lottie')) {
        if (fileReaderResult) {
          const url = URL.createObjectURL(file);
          const dotlottie = await DotLottieUtils.build(url);
          try {
            const dotLottieId = dotlottie.getManifest()['animations'][0].id;
            const dLottie = dotlottie.extract(dotLottieId, ExtractionType.String, true);

            if (dLottie && typeof dLottie === 'string') {
              const blobPart = new Blob([dLottie], { type: 'application/json' });
              const newFile = createJsonFile(blobPart);

              setPreviewFile({ json: dLottie, path: `${newFile.name}`, file: newFile });
              navigate('/preview');
            }
          } catch (ex) {
            console.error(ex);
          }
        }
      } else {
        const json = JSON.parse(decoder.decode(fileReaderResult as ArrayBuffer));
        setPreviewFile({ json: JSON.stringify(json), path: file.name, file });

        navigate('/preview');
      }
    };

    fileReader.readAsArrayBuffer(file);
  };

  return (
    <div className="lf-flex-grow p-1 lf-bg-gray-50 lf-overflow-auto lf-h-full">
      <Transition
        show={show}
        enter="lf-transition lf-ease-in-out lf-duration-300 lf-transform"
        enterFrom="lf-translate-x-full"
        enterTo="lf-translate-x-0"
        leave="lf-transition lf-ease-in-out lf-duration-300 lf-transform"
        leaveFrom="lf-translate-x-0"
        leaveTo="lf--translate-x-full"
        className="lf-h-full"
      >
        <div className="lf-p-8 lf-h-full">
          <div className="lf-flex lf-flex-col lf-justify-center lf-h-full">
            <DragDrop
              onDrag={(event: DragEvent): void => {
                if (event.dataTransfer) {
                  event.dataTransfer.dropEffect = 'copy';
                }
              }}
              onDragIn={(event: DragEvent): void => {
                if (event.dataTransfer) {
                  event.dataTransfer.setData('text', 'data');
                  event.dataTransfer.dropEffect = 'move';
                }
              }}
              onDragOut={(): void => {
                /** */
              }}
              onDragDrop={(files: FileList): void => {
                tracker.pluginTracking({
                  eventType: eventsConst.import,
                  userId: appData.userData.id,
                  eventProperties: { type: eventEnums.importType.json },
                });
                handleFileChosen(files[0]);
              }}
              handleFileChosen={(files: FileList): void => {
                tracker.pluginTracking({
                  eventType: eventsConst.import,
                  userId: appData.userData.id,
                  eventProperties: { type: eventEnums.importType.json },
                });
                handleFileChosen(files[0]);
              }}
              bordered
            />
          </div>
        </div>
      </Transition>
    </div>
  );
};

export default withBase({ SidebarContent: ImportSidebar })(Import);
