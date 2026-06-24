/**
 * Copyright 2020 Design Barn Inc.
 */

import React from 'react';

interface PaginationProps {
  disabled?: boolean;
  limit: number;
  next: () => void;
  page: number;
  prev: () => void;
  total: number;
}

export const Pagination = ({ disabled = false, limit, next, page, prev, total }: PaginationProps) => {
  const offset = page * limit - limit;

  const currentPage = Math.ceil(offset / limit) + 1;
  const totalPages = Math.ceil(total / limit);

  const hasNext = offset + limit < total;
  const hasPrev = offset >= limit;

  const prevDisabled = disabled || !hasPrev;
  const nextDisabled = disabled || !hasNext;

  if (nextDisabled && prevDisabled) return null;

  return (
    <div className="lf-flex lf-gap-2 lf-w-full lf-justify-between lf-items-center lf-mt-6">
      <div className=" lf-text-gray-600 lf-text-sm lf-font-semibold">
        Page {currentPage} of {totalPages}
      </div>
      <div className="lf-flex lf-gap-2">
        <button
          disabled={prevDisabled}
          onClick={prev}
          style={{ borderRadius: '4px' }}
          className={`lf-py-1 lf-px-2 lf-font-semibold lf-text-gray-600 lf-w-16 lf-border lf-border-gray-600 ${
            prevDisabled ? ' lf-opacity-10' : 'lf-text-gray-600'
          }`}
        >
          Previous
        </button>
        <button
          disabled={nextDisabled}
          onClick={next}
          style={{ borderRadius: '4px' }}
          className={`lf-py-1 lf-px-2 lf-font-semibold lf-text-gray-600 lf-border lf-w-16 lf-border-gray-600 ${
            nextDisabled ? 'lf-opacity-10' : 'lf-text-gray-600'
          }`}
        >
          Next
        </button>
      </div>
    </div>
  );
};
