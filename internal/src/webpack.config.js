
const glob = require('glob')
const path = require('path')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
// const ManifestPlugin = require('webpack-manifest-plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
// const WatchIgnorePlugin = require('webpack/lib/WatchIgnorePlugin')

// Webpack Entry Point Registration Overview
// Create object with:
// Key = output name, Value = source sass file
// for every scss file in the cssPath directory
// EX: { 'css/<filename>.css' : './src/css/filename.scss', ...}
const entry = {}
const jsPath = path.join(__dirname)
glob.sync(path.join(jsPath, '*.js')).forEach(file => {
	// locates all `./*.js` files
	if(file.endsWith('webpack.config.js')) return
	entry[path.basename(file, '.js')] = file
})

module.exports =
	// built client files
	(env, argv) => {
		const is_production = argv.mode === 'production'
		const filename = is_production ? '[name]-[contenthash].min' : '[name]'
		return {
			entry,
			stats: { children: false, modules: false },
			optimization: { minimize: true },
			performance: { hints: false },
			mode: is_production ? 'production' : 'development',
			target: 'web',
			devServer: {
				https: true,
				host: '127.0.0.1',
				publicPath: '/assets/dist',
				watchOptions: {
					ignored: [
						'/node_modules/',
						'/internal/admin/',
						'/internal/classes/',
						'/internal/config/',
						'/internal/vendor/',
						'/internal/logs',
						'/internal/includes',
						'/assets',

					]
				},
				stats: { children: false, modules: false },
				openPage: 'repository.html',
				open: true,
				proxy: {
					// proxy everything back into docker
					context: () => true,
					target: 'http://127.0.0.1',
					secure: false,
					headers: {
						'X-Use-Webpack': 'true'
					}
				}
			},
			output: {
				publicPath: '/',
				path: path.join(__dirname, '..', '..', 'assets', 'dist'),
				filename: `${filename}.js`
			},
			module: {
				rules: [
					{
						test: /\.svg/,
						use: {
							loader: 'svg-url-loader',
							options: {
								stripdeclarations: true,
								iesafe: true
							}
						}
					},
					{
						test: /\.(js|jsx)$/,
						exclude: /node_modules/,
						use: {
							loader: 'babel-loader',
							options: {
								presets: ['@babel/preset-react', '@babel/preset-env']
							}
						}
					},
					{
						test: /\.s?css$/,
						use: [
							MiniCssExtractPlugin.loader,
							'css-loader',
							{
								loader: 'postcss-loader',
								options: {
									postcssOptions: {
										plugins: [
											[
												'autoprefixer', {/* Options */}
											],
										],
									}
								}
							},
							{
								loader: 'sass-loader',
								options: {
									// expose SASS variable for build environment
									prependData: `$is_production: '${is_production}';`
								}
							}
						]
					}
				]
			},
			externals: {
				react: 'React',
				'react-dom': 'ReactDOM',
			},
			plugins: [
				// new WatchIgnorePlugin([
				// 	path.join(__dirname, 'server', 'public', 'compiled', 'manifest.json')
				// ]),
				new CleanWebpackPlugin(), // clear the dist folder before build
				new MiniCssExtractPlugin({ filename: `${filename}.css` }),
				// new ManifestPlugin({ publicPath: '/static/' })
			],
			resolve: {
				extensions: ['.js', '.jsx']
			}
		}
	}