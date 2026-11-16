import js from '@eslint/js';
import prettier from 'eslint-config-prettier';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    { ignores: ['vendor/', 'node_modules/', 'public/', 'bootstrap/ssr/'] },
    js.configs.recommended,
    ...tseslint.configs.recommended,
    prettier,
);
