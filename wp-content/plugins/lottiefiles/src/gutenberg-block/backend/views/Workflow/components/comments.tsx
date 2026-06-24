/**
 * Copyright 2021 Design Barn Inc.
 */

import { Collapsible } from '@lottiefiles/react-ui-kit';
import React from 'react';

import { CommentSquareIcon } from '../../../../../assets/Icons';

export const Comments = () => {
  return (
    <div className="p-4">
      <Collapsible
        fullWidthContent={false}
        title={
          <div className="flex items-center">
            <CommentSquareIcon className="mr-3" />
            Comments (0)
          </div>
        }
      ></Collapsible>
    </div>
  );
};
