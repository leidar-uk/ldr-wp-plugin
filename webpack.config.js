const path = require('path');
const fs = require('fs');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

function getBlockEntries() {
  const blocksDir = path.resolve(__dirname, 'src/blocks');
  const entries = {};

  if (!fs.existsSync(blocksDir)) {
    return entries;
  }

  fs.readdirSync(blocksDir, { withFileTypes: true })
    .filter((d) => d.isDirectory())
    .forEach((dirent) => {
      const name = dirent.name;
      const jsPath = path.join(blocksDir, name, 'script.js');
      const scssPath = path.join(blocksDir, name, 'style.scss');

      const e = [];
      if (fs.existsSync(jsPath)) e.push(jsPath);
      if (fs.existsSync(scssPath)) e.push(scssPath);

      if (e.length) {
        entries[name] = e;
      }
    });

  return entries;
}

const sharedRules = [
  {
    test: /\.js$/,
    exclude: /(node_modules)/,
    use: {
      loader: 'babel-loader',
      options: {
        presets: [ '@babel/preset-env' ]
      }
    }
  },
  {
    test: /\.s?css$/,
    use: [
      MiniCssExtractPlugin.loader,
      { loader: 'css-loader', options: { importLoaders: 1 } },
      {
        loader: 'postcss-loader',
        options: {
          postcssOptions: {
            plugins: [ require('autoprefixer')() ]
          }
        }
      },
      {
        loader: 'sass-loader',
        options: {
          sassOptions: {
            loadPaths: [
              path.resolve(__dirname, 'src/scss'),
              path.resolve(__dirname, 'node_modules')
            ]
          }
        }
      }
    ]
  },
  {
    test: /\.(png|jpe?g|gif|svg|woff2?|ttf|eot)$/i,
    type: 'asset/resource',
    generator: {
      filename: 'assets/img/[name][ext]'
    }
  }
];

const externalLibs = {
  jquery: 'jQuery'
};

const themeConfig = {
  entry: {
    main: './src/js/main.js',
  },
  output: {
    path: path.resolve(__dirname),
    filename: 'assets/js/[name].min.js'
  },
  module: {
    rules: sharedRules
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'assets/css/[name].min.css'
    })
  ],
  externals: externalLibs
};

const blocksConfig = {
  entry: getBlockEntries(),
  output: {
    path: path.resolve(__dirname),
    filename: 'blocks/[name]/script.min.js',
    clean: false
  },
  module: {
    rules: sharedRules
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'blocks/[name]/style.min.css'
    })
  ],
  externals: externalLibs
};

module.exports = [themeConfig, blocksConfig];