import ractive from 'rollup-plugin-ractive';

export default {
	entry: 'components/index.html',
	dest: 'static/apps/index.js',
	plugins: [ ractive() ],
	external: [ 'ractive' ],
	globals: { ractive: 'Ractive' },
	format: 'iife',
	moduleName: 'RactiveApp'
}
