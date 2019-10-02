// 1
// npm install
//
// 2
// webpack -w ./index.js --devtool source-map

module.exports = {
	entry: './index.js',
	output: {
		filename: 'main.js'
	},

	module: {
		rules: [
			{
				test: /\.css$/i,
				use: ['style-loader', 'css-loader'],
			},
			{
				test: /\.(gif|svg|jpg|png)$/,
				loader: "file-loader",
			}
		],
	}
};