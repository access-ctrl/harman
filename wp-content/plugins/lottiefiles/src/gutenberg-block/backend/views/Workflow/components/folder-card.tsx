/**
 * Copyright 2021 Design Barn Inc.
 */

import React, { Fragment } from 'react';

import { EmptyHamsterIcon, FolderBigIcon } from '../../../../../assets/Icons';
import { useWorkspace } from '../../../../../context/workspace-provider';
import { toHumanDate } from '../../../../../utils/helpers';

import './folder-card.css';

interface FolderCardProps {
  filesCount: number;
  id: string;
  meta: {
    modifiedAt: Date;
    modifiedBy: string;
  };
  name: string;
  thumbnails: Thumbnail[];
}

interface Thumbnail {
  thumbnails: {
    png: {
      small: {
        url: string;
      };
    };
  };
}

export const FolderCard: React.FC<FolderCardProps> = ({ filesCount, id, meta, name, thumbnails }: FolderCardProps) => {
  const { goTo } = useWorkspace();

  const goToFolder = () => {
    goTo({ id, node: 'Folder' });
  };

  const renderThumbnails = () => {
    const previews = [...thumbnails, '', '', ''].slice(0, filesCount > 3 ? 3 : filesCount);

    let className = '';

    if (filesCount >= 3) {
      className = 'thumbnail-grid';
    } else if (filesCount === 2) {
      className = 'h-full grid grid-cols-2 grid-rows-1 gap-2';
    }

    if (filesCount > 0) {
      return (
        <div style={{ minHeight: 0, width: '100%' }} className={className}>
          {previews.map((previewObj: Thumbnail, i: number) => {
            const preview = previewObj?.thumbnails?.png?.small?.url;

            if (filesCount > 3 && i === 2) {
              return (
                <div
                  key={i}
                  title={`${filesCount - 3} more`}
                  style={{
                    minHeight: 0,
                    borderRadius: '4px',
                    background: '#D9E0E6',
                  }}
                  className="h-full w-full flex items-center justify-center p-1"
                >
                  <span className="font-bold text-lg text-white">
                    {filesCount - 3 > 99 ? '99+' : `+${filesCount - 3}`}
                  </span>
                </div>
              );
            }

            return (
              <Fragment key={i}>
                {preview ? (
                  <img
                    style={{
                      minHeight: 0,
                      borderRadius: '4px',
                      background: '#fff',
                    }}
                    src={preview}
                    className="h-full w-full object-contain lf-border-none"
                  />
                ) : (
                  <EmptyHamsterIcon
                    style={{
                      minHeight: 0,
                      borderRadius: '4px',
                      backgroundColor: '#fff',
                    }}
                    className="h-full w-full object-contain"
                  />
                )}
              </Fragment>
            );
          })}
        </div>
      );
    }

    return <EmptyHamsterIcon />;
  };

  return (
    <div onClick={goToFolder} className="folder-card relative cursor-pointer">
      <FolderBigIcon className="folder-card-icon" style={{ zIndex: -1 }} />
      <div className="absolute top-2 bottom-0 right-0 left-0 flex flex-col justify-between">
        {/* Without min-height: 0 content overshoots :/ */}
        <div style={{ minHeight: 0, width: '100%' }} className="flex-1 flex flex-col items-center justify-center p-2">
          {renderThumbnails()}
        </div>
        <div className="px-2 pb-2">
          <div title={name} className="text-xs tracking-wider overflow-hidden overflow-ellipsis whitespace-nowrap">
            {name}
          </div>
          <div className="text-gray-300 w-full flex gap-1">
            <small
              title={`Modified by ${meta.modifiedBy}`}
              style={{
                fontSize: '10px',
              }}
              className="flex-1 tracking-wider overflow-hidden overflow-ellipsis whitespace-nowrap"
            >
              Modified by {meta.modifiedBy}
            </small>
            <small
              style={{
                fontSize: '10px',
              }}
              title={toHumanDate(new Date(meta.modifiedAt), true) as string}
            >
              {toHumanDate(new Date(meta.modifiedAt)) as string}
            </small>
          </div>
        </div>
      </div>
    </div>
  );
};
