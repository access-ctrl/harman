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

export const Featured: React.FC = () => {
  const [currentPage, setPage] = useState(1);

  // const [{ data, fetching }] = useQuery({
  //   query: queries.featured,
  //   variables: { first: 10 },
  // });

  const [{ data, fetching }, refetchResults] = useLazyQuery({
    query: queries.featured,
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
        data.featuredPublicAnimations &&
        data.featuredPublicAnimations.pageInfo &&
        data.featuredPublicAnimations.pageInfo.endCursor,
      before: null,
      last: null
    });
    setPage((pg: number) => pg + 1);
  };

  const prev = () => {
    refetchResults({
      last: PAGE_SIZE,
      before:
        data &&
        data.featuredPublicAnimations &&
        data.featuredPublicAnimations.pageInfo &&
        data.featuredPublicAnimations.pageInfo.startCursor,
      after: null,
      first: null,
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
        totalCount={data?.featuredPublicAnimations?.totalCount || 0}
        hasNext={data?.featuredPublicAnimations?.pageInfo?.hasNextPage || false}
        hasPrev={data?.featuredPublicAnimations?.pageInfo?.hasPreviousPage || false}
      >
        <ListView list={[]} isLoading={fetching} />
      </ListViewWrapper>
    );
  }

  if (data && data.featuredPublicAnimations.edges && data.featuredPublicAnimations.edges.length > 0) {
    return (
      // <ListViewWrapper
      //   onChangePage={setPage}
      //   totalPages={data.featured.totalPages}
      //   currentPage={data.featured.currentPage}
      // >
      <ListViewWrapper
        onNext={next}
        onPrev={prev}
        page={currentPage}
        pageSize={PAGE_SIZE}
        totalCount={data?.featuredPublicAnimations?.totalCount || 0}
        hasNext={data?.featuredPublicAnimations?.pageInfo?.hasNextPage || false}
        hasPrev={data?.featuredPublicAnimations?.pageInfo?.hasPreviousPage || false}
      >
        <ListView list={data.featuredPublicAnimations.edges} isLoading={fetching} />
      </ListViewWrapper>
    );
  }

  return (
    <NoData lottieBy="Radhikakpor" noDataText="No result found">
      <NoSearchData />
    </NoData>
  );
};
