import js from '@eslint/js';
import prettier from 'eslint-config-prettier';
export default [
 { ignores: ['vendor/**','node_modules/**','public/**'] },
 js.configs.recommended,
 { languageOptions: { globals: { document: 'readonly', window: 'readonly', localStorage: 'readonly', location: 'readonly', URLSearchParams: 'readonly', structuredClone: 'readonly', URL: 'readonly', fetch: 'readonly', console: 'readonly' } } },
 prettier,
];
