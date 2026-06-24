/**
 * Copyright 2021 Design Barn Inc.
 */

/* eslint-disable @typescript-eslint/no-explicit-any */

import React from 'react';
import { useParams } from 'react-router-dom';
import { useQuery } from 'urql';

import { ArrowLeftIcon } from '../../../../assets/Icons';
import { useWorkspace } from '../../../../context/workspace-provider';
import { queries } from '../../../../helpers/query-strings';

import { Empty } from './components/empty';
import { ErrorView } from './components/error-view';
import { FileCard } from './components/file-card';
import { Loading } from './components/loading';

export const Folder = () => {
  const { goBack, setProject } = useWorkspace();
  const params = useParams<{ folderId: string; projectId: string }>();

  const [{ data: projectData }] = useQuery({
    query: queries.project,
    variables: {
      id: params.projectId,
    },
  });

  const [{ data, error, fetching: loading }] = useQuery({
    query: queries.folder,
    requestPolicy: 'network-only',
    variables: { id: params.folderId },
  });

  const [{ data: folderFilesData, error: folderFilesError, fetching: folderFilesLoading }] = useQuery({
    query: queries.folderFiles,
    requestPolicy: 'network-only',
    variables: {
      id: params.folderId,
      first: 15,
    },
  });

  React.useEffect(() => {
    if (projectData) {
      setProject(projectData.project);
    }
  }, [projectData]);

  const folder = (data && data.folder) || {};
  const files = (folderFilesData && folderFilesData.folderFiles.edges) || [];

  const renderContent = () => {
    if (loading || folderFilesLoading) {
      return <Loading content="Loading..." />;
    }

    if (error || folderFilesError) {
      return (
        <ErrorView
          title="Oops!"
          description="An error occurred."
          action={{
            name: 'Go Back',
            onAction: () => {
              goBack();
            },
          }}
        />
      );
    }

    if (files.length === 0) {
      return (
        <Empty
          title="Hey, nothing to see here"
          description="Either you have no files / folders yet or something went wrong."
        />
      );
    }

    return (
      <div className="flex flex-wrap gap-4">
        {files.map((file: any) => (
          <FileCard
            key={file.node.id}
            id={file.node.id}
            status={file.node.status}
            animationUrl={file.node && file.node.fileObject?.url}
            name={file.node.name}
            meta={{
              modifiedAt: file.node.updatedAt,
              modifiedBy: file.node.modifiedBy ? file.node.modifiedBy.name : file.node.createdBy.name,
            }}
          />
        ))}
      </div>
    );
  };

  const path = folder.project ? (
    <div>
      {folder.project.title} <span className="mx-0.5">/</span> {folder.name}
    </div>
  ) : (
    '...'
  );

  return (
    <>
      <div className="flex items-center gap-1">
        <button style={{ zIndex: 1 }} className="p-1" onClick={goBack}>
          <ArrowLeftIcon />
        </button>
        <div style={{ zIndex: 1 }} className="text-xs">
          {path}
        </div>
      </div>
      <div className="my-4 mx-2">{renderContent()}</div>
    </>
  );
};
