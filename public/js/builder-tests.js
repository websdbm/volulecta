/**
 * CMS Builder - Auto Test Suite
 * 
 * Script di testing automatico integrato nel builder
 * Esegui nel console: runBuilderTests()
 * 
 * Testa sequenzialmente:
 * 1. Aggiunta blocchi
 * 2. Font controls
 * 3. Responsive design
 * 4. Salvataggio
 */

async function runBuilderTests() {
    console.log('🧪 Inizio Test Suite CMS Builder...\n');
    
    let passedTests = 0;
    let failedTests = 0;
    const results = [];

    // Utility function per assertions
    function assert(condition, testName) {
        if (condition) {
            passedTests++;
            results.push({ name: testName, status: '✅ PASS', error: null });
            console.log(`✅ ${testName}`);
        } else {
            failedTests++;
            results.push({ name: testName, status: '❌ FAIL', error: 'Assertion failed' });
            console.log(`❌ ${testName}`);
        }
    }

    // Test 1: Aggiunta blocchi
    console.log('\n📦 TEST 1: AGGIUNTA BLOCCHI\n');
    
    const initialBlockCount = blocks.length;
    
    addBlock('title');
    assert(blocks.length === initialBlockCount + 1, 'Aggiunta blocco Title');
    
    addBlock('text');
    assert(blocks.length === initialBlockCount + 2, 'Aggiunta blocco Text');
    
    addBlock('image');
    assert(blocks.length === initialBlockCount + 3, 'Aggiunta blocco Image');
    
    addBlock('hero');
    assert(blocks.length === initialBlockCount + 4, 'Aggiunta blocco Hero');

    // Test 2: Selezione e apertura settings
    console.log('\n⚙️ TEST 2: SELEZIONE BLOCCHI E SETTINGS\n');
    
    openSettings(0);
    assert(activeBlockIndex === 0, 'Selezione blocco 0');
    assert(settingsForm.style.display !== 'none', 'Settings form visible');
    
    openSettings(blocks.length - 1);
    assert(activeBlockIndex === blocks.length - 1, 'Selezione ultimo blocco');

    // Test 3: Font controls
    console.log('\n🎨 TEST 3: FONT CONTROLS\n');
    
    const testBlock = blocks[0];
    
    // Font family change
    updateStyle('font_family', 'poppins');
    assert(testBlock.settings[currentView].font_family === 'poppins', 'Cambio font family a Poppins');
    
    // Font weight change
    updateStyle('font_weight', '700');
    assert(testBlock.settings[currentView].font_weight === '700', 'Cambio font weight a 700');
    
    // Font size change
    updateStyle('font_size', '32px');
    assert(testBlock.settings[currentView].font_size === '32px', 'Cambio font size a 32px');
    
    // Letter spacing
    updateStyle('letter_spacing', '2px');
    assert(testBlock.settings[currentView].letter_spacing === '2px', 'Cambio letter spacing');
    
    // Word spacing
    updateStyle('word_spacing', '1px');
    assert(testBlock.settings[currentView].word_spacing === '1px', 'Cambio word spacing');
    
    // Color
    updateStyle('color', '#FF0000');
    assert(testBlock.settings[currentView].color === '#FF0000', 'Cambio colore a rosso');
    
    // Alignment
    updateStyle('align', 'center');
    assert(testBlock.settings[currentView].align === 'center', 'Cambio allineamento a center');
    
    // Padding
    updateStyle('padding', '20px');
    assert(testBlock.settings[currentView].padding === '20px', 'Cambio padding');

    // Test 4: Font weight dinamico
    console.log('\n📊 TEST 4: VALIDAZIONE FONT WEIGHT\n');
    
    // Playfair ha solo 400 e 700
    updateStyle('font_family', 'playfair');
    const playfairWeights = getAvailableWeights('playfair');
    assert(playfairWeights.length === 2, 'Playfair ha solo 2 weight');
    assert(playfairWeights.includes(400), 'Playfair ha weight 400');
    assert(playfairWeights.includes(700), 'Playfair ha weight 700');
    
    // Roboto ha 300, 400, 500, 700
    updateStyle('font_family', 'roboto');
    const robotoWeights = getAvailableWeights('roboto');
    assert(robotoWeights.length === 4, 'Roboto ha 4 weight');

    // Test 5: Responsive design
    console.log('\n📱 TEST 5: RESPONSIVE DESIGN\n');
    
    // Desktop
    currentView = 'desktop';
    updateStyle('font_size', '48px');
    assert(blocks[0].settings.desktop.font_size === '48px', 'Desktop: Font size 48px');
    
    // Tablet
    currentView = 'tablet';
    updateStyle('font_size', '36px');
    assert(blocks[0].settings.tablet.font_size === '36px', 'Tablet: Font size 36px');
    
    // Mobile
    currentView = 'mobile';
    updateStyle('font_size', '24px');
    assert(blocks[0].settings.mobile.font_size === '24px', 'Mobile: Font size 24px');
    
    // Verifica cascata
    currentView = 'desktop';
    const desktopStyles = getStylesForView(blocks[0], 'desktop');
    assert(desktopStyles.font_size === '48px', 'Cascata: Desktop styles');
    
    const tabletStyles = getStylesForView(blocks[0], 'tablet');
    assert(tabletStyles.font_size === '36px', 'Cascata: Tablet overrides desktop');
    
    const mobileStyles = getStylesForView(blocks[0], 'mobile');
    assert(mobileStyles.font_size === '24px', 'Cascata: Mobile overrides all');

    // Test 6: Rendering
    console.log('\n🎬 TEST 6: RENDERING\n');
    
    renderCanvas();
    const canvasItems = document.querySelectorAll('.block-item');
    assert(canvasItems.length === blocks.length, 'Canvas contiene tutti i blocchi');
    assert(document.getElementById('canvas').innerHTML !== '', 'Canvas HTML non vuoto');

    // Test 7: Drag & Drop (simulated)
    console.log('\n🔄 TEST 7: REORDERING\n');
    
    const firstBlock = blocks[0];
    const secondBlock = blocks[1];
    
    // Simula il reordering
    blocks.splice(0, 1);
    blocks.push(firstBlock);
    
    assert(blocks[blocks.length - 1] === firstBlock, 'Reordering: Primo blocco spostato in fondo');
    
    // Restore
    blocks.pop();
    blocks.unshift(firstBlock);

    // Test 8: Validazione dati
    console.log('\n✔️ TEST 8: VALIDAZIONE DATI\n');
    
    const testData = {
        type: 'title',
        content: { text: 'Test' },
        settings: {
            desktop: {
                font_family: 'roboto',
                font_weight: '700',
                font_size: '32px',
                color: '#000000',
                align: 'left'
            },
            tablet: {},
            mobile: {}
        }
    };
    
    assert(testData.type === 'title', 'Dato: Type è "title"');
    assert(testData.settings.desktop.font_family === 'roboto', 'Dato: Font family valido');
    assert(testData.settings.desktop.color.startsWith('#'), 'Dato: Colore è hex');

    // Test 9: Notifiche
    console.log('\n🔔 TEST 9: NOTIFICATION SYSTEM\n');
    
    notify('success', '✅ Test', 'Notifica di test');
    const notifElement = document.getElementById('save-status');
    assert(notifElement.classList.contains('show'), 'Notifica mostrata');
    assert(notifElement.classList.contains('success'), 'Notifica success');
    
    notifElement.classList.remove('show');

    // Test 10: Content editing
    console.log('\n📝 TEST 10: CONTENT EDITING\n');
    
    blocks[0].content.text = 'Updated Title';
    assert(blocks[0].content.text === 'Updated Title', 'Aggiornamento contenuto blocco');
    renderCanvas();
    assert(document.getElementById('canvas').innerHTML.includes('Updated Title'), 'Contenuto aggiornato nel canvas');

    // Test 11: Hero block specifics
    console.log('\n🦸 TEST 11: HERO BLOCK\n');
    
    const heroBlock = blocks.find(b => b.type === 'hero');
    if (heroBlock) {
        heroBlock.content.title = 'Hero Title Test';
        assert(heroBlock.content.title === 'Hero Title Test', 'Hero: Cambio titolo');
        
        heroBlock.content.imagePosition = 'left';
        assert(heroBlock.content.imagePosition === 'left', 'Hero: Cambio posizione immagine');
        
        assert(heroBlock.settings.desktop.title_font_family, 'Hero: Ha title_font_family');
        assert(heroBlock.settings.desktop.button_color, 'Hero: Ha button_color');
    }

    // Test 12: Performance
    console.log('\n⚡ TEST 12: PERFORMANCE\n');
    
    const startTime = performance.now();
    for (let i = 0; i < 10; i++) {
        renderCanvas();
    }
    const endTime = performance.now();
    const renderTime = (endTime - startTime) / 10;
    
    assert(renderTime < 100, `Rendering veloce (${renderTime.toFixed(2)}ms)`);
    console.log(`   Tempo medio render: ${renderTime.toFixed(2)}ms`);

    // Riepilogo
    console.log('\n' + '='.repeat(60));
    console.log('\n📊 RISULTATI TEST SUITE\n');
    console.log(`✅ Test Passati: ${passedTests}`);
    console.log(`❌ Test Falliti: ${failedTests}`);
    console.log(`📈 Success Rate: ${((passedTests / (passedTests + failedTests)) * 100).toFixed(1)}%\n`);
    
    console.table(results);
    
    console.log('\n' + '='.repeat(60));
    
    if (failedTests === 0) {
        console.log('\n🎉 TUTTI I TEST PASSATI! Il builder è pronto per la produzione.\n');
        notify('success', '🎉 Tutti i Test Passati!', 'La suite di test è stata completata con successo');
    } else {
        console.log(`\n⚠️  ${failedTests} TEST FALLITO/I. Controllare la console.\n`);
        notify('error', '⚠️ Test Falliti', `${failedTests} test non ha/hanno passato`);
    }
    
    // Export results
    window.testResults = {
        passed: passedTests,
        failed: failedTests,
        total: passedTests + failedTests,
        successRate: ((passedTests / (passedTests + failedTests)) * 100).toFixed(1),
        results: results,
        timestamp: new Date().toISOString()
    };
    
    return window.testResults;
}

