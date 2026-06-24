/**
 * Copyright 2021 Design Barn Inc.
 */

import { Appearance, Size, TextColor } from '@lottiefiles/react-ui-kit';
import React from 'react';

import { Button, Card } from '../../../../../_components';
import { LottiePlayer } from '../../../../../components/LottiePlayer';
import { useWorkspace } from '../../../../../context/workspace-provider';
import { toHumanDate } from '../../../../../utils/helpers';
import './file-card.css';

interface FileCardProps {
  animationUrl: string;
  id: string;
  meta: {
    modifiedAt: Date;
    modifiedBy: string;
  };
  name: string;
  status: string;
}

const statusColors: { [key: string]: string } = {
  Approved: 'bg-green-500',
  InProgress: 'bg-purple-500',
  NeedsReview: 'bg-orange-500',
  NoStatus: 'bg-gray-400',
};

const statusToText: { [key: string]: string } = {
  Approved: 'Approved',
  InProgress: 'In Progress',
  NeedsReview: 'Needs Review',
  NoStatus: 'No Status',
};

export const FileCard: React.FC<FileCardProps> = ({ animationUrl, id, meta, name, status }: FileCardProps) => {
  const { setFile } = useWorkspace();
  const goToFile = (): void => {
    // goTo({ id, node: 'File' });
    // Fetch file and setfile
    setFile(id);
  };

  const actualStatus = status || 'NoStatus';

  if (!animationUrl || animationUrl === '') {
    return <></>;
  }

  return (
    <div
      key={`project-${id}`}
      onKeyDown={(): null => null}
      className="list-item lf-cursor-pointer lf-list-none lf-show lf-relative lf-group"
    >
      <Card size={Size.fluid} style={{ height: '178px', width: '174px' }}>
        <LottiePlayer bgColor={'white'} src={animationUrl} isPreview />
        <div className="lf-show-item lf-absolute lf-bottom-4 z-10 ">
          <Button appearance={Appearance.primary} size={Size.tiny} textColor={TextColor.white} onClick={goToFile}>
            Insert animation
          </Button>
        </div>
        <div
          style={{
            borderBottomLeftRadius: '4px',
            borderBottomRightRadius: '4px',
          }}
          className="bg-white p-1  absolute bottom-0 right-1 left-1"
        >
          <div title={name} className="text-xs overflow-hidden overflow-ellipsis whitespace-nowrap">
            {name}
          </div>
          <div className="text-gray-300 flex gap-1">
            <small
              title={`Modified by ${meta.modifiedBy}`}
              style={{ fontSize: '10px', flex: 1 }}
              className="overflow-hidden overflow-ellipsis whitespace-nowrap"
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

        <div
          className={`lf-z-10 lf-absolute top-2.5 left-2.5 h-2.5 w-2.5 rounded-full ${statusColors[actualStatus]}`}
        />
        <div
          style={{ paddingLeft: '1.35rem', borderRadius: '4px' }}
          className="lf-absolute top-1 left-1 pr-2 py-0.5 lf-bg-gray-50 lf-shadow-md lf-show-item"
        >
          <div className="text-black text-xs">{statusToText[actualStatus]}</div>
        </div>
      </Card>
    </div>
  );
};
