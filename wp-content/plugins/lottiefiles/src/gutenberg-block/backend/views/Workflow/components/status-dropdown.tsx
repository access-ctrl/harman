/**
 * Copyright 2021 Design Barn Inc.
 */

// NOT IN USE

import React, { useEffect, useState } from 'react';
import { gql, useMutation } from 'urql';

import { CrownIcon, DownChevronIcon } from '../../../../../assets/Icons';
import { useWorkspace } from '../../../../../context/workspace-provider';

const statusOptions = ['Approved', 'In Progress', 'Needs Review', 'No Status'] as const;

const statusColors: { [key: string]: string } = {
  Approved: 'bg-green-500',
  'In Progress': 'bg-purple-500',
  'Needs Review': 'bg-orange-500',
  'No Status': 'bg-gray-400',
};

const textToStatus: { [key: string]: string | null } = {
  Approved: 'Approved',
  'In Progress': 'InProgress',
  'Needs Review': 'NeedsReview',
  'No Status': null,
};

const statusToText: { [key: string]: typeof statusOptions[number] } = {
  Approved: 'Approved',
  InProgress: 'In Progress',
  NeedsReview: 'Needs Review',
  NoStatus: 'No Status',
};

interface StatusTextProps {
  light?: boolean;
  showCrown?: boolean;
  value: string;
}

const StatusText: React.FC<StatusTextProps> = ({ light = false, showCrown = false, value }: StatusTextProps) => (
  <div className="flex items-center gap-2 rounded-md hover:bg-gray-50 px-2 py-1.5">
    <div className="flex-1 flex items-center gap-2 text-xs">
      <div className={`h-4 w-4 rounded-full ${statusColors[value]}`} />
      <div className={light ? 'text-gray-200' : 'text-gray-600'}>{value}</div>
    </div>
    {showCrown && <CrownIcon className="w-4 h-4" />}
  </div>
);

export const UPDATE_ANIMATION_STATUS = gql`
  mutation updateAnimationStatus($status: FileStatus, $id: String!) {
    updateAnimationStatus(status: $status, id: $id) {
      id
      status
      animationUrl
    }
  }
`;

export interface StatusDropdownProps {
  id: string;
  isPremium?: boolean;
  status: string;
}

export const StatusDropdown: React.FC<StatusDropdownProps> = ({
  id,
  isPremium = false,
  status,
}: StatusDropdownProps) => {
  const workspace = useWorkspace();

  const [isOpen, setIsOpen] = useState(false);
  const [selected, setSelected] = useState<typeof statusOptions[number]>(statusToText[status || 'NoStatus']);

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [_, updateAnimationStatus] = useMutation(UPDATE_ANIMATION_STATUS);

  useEffect(() => {
    function handleClickOutside() {
      setIsOpen(false);
    }
    window.addEventListener('click', handleClickOutside);

    return () => {
      window.removeEventListener('click', handleClickOutside);
    };
  }, []);

  const toggleDropdown = () => {
    setIsOpen(!isOpen);
  };

  const chooseStatus = (sts: typeof statusOptions[number]) => {
    if (isPremium) {
      updateAnimationStatus({
        variables: {
          id,
          status: textToStatus[sts],
        },
      });
      setSelected(sts);
      toggleDropdown();
    } else {
      workspace.showUpsellModal();
    }
  };

  return (
    <div
      onClick={(event: React.MouseEvent<HTMLDivElement, MouseEvent>) => event.stopPropagation()}
      className="relative inline-block w-40"
    >
      <div onClick={toggleDropdown} className="flex items-center justify-between rounded-md bg-gray-50">
        <StatusText value={selected} />
        <div className="flex gap-2">
          {!isPremium && <CrownIcon className="w-4 h-4" />}
          <DownChevronIcon className="mt-1 mr-3 cursor-pointer" />
        </div>
      </div>
      {isOpen && (
        <ul className="absolute bg-white rounded-md shadow-md p-1 z-10 w-full">
          {statusOptions.map((stsOpt: typeof statusOptions[number], i: number) => {
            return (
              <li
                key={i}
                onClick={() => chooseStatus(stsOpt)}
                className="group p-0.5 cursor-pointer rounded-md hover-black-light"
              >
                <StatusText showCrown={!isPremium} value={stsOpt} />
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
};