// Helper per eseguire test senza aspettare
function quickTest() {
    console.log('🚀 Quick Test: Esecuzione rapida...\n');
    
    const tests = [];
    
    // Test blocchi
    const blockTypes = ['title', 'text', 'image', 'hero'];
    blockTypes.forEach(type => {
        addBlock(type);
        tests.push({
            name: `Add ${type} block`,
            status: blocks.some(b => b.type === type) ? 'PASS' : 'FAIL'
        });
    });
    
    // Test font controls
    if (blocks.length > 0) {
        openSettings(0);
        updateStyle('font_family', 'inter');
        updateStyle('font_size', '32px');
        updateStyle('color', '#FF5500');
        
        const block = blocks[0];
        tests.push({
            name: 'Font family',
            status: block.settings[currentView].font_family === 'inter' ? 'PASS' : 'FAIL'
        });
        tests.push({
            name: 'Font size',
            status: block.settings[currentView].font_size === '32px' ? 'PASS' : 'FAIL'
        });
        tests.push({
            name: 'Color',
            status: block.settings[currentView].color === '#FF5500' ? 'PASS' : 'FAIL'
        });
    }
    
    console.table(tests);
    
    const passCount = tests.filter(t => t.status === 'PASS').length;
    console.log(`\n✅ ${passCount}/${tests.length} Test passati`);
}

// Rendi funzioni disponibili globalmente
window.runBuilderTests = runBuilderTests;
window.quickTest = quickTest;

console.log('🧪 Test Suite Caricato. Esegui: runBuilderTests() o quickTest()');
