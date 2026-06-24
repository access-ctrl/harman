/**
 * Copyright 2022 Design Barn Inc.
 */

import { useTracker } from '@context/tracker-provider';
import { eventsConst } from '@lottiefiles/plugin-tracker';
import { Avatar, Size } from '@lottiefiles/react-ui-kit';
import { useContext } from '@wordpress/element';
import * as React from 'react';

import { color } from '../../../assets/colors';
import { LinkIcon } from '../../../assets/Icons/Link';
import { LottieContext } from '../../../context/lottie-provider';

interface ISidebarProps {
  children?: React.ReactNode;
}

export const Sidebar: React.FC = ({ children }: ISidebarProps) => {
  const tracker = useTracker();
  const { appData, onLogout, onSwitch } = useContext(LottieContext);

  return (
    <div className="lf-flex lf-w-72 lf-p-2 lf-border-r lf-border-gray-200">
      <div className="lf-flex lf-flex-col lf-justify-between lf-items-center lf-w-full h-full">
        <div
          className="lf-w-full lf-p-4"
          style={{
            height: 'calc(100% - 214px)',
          }}
        >
          {children}
        </div>
        <div className="lf-w-full lf-p-4">
          <div className="lf-flex lf-flex-col">
            <div className="lf-flex lf-items-center">
              <div className="lf-w-14 lf-mr-4">
                <Avatar src={appData.userData.avatarUrl} size={Size.fluid} className="lf-border-0" />
              </div>
              <div>
                <h5 className="lf-font-bold lf-text-sm">{appData.userData.name}</h5>
                <h5 className="lf-text-gray-400 lf-text-xs">{appData.userData.email}</h5>
              </div>
            </div>
            <div className="lf-pt-4 lf-gap-2 lf-flex lf-flex-col">
              <a
                href={`https://lottiefiles.com${appData.userData.username}`}
                target="_blank"
                className="lf-text-sm lf-flex lf-items-center lf-gap-1 lf-text-teal-300"
                style={{
                  color: '#018889',
                }}
              >
                View Profile <LinkIcon color={color.$teal300} />
              </a>
              <div>
                <button
                  className="lf-text-sm"
                  onClick={(): void => {
                    if (!appData.switchAccount && !appData.isAdmin) {
                      onSwitch();
                    } else {
                      tracker.pluginTracking({
                        eventType: eventsConst.click.logout,
                        userId: appData?.userData?.id,
                      });
                      onLogout();
                    }
                  }}
                >
                  Logout
                </button>
              </div>
              <p className="lf-text-xs lf-text-gray-400 lf-mt-3">Copyright © 2022 Design Barn Inc.</p>
              <ul className="lf-text-xs lf-flex lf-gap-6">
                <li>
                  <a
                    className="lf-text-sm lf-text-gray-500 hover:lf-text-gray-700"
                    target="_blank"
                    href="https://feedback.lottiefiles.com/plugin-wordpress"
                  >
                    Feedback
                  </a>
                </li>
                <li>
                  <a
                    className="lf-text-sm lf-text-gray-500 hover:lf-text-gray-700"
                    target="_blank"
                    href="https://lottiefiles.zendesk.com"
                  >
                    Help Center
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
