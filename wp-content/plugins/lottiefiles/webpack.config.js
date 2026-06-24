/**
 * Copyright 2022 Design Barn Inc.
 */

const path = require('path');

const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const Dotenv = require('dotenv-webpack');

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
  mode: isProduction ? 'production' : 'development',
  ...defaultConfig,
  plugins: [
    ...defaultConfig.plugins,
    new Dotenv(),
  ],
  resolve: {
    ...defaultConfig.resolve,
    alias: {
      ...defaultConfig.resolve.alias,
      'react': path.resolve(__dirname, 'node_modules/react'),
      'react-dom': path.resolve(__dirname, 'node_modules/react-dom'),
      'react/jsx-runtime': path.resolve(__dirname, 'node_modules/react/jsx-runtime'),
      '@babel/runtime-corejs3': path.resolve(__dirname, 'node_modules/@babel/runtime-corejs3'),
      'core-js': path.resolve(__dirname, 'node_modules/core-js'),
      '@context': path.resolve(__dirname, 'src/context'),
      '@host': path.resolve(__dirname, 'src/host'),
      '@api': path.resolve(__dirname, 'src/api'),
      '@helpers': path.resolve(__dirname, 'src/helpers'),
    },
    fallback: {
      https: false,
      http: false,
      url: false,
    },
    extensions: ['.tsx', '.ts', '.jsx', '.js'],
  },
  externals: {
    ...defaultConfig.externals,
    react: 'React',
    'react-dom': 'ReactDOM',
    '@wordpress/element': 'wp.element',
  },
  module: {
    ...defaultConfig.module,
    rules: [
      ...defaultConfig.module.rules,
      {
        test: /\.(ts|tsx|js|jsx)$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              cacheDirectory: true,
              presets: [
                ['@babel/preset-env', {
                  targets: {
                    browsers: [
                      'last 2 versions',
                      'safari >= 7',
                      'ie >= 11'
                    ]
                  },
                  useBuiltIns: 'usage',
                  corejs: 3,
                  modules: false
                }],
                '@babel/preset-typescript',
                ['@babel/preset-react', {
                  runtime: 'classic'
                }]
              ],
              plugins: [
                ['@babel/plugin-transform-runtime', {
                  corejs: 3,
                  helpers: true,
                  regenerator: true
                }],
                '@babel/plugin-transform-async-to-generator',
                '@babel/plugin-syntax-top-level-await'
              ]
            }
          }
        ]
      },
      {
        test: /\.css$/,
        // use: [
        //   MiniCssExtractPlugin.loader,
        //   {
        //     loader: 'css-loader',
        //     options: {
        //       importLoaders: 1,
        //       modules: {
        //         auto: true,
        //         localIdentName: '[name]__[local]--[hash:base64:5]'
        //       }
        //     }
        //   },
        //   {
        //     loader: 'postcss-loader',
        //     options: {
        //       postcssOptions: {
        //         plugins: [
        //           'postcss-preset-env',
        //           'autoprefixer',
        //           'tailwindcss'
        //         ]
        //       }
        //     }
        //   }
        // ]
      }
    ]
  },
  entry: {
    ...defaultConfig.entry,
    'lottiefiles-admin-settings-page': path.join(__dirname, 'src/admin/settings', 'index.tsx'),
    'lottiefiles-admin-settings-page-style': path.join(__dirname, 'src/admin/settings', 'style.ts'),
    'lottiefiles-interactivity': path.join(__dirname, 'src/scripts', 'lottie-interactivity.min.js'),
    'lottiefiles-player': path.join(__dirname, 'src/scripts', 'lottie-player.js'),
  },
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: '[name].js',
    clean: true
  }
};
