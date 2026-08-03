import js from '@eslint/js';
import prettier from 'eslint-config-prettier';
import jsxA11y from 'eslint-plugin-jsx-a11y';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    {
        ignores: [
            'public/**',
            'bootstrap/ssr/**',
            'vendor/**',
            'node_modules/**',
            'storage/**',
            'resources/js/types/generated.d.ts',
        ],
    },

    js.configs.recommended,
    ...tseslint.configs.recommended,

    {
        files: ['**/*.{js,jsx,ts,tsx}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
                ...globals.es2025,
                route: 'readonly',
            },
            parserOptions: {
                ecmaFeatures: { jsx: true },
            },
        },
        plugins: {
            react,
            'react-hooks': reactHooks,
            'jsx-a11y': jsxA11y,
        },
        settings: {
            react: { version: 'detect' },
        },
        rules: {
            ...react.configs.flat.recommended.rules,
            ...react.configs.flat['jsx-runtime'].rules,
            ...reactHooks.configs.recommended.rules,
            ...jsxA11y.flatConfigs.recommended.rules,

            /* Accessibility is a hard requirement (PROJECT_PLAN §29, WCAG 2.1
               AA), so a11y problems fail the build rather than warn. */
            'jsx-a11y/alt-text': 'error',
            'jsx-a11y/anchor-has-content': 'error',
            'jsx-a11y/aria-props': 'error',
            'jsx-a11y/label-has-associated-control': 'error',
            'jsx-a11y/no-autofocus': ['error', { ignoreNonDOM: true }],

            /* XSS: §24.1 bans dangerouslySetInnerHTML outside one audited,
               sanitising component. */
            'react/no-danger': 'error',

            '@typescript-eslint/no-unused-vars': [
                'error',
                { argsIgnorePattern: '^_', varsIgnorePattern: '^_' },
            ],
            '@typescript-eslint/consistent-type-imports': [
                'error',
                { prefer: 'type-imports', fixStyle: 'inline-type-imports' },
            ],
            'no-console': ['error', { allow: ['warn', 'error'] }],
            eqeqeq: ['error', 'always', { null: 'ignore' }],
        },
    },

    {
        files: ['**/*.config.js', '**/*.config.ts'],
        languageOptions: {
            globals: { ...globals.node },
        },
    },

    /* Must stay last: turns off every rule that fights Prettier. */
    prettier,
);
