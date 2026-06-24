/**
 * Copyright 2021 Design Barn Inc.
 */

/* eslint-disable @typescript-eslint/no-explicit-any */

import React from 'react';
import { useParams } from 'react-router-dom';
import { useQuery } from 'urql';

import { useWorkspace } from '../../../../context/workspace-provider';
import { queries } from '../../../../helpers/query-strings';

import { Empty } from './components/empty';
import { ErrorView } from './components/error-view';
import { FileCard } from './components/file-card';
import { FolderCard } from './components/folder-card';
import { Loading } from './components/loading';

export const Project = () => {
  const { setProject } = useWorkspace();

  const params = useParams<{ projectId: string }>();

  const [{ data: projectData, error: projectError, fetching: projectLoading }] = useQuery({
    query: queries.project,
    variables: {
      id: params.projectId,
    },
  });

  const [{ data: folderData, error: folderError, fetching: folderLoading }] = useQuery({
    query: queries.projectFiles,
    requestPolicy: 'network-only',
    variables: {
      id: params.projectId,
      type: 'folders',
      orderBy: JSON.stringify({ updatedAt: 'DESC' }),
    },
  });

  const [{ data: fileData, error: fileError, fetching: fileLoading }] = useQuery({
    query: queries.projectFiles,
    requestPolicy: 'network-only',
    variables: {
      id: params.projectId,
      type: 'animations',
      orderBy: JSON.stringify({ updatedAt: 'DESC' }),
    },
  });

  // const onFileSave = (src: string): void => {
  //   setAttributes({ src });
  //   exploreLottie(false);

  //   // tracker.pluginTracking({
  //   //   eventType: eventsConst.click.insertAnimation,
  //   //   userId: appData.userData.id,
  //   //   eventProperties: { animationId: id, type: eventEnums.animationsType.lottie },
  //   //   resourceId: id,
  // eslint-disable-next-line no-secrets/no-secrets
  //   //   method: HitCountEvents.DOWNLOAD_LOTTIE_JSON,
  //   // });
  // };

  // const onInsert = (lottieUrl: string): void => {
  //   if (appData.copyLottieToMedia) {
  //     saveToMediaLibrary({
  //       url: lottieUrl,
  //       onFileSave: (src: string) => onFileSave(src),
  //     });
  //   } else {
  //     onFileSave(lottieUrl);
  //   }
  // };

  const folders = (folderData && folderData.projectFiles.edges) || [];
  const files = (fileData && fileData.projectFiles.edges) || [];
  const project = projectData && projectData.project;

  React.useEffect(() => {
    if (project) {
      setProject(project);
    }
  }, [projectData]);

  const renderContent = () => {
    const noFolders = folders.length === 0;
    const noFiles = files.length === 0;
    const noProject = params.projectId === '';

    if (folderLoading || fileLoading || projectLoading) {
      return <Loading content="Loading..." />;
    }

    if ((noFolders && noFiles) || noProject) {
      return (
        <Empty title="Got a Lottie to share?" description="Start building your Lottie assets library with your team." />
      );
    }

    if (folderError || fileError || projectError) {
      return <ErrorView />;
    }

    return (
      <div className="flex flex-wrap gap-4">
        {!noFolders &&
          folders.map((folder: any) => (
            <FolderCard
              key={folder.node.id}
              id={folder.node.id}
              name={folder.node.name}
              meta={{
                modifiedAt: folder.node.updatedAt,
                modifiedBy: folder.node.modifiedBy
                  ? `${folder.node.modifiedBy.firstName} ${folder.node.modifiedBy.lastName}`
                  : `${folder.node.createdBy.firstName} ${folder.node.createdBy.lastName}`,
              }}
              thumbnails={folder.node.thumbnails || []}
              filesCount={folder.node.filesCount}
            />
          ))}
        {!noFiles &&
          files.map((fileCard: any) => (
            <FileCard
              key={fileCard.node.id}
              id={fileCard.node.id}
              status={fileCard.node.status}
              animationUrl={fileCard.node && fileCard.node.fileObject?.url}
              name={fileCard.node.name}
              meta={{
                modifiedAt: fileCard.node.updatedAt,
                modifiedBy: fileCard.node.modifiedBy
                  ? `${fileCard.node.modifiedBy.name} `
                  : `${fileCard.node.createdBy.name} `,
              }}
            />
          ))}
      </div>
    );
  };

  return (
    <>
      <div className="lf-flex lf-items-center lf-gap-1"></div>
      <div className="lf-my-4 lf-mx-2">{renderContent()}</div>
    </>
  );
};
