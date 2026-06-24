/**
 * Copyright 2020 Design Barn Inc.
 */

import * as React from 'react';

export const DotLoading = () => {
  const dotClasses = 'w-1.5 h-1.5 bg-gray-500 rounded-full';

  return (
    <div style={{ animationDuration: '1s' }} className="flex items-center space-x-0.5 animate-pulse">
      <div className={dotClasses} />
      <div className={dotClasses} />
      <div className={dotClasses} />
    </div>
  );
};
