const { defineConfig } = require('cypress');

module.exports = defineConfig({
  projectId: 'volulecta-cms-builder',
  e2e: {
    baseUrl: 'http://localhost:8080',
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'cypress/support/e2e.js',
    setupNodeEvents: null,
    defaultCommandTimeout: 10000,
    requestTimeout: 10000,
    responseTimeout: 10000,
    pageLoadTimeout: 30000,
    chromeWebSecurity: false,
    viewportWidth: 1280,
    viewportHeight: 720
  },
  component: {
    devServer: {
      framework: 'next',
      bundler: 'webpack'
    }
  }
});
