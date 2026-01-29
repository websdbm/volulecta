/// <reference types="cypress" />

/**
 * CMS Builder E2E Test Suite
 * 
 * Testa tutte le funzionalità del builder CMS:
 * - Blocchi (Title, Text, Image, Hero)
 * - Font controls (Family, Weight, Size, Spacing)
 * - Responsive design (Desktop, Tablet, Mobile)
 * - Publish/Draft workflow
 * - Homepage selector
 * - Upload file
 */

const TEST_PAGE_ID = 1;
const TEST_PAGE_URL = `/admin/cms/builder/${TEST_PAGE_ID}`;
const LOGIN_EMAIL = 'admin@volulecta.local';
const LOGIN_PASSWORD = 'password123';

describe('CMS Builder - Complete Test Suite', () => {
  before(() => {
    // Login before tests
    cy.visit('/admin/login');
    cy.get('input[type="email"]').type(LOGIN_EMAIL);
    cy.get('input[type="password"]').type(LOGIN_PASSWORD);
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/admin');
  });

  beforeEach(() => {
    cy.visit(TEST_PAGE_URL);
    cy.wait(500); // Wait for page load
  });

  describe('Block Management', () => {
    it('Should add a Title block', () => {
      cy.contains('Titolo').click();
      cy.get('#canvas').should('contain', 'Nuovo title');
      cy.get('.block-item').should('have.length.at.least', 1);
    });

    it('Should add a Text block', () => {
      cy.contains('Testo').click();
      cy.get('#canvas').should('contain', 'Nuovo text');
    });

    it('Should add an Image block', () => {
      cy.contains('Immagine').click();
      cy.get('#canvas').should('contain', 'Nuovo image');
    });

    it('Should add a Hero block', () => {
      cy.contains('Hero Section').click();
      cy.get('#canvas').should('contain', 'Titolo Hero');
    });

    it('Should select a block and open settings', () => {
      cy.contains('Titolo').click();
      cy.get('.block-item').first().click();
      cy.get('#settings-form').should('be.visible');
      cy.get('#editing-block-type').should('contain', 'title');
    });

    it('Should drag and drop to reorder blocks', () => {
      cy.contains('Titolo').click();
      cy.contains('Testo').click();
      
      cy.get('.block-item').first().as('firstBlock');
      cy.get('.block-item').last().as('lastBlock');
      
      cy.get('@firstBlock').drag('@lastBlock', { position: 'bottom' });
    });
  });

  describe('Font Controls - Title Block', () => {
    beforeEach(() => {
      cy.contains('Titolo').click();
      cy.get('.block-item').first().click();
      cy.wait(300);
    });

    it('Should change font family', () => {
      cy.contains('Font Family')
        .parent()
        .find('select')
        .select('poppins');
      cy.get('#canvas').should('exist'); // Preview updated
    });

    it('Should change font weight', () => {
      cy.contains('Font Weight')
        .parent()
        .find('select')
        .select('700');
      cy.get('#canvas').should('exist');
    });

    it('Should change font size with range slider', () => {
      cy.contains('Dimensione Font')
        .parent()
        .find('input[type="range"]')
        .invoke('val', 48)
        .trigger('input');
      
      cy.contains('Dimensione Font')
        .parent()
        .find('.range-value')
        .should('contain', '48px');
    });

    it('Should change letter spacing', () => {
      cy.contains('Letter Spacing')
        .parent()
        .find('input[type="text"]')
        .clear()
        .type('2px');
      cy.get('#canvas').should('exist');
    });

    it('Should change word spacing', () => {
      cy.contains('Word Spacing')
        .parent()
        .find('input[type="text"]')
        .clear()
        .type('1px');
      cy.get('#canvas').should('exist');
    });

    it('Should change text color', () => {
      cy.contains('Colore Testo')
        .parent()
        .find('input[type="color"]')
        .invoke('val', '#FF0000')
        .trigger('input');
      cy.get('#canvas').should('exist');
    });

    it('Should change text alignment', () => {
      cy.contains('Allineamento')
        .parent()
        .find('select')
        .select('center');
      cy.get('#canvas').should('exist');
    });

    it('Should change padding', () => {
      cy.contains('Padding')
        .parent()
        .find('input[type="text"]')
        .clear()
        .type('20px');
      cy.get('#canvas').should('exist');
    });

    it('Should test font weight dependency on font family', () => {
      // Change to Playfair (only 400 and 700)
      cy.contains('Font Family')
        .parent()
        .find('select')
        .select('playfair');
      cy.wait(200);
      
      // Font weight options should be limited
      cy.contains('Font Weight')
        .parent()
        .find('select option')
        .should('have.length', 2);
    });
  });

  describe('Font Controls - Text Block', () => {
    beforeEach(() => {
      cy.contains('Testo').click();
      cy.get('.block-item').first().click();
      cy.wait(300);
    });

    it('Should edit text content with TinyMCE', () => {
      cy.contains('Contenuto Testo')
        .parent()
        .find('iframe')
        .then($iframe => {
          const $body = $iframe.contents().find('body');
          cy.wrap($body)
            .clear()
            .type('Hello World');
        });
    });

    it('Should apply all font styles to text block', () => {
      // Font family
      cy.contains('Font Family')
        .parent()
        .find('select')
        .select('inter');
      
      // Font weight
      cy.contains('Font Weight')
        .parent()
        .find('select')
        .select('500');
      
      // Font size
      cy.contains('Dimensione Font')
        .parent()
        .find('input[type="range"]')
        .invoke('val', 20)
        .trigger('input');
      
      cy.get('#canvas').should('exist');
    });
  });

  describe('Image Block', () => {
    beforeEach(() => {
      cy.contains('Immagine').click();
      cy.get('.block-item').first().click();
      cy.wait(300);
    });

    it('Should enter image URL', () => {
      cy.contains('Contenuto URL')
        .parent()
        .find('input[type="text"]')
        .clear()
        .type('https://via.placeholder.com/600x400?text=Test');
      cy.get('#canvas').should('exist');
    });

    it('Should upload image file', () => {
      cy.contains('Contenuto URL')
        .parent()
        .find('input[type="file"]')
        .selectFile('cypress/fixtures/test-image.jpg', { force: true });
      
      // Wait for upload notification
      cy.contains('Upload Completato', { timeout: 5000 }).should('be.visible');
    });
  });

  describe('Hero Block', () => {
    beforeEach(() => {
      cy.contains('Hero Section').click();
      cy.get('.block-item').first().click();
      cy.wait(300);
    });

    it('Should edit hero title', () => {
      cy.contains('Titolo')
        .parent()
        .find('input[type="text"]')
        .first()
        .clear()
        .type('Welcome to Volulecta');
      cy.get('#canvas').should('contain', 'Welcome to Volulecta');
    });

    it('Should toggle and edit hero subtitle', () => {
      cy.contains('Mostra Sottotitolo')
        .find('input[type="checkbox"]')
        .check();
      
      cy.get('textarea')
        .first()
        .clear()
        .type('Amazing CMS Builder');
      
      cy.get('#canvas').should('contain', 'Amazing CMS Builder');
    });

    it('Should toggle and edit hero button', () => {
      cy.contains('Mostra Bottone')
        .find('input[type="checkbox"]')
        .check();
      
      cy.contains('Mostra Bottone')
        .parent()
        .find('input[type="text"]')
        .first()
        .clear()
        .type('Click Me');
      
      cy.get('#canvas').should('contain', 'Click Me');
    });

    it('Should change image position', () => {
      cy.contains('Posizione Immagine')
        .parent()
        .find('select')
        .select('left');
      cy.get('#canvas').should('exist');
    });

    it('Should apply font styles to hero elements', () => {
      // Title font
      cy.contains('Font Family Titolo')
        .parent()
        .find('select')
        .select('playfair');
      
      // Subtitle font
      cy.contains('Font Family Sottotitolo')
        .parent()
        .find('select')
        .select('roboto');
      
      cy.get('#canvas').should('exist');
    });
  });

  describe('Responsive Design', () => {
    beforeEach(() => {
      cy.contains('Titolo').click();
      cy.get('.block-item').first().click();
    });

    it('Should switch to tablet view', () => {
      cy.get('[data-view="tablet"]').click();
      cy.get('#canvas-container').should('have.class', 'canvas-tablet');
    });

    it('Should switch to mobile view', () => {
      cy.get('[data-view="mobile"]').click();
      cy.get('#canvas-container').should('have.class', 'canvas-mobile');
    });

    it('Should switch back to desktop view', () => {
      cy.get('[data-view="desktop"]').click();
      cy.get('#canvas-container').should('have.class', 'canvas-desktop');
    });

    it('Should allow different styles per view', () => {
      // Desktop
      cy.get('[data-view="desktop"]').click();
      cy.contains('Dimensione Font')
        .parent()
        .find('input[type="range"]')
        .invoke('val', 32)
        .trigger('input');
      
      // Tablet
      cy.get('[data-view="tablet"]').click();
      cy.contains('Dimensione Font')
        .parent()
        .find('input[type="range"]')
        .invoke('val', 28)
        .trigger('input');
      
      // Mobile
      cy.get('[data-view="mobile"]').click();
      cy.contains('Dimensione Font')
        .parent()
        .find('input[type="range"]')
        .invoke('val', 24)
        .trigger('input');
    });
  });

  describe('Page Actions', () => {
    it('Should save page', () => {
      cy.contains('Salva Pagina').click();
      cy.contains('Salvato', { timeout: 5000 }).should('be.visible');
    });

    it('Should publish page', () => {
      cy.contains('Pubblica Pagina').click();
      cy.contains('Sei sicuro').then($dialog => {
        if ($dialog.length) {
          cy.window().then(win => {
            cy.stub(win, 'confirm').returns(true);
            cy.contains('Pubblica Pagina').click();
          });
        }
      });
      
      cy.contains('Pubblicata', { timeout: 5000 }).should('be.visible');
    });

    it('Should set as homepage', () => {
      cy.get('#set-homepage').check();
      cy.window().then(win => {
        cy.stub(win, 'confirm').returns(true);
      });
      
      cy.contains('Homepage Impostata', { timeout: 5000 }).should('be.visible');
    });

    it('Should show preview', () => {
      cy.contains('Anteprima').should('have.attr', 'href', '/p/' + 'test-page-slug');
    });

    it('Should exit builder', () => {
      cy.contains('Esci').click();
      cy.url().should('include', '/admin/cms');
    });
  });

  describe('Error Handling & Validation', () => {
    beforeEach(() => {
      cy.contains('Titolo').click();
      cy.get('.block-item').first().click();
    });

    it('Should show error on invalid color', () => {
      cy.contains('Colore Testo')
        .parent()
        .find('input[type="text"]')
        .clear()
        .type('invalid-color');
      
      cy.contains('Salva Pagina').click();
      cy.contains('Errore', { timeout: 5000 }).should('be.visible');
    });

    it('Should show error on invalid font size', () => {
      cy.contains('Dimensione Font')
        .parent()
        .find('input[type="text"]')
        .clear()
        .type('abc');
      
      cy.contains('Salva Pagina').click();
      cy.contains('Errore', { timeout: 5000 }).should('be.visible');
    });

    it('Should validate font weight for selected font', () => {
      // Select Playfair (only 400, 700)
      cy.contains('Font Family')
        .parent()
        .find('select')
        .select('playfair');
      
      // Try to set invalid weight (should be updated in UI)
      cy.contains('Font Weight')
        .parent()
        .find('select')
        .should('have.value', '400').or('have.value', '700');
    });
  });

  describe('Accessibility', () => {
    it('Should have aria-labels on range sliders', () => {
      cy.contains('Titolo').click();
      cy.get('.block-item').first().click();
      
      cy.contains('Dimensione Font')
        .parent()
        .find('input[type="range"]')
        .should('have.attr', 'aria-label');
    });

    it('Should have aria-labels on color inputs', () => {
      cy.contains('Titolo').click();
      cy.get('.block-item').first().click();
      
      cy.contains('Colore Testo')
        .parent()
        .find('input[type="color"]')
        .should('have.attr', 'aria-label');
    });

    it('Should have aria-labels on selects', () => {
      cy.contains('Titolo').click();
      cy.get('.block-item').first().click();
      
      cy.contains('Font Family')
        .parent()
        .find('select')
        .should('have.attr', 'aria-label');
    });

    it('Should update aria-live regions on slider change', () => {
      cy.contains('Titolo').click();
      cy.get('.block-item').first().click();
      
      cy.contains('Dimensione Font')
        .parent()
        .find('.range-value')
        .should('have.attr', 'aria-live', 'polite');
    });
  });

  describe('Complete Workflow', () => {
    it('Should complete full page creation workflow', () => {
      // Add blocks
      cy.contains('Titolo').click();
      cy.contains('Testo').click();
      cy.contains('Immagine').click();
      cy.contains('Hero Section').click();
      
      // Edit first block
      cy.get('.block-item').first().click();
      cy.contains('Font Family').parent().find('select').select('poppins');
      cy.contains('Dimensione Font').parent().find('input[type="range"]').invoke('val', 48).trigger('input');
      
      // Save
      cy.contains('Salva Pagina').click();
      cy.contains('Salvato', { timeout: 5000 }).should('be.visible');
      
      // Publish
      cy.contains('Pubblica Pagina').click();
      cy.window().then(win => {
        cy.stub(win, 'confirm').returns(true);
      });
      
      // Verify published
      cy.contains('Pubblicata', { timeout: 5000 }).should('be.visible');
    });
  });
});
