/**
 * Copyright 2022 Design Barn Inc.
 */

import * as React from 'react';
import { useState } from 'react';

import { EmptyIcon } from '../../../../../assets/Icons';
import { useWorkspace } from '../../../../../context/workspace-provider';
import { queries } from '../../../../../helpers/query-strings';
import useLazyQuery from '../../../../../hooks/use-lazy-query';

import { Empty } from './empty';
import { FileCard } from './file-card';
import { FileModal } from './file-modal';
import { FolderCard } from './folder-card';
import { Loading } from './loading';
import { MiniTabs } from './mini-tabs';
import { Pagination } from './Pagination';
import { ProjectSearchCard } from './project-search-card';

interface SearchWrapperProps {
  children?: React.ReactNode;
  // isSearch: boolean;
  // searchQuery: string;
}

type IncludeFields = 'files' | 'folders' | 'projects';

// if key provided all true, otherwise only the value with the key is true
const include = (key?: IncludeFields) => ({
  inclFiles: key ? key === 'files' : true,
  inclFolders: key ? key === 'folders' : true,
  inclProjects: key ? key === 'projects' : true,
});

const LIMIT = 10;

export const SearchWrapper = ({ children }: SearchWrapperProps) => {
  const { searchQuery, workspace } = useWorkspace();

  const [page, setPage] = useState(1);

  const [{ data, fetching: loading }, searchWorkspace] = useLazyQuery({
    query: queries.searchWorkspace,
    requestPolicy: "network-only",
  });

  const [activeTab, setActiveTab] = useState('Animations');

  const isPremium = workspace && workspace.features ? workspace.features['search-workspace-assets'] : false;

  const first = (key: IncludeFields) => {
    setPage(1);
    searchWorkspace({
      query: searchQuery,
      first: LIMIT,
      ...include(key),
    });
  };

  const next = (key: IncludeFields) => {
    searchWorkspace({
      query: searchQuery,
      first: LIMIT,
      after:
        data &&
        data.searchWorkspace &&
        data.searchWorkspace[key] &&
        data.searchWorkspace[key].pageInfo &&
        data.searchWorkspace[key].pageInfo.endCursor,
      before: null,
      last: null,
      ...include(key),
    });
    setPage((pg: number) => pg + 1);
  };

  const prev = (key: IncludeFields) => {
    searchWorkspace({
      query: searchQuery,
      last: LIMIT,
      before:
        data &&
        data.searchWorkspace &&
        data.searchWorkspace[key] &&
        data.searchWorkspace[key].pageInfo &&
        data.searchWorkspace[key].pageInfo.startCursor,
      after: null,
      first: null,
      ...include(key),
    });
    setPage((pg: number) => pg - 1);
  };

  const searching = <Loading />;

  const empty = (
    <Empty
      icon={<EmptyIcon />}
      title="No results found"
      description="We couldn't find any item based on your search."
    />
  );

  const renderFiles = () => {
    const files = (data && data.searchWorkspace.files && data.searchWorkspace.files.edges) || [];

    const pageInfo = (data && data.searchWorkspace.files && data.searchWorkspace.files.pageInfo) || [];
    const totalCount = (data && data.searchWorkspace.files && data.searchWorkspace.files.totalCount) || [];

    const anyOther = (data && data.searchWorkspace.folders) || (data && data.searchWorkspace.projects);

    if (loading || anyOther) return searching;
    if (files.length === 0) return empty;

    return (
      <>
        <div className="flex flex-wrap gap-4">
          {files.map((file: unknown) => {
            if (file && file.node && file.node.fileObject && file.node.fileObject.url) {
              return (
                <FileCard
                  key={file.node.id}
                  id={file.node.id}
                  status={file.node.status}
                  animationUrl={file.node.fileObject && file.node.fileObject.url}
                  name={file.node.name}
                  meta={{
                    modifiedAt: file.node.updatedAt,
                    modifiedBy:
                      (file.node.modifiedBy && file.node.modifiedBy.name) ||
                      (file.node.createdBy && file.node.createdBy.name),
                  }}
                />
              );
            } else {
              return <div />;
            }
          })}
        </div>
        <Pagination
          limit={LIMIT}
          next={() => next('files')}
          prev={() => prev('files')}
          page={page}
          total={totalCount}
          hasNext={pageInfo?.hasNextPage || false}
          hasPrev={pageInfo?.hasPreviousPage || false}
        />
      </>
    );
  };

  const renderFolders = () => {
    const folders = (data && data.searchWorkspace.folders && data.searchWorkspace.folders.edges) || [];

    const pageInfo = (data && data.searchWorkspace.folders && data.searchWorkspace.folders.pageInfo) || [];
    const totalCount = (data && data.searchWorkspace.folders && data.searchWorkspace.folders.totalCount) || [];

    const anyOther = (data && data.searchWorkspace.files) || (data && data.searchWorkspace.projects);

    if (loading || anyOther) return searching;
    if (folders.length === 0) return empty;

    return (
      <>
        <div className="flex flex-wrap gap-4">
          {folders.map((folder: unknown) => (
            <FolderCard
              key={folder.node.id}
              id={folder.node.id}
              name={folder.node.name}
              meta={{
                modifiedAt: folder.node.updatedAt,
                modifiedBy:
                  (folder.node.modifiedBy &&
                    `${folder.node.modifiedBy.firstName} ${folder.node.modifiedBy.lastName}`) ||
                  (folder.node.createdBy && `${folder.node.createdBy.firstName} ${folder.node.createdBy.lastName}`),
              }}
              thumbnails={folder.node.thumbnails || []}
              filesCount={folder.node.filesCount}
            />
          ))}
        </div>
        <Pagination
          limit={LIMIT}
          next={() => next('folders')}
          prev={() => prev('folders')}
          page={page}
          total={totalCount}
          hasNext={pageInfo?.hasNextPage || false}
          hasPrev={pageInfo?.hasPreviousPage || false}
        />
      </>
    );
  };

  const renderProjects = () => {
    const projects = (data && data.searchWorkspace.projects && data.searchWorkspace.projects.edges) || [];

    const pageInfo = (data && data.searchWorkspace.projects && data.searchWorkspace.projects.pageInfo) || [];
    const totalCount = (data && data.searchWorkspace.projects && data.searchWorkspace.projects.totalCount) || [];

    const anyOther = (data && data.searchWorkspace.files) || (data && data.searchWorkspace.folders);

    if (loading || anyOther) return searching;
    if (projects.length === 0) return empty;

    return (
      <div className="flex flex-wrap gap-4">
        {projects.map((project: unknown) => (
          <ProjectSearchCard
            key={project.node.id}
            id={project.node.id}
            isPrivate={project.node.isPrivate}
            title={project.node.title}
            filesCount={project.node.filesCount}
          />
        ))}
        <Pagination
          limit={LIMIT}
          next={() => next('projects')}
          prev={() => prev('projects')}
          page={page}
          total={totalCount}
          hasNext={pageInfo?.hasNextPage || false}
          hasPrev={pageInfo?.hasPreviousPage || false}
        />
      </div>
    );
  };

  const renderSearch = () => {
    if (!isPremium) {
      return (
        <div className="lf-h-full lf-flex lf-flex-col">
          <div className="lf-flex-1 lf-relative">
            <div className="lf-p-4 lf-absolute lf-top-0 lf-right-0 lf-bottom-0 lf-left-0 lf-overflow-auto lf-hidescroll">
              {children}
            </div>
          </div>
        </div>
      );
    }

    // const fileCount = (data && data.searchWorkspace.files && data.searchWorkspace.files.totalCount) || 0;
    // const folderCount = (data && data.searchWorkspace.folders && data.searchWorkspace.folders.totalCount) || 0;
    // const projectCount = (data && data.searchWorkspace.projects && data.searchWorkspace.projects.totalCount) || 0;

    return (
      <MiniTabs
        onChange={(tab: string) => {
          if (tab.startsWith('Animations')) {
            first('files');
            setActiveTab('Animations');
          } else if (tab.startsWith('Folders')) {
            first('folders');
            setActiveTab('Folders');
          } else if (tab.startsWith('Projects')) {
            first('projects');
            setActiveTab('Projects');
          }
        }}
        tabs={[
          {
            name: `Animations`,
            children: renderFiles(),
          },
          {
            name: `Folders`,
            children: renderFolders(),
          },
          {
            name: `Projects`,
            children: renderProjects(),
          },
        ]}
      />
    );
  };

  React.useEffect(() => {
    if (searchQuery) {
      if (activeTab === 'Animations') {
        first('files');
      } else if (activeTab === 'Folders') {
        first('folders');
      } else if (activeTab === 'Projects') {
        first('projects');
      }
    }
  }, [searchQuery, activeTab]);

  return (
    <>
      <FileModal />
      <div className="h-full flex flex-col">
        <div className="flex-1 relative">
          <div className="p-4 absolute top-0 right-0 bottom-0 left-0 overflow-auto hidescroll">
            {searchQuery ? renderSearch() : children}
          </div>
        </div>
      </div>
    </>
  );
};
