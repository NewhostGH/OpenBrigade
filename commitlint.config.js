// Conventional Commits config for OpenBrigade.
// Docs: https://commitlint.js.org
export default {
    extends: ['@commitlint/config-conventional'],

    rules: {
        // Allowed commit types
        'type-enum': [
            2,
            'always',
            [
                'feat',     // new feature
                'fix',      // bug fix
                'docs',     // documentation only
                'style',    // formatting, no logic change
                'refactor', // code change that neither fixes a bug nor adds a feature
                'perf',     // performance improvement
                'test',     // adding or fixing tests
                'build',    // build system / external dependencies
                'ci',       // CI/CD configuration
                'chore',    // other changes that don't modify src or test files
                'revert',   // reverts a previous commit
            ],
        ],

        // Subject line rules
        'subject-case': [0],           // no case enforcement — allows French text
        'subject-max-length': [2, 'always', 100],
        'subject-empty': [2, 'never'],

        // Body rules
        'body-max-line-length': [1, 'always', 100], // warn only
        'body-leading-blank':   [2, 'always'],       // blank line before body

        // Footer rules
        'footer-leading-blank': [2, 'always'],       // blank line before footer
    },
};
