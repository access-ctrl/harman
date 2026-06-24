import { useRef, useState, useEffect, useCallback } from "react";
import { useQuery, UseQueryArgs, UseQueryResponse } from "urql";

const useLazyQuery: (
  args: Omit<UseQueryArgs, "pause">
) => UseQueryResponse = (args) => {
  const firstUpdate = useRef(true);
  const [variables, setVariables] = useState<any>();

  const [result, refetch] = useQuery({
    ...args,
    variables,
    pause: true
  });

  useEffect(() => {
    if (firstUpdate.current) {
      firstUpdate.current = false;
      return;
    }
    refetch();
  }, [variables]);

  const makeRequest = useCallback((reqVariables: unknown) => {
    setVariables(reqVariables);
  }, []);

  return [result, makeRequest];
};

export default useLazyQuery;