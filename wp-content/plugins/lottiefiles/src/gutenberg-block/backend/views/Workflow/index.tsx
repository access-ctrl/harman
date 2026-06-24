/**
 * Copyright 2021 Design Barn Inc.
 */

import { useTracker } from '@context/tracker-provider';
import { eventsConst } from '@lottiefiles/plugin-tracker';
import { IconInput, InputAppearance, Size } from '@lottiefiles/react-ui-kit';
import { useContext, useEffect, useState } from '@wordpress/element';
import * as React from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useMutation, useQuery } from 'urql';

import { SearchIcon } from '../../../../assets/Icons';
import { LottieContext } from '../../../../context/lottie-provider';
import { useWorkspace } from '../../../../context/workspace-provider';
import { mutation, queries } from '../../../../helpers/query-strings';
import { withBase } from '../../../../hoc';
import useLazyQuery from '../../../../hooks/use-lazy-query';
import { debounce } from '../../../../utility';

import { ErrorView } from './components/error-view';
import { Loading } from './components/loading';
import { SearchWrapper } from './components/search-wrapper';
import { WorkspaceSection, WorkspaceSectionProps } from './components/workspace-section';
import { Folder } from './folder';
import { Project } from './project';

export interface Workspace extends WorkspaceSectionProps {}

const Sidebar: React.FC = () => {
  // Here is sidebar logic
  const tracker = useTracker();
  const navigate = useNavigate();
  const { query } = useParams();
  const { setSearchQuery, workspace } = useWorkspace();
  const [workspaces, setWorkspaces] = useState([]);

  const { appData } = useContext(LottieContext);

  const [localQuery, setLocalQuery] = React.useState('');

  const workspaceName = workspace ? workspace.name : 'workspaces';

  const onKeyPress = (event: React.KeyboardEvent): void => {
    if (event.key === 'Enter') {
      setSearchQuery(localQuery);
    }
  };

  const debouncedSearch = debounce((input: string) => {
    setLocalQuery(input.trim());
    setSearchQuery(input.trim());
    tracker.pluginTracking({
      eventType: eventsConst.keyed.search,
      userId: appData.userData.id.toString(),
      eventProperties: { input },
    });
  }, 500);

  const [{ data: hasAccessData, fetching: hasAccessLoading }] = useQuery({
    query: queries.hasAccessToAWorkspace,
  });

  const [{ data: setupWorkspaceData, fetching: setupWorkspaceLoading }, setupInitialWorkspace] = useMutation(
    mutation.setupInitialWorkspace,
  );

  const [{ data, error, fetching: loading }, refetchResults] = useLazyQuery({
    query: queries.myWorkspaces,
    requestPolicy: "network-only",
  });

  const [{ data: currentWorkspaceData, fetching: updateWorkspaceLoading }, updateCurrentWorkspace] = useMutation(
    mutation.updateCurrentWorkspace,
  );

  useEffect(() => {
    if (!hasAccessLoading) {
      if (hasAccessData.hasAccessToAWorkspace) {
        refetchResults({});
      } else {
        setupInitialWorkspace();
      }
    }
  }, [hasAccessData]);

  useEffect(() => {
    if (!setupWorkspaceLoading) {
      if (setupWorkspaceData && setupWorkspaceData.setupInitialWorkspace) {
        updateCurrentWorkspace({ workspaceId: setupWorkspaceData && setupWorkspaceData.setupInitialWorkspace.id });
      }
    }
  }, [setupWorkspaceLoading]);

  useEffect(() => {
    if (!updateWorkspaceLoading) {
      if (currentWorkspaceData && currentWorkspaceData.updateCurrentWorkspace) {
        refetchResults({});

        // Trigger re-render
      }
    }
  }, [updateWorkspaceLoading]);

  useEffect(() => {
    if (data) {
      setWorkspaces(data.workspaces);
    }
  }, [data]);

  const renderWorkspaces = () => {
    if (loading || setupWorkspaceLoading) {
      return <Loading content="Loading..." />;
    } else if (error) {
      return (
        <ErrorView
          title="Oops!"
          description="An error occurred."
          action={{
            name: 'Try Again',
            onAction: () => {
              navigate('/workspaces');
            },
          }}
        />
      );
    }

    return workspaces.map((entity: Workspace) => <WorkspaceSection key={entity.id} {...entity} />);
  };

  return (
    <>
      <div className="lf-relative lf-flex lf-flex-col">
        <IconInput
          id="searchInput"
          icon={<SearchIcon className="text-lf-plugin-grey-lighter lf-text-sm" />}
          value={query}
          type="search"
          onChange={(event: React.ChangeEvent<HTMLInputElement>): void => debouncedSearch(event.target.value)}
          onKeyPress={onKeyPress}
          placeholder={`Search ${workspaceName}...`}
          style={{ background: '#fff', borderColor: '#DCDCDC' }}
          appearance={InputAppearance.standard}
          inputSize={Size.fluid}
        />
      </div>

      <ul
        style={{
          height: 'calc(100% - 25px)',
          overflow: 'auto',
          marginTop: '10px',
        }}
      >
        <div className="lf-flex lf-flex-col lf-mt-4">{renderWorkspaces()}</div>
      </ul>
    </>
  );
};

const Workflow = () => {
  // Here is where main logic will live
  // get current workspace
  // get first project
  const { project } = useWorkspace();

  const params = useParams();

  // const [] = useQuery({
  //   query: queries.workspaceProjects,
  //   variables: { workspaceId: workspace.id },
  // });

  // Temporary commented out because i wanna test without the auto-redirect if null
  // React.useEffect(() => {
  //   if (workspace && data && !loading && !project) {
  //     const projects = data.workspaceProjects.edges;

  //     if (projects && projects.length > 0) {
  //       const id = projects[0]?.node?.id;

  //       setProject(projects[0].node);
  //       goTo({ id, node: 'Project' });
  //     }
  //   }
  // }, [data, project]);

  return (
    <SearchWrapper>
      <div className="lf-p-4">
        <div className="lf-flex lf-flex-1 lf-flex-col lf-gap-1">
          <div style={{ zIndex: 1 }} className="lf-text-xl lf-font-bold lf-pt-4 lf-px-4">
            {project?.title || '...'}
          </div>
          <div className="lf-flex lf-flex-col lf-gap-4">
            {!params?.folderId && <Project />}
            {params?.folderId && <Folder />}
          </div>
        </div>
      </div>
    </SearchWrapper>
  );
};

export default withBase({ SidebarContent: Sidebar })(Workflow);
