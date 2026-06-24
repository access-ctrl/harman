/**
 * Copyright 2022 Design Barn Inc.
 */

import { Appearance, Size, TextColor } from '@lottiefiles/react-ui-kit';
import { useContext, useEffect } from '@wordpress/element';
import * as React from 'react';
import { useQuery } from 'urql';

import { Button } from '../../../../../_components';
import { CloseIcon, CrownIcon } from '../../../../../assets/Icons';
import { LottiePlayer } from '../../../../../components/LottiePlayer';
import { LottieContext } from '../../../../../context/lottie-provider';
import { useWorkspace } from '../../../../../context/workspace-provider';
import { URLS } from '../../../../../helpers/consts';
import { queries } from '../../../../../helpers/query-strings';
import { saveToMediaLibrary } from '../../../../../utility';
import { isDotLottie, extractDotLottie } from '../../../../../utils';

export const FileModal: React.FC = () => {
  const { file, setFile, workspace } = useWorkspace();
  const { appData, closeModal, setAttributes } = useContext(LottieContext);
  const [fileUrl, setFileUrl] = React.useState('');

  const [{ data, fetching }] = useQuery({
    query: queries.fileVersions,
    variables: { fileId: file, first: 30, orderBy: JSON.stringify({ updatedAt: 'DESC' }) },
  });

  const fileVersions = (data && data.fileVersions.edges) || [];

  useEffect(() => {
    if (!fetching && fileVersions.length > 0) {
      const defaultUrl = fileVersions[0].node.fileObject.url;

      setFileUrl(defaultUrl);
    }
  }, [fetching]);

  const isPremium = workspace && workspace.features ? workspace.features['use-private-share'] : false;
  const onFileSave = (src: string, jsonSrc?: Record<string, unknown> | null): void => {
    setAttributes({ src, jsonSrc });
    closeModal(false);

    // eslint-disable-next-line no-secrets/no-secrets
    /* tracker.pluginTracking({
      eventType: eventsConst.click.insertAnimation,
      userId: appData.userData.id,
      eventProperties: { animationId: id, type: eventEnums.animationsType.lottie },
      resourceId: id,
      method: HitCountEvents.DOWNLOAD_LOTTIE_JSON,
    });*/
  };

  const onInsert = (lottieUrl: string): void => {
    if (isPremium) {
      if (appData.copyLottieToMedia) {
        saveToMediaLibrary({
          url: lottieUrl,
          onFileSave: (src: string, jsonSrc?: Record<string, unknown>) => onFileSave(src, jsonSrc),
          onError: (error: unknown) => {
            console.log(error);
          },
        });
      } else {
        if (isDotLottie(lottieUrl)) {
          const getLottie = async () => {
            try {
              const lottieJson = await extractDotLottie(lottieUrl);

              onFileSave(lottieUrl, lottieJson);
            } catch (error) {
              console.log(error);
            }
          };

          getLottie();

          return;
        }
        onFileSave(lottieUrl);
      }
    } else {
      saveToMediaLibrary({
        url: lottieUrl,
        onFileSave: (src: string, jsonSrc?: Record<string, unknown>) => onFileSave(src, jsonSrc),
        onError: (error: unknown) => {
          console.log(error);
        },
      });
    }
  };

  const renderDropdown = () => {
    if (!fetching && fileVersions) {
      return (
        <select
          className={`lf-dropdown form-select standard lf-my-4 lf-p-1 lf-bg-gray-50 lf-border-gray-200 lf-text-gray-600`}
          onChange={event => {
            setFileUrl(event.target.value);
          }}
        >
          {fileVersions.map((item: unknown) => {
            const node = item.node;
            const versionNr = node.versionNumber;
            const versionUrl = node.fileObject.url;

            const nodeName = `  Version ${versionNr}`;

            return (
              <option key={versionNr} value={versionUrl}>
                {nodeName}
              </option>
            );
          })}
        </select>
      );
    } else return <div />;
  };

  return (
    fileUrl && (
      <div
        className="lf-z-20 lf-absolute lf-top-0  lf-left-0 lf-right-0 lf-bottom-0"
        style={{ backgroundColor: 'rgba(0, 0, 0, 0.1)' }}
      >
        <div className="lf-relative lf-h-full lf-w-full  lf-place-content-center lf-place-items-center lf-flex">
          <div className="lf-relative lf-flex lf-bg-white  lf-justify-center lf-items-center lf-rounded-md lf-w-1/3 lf-flex-col">
            <button
              onClick={() => {
                // Clear file, dont insert
                setFile('');
              }}
              className="lf-absolute lf-top-4 lf-right-4"
            >
              <CloseIcon className="lf-text-gray-600 lf-fill-current lf-h-5 lf-w-5" />
            </button>
            <div className="lf-p-6 lf-w-full">
              <LottiePlayer bgColor={'white'} src={fileUrl} isLarge />
              {isPremium && renderDropdown()}
              <Button
                appearance={Appearance.primary}
                size={Size.fluid}
                textColor={TextColor.white}
                onClick={() => {
                  // Clear file, insert file
                  onInsert(fileUrl);
                  setFile('');
                }}
              >
                Insert animation
              </Button>
              {!isPremium && (
                <div>
                  <p>This animation will be copied to your media library and inserted as a local Lottie JSON.</p>
                  <a
                    href={URLS.upsell}
                    className=" lf-text-teal-300 hover:lf-text-teal-400 lf-w-full lf-flex lf-gap-2 lf-place-items-center"
                    target="_blank"
                  >
                    <CrownIcon /> Upgrade to link this animation directly with custom embed
                  </a>
                  <ul className="lf-mt-2">
                    <li>· Changes to the animation will be reflected immediately</li>
                    <li>· Improve your website's performance</li>
                  </ul>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    )
  );
};
