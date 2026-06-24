/**
 * Copyright 2022 Design Barn Inc.
 */

import { useTracker } from '@context/tracker-provider';
import { routes } from '@helpers/consts';
import { eventEnums, eventsConst } from '@lottiefiles/plugin-tracker';
import { useContext } from '@wordpress/element';
import * as React from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';

import { LottieContext } from '../../../../../context/lottie-provider';
import { INavbarProps } from '../../../../../interfaces';

export const Menu: React.FC<INavbarProps> = () => {
  const prevLocation = useLocation();
  const navigate = useNavigate();
  const tracker = useTracker();
  const isCurrentTab = (tabName: string): boolean => prevLocation.pathname.includes(tabName);
  const { appData } = useContext(LottieContext);

  const onChangeTab = (tab: string): void => {
    let type = '';

    if (tab === routes.discover) {
      type = eventEnums.tabsType.discover;
    } else if (tab === routes.myAnimations) {
      type = eventEnums.tabsType.myAnimation;
    } else if (tab === routes.import) {
      type = eventEnums.tabsType.import;
    } else {
      type = '/';
    }
    tracker.pluginTracking({
      eventType: eventsConst.click.tab,
      userId: appData.userData.id,
      eventProperties: { type },
    });

    navigate(`${tab}`);
  };

  return (
    <div className="lf-flex m-0 lf-list-none lf-h-full">
      {appData && (
        <>
          <li
            className="lf-m-0 lf-cursor-pointer lf-px-4 lf-flex lf-items-center"
            onClick={(): void => onChangeTab(routes.discover)}
          >
            <Link
              className={`lf-_menu-item lf-h-full lf-flex lf-items-center lf-text-sm lf-font-bold focus:lf-outline-none focus:lf-shadow-none lf-border-b-2 lf-border-white ${
                isCurrentTab(`${routes.discover}`)
                  ? 'focus:lf-text-teal-300 lf-text-teal-300 lf-border-teal-300'
                  : 'lf-text-gray-800 hover:lf-border-gray-200'
              }`}
              to={`${routes.discover}`}
              state={{ prevLocation }}
            >
              Discover
            </Link>
          </li>
          {/* <li
            className="lf-m-0 lf-cursor-pointer lf-px-4 lf-flex lf-items-center"
            onClick={(): void => onChangeTab(routes.myAnimation)}
          >
            <Link
              className={`lf-_menu-item lf-h-full lf-flex lf-items-center lf-text-sm lf-font-bold focus:lf-outline-none focus:lf-shadow-none lf-border-b-2 lf-border-white ${
                isCurrentTab(`${routes.myAnimation}`)
                  ? 'focus:lf-text-teal-300 lf-text-teal-300 lf-border-teal-300'
                  : 'lf-text-gray-800 hover:lf-border-gray-200'
              }`}
              to={`${routes.myAnimations}`}
              state={{ prevLocation }}
            >
              My Animations
            </Link>
          </li> */}
          <li
            className="lf-m-0 lf-cursor-pointer lf-px-4 lf-flex lf-items-center"
            onClick={(): void => onChangeTab(routes.workspaces)}
          >
            <Link
              className={`lf-_menu-item lf-h-full lf-flex lf-items-center lf-text-sm lf-font-bold focus:lf-outline-none focus:lf-shadow-none lf-border-b-2 lf-border-white ${
                isCurrentTab(`${routes.workspaces}`)
                  ? 'focus:lf-text-teal-300 lf-text-teal-300 lf-border-teal-300'
                  : 'lf-text-gray-800 hover:lf-border-gray-200'
              }`}
              to={`${routes.workspaces}`}
              state={{ prevLocation }}
            >
              Workspaces
            </Link>
          </li>
          <li
            className="lf-m-0 lf-cursor-pointer lf-px-4 lf-flex lf-items-center"
            onClick={(): void => onChangeTab(routes.import)}
          >
            <Link
              className={`lf-_menu-item lf-h-full lf-flex lf-items-center lf-text-sm lf-font-bold focus:lf-outline-none focus:lf-shadow-none lf-border-b-2 lf-border-white  ${
                isCurrentTab(`${routes.import}`) || isCurrentTab(`${routes.preview}`)
                  ? 'focus:lf-text-teal-300 lf-text-teal-300 lf-border-teal-300'
                  : 'lf-text-gray-800 hover:lf-border-gray-200'
              }`}
              to={`${routes.import}`}
              state={{ prevLocation }}
            >
              Import
            </Link>
          </li>
        </>
      )}
    </div>
  );
};
