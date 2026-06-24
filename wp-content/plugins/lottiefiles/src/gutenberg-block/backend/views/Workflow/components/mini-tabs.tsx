/**
 * Copyright 2020 Design Barn Inc.
 */

import React, { Dispatch, FC, ReactNode, SetStateAction, useState } from 'react';

interface Tab {
  children?: ReactNode;
  name: string;
}
interface MiniTabsProps {
  onChange: (tab: string) => void;
  tabs: Tab[];
}

export const MiniTabs: FC<MiniTabsProps> = ({ onChange, tabs }: MiniTabsProps) => {
  const [activeTab, setActiveTab]: [number, Dispatch<SetStateAction<number>>] = useState(0);

  return (
    <div>
      <ul style={{ zIndex: 10 }} className="relative flex gap-2 pb-4 flex-wrap">
        {tabs.map((tab: Tab, i: number) => (
          <li key={i}>
            <button
              onClick={() => {
                setActiveTab(i);
                onChange(tab.name);
              }}
              style={{ borderRadius: '4px' }}
              className={`lf-py-1 lf-px-2 lf-font-semibold lf-text-base ${
                i === activeTab ? ' lf-text-gray-700 lf-bg-gray-200' : 'lf-text-gray-400'
              }`}
            >
              {tab.name}
            </button>
          </li>
        ))}
      </ul>
      {tabs[activeTab] ? <div>{tabs[activeTab].children}</div> : null}
    </div>
  );
};
