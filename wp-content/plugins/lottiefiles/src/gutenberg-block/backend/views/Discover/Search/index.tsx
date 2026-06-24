/**
 * Copyright 2022 Design Barn Inc.
 */

import { useState } from '@wordpress/element';
import * as React from 'react';
import { useParams } from 'react-router-dom';

import { NoSearchData } from '../../../../../assets/Icons';
import { ListView, ListViewWrapper } from '../../../../../components';
import { NoData } from '../../../../../components/NoData';
import { PAGE_SIZE } from '../../../../../helpers/consts';
import { queries } from '../../../../../helpers/query-strings';
import useLazyQuery from '../../../../../hooks/use-lazy-query';

export const Search: React.FC = () => {
  const [currentPage, setPage] = useState(1);
  const { query } = useParams();

  // const [{ data, fetching }, refetchResults] = useQuery({
  //   query: queries.search,
  //   requestPolicy: 'network-only',
  //   variables: { page: currentPage, pageSize: PAGE_SIZE, query },
  // });

  const [{ data, fetching }, refetchResults] = useLazyQuery({
    query: queries.search,
    requestPolicy: "network-only",
  });

  React.useEffect(() => {
    refetchResults({
      first: PAGE_SIZE,
      query,
    });
  }, []);

  const next = () => {
    refetchResults({
      first: PAGE_SIZE,
      after:
        data &&
        data.searchPublicAnimations &&
        data.searchPublicAnimations.pageInfo &&
        data.searchPublicAnimations.pageInfo.endCursor,
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
        data.searchPublicAnimations &&
        data.searchPublicAnimations.pageInfo &&
        data.searchPublicAnimations.pageInfo.startCursor,
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
        totalCount={data?.searchPublicAnimations?.totalCount || 0}
        hasNext={data?.searchPublicAnimations?.pageInfo?.hasNextPage || false}
        hasPrev={data?.searchPublicAnimations?.pageInfo?.hasPreviousPage || false}
      >
        <ListView list={[]} isLoading={fetching} />
      </ListViewWrapper>
    );
  }

  if (data && data.searchPublicAnimations.edges && data.searchPublicAnimations.edges.length > 0) {
    return (
      // <ListViewWrapper onChangePage={setPage} totalPages={data.recent.totalPages} currentPage={data.recent.currentPage}>
      <ListViewWrapper
        onNext={next}
        onPrev={prev}
        page={currentPage}
        pageSize={PAGE_SIZE}
        totalCount={data?.searchPublicAnimations?.totalCount || 0}
        hasNext={data?.searchPublicAnimations?.pageInfo?.hasNextPage || false}
        hasPrev={data?.searchPublicAnimations?.pageInfo?.hasPreviousPage || false}
      >
        <ListView list={data.searchPublicAnimations.edges} isLoading={fetching} />
      </ListViewWrapper>
    );
  }

  return (
    <NoData lottieBy="Radhikakpor" noDataText="No result found">
      <NoSearchData />
    </NoData>
  );
};
