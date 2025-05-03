import neostandard from 'neostandard';

export default [
    ...neostandard({
        env: ['browser'], // Specify the environment
        globals: ['$', 'jQuery'],  // Treat $ and jQuery as global variables
        semi: true, // Enforce semicolons
    }),
    {
        rules: {
            '@stylistic/indent': ['error', 4], // Enforce 4 spaces for indentation
        },
    },
];
