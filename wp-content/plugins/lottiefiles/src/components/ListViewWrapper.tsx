/**
 * Copyright 2022 Design Barn Inc.
 */

import * as React from 'react';

import { Pagination } from '../gutenberg-block/backend/views/Workflow/components/Pagination';

interface IListWrapperProps {
  children: React.ReactNode;
  hasNext: boolean;
  hasPrev: boolean;
  onNext(): void;
  onPrev(): void;
  page: number;
  pageSize: number;
  totalCount: number;
}

export const ListViewWrapper: React.FC<IListWrapperProps> = ({
  children,
  hasNext,
  hasPrev,
  onNext,
  onPrev,
  page,
  pageSize,
  totalCount,
}: IListWrapperProps) => {
  return (
    <div className="lf-pl-8 lf-pr-8 lf-pt-8 lf-text-xs lf-flex lf-flex-col lf-justify-between lf-h-full lf-relative">
      {children}
      <div id="pagination" className="lf-sticky lf-bottom-0 lf-right-0 lf-left-0 lf-bg-contentBg lf-opacity-90">
        <Pagination
          limit={pageSize}
          next={onNext}
          prev={onPrev}
          page={page}
          total={totalCount}
          hasNext={hasNext}
          hasPrev={hasPrev}
        />
      </div>
    </div>
  );
};
