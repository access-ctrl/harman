/**
 * Copyright 2022 Design Barn Inc.
 */

const plugin = require('tailwindcss/plugin');

const colors = {
  transparent: 'transparent',

  white: '#ffffff',
  black: '#22292f',
  bg: '#f6f8f9',
  contentBg: '#FAFAFA',

  gray: {
    50: '#f6f8f9',
    100: '#ebf1f5',
    200: '#dae1e7',
    300: '#b8c2cc',
    400: '#8795a1',
    500: '#606f7b',
    600: '#3d4852',
    700: '#2b343b',
    800: '#22292f',
    900: '#111417',
  },
  teal: {
    50: '#D6FEF1',
    100: '#e4f7f7',
    200: '#00DDB3',
    300: '#00C1A3',
    400: '#029D91',
    500: '#0fccce',
    600: '#1fa4ab',
    700: '#19858b',
    800: '#13666b',
    900: '#002B2B',
  },
  orange: {
    100: '#ffeed3',
    500: '#f56e21',
    700: '#c34c09',
  },
  green: {
    100: '#cffcda',
    500: '#38c172',
    700: '#178233',
  },
  red: {
    100: '#ffdce3',
    500: '#ec3257',
    700: '#c91a3d',
  },
};

module.exports = {
  content: ['./src/**/*.{js,jsx,ts,tsx}'],
  important: true,
  prefix: 'lf-',
  darkMode: false,
  theme: {
    colors,
    extends: {
      spacing: {
        128: '32rem',
      },
      height: {
        128: '32rem',
      },
    },
  },
  variants: {
    extend: {
      display: ['group-hover'],
    },
  },
  plugins: [
    plugin(function ({ addComponents, theme }) {
      addComponents({
        '._lf-tab': {
          '@apply lf-w-full lf-p-2 lf-mb-2 lf-text-gray-600 lf-rounded-md lf-cursor-pointer': {},
          '&:hover': {
            '@apply lf-bg-gray-50 lf-text-teal-300': {},
          },
          '&.active': {
            '@apply lf-bg-gray-50 lf-text-teal-300': {},
          },
        },
        '._lf-drag-box': {
          '@apply lf-flex lf-justify-center lf-items-center lf-h-full lf-p-8 lf-rounded-md': {},
          '&.bordered': {
            '@apply lf-border-2 lf-border-gray-200 lf-border-dashed': {},
          },
        },
        '._lf-btn': {
          fontFamily: theme('fontFamily.lf-bold'),
          borderRadius: theme('borderRadius.lg'),
          display: 'inline-block !important',
          cursor: theme('cursor.pointer'),
          transitionProperty: theme('transitionProperty.all'),
          transitionDuration: '0.25s',
          paddingLeft: theme('padding.8'),
          paddingRight: theme('padding.8'),
          paddingTop: theme('padding.2'),
          paddingBottom: theme('padding.2'),
          color: theme('colors.gray.500'),
          outline: theme('outline.none'),
          position: 'relative',
          justifyContent: 'center',
          '&:focus': {
            outline: theme('outline.none'),
          },
          '&:hover': {
            backgroundColor: theme('colors.teal.600'),
          },
          '&.primary': {
            backgroundColor: theme('colors.teal.300'),
            color: theme('colors.white'),
            '&:hover': {
              backgroundColor: theme('colors.teal.400'),
            },
          },
          '&.secondary': {
            backgroundColor: theme('colors.teal.700'),
            color: theme('colors.white'),
            '&:hover': {
              backgroundColor: theme('colors.teal.800'),
            },
          },
          '&.red': {
            backgroundColor: theme('colors.red.500'),
            color: theme('colors.white'),
            '&:hover': {
              backgroundColor: theme('colors.red.700'),
            },
          },
          '&.link': {
            backgroundColor: 'transparent',
            color: theme('colors.teal.700'),
            '&:hover': {
              backgroundColor: 'transparent',
              color: theme('colors.teal.700'),
            },
          },
          '&.outline': {
            borderWidth: theme('borderWidth.DEFAULT'),
            borderColor: theme('borderColor.DEFAULT'),
            color: theme('colors.DEFAULT'),
            fontFamily: theme('lf-regular'),
            '&.black': {
              backgroundColor: theme('colors.transparent'),
              borderColor: theme('colors.black'),
              color: theme('colors.black'),
            },
            '&.orange': {
              backgroundColor: theme('colors.transparent'),
              borderColor: theme('colors.orange.500'),
              color: theme('colors.orange.500'),
            },
            '&.green': {
              backgroundColor: theme('colors.transparent'),
              borderColor: theme('colors.green.500'),
              color: theme('colors.lf-green.500'),
            },
            '&.red': {
              backgroundColor: theme('colors.transparent'),
              borderColor: theme('colors.red.500'),
              color: theme('colors.red.500'),
            },
            '&.teal': {
              backgroundColor: theme('colors.transparent'),
              borderColor: theme('colors.teal.600'),
              color: theme('colors.teal.600'),
            },
          },
          '&.tiny': {
            paddingLeft: theme('padding.4'),
            paddingRight: theme('padding.4'),
            paddingTop: theme('padding.2'),
            paddingBottom: theme('padding.2'),
            fontSize: theme('fontSize.xs'),
          },
          '&.small': {
            fontSize: theme('fontSize.sm'),
          },
          '&.medium': {
            fontSize: theme('fontSize.lg'),
          },
          '&.large': {
            fontSize: theme('fontSize.xl'),
          },
          '&.fluid': {
            width: theme('width.full'),
          },
          '&.icon': {
            paddingTop: theme('padding.2'),
            paddingBottom: theme('padding.2'),
            paddingLeft: theme('padding.4'),
            paddingRight: theme('padding.4'),
          },
          '&.compact': {
            padding: theme('padding[1.5]'),
            borderRadius: theme('borderRadius.md'),
          },
        },
        '._lf-link': {
          color: theme('colors.teal.600'),
          '&:focus': {
            outline: theme('outline.none'),
          },
          '&:hover': {
            color: theme('colors.teal.700'),
          },
          '&.active': {
            '@apply lf-bg-gray-100 lf-text-teal-600': {},
          },
        },
        '._lf-card': {
          width: theme('width.32'),
          height: theme('height.32'),
          borderWidth: '1px',
          borderColor: theme('colors.gray.100'),
          borderRadius: theme('borderRadius.lg'),
          backgroundColor: theme('colors.white'),
          boxShadow: theme('boxShadow.lf'),
          position: 'relative',
          display: 'flex',
          justifyContent: 'center',
          '&.tiny': {
            width: theme('width.24'),
            height: theme('height.24'),
          },
          '&.small': {
            width: theme('width.32'),
            height: theme('height.32'),
          },
          '&.medium': {
            width: theme('width.48'),
            height: theme('height.48'),
          },
          '&.large': {
            width: theme('width.96'),
            height: theme('height.96'),
          },
          '&.x-large': {
            width: theme('width.110'),
            height: theme('height.110'),
          },
          '&.fluid': {
            width: theme('width.full'),
            height: theme('height.full'),
          },
          '&.card-caption': {
            overflow: 'hidden',
          },
          '&:hover': {
            borderWidth: '1px',
            borderColor: theme('colors.teal.300'),
            '.card-caption.hover': {
              position: 'absolute',
              bottom: 0,
              display: 'inherit',
              background: 'rgba(78, 78, 78, 0.75)',
              '-webkit-animation': 'slide-up-fade-in 0.3s ease-out',
              '-mos-animation': 'slide-up-fade-in 0.3s ease-out',
            },
          },
        },
        '.lf-input': {
          '&.icon': {
            borderWidth: '2px',
            '&:focus': {
              borderColor: `${theme('colors.teal.200')} !important`,
              boxShadow: 'none',
            },
          },
        },
        '.card-caption': {
          color: theme('colors.gray.700'),
          background: 'rgba(78, 78, 78, 0.75)',
          padding: '5px 10px',
          width: '100%',
          '&:hover': {
            display: 'none',
            background: 'rgba(78, 78, 78, 0.75)',
            padding: '5px 10px',
            width: '100%',
          },
        },
        '#pagination': {
          paddingBottom: '20px !important',
          li: {
            width: ' 32px !important',
            height: '26px !important',
            padding: '0px !important',
            borderRadius: '4px !important',
            a: {
              '&:focus': {
                color: theme('colors.white'),
              },
            },
          },
          '.bg-lf-teal': {
            backgroundColor: theme('colors.teal.300'),
          },
        },
      });
    }),
  ],
};
