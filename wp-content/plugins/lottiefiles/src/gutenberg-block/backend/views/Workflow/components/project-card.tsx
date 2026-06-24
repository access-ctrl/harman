/**
 * Copyright 2021 Design Barn Inc.
 */

import React from 'react';

import { FolderOutlineIcon, LockedIcon } from '../../../../../assets/Icons';
import { useWorkspace } from '../../../../../context/workspace-provider';

export interface ProjectCardProps {
  filesCount: number;
  id: string;
  isPrivate?: boolean;
  title: string;
}

export const ProjectCard: React.FC<ProjectCardProps> = ({
  filesCount,
  id,
  isPrivate = false,
  title,
}: ProjectCardProps) => {
  const { goTo } = useWorkspace();

  const goToProjectDetails = () => {
    goTo({ id, node: 'Project' });
  };

  return (
    <button
      onClick={goToProjectDetails}
      className="lf-text-left rounded-md cursor-pointer w-full flex px-2.5 py-2 hover-black"
      title={`${filesCount} animations`}
    >
      {isPrivate ? (
        <LockedIcon className="text-gray-600 fill-current" />
      ) : (
        <FolderOutlineIcon className="text-gray-600 h-4" />
      )}

      <div className="ml-2 text-xs tracking-wider" title={title}>
        {title}
      </div>
    </button>
  );
};
