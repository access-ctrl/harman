/**
 * Copyright 2022 Design Barn Inc.
 */

import { useTracker } from '@context/tracker-provider';
import { Transition } from '@headlessui/react';
import { eventsConst } from '@lottiefiles/plugin-tracker';
import { IconInput } from '@lottiefiles/react-ui-kit';
import { useEffect, useState, useContext } from '@wordpress/element';
import * as React from 'react';
import { Route, Routes, useNavigate, useParams, useLocation, useMatch } from 'react-router-dom';

import { SearchIcon } from '../../../../assets/Icons';
import { LottieContext } from '../../../../context/lottie-provider';
import { withBase } from '../../../../hoc';
import { debounce } from '../../../../utility';

import { Featured } from './Featured';
import { Popular } from './Popular';
import { Recent } from './Recent';
import { Search } from './Search';

const Sidebar: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const tracker = useTracker();
  const { appData } = useContext(LottieContext);

  const match = useMatch('/discover/search/:query')

  const { query } = useParams();
  const [searchQuery, setSearchQuery] = useState(query);


  useEffect(() => {
    // eslint-disable-next-line no-undefined
    if (searchQuery !== undefined) {
      navigate(`/discover/search/${searchQuery}`);
    }
  }, [searchQuery]);

  const debouncedSearch = debounce((input: string) => {
    setSearchQuery(input);
    tracker.pluginTracking({
      eventType: eventsConst.keyed.search,
      userId: appData.userData.id.toString(),
      eventProperties: { input },
    });
  }, 500);

  const active = (tabName: string): string => {
    const isRecent = tabName === 'recent' && location.pathname === '/discover';

    return location.pathname === `/discover/${tabName}` || isRecent ? 'lf-active' : '';
  };

  return (
    <>
      <IconInput
        id="searchInput"
        icon={<SearchIcon className="text-lf-plugin-grey-lighter" />}
        value={match?.params.query}
        type="search"
        onChange={(event: React.ChangeEvent<HTMLInputElement>): void => debouncedSearch(event.target.value)}
        onKeyPress={(): null => null}
        placeholder="Search..."
        style={{ background: '#fff', borderColor: '#DCDCDC' }}
      />
      <ul className="lf-mt-4">
        <li className={`lf-_lf-tab ${active('recent')}`} onClick={(): void => navigate('/discover/recent')}>
          <span className="lf-text-sm lf-font-semibold">Recent</span>
        </li>
        <li className={`lf-_lf-tab ${active('featured')}`} onClick={(): void => navigate('/discover/featured')}>
          <span className="text-sm lf-font-semibold">Featured</span>
        </li>
        <li className={`lf-_lf-tab ${active('popular')}`} onClick={(): void => navigate('/discover/popular')}>
          <span className="lf-text-sm lf-font-semibold">Popular</span>
        </li>
      </ul>
    </>
  );
};
const Discover: React.FC = () => {
  const [show, setShow] = useState(false);

  useEffect(() => {
    setShow(true);

    return (): void => setShow(false);
  }, []);

  return (
    <Transition
      show={show}
      enter="lf-transition lf-ease-in-out lf-duration-300 lf-transform"
      enterFrom="lf--translate-x-full"
      enterTo="lf-translate-x-0"
      leave="lf-transition lf-ease-in-out lf-duration-300 lf-transform"
      leaveFrom="lf--translate-x-0"
      leaveTo="lf-translate-x-full"
      className="lf-h-full"
    >
      <Routes>
        <Route path="/*" element={<Recent />} />
        <Route path="/recent" element={<Recent />} />
        <Route path={`/featured`} element={<Featured />} />
        <Route path={`/popular`} element={<Popular />} />
        <Route path={`/search/:query`} element={<Search />} />
      </Routes>
    </Transition>
  );
};

export default withBase({
  SidebarContent: Sidebar,
})(Discover);
