/**
 * Copyright 2022 Design Barn Inc.
 */

import { createContext, useEffect, useState } from '@wordpress/element';
import * as React from 'react';
import { Dispatch, FC, ReactNode, SetStateAction, useContext } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery } from 'urql';

// eslint-disable-next-line import/no-cycle
import { UpsellModal } from '../gutenberg-block/backend/views/Workflow/components/upsell-modal';
import { queries } from '../helpers/query-strings';

type Node = 'Project' | 'File' | 'Folder';

const FEATURES = [
  'comment',
  'create-projects',
  'create-folders',
  'update-animation-status',
  'upload-new-versions',
  'search-workspace-assets',
  'use-private-share',
] as const;

type FeatureKeys = typeof FEATURES[number];
type Feature = { [key in FeatureKeys]: boolean };

export interface Workspace {
  features: Feature;
  icon?: string;
  id: string;
  name: string;
}

interface FeatureObject {
  currentCount: number | null;
  isEnabled: boolean;
  max: number | null;
  name: string;
  slug: string;
}

// interface WorkspaceProps {
//   icon: string;
//   id: string;
//   name: string;
//   projects: WorkspaceProjectsProps[];
// }

// interface WorkspaceProjectsProps {
//   filesCount: number;
//   id: string;
//   isPrivate: boolean;
//   title: string;
//   updatedAt: string;
// }

interface FileObject {
  animationUrl: string;
  currentVersionId: string;
  id: string;
  name: string;
}

interface WorkspaceContextProps {
  file: FileObject;

  goBack: () => void;
  goTo: ({ id, node }: { id: string; node: Node }) => void;
  isClosed: boolean;
  project: string;
  searchQuery: string;

  setFile: Dispatch<SetStateAction<FileObject>>;

  setIsClosed: Dispatch<SetStateAction<boolean>>;

  setProject: Dispatch<SetStateAction<Project>>;
  setSearchQuery: Dispatch<SetStateAction<string>>;
  // set current workspaceId
  setWorkspace: Dispatch<SetStateAction<Workspace>>;
  // set current workspaceId
  setWorkspacesList: Dispatch<SetStateAction<Workspace[]>>;

  // show upsell modal
  showUpsellModal: () => void;
  // current workspaceId
  workspace: Workspace;
  // workspaces list
  workspacesList: Workspace[];
}

export const extractFeatures = (featObjects: FeatureObject[]): Feature => {
  const requiredFeats = featObjects.filter((obj: FeatureObject) => FEATURES.includes(obj.slug as FeatureKeys));

  const feature: { [key: string]: boolean } = {};

  requiredFeats.forEach((obj: FeatureObject) => {
    feature[obj.slug as FeatureKeys] = obj.isEnabled && (obj.currentCount || 0) < (obj.max || 1);
  });

  return feature as Feature;
};

export const WorkspaceContext = createContext<WorkspaceContextProps>({
  goBack: () => {
    // go back
  },
  goTo: () => {
    // go to page
  },
  project: '',
  workspace: null,
  setWorkspace: () => {
    // Update Workspace
  },
  file: null,
  setFile: () => {
    // Update File
  },
  showUpsellModal: () => {
    // Update isClosed
  },
  workspacesList: null,
  setWorkspacesList: () => {
    // Update Workspace
  },
  searchQuery: '',
  isClosed: true,
  setIsClosed: () => {
    // Close upsell modal
  },
});

interface WorkspaceProviderProps {
  children?: ReactNode;
}

interface Project {
  id: string;
  name: string;
}

export const WorkspaceProvider: FC<WorkspaceProviderProps> = ({ children }: WorkspaceProviderProps) => {
  const navigate = useNavigate();

  const [file, setFile]: [string, Dispatch<SetStateAction<FileObject>>] = useState(null);
  const [project, setProject]: [string, Dispatch<SetStateAction<Project>>] = useState(null);
  const [workspace, setWorkspace]: [string, Dispatch<SetStateAction<Workspace>>] = useState('');
  const [searchQuery, setSearchQuery]: [string, Dispatch<SetStateAction<Project>>] = useState('');
  const [isClosed, setIsClosed]: [boolean, Dispatch<SetStateAction<boolean>>] = useState(true as boolean);

  const [workspacesList, setWorkspacesList]: [Workspace[], Dispatch<SetStateAction<Workspace[]>>] = useState(null);

  const [{ data, error }] = useQuery({
    query: queries.currentWorkspace,
    requestPolicy: 'network-only',
  });

  const goTo = ({ id, node }: { id: string; node: Node }) => {
    switch (node) {
      case 'Project':
        navigate(`/workspaces/project/${id}`);

        break;

      case 'Folder':
        navigate(`/workspaces/project/${project.id}/${id}`);

        break;

      case 'File':
        navigate(`/workspaces/file/${id}`);

        break;

      default:
        navigate('/workspaces');

        break;
    }
  };

  const goBack = () => {
    navigate(-1);
  };

  useEffect(() => {
    if (data && !error) {
      setWorkspace({ ...data.currentWorkspace, features: extractFeatures(data.currentWorkspace.features) });
    }
  }, [data]);

  return (
    <WorkspaceContext.Provider
      value={{
        showUpsellModal: () => setIsClosed(false),
        project,
        file,
        setFile,
        workspace,
        setWorkspace,
        setProject,
        goTo,
        goBack,
        workspacesList,
        setWorkspacesList,
        searchQuery,
        setSearchQuery,
        isClosed,
        setIsClosed,
      }}
    >
      {children}
      <UpsellModal
        isClosed={isClosed}
        closeModal={() => {
          setIsClosed(true);
        }}
      />
    </WorkspaceContext.Provider>
  );
};

export const useWorkspace = () => useContext(WorkspaceContext);
