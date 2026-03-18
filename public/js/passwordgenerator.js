'use strict';

document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       CHARACTER SETS
    ========================================================= */
    const CHARS = {
        upper:   'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        lower:   'abcdefghijklmnopqrstuvwxyz',
        numbers: '0123456789',
        symbols: '!@#$%^&*()-_=+[]{}|;:,.<>?/~`',
        similar: /[0Ol1I]/g,
    };

    const WORDS = [
        'apple','brave','cloud','delta','eagle','flame','grace','honey',
        'ivory','jungle','kite','lemon','maple','noble','ocean','piano',
        'quest','river','storm','tiger','ultra','vivid','water','xenon',
        'yacht','zebra','amber','blaze','coral','drift','ember','frost',
        'glow','haze','iron','jade','karma','lunar','magic','nexus',
        'orbit','pulse','quartz','realm','solar','titan','unity','volt',
        'wave','xray','yield','zone',
    ];

    /* =========================================================
       STATE
    ========================================================= */
    let length      = 16;
    let mode        = 'random';
    let bulkCount   = 5;
    let lastPassword = '';

    /* =========================================================
       DOM
    ========================================================= */
    const outputText    = document.getElementById('output-text');
    const outputField   = document.getElementById('output-field');
    const btnCopy       = document.getElementById('btn-copy');
    const btnRefresh    = document.getElementById('btn-refresh');
    const copyIcon      = document.getElementById('copy-icon');
    const refreshIcon   = document.getElementById('refresh-icon');

    const strengthSegs  = [1,2,3,4,5].map(i => document.getElementById('seg-' + i));
    const strengthLabel = document.getElementById('strength-label');
    const strengthEntropy = document.getElementById('strength-entropy');

    const lengthSlider  = document.getElementById('length-slider');
    const lenVal        = document.getElementById('len-val');
    const lenMinus      = document.getElementById('len-minus');
    const lenPlus       = document.getElementById('len-plus');
    const lenPresets    = document.querySelectorAll('.pg-len-preset');

    const useUpper      = document.getElementById('use-upper');
    const useLower      = document.getElementById('use-lower');
    const useNumbers    = document.getElementById('use-numbers');
    const useSymbols    = document.getElementById('use-symbols');
    const charOpts      = document.querySelectorAll('.pg-char-opt');

    const excludeSimilar = document.getElementById('exclude-similar');
    const easyRead      = document.getElementById('easy-read');
    const ensureAll     = document.getElementById('ensure-all');

    const modeBtns      = document.querySelectorAll('.pg-mode-btn');
    const btnGenerate   = document.getElementById('btn-generate');

    const bulkToggle    = document.getElementById('bulk-toggle');
    const bulkBody      = document.getElementById('bulk-body');
    const bulkMinus     = document.getElementById('bulk-minus');
    const bulkPlus      = document.getElementById('bulk-plus');
    const bulkValEl     = document.getElementById('bulk-val');
    const btnBulkGen    = document.getElementById('btn-bulk-generate');
    const bulkList      = document.getElementById('bulk-list');
    const btnCopyAll    = document.getElementById('btn-copy-all');

    const toast         = document.getElementById('pg-toast');
    const toastMsg      = document.getElementById('pg-toast-msg');

    /* =========================================================
       HELPERS
    ========================================================= */
    function randomInt(max) {
        const arr = new Uint32Array(1);
        crypto.getRandomValues(arr);
        return arr[0] % max;
    }

    function showToast(msg) {
        toastMsg.textContent = msg;
        toast.classList.add('show');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => toast.classList.remove('show'), 2200);
    }

    function flashOutput() {
        outputText.classList.remove('flash');
        void outputText.offsetWidth;
        outputText.classList.add('flash');
        setTimeout(() => outputText.classList.remove('flash'), 350);
    }

    function updateLengthUI(val) {
        length = Math.min(128, Math.max(4, val));
        lengthSlider.value = length;
        lenVal.textContent  = length;
        lenPresets.forEach(p => p.classList.toggle('active', parseInt(p.dataset.len) === length));
    }

    /* =========================================================
       CHECKBOX CHAR OPTS
    ========================================================= */
    charOpts.forEach(opt => {
        const cb = opt.querySelector('input[type="checkbox"]');
        // Sync initial state
        opt.classList.toggle('checked', cb.checked);

        opt.addEventListener('click', function (e) {
            // Prevent unchecking if it's the last one checked (in random mode)
            const checkedCount = [useUpper, useLower, useNumbers, useSymbols].filter(c => c.checked).length;
            if (cb.checked && checkedCount === 1 && mode === 'random') {
                e.preventDefault();
                showToast('Minimal satu jenis karakter harus dipilih.');
                return;
            }
        });

        cb.addEventListener('change', function () {
            opt.classList.toggle('checked', this.checked);
        });
    });

    /* =========================================================
       LENGTH CONTROLS
    ========================================================= */
    lengthSlider.addEventListener('input', function () {
        updateLengthUI(parseInt(this.value));
    });

    lenMinus.addEventListener('click', () => updateLengthUI(length - 1));
    lenPlus.addEventListener('click',  () => updateLengthUI(length + 1));

    lenPresets.forEach(btn => {
        btn.addEventListener('click', function () {
            updateLengthUI(parseInt(this.dataset.len));
        });
    });

    /* =========================================================
       MODE BUTTONS
    ========================================================= */
    modeBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            mode = this.dataset.mode;
            modeBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    /* =========================================================
       CORE GENERATOR
    ========================================================= */
    function buildCharset() {
        let pool = '';
        if (useUpper.checked)   pool += CHARS.upper;
        if (useLower.checked)   pool += CHARS.lower;
        if (useNumbers.checked) pool += CHARS.numbers;
        if (useSymbols.checked) pool += CHARS.symbols;

        if (easyRead.checked) {
            // Keep only clearly distinguishable characters
            pool = pool.split('').filter(c => !'0Ol1I|`\'"\\'.includes(c)).join('');
        } else if (excludeSimilar.checked) {
            pool = pool.replace(CHARS.similar, '');
        }

        return pool || CHARS.lower + CHARS.numbers;
    }

    function generateRandom() {
        const pool = buildCharset();
        const required = [];

        if (ensureAll.checked) {
            // Guarantee at least one from each selected type
            if (useUpper.checked)   required.push(CHARS.upper[randomInt(CHARS.upper.length)]);
            if (useLower.checked)   required.push(CHARS.lower[randomInt(CHARS.lower.length)]);
            if (useNumbers.checked) required.push(CHARS.numbers[randomInt(CHARS.numbers.length)]);
            if (useSymbols.checked) required.push(CHARS.symbols[randomInt(CHARS.symbols.length)]);
        }

        const remaining = length - required.length;
        const chars = [];
        for (let i = 0; i < Math.max(0, remaining); i++) {
            chars.push(pool[randomInt(pool.length)]);
        }

        // Shuffle required + random together
        const combined = [...required, ...chars];
        for (let i = combined.length - 1; i > 0; i--) {
            const j = randomInt(i + 1);
            [combined[i], combined[j]] = [combined[j], combined[i]];
        }

        return combined.join('');
    }

    function generateMemorable() {
        // Pattern: Word + Separator + Number + Word + Separator + Number
        const sep = useSymbols.checked ? ['!','@','#','-','_','*'][randomInt(6)] : '-';
        const w1  = WORDS[randomInt(WORDS.length)];
        const w2  = WORDS[randomInt(WORDS.length)];
        const n1  = randomInt(999) + 1;
        const n2  = randomInt(99) + 1;

        let pw = w1 + sep + n1 + sep + w2 + n2;
        if (useUpper.checked) {
            pw = pw.charAt(0).toUpperCase() + pw.slice(1);
        }

        // Pad or trim to length if needed
        if (pw.length > length) pw = pw.slice(0, length);

        return pw;
    }

    function generatePIN() {
        let pw = '';
        for (let i = 0; i < length; i++) {
            pw += CHARS.numbers[randomInt(10)];
        }
        return pw;
    }

    function generate() {
        let pw;
        if (mode === 'memorable') pw = generateMemorable();
        else if (mode === 'pin')  pw = generatePIN();
        else                      pw = generateRandom();
        return pw;
    }

    /* =========================================================
       STRENGTH CALCULATOR
    ========================================================= */
    function calcStrength(pw) {
        if (!pw || pw === 'Klik Generate') return { score: 0, label: '—', cls: '', entropy: 0 };

        let pool = 0;
        if (/[A-Z]/.test(pw)) pool += 26;
        if (/[a-z]/.test(pw)) pool += 26;
        if (/[0-9]/.test(pw)) pool += 10;
        if (/[^A-Za-z0-9]/.test(pw)) pool += 32;

        const entropy = Math.log2(Math.pow(pool || 26, pw.length));
        let score, label, cls;

        if (entropy < 28)      { score = 1; label = 'Sangat Lemah'; cls = 's1'; }
        else if (entropy < 40) { score = 2; label = 'Lemah';        cls = 's2'; }
        else if (entropy < 60) { score = 3; label = 'Cukup';        cls = 's3'; }
        else if (entropy < 80) { score = 4; label = 'Kuat';         cls = 's4'; }
        else                   { score = 5; label = 'Sangat Kuat';  cls = 's5'; }

        return { score, label, cls, entropy: Math.round(entropy) };
    }

    function renderStrength(pw) {
        const { score, label, cls, entropy } = calcStrength(pw);
        strengthSegs.forEach((seg, i) => {
            seg.className = 'pg-strength-seg';
            if (i < score) seg.classList.add('active-' + score);
        });
        strengthLabel.textContent = label;
        strengthLabel.className = 'pg-strength-label' + (cls ? ' ' + cls : '');
        strengthEntropy.textContent = entropy ? `~${entropy} bits` : '';
    }

    /* =========================================================
       GENERATE BUTTON
    ========================================================= */
    btnGenerate.addEventListener('click', function () {
        // Brief spin animation
        this.classList.add('generating');
        setTimeout(() => this.classList.remove('generating'), 400);

        const pw = generate();
        lastPassword = pw;

        outputText.textContent = pw;
        outputText.classList.remove('placeholder');
        outputField.classList.add('has-value');
        btnCopy.disabled = false;

        flashOutput();
        renderStrength(pw);
    });

    /* =========================================================
       REFRESH BUTTON
    ========================================================= */
    btnRefresh.addEventListener('click', function () {
        if (!lastPassword) return;
        refreshIcon.style.transform = 'rotate(360deg)';
        refreshIcon.style.transition = 'transform 0.4s ease';
        setTimeout(() => {
            refreshIcon.style.transform = '';
            refreshIcon.style.transition = '';
        }, 400);

        const pw = generate();
        lastPassword = pw;
        outputText.textContent = pw;
        flashOutput();
        renderStrength(pw);
    });

    /* =========================================================
       COPY BUTTON
    ========================================================= */
    btnCopy.addEventListener('click', function () {
        if (!lastPassword) return;
        copyToClipboard(lastPassword, () => {
            copyIcon.className = 'fa-solid fa-check';
            this.classList.add('copied');
            showToast('Password disalin ke clipboard!');
            setTimeout(() => {
                copyIcon.className = 'fa-regular fa-copy';
                this.classList.remove('copied');
            }, 2000);
        });
    });

    function copyToClipboard(text, callback) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(callback).catch(() => fallbackCopy(text, callback));
        } else {
            fallbackCopy(text, callback);
        }
    }

    function fallbackCopy(text, callback) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try { document.execCommand('copy'); callback(); } catch(e) {}
        document.body.removeChild(ta);
    }

    /* =========================================================
       BULK GENERATE
    ========================================================= */
    bulkToggle.addEventListener('click', function () {
        this.classList.toggle('open');
        bulkBody.classList.toggle('open');
    });

    bulkMinus.addEventListener('click', () => {
        bulkCount = Math.max(1, bulkCount - 1);
        bulkValEl.textContent = bulkCount;
    });

    bulkPlus.addEventListener('click', () => {
        bulkCount = Math.min(50, bulkCount + 1);
        bulkValEl.textContent = bulkCount;
    });

    btnBulkGen.addEventListener('click', function () {
        bulkList.innerHTML = '';
        const passwords = [];

        for (let i = 0; i < bulkCount; i++) {
            const pw = generate();
            passwords.push(pw);

            const item = document.createElement('div');
            item.className = 'pg-bulk-item';
            item.innerHTML = `
                <span class="pg-bulk-num">${i + 1}.</span>
                <span class="pg-bulk-pw">${pw}</span>
                <button class="pg-bulk-copy" title="Salin">
                    <i class="fa-regular fa-copy"></i>
                </button>
            `;

            const copyBtn = item.querySelector('.pg-bulk-copy');
            copyBtn.addEventListener('click', () => {
                copyToClipboard(pw, () => {
                    copyBtn.innerHTML = '<i class="fa-solid fa-check"></i>';
                    copyBtn.style.color = 'var(--accent)';
                    showToast('Password #' + (i + 1) + ' disalin!');
                    setTimeout(() => {
                        copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i>';
                        copyBtn.style.color = '';
                    }, 1800);
                });
            });

            bulkList.appendChild(item);
        }

        btnCopyAll.classList.remove('ic-hidden');
        btnCopyAll._passwords = passwords;
    });

    btnCopyAll.addEventListener('click', function () {
        const all = (this._passwords || []).join('\n');
        if (!all) return;
        copyToClipboard(all, () => {
            showToast(`${(this._passwords || []).length} password disalin!`);
        });
    });

    /* =========================================================
       KEYBOARD SHORTCUT
    ========================================================= */
    document.addEventListener('keydown', function (e) {
        // Space or Enter on the page = generate (if not in input)
        if ((e.code === 'Space' || e.code === 'Enter') &&
            !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            btnGenerate.click();
        }
        // Ctrl/Cmd + G = generate
        if ((e.ctrlKey || e.metaKey) && e.key === 'g') {
            e.preventDefault();
            btnGenerate.click();
        }
        // Ctrl/Cmd + C while no text selected = copy password
        if ((e.ctrlKey || e.metaKey) && e.key === 'c' && !window.getSelection().toString()) {
            if (lastPassword) btnCopy.click();
        }
    });

    /* =========================================================
       AUTO-GENERATE ON LOAD
    ========================================================= */
    btnGenerate.click();

});