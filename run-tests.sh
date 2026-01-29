#!/bin/bash

# 🧪 CMS Builder - Quick Test Script
# Esegui i test in modo rapido senza configurazione

set -e

echo "🧪 CMS Builder - Test Suite"
echo "============================"
echo ""

# Colori
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

show_menu() {
    echo -e "${BLUE}Seleziona il tipo di test:${NC}"
    echo ""
    echo "1) ${GREEN}Test Rapido${NC} - runBuilderTests() in console (30 sec)"
    echo "2) ${GREEN}Test Cypress GUI${NC} - npx cypress open"
    echo "3) ${GREEN}Test Cypress Headless${NC} - npx cypress run (45 sec)"
    echo "4) ${GREEN}Test Backend${NC} - php tests/BlockValidatorTest.php (1 sec)"
    echo "5) ${GREEN}Tutti i Test${NC} - Esegui tutti i test"
    echo "6) ${YELLOW}Info${NC} - Mostra info sui test"
    echo "0) ${RED}Esci${NC}"
    echo ""
    read -p "Scegli (0-6): " choice
}

run_quick_test() {
    echo -e "${BLUE}📝 Test Rapido${NC}"
    echo "Open the builder page in your browser"
    echo "Press F12 and paste this in the console:"
    echo ""
    echo -e "${YELLOW}runBuilderTests()${NC}"
    echo ""
    echo "Or for quick test:"
    echo -e "${YELLOW}quickTest()${NC}"
    echo ""
}

run_cypress_gui() {
    echo -e "${BLUE}🎬 Cypress GUI${NC}"
    echo "Installing dependencies..."
    if [ ! -d "node_modules" ]; then
        npm install
    fi
    echo "Opening Cypress..."
    npx cypress open
}

run_cypress_headless() {
    echo -e "${BLUE}🎬 Cypress Headless${NC}"
    echo "Installing dependencies..."
    if [ ! -d "node_modules" ]; then
        npm install
    fi
    echo "Running E2E tests..."
    npx cypress run --spec "cypress/e2e/cms-builder.cy.js"
}

run_backend_test() {
    echo -e "${BLUE}🔧 Backend Test${NC}"
    echo "Running BlockValidator tests..."
    if command -v php &> /dev/null; then
        php tests/BlockValidatorTest.php
    else
        echo -e "${RED}❌ PHP non trovato${NC}"
        exit 1
    fi
}

run_all_tests() {
    echo -e "${BLUE}🧪 Tutti i Test${NC}"
    echo ""
    
    # 1. Backend Test
    echo "1/3 Backend Test..."
    if command -v php &> /dev/null; then
        php tests/BlockValidatorTest.php
        echo ""
    fi
    
    # 2. Cypress Headless
    echo "2/3 Cypress E2E Test..."
    if [ ! -d "node_modules" ]; then
        npm install
    fi
    npx cypress run --spec "cypress/e2e/cms-builder.cy.js"
    echo ""
    
    # 3. Info
    echo "3/3 Test Console (Manual)..."
    echo -e "${YELLOW}Per eseguire il test console, apri il builder e digita: runBuilderTests()${NC}"
    echo ""
}

show_info() {
    echo -e "${BLUE}ℹ️  Informazioni sui Test${NC}"
    echo ""
    echo "📊 TEST SUITE:"
    echo ""
    echo "1️⃣  JavaScript Console Test"
    echo "   - 98 assertions, 12 suite"
    echo "   - Esecuzione: ~2.1 secondi"
    echo "   - Comando: runBuilderTests()"
    echo "   - Localizzazione: public/js/builder-tests.js"
    echo ""
    echo "2️⃣  Cypress E2E Test"
    echo "   - 41 test cases, 100+ assertions"
    echo "   - Esecuzione: ~45 secondi"
    echo "   - File: cypress/e2e/cms-builder.cy.js"
    echo ""
    echo "3️⃣  PHP Backend Test"
    echo "   - 20+ assertions, 6 suite"
    echo "   - Esecuzione: ~1 secondo"
    echo "   - File: tests/BlockValidatorTest.php"
    echo "   - Comando: php tests/BlockValidatorTest.php"
    echo ""
    echo "📚 DOCUMENTAZIONE:"
    echo "   - TEST_SUITE_GUIDE.md (guide complete)"
    echo "   - FINAL_STATUS.md (status finale)"
    echo "   - IMPLEMENTATION_COMPLETE.md (riepilogo)"
    echo ""
    echo "🔗 QUICK LINKS:"
    echo "   - Builder: http://localhost:8080/admin/cms/builder/1"
    echo "   - Login: admin@volulecta.local / password123"
    echo ""
}

# Main loop
while true; do
    show_menu
    case $choice in
        1)
            run_quick_test
            ;;
        2)
            run_cypress_gui
            ;;
        3)
            run_cypress_headless
            ;;
        4)
            run_backend_test
            ;;
        5)
            run_all_tests
            ;;
        6)
            show_info
            ;;
        0)
            echo -e "${GREEN}Arrivederci! 👋${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}Scelta non valida${NC}"
            ;;
    esac
    
    echo ""
    read -p "Premi Enter per continuare..."
    clear
done
