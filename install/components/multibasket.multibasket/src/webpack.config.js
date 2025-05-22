const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

module.exports = (env, option) => ({
    mode: 'development',
    entry: './index.js',
    output: {
        filename: '../multibasket.js',
        path: __dirname,
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    externals: {
      BX: 'BX'
    },
    devtool: option.mode === 'development' ? 'source-map' : false,
    plugins: [],
    module: {
        rules: [
          {
            test: /\.m?js$/,
            exclude: /(node_modules|bower_components)/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env']
              }
            }
          },
          {
            test: /\.m?js$/,
            exclude: /node_modules/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env']
              }
            }
          },
          {
            test: /\.jsx$/,
            exclude: /node_modules/,
            use: {
              loader: "babel-loader",
              options: {
                presets: ['@babel/preset-react']
              }
            }
          },
          {
            test: /\.css$/i,
            use: [MiniCssExtractPlugin.loader, 'css-loader'],
          },
        ]
      },
      optimization: {
        minimizer: [new CssMinimizerPlugin(), '...'],
      },
})