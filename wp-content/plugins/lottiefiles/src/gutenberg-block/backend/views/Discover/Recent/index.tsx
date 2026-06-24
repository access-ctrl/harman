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

export const Recent: React.FC = () => {
  const [currentPage, setPage] = useState(1);

  // const [{ data, fetching }, refetchResults] = useQuery({
  //   query: queries.recent,
  //   requestPolicy: 'network-only',
  //   variables: { first: PAGE_SIZE },
  // });

  const [{ data, fetching }, refetchResults] = useLazyQuery({
    query: queries.recent,
    requestPolicy: "network-only",
  });

  React.useEffect(() => {
    refetchResults({
      first: PAGE_SIZE,
    });
  }, []);

  const pageInfo = 
  data &&
  data.recentPublicAnimations &&
  data.recentPublicAnimations.pageInfo

  const next = () => {
    refetchResults({
      first: PAGE_SIZE,
      after: pageInfo.endCursor,
      last: null,
      before: null,
    });
    setPage((pg: number) => pg + 1);
  };

  const prev = () => {
    refetchResults({
      last: PAGE_SIZE,
      before: pageInfo.startCursor,
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
        totalCount={data?.recentPublicAnimations?.totalCount || 0}
        hasNext={data?.recentPublicAnimations?.pageInfo?.hasNextPage || false}
        hasPrev={data?.recentPublicAnimations?.pageInfo?.hasPreviousPage || false}
      >
        <ListView list={[]} isLoading={fetching} />
      </ListViewWrapper>
    );
  }

  if (data && data.recentPublicAnimations.edges && data.recentPublicAnimations.edges.length > 0) {
    return (
      // <ListViewWrapper onChangePage={setPage} totalPages={data.recentPublicAnimations.totalPages} currentPage={data.recentPublicAnimations.currentPage}>
      <ListViewWrapper
        onNext={next}
        onPrev={prev}
        page={currentPage}
        pageSize={PAGE_SIZE}
        totalCount={data?.recentPublicAnimations?.totalCount || 0}
        hasNext={data?.recentPublicAnimations?.pageInfo?.hasNextPage || false}
        hasPrev={data?.recentPublicAnimations?.pageInfo?.hasPreviousPage || false}
      >
        <ListView list={data.recentPublicAnimations.edges} isLoading={fetching} />
      </ListViewWrapper>
    );
  }

  return (
    <NoData lottieBy="Radhikakpor" noDataText="No result found">
      <NoSearchData />
    </NoData>
  );
};
