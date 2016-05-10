import ractive from 'rollup-plugin-ractive';

export default {
	entry: 'static/components/index.html',
	dest: 'static/bundle.js',
	plugins: [ ractive() ],
	external: [ 'ractive' ],
	globals: { ractive: 'Ractive' },
	format: 'iife',
	moduleName: 'Index'
};
