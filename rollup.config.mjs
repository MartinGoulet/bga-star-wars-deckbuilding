import typescript from '@rollup/plugin-typescript';

export default {
  input: 'src/game.ts',
  output: {
    file: 'modules/js/Game.js',
    format: 'es',
    sourcemap: false,
    inlineDynamicImports: true,
  },
  plugins: [
    typescript({
      tsconfig: './tsconfig.json',
    }),
  ],
  treeshake: true,
};
