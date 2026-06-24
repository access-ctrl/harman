/**
 * Copyright 2021 Design Barn Inc.
 */

import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useMutation, useQuery } from 'urql';

import { ChevronDown, ChevronUp, TriangleDown } from '../../../../../assets/Icons';
import { useWorkspace } from '../../../../../context/workspace-provider';
import { queries, mutation } from '../../../../../helpers/query-strings';

import { DotLoading } from './dot-loading';
import { Empty } from './empty';
import { Loading } from './loading';
import { ProjectCard } from './project-card';

export interface WorkspaceSectionProps {
  icon?: string;
  id: string;
  name: string;
}

export const WorkspaceSection: React.FC<WorkspaceSectionProps> = ({ icon, id, name }: WorkspaceSectionProps) => {
  const { project, setProject, setWorkspace, setWorkspacesList, workspace } = useWorkspace();
  const navigate = useNavigate();

  const [{ data, fetching: loading }] = useQuery({
    query: queries.workspaceProjects,
    variables: { workspaceId: id },
  });
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [_currentWorkspaceResponse, executeCurrentWorkspaceQuery] = useQuery({
    query: queries.currentWorkspace,
  });
  const [{ data: workspacesData }, executeWorkspacesQuery] = useQuery({
    query: queries.myWorkspaces,
  });

  const [{ data: currentWorkspaceData, fetching: updating }, updateCurrentWorkspace] = useMutation(
    mutation.updateCurrentWorkspace,
  );

  const projects = (data && data.workspaceProjects.edges) || [];

  const goToProjectDetails = () => {
    let _project = project;

    if (projects.length > 0 && projects[0].node) {
      _project = projects[0].node;
      const projectId = _project.id;

      setProject(_project);
      navigate(`/workspaces/project/${projectId}`);
    }
  };

  const switchWorkspace = async () => {
    const variables = { workspaceId: id };

    await updateCurrentWorkspace(variables);
    executeWorkspacesQuery();
    executeCurrentWorkspaceQuery();
    goToProjectDetails();
  };

  // When currentWorkspace is updated
  useEffect(() => {
    if (currentWorkspaceData) {
      setWorkspace(currentWorkspaceData.updateCurrentWorkspace);
    }
  }, [currentWorkspaceData]);

  // Loaded immediately
  useEffect(() => {
    if (workspacesData) {
      setWorkspacesList(workspacesData.workspaces);
    }
  }, [workspacesData]);

  const renderProjects = () => {
    if (loading) {
      return <Loading content="Loading..." />;
    }

    return projects.map((item: unknown) => (
      <li className="hover:bg-gray-50 rounded-md" key={item.node.id}>
        <ProjectCard
          key={item.node.id}
          id={item.node.id}
          isPrivate={item.node.isPrivate}
          title={item.node.title}
          filesCount={item.node.filesCount}
        />
      </li>
    ));
  };

  const isActiveWorkspace = workspace.id === id;

  // Redirect to default project
  useEffect(() => {
    if (data && isActiveWorkspace && !project) {
      goToProjectDetails();
    }
  }, [data]);

  const renderSwitchWorkspace = () => {
    if (isActiveWorkspace) {
      // @ts-ignore
      return <ChevronUp height={10} width={10} />;
    }

    return updating ? (
      <DotLoading />
    ) : (
      <button onClick={switchWorkspace}>
        {/* @ts-ignore */}
        <ChevronDown height={10} width={10} />
      </button>
    );
  };

  return (
    <div>
      {/* <li className={`lf-_lf-tab ${active('downloads')}`} onClick={(): void => navigate('/my-animations/downloads')}>
        <span className="lf-text-sm lf-font-semibold">My Downloads</span>
      </li> */}
      <div className="lf-flex lf-items-center lf-_lf-tab">
        {icon ? (
          <img className="lf-h-6 lf-w-6 lf-rounded-full lf-border-0" src={icon} alt="" />
        ) : (
          <div className="lf-rounded-full lf-h-6 lf-w-6 lf-flex lf-items-center lf-justify-center lf-text-black lf-font-bold">
            {name[0] || ''}
            {(name.split(' ')[1] && name.split(' ')[1][0]) || ''}
          </div>
        )}
        <div className="lf-ml-1 lf-text-sm lf-font-semibold lf-flex-1">{name}</div>
        {renderSwitchWorkspace()}
      </div>

      {isActiveWorkspace && (
        <div className="lf-mt-2 lf-ml-6">
          <div className="lf-flex lf-items-center lf-gap-2 lf-mb-2">
            <TriangleDown className="lf-text-gray-100 lf-fill-current" />

            <div className="lf-ml-1 lf-text-xs lf-font-lf-bold">Projects</div>
          </div>

          {!loading && projects.length === 0 ? (
            <Empty title="" description="" action={null} />
          ) : (
            <ul className="lf-flex lf-flex-col">{renderProjects()}</ul>
          )}
        </div>
      )}
    </div>
  );
};
