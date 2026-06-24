/**
 * Copyright 2021 Design Barn Inc.
 */

import React from 'react';

import { FolderOutlineIcon, LockedIcon } from '../../../../../assets/Icons';
import { useWorkspace } from '../../../../../context/workspace-provider';

import { ProjectCardProps } from './project-card';

export const ProjectSearchCard: React.FC<ProjectCardProps> = ({
  filesCount,
  id,
  isPrivate = false,
  title,
}: ProjectCardProps) => {
  const { goTo, workspace } = useWorkspace();

  const goToProjectDetails = () => {
    goTo({ id, node: 'Project' });
  };

  return (
    <button
      onClick={goToProjectDetails}
      className="lf-text-left rounded-md cursor-pointer lf-flex lf-flex-col lf-p-3 lf-border lf-border-gray-50 lf-bg-white"
      title={`${filesCount} animations`}
    >
      <div className=" lf-flex lf-items-center">
        {isPrivate ? (
          <LockedIcon className="text-gray-600 fill-current" />
        ) : (
          <FolderOutlineIcon className="text-gray-600 h-4" />
        )}

        <div className="lf-ml-2 lf-text-sm lf-text-gray-600 lf-tracking-wider" title={title}>
          {title}
        </div>
      </div>
      <div className="lf-text-xs lf-text-gray-400">{workspace.name}</div>
    </button>
  );
};
