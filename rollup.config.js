import ractive from 'rollup-plugin-ractive';

export default {
	plugins: [ ractive() ],
	external: [ 'ractive' ],
	globals: { ractive: 'Ractive' },
	format: 'iife',
	moduleName: 'RactiveApp'
}
