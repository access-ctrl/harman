/**
 * Copyright 2022 Design Barn Inc.
 */

import { useState } from '@wordpress/element';
import * as React from 'react';

import { NoSearchData } from '../../../../../assets/Icons';
import { ListView, ListViewWrapper } from '../../../../../components';
import { NoData } from '../../../../../components/NoData';
import { PAGE_SIZE } from '../../../../../helpers/consts';
import { queries } from '../../../../../helpers/query-strings';
import useLazyQuery from '../../../../../hooks/use-lazy-query';

export const Popular: React.FC = () => {
  const [currentPage, setPage] = useState(1);

  // const [{ data, fetching }, refetchResults] = useQuery({
  //   query: queries.popular,
  //   requestPolicy: 'network-only',
  //   variables: { first: PAGE_SIZE },
  // });

  const [{ data, fetching }, refetchResults] = useLazyQuery({
    query: queries.popular,
    requestPolicy: "network-only",
  });

  React.useEffect(() => {
    refetchResults({
      first: PAGE_SIZE,
    });
  }, []);

  const next = () => {
    refetchResults({
      first: PAGE_SIZE,
      after:
        data &&
        data.popularPublicAnimations &&
        data.popularPublicAnimations.pageInfo &&
        data.popularPublicAnimations.pageInfo.endCursor,
      last: null,
      before: null,
    });
    setPage((pg: number) => pg + 1);
  };

  const prev = () => {
    refetchResults({
      last: PAGE_SIZE,
      before:
        data &&
        data.popularPublicAnimations &&
        data.popularPublicAnimations.pageInfo &&
        data.popularPublicAnimations.pageInfo.startCursor,
      first: null,
      after: null,
    });
    setPage((pg: number) => pg - 1);
  };

  if (fetching) {
    return (
      // <ListViewWrapper onChangePage={setPage} totalPages={0}>
      <ListViewWrapper
        onNext={next}
        onPrev={prev}
        page={currentPage}
        pageSize={PAGE_SIZE}
        totalCount={data?.popularPublicAnimations?.totalCount || 0}
        hasNext={data?.popularPublicAnimations?.pageInfo?.hasNextPage || false}
        hasPrev={data?.popularPublicAnimations?.pageInfo?.hasPreviousPage || false}
      >
        <ListView list={[]} isLoading={fetching} />
      </ListViewWrapper>
    );
  }

  if (data && data.popularPublicAnimations.edges && data.popularPublicAnimations.edges.length > 0) {
    return (
      // <ListViewWrapper
      //   onChangePage={setPage}
      //   totalPages={data.popular.totalPages}
      //   currentPage={data.popular.currentPage}
      // >
      <ListViewWrapper
        onNext={next}
        onPrev={prev}
        page={currentPage}
        pageSize={PAGE_SIZE}
        totalCount={data?.popularPublicAnimations?.totalCount || 0}
        hasNext={data?.popularPublicAnimations?.pageInfo?.hasNextPage || false}
        hasPrev={data?.popularPublicAnimations?.pageInfo?.hasPreviousPage || false}
      >
        <ListView list={data.popularPublicAnimations.edges} isLoading={fetching} />
      </ListViewWrapper>
    );
  }

  return (
    <NoData lottieBy="Radhikakpor" noDataText="No result found">
      <NoSearchData />
    </NoData>
  );
};
