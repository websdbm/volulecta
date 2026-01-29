// cypress/support/e2e.js
// Support file for Cypress E2E tests

// Aggiungi comandi personalizzati
Cypress.Commands.add('login', (email, password) => {
    cy.visit('/admin/login');
    cy.get('input[type="email"]').type(email);
    cy.get('input[type="password"]').type(password);
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/admin');
});

Cypress.Commands.add('goToBuilder', (pageId = 1) => {
    cy.visit(`/admin/cms/builder/${pageId}`);
    cy.wait(500);
});

Cypress.Commands.add('selectBlock', (index = 0) => {
    cy.get('.block-item').eq(index).click();
    cy.get('#settings-form').should('be.visible');
});

Cypress.Commands.add('addBlock', (type) => {
    cy.contains(type === 'title' ? 'Titolo' : type === 'text' ? 'Testo' : type === 'image' ? 'Immagine' : 'Hero Section').click();
});

Cypress.Commands.add('savePage', () => {
    cy.contains('Salva Pagina').click();
    cy.contains('Salvato', { timeout: 5000 }).should('be.visible');
});

Cypress.Commands.add('publishPage', () => {
    cy.contains('Pubblica Pagina').click();
    cy.on('window:confirm', () => true);
});

Cypress.Commands.add('setAsHomepage', () => {
    cy.get('#set-homepage').check();
    cy.on('window:confirm', () => true);
});

// Aumenta il timeout globale per operazioni lente
Cypress.config('defaultCommandTimeout', 10000);
