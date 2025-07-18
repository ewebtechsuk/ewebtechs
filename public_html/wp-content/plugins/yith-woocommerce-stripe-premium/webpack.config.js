const path = require( 'path' ),
	externals = {
		'@wordpress/element': [ 'wp', 'element' ],
	};

module.exports = {
	devtool: 'source-map',
	entry: {
		checkout: './assets/js/stripe-checkout.js',
		elements: './assets/js/stripe-elements.js',
		block: './assets/js/stripe-block.js',
	},
	externals,
	mode: 'production',
	module: {
		rules: [
			{
				test: /\.js/,
				exclude: /(node_modules|bower_components)/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [ '@babel/preset-env', '@babel/react' ],
						plugins: [ [ '@babel/transform-runtime' ] ],
					},
				},
			},
		],
	},
	optimization: {
		minimize: false,
	},
	output: {
		filename: ( pathData, assetInfo ) => {
			let name = pathData.chunk.name,
				components = name.split( '/' );

			components[ components.length - 1 ] =
				'stripe-' +
				components[ components.length - 1 ] +
				'.bundle.js';

			return components.join( '/' );
		},
		path: path.resolve( __dirname, 'assets/js' ),
		libraryTarget: 'window',
	},
	resolve: {
		extensions: [ '*', '.js' ],
	},
};
