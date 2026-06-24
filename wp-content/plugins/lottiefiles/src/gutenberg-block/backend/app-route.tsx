/**
 * Copyright 2022 Design Barn Inc.
 */

import { Appearance, Size, TextColor } from '@lottiefiles/react-ui-kit';
import { useContext } from '@wordpress/element';
import * as React from 'react';
import { Navigate, Route, Routes, useLocation } from 'react-router-dom';
import * as semver from 'semver';
import { useQuery } from 'urql';

import packageJson from '../../../package.json';
import { Button } from '../../_components';
import { UpdateIcon } from '../../assets/Icons';
import { LottieContext } from '../../context/lottie-provider';
import { WorkspaceProvider } from '../../context/workspace-provider';
import { queries } from '../../helpers/query-strings';
import { IAppRouteProps } from '../../interfaces';

import { Discover, Import, OnBoarding, Preview, Workflow } from './views';
import { ErrorView } from './views/Workflow/components/error-view';

export const AppRoute: React.FC<IAppRouteProps> = ({ attributes, setAttributes }: IAppRouteProps) => {
  const { appData, isAppLoading } = useContext(LottieContext);
  const prevLocation = useLocation();
  const [{ data, error, fetching }] = useQuery({
    query: queries.softwareUpdates,
    variables: { version: packageJson.version },
  });

  if (isAppLoading) {
    return <div className="lf-spinner large"></div>;
  }

  if (error) {
    return <ErrorView title="Oops!" description="A server error occured. Please try again later." />;
  }

  if (!fetching) {
    if (data && data.softwareUpdates) {
      if (semver.lt(packageJson.version, data.softwareUpdates.version)) {
        return (
          <div className="lf-w-full lf-flex lf-justify-center lf-text-center">
            <div className="lf-flex lf-mt-20 lf-items-center lf-flex-col lf-max-w-lg">
              <>
                <UpdateIcon />
                <h2 className="text-4xl font-lf-bold lf-leading-snug lf-mb-4">Update your plugin to continue</h2>
                <h5 className="lf-text-lg lf-font-light lf-mb-6">
                  The plugin version you’ve installed is no longer supported. Please update your plugin to the latest
                  version.
                </h5>
                <Button
                  appearance={Appearance.primary}
                  size={Size.small}
                  textColor={TextColor.white}
                  onClick={() => {
                    window.open(data.softwareUpdates.downloadUrl, '_blank');
                  }}
                >
                  Update now
                </Button>
              </>
            </div>
            <div className="lf-absolute lf-bottom-4 lf-left-4">
              <p className="lf-text-xs lf-text-gray-400">Copyright © 2022 Design Barn Inc.</p>
            </div>
          </div>
        );
      }
    }

    return (
      <WorkspaceProvider>
        <Routes>
          {appData ? (
            <>
              <Route path="/discover/*" element={<Discover />} />
              <Route path="/*" element={<Navigate to="/discover/recent" state={{ prevLocation }} />} />
              <Route path="/preview" element={<Preview attributes={attributes} setAttributes={setAttributes} />} />
              {/* <Route path="/my-animations/*" element={<MyAnimations />} /> */}
              <Route path="/workspaces/project/:projectId/:folderId" element={<Workflow />} />
              <Route path="/workspaces/project/:projectId" element={<Workflow />} />
              <Route path="/workspaces/*" element={<Workflow />} />
              <Route path="/import" element={<Import />} />
            </>
          ) : (
            <Route path="/*" element={<OnBoarding />} />
          )}
        </Routes>
      </WorkspaceProvider>
    );
  }

  return <div className="lf-spinner large"></div>;
};
