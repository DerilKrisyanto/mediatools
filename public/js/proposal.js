/**
 * PROPOSAL BUILDER — proposal.js
 * MediaTools — AI Powered Proposal Generator
 */
(function () {
    'use strict';

    /* ── Constants ── */
    const GENERATE_URL = window.PB_GENERATE_URL || '/proposal/generate';
    const DOWNLOAD_URL = window.PB_DOWNLOAD_URL || '/proposal/download';
    const SERVE_URL    = window.PB_SERVE_URL    || '/proposal/serve';

    /* ── State ── */
    const state = {
        view: 'landing',       // landing | wizard | generating | preview
        template: null,        // mahasiswa | freelancer | bisnis | event
        currentStep: 0,
        logoBase64: null,
        formData: {},
        generatedHTML: '',
        cacheKey: null,
        templateName: '',
    };

    /* ── Template Definitions ── */
    const TEMPLATES = {
        mahasiswa: {
            name: 'Proposal Tugas Akhir',
            icon: '🎓',
            color: '#60a5fa',
            iconClass: 'pb-tpl-icon--mahasiswa',
            steps: [
                {
                    id: 'sampul', label: 'Identitas Sampul', sub: 'Logo, nama, NIM & info kampus',
                    fields: 'sampul'
                },
                {
                    id: 'pendahuluan', label: 'Latar Belakang', sub: 'Latar belakang & identifikasi masalah',
                    fields: 'pendahuluan'
                },
                {
                    id: 'rumusan', label: 'Rumusan & Tujuan', sub: 'Rumusan, tujuan & manfaat',
                    fields: 'rumusan'
                },
                {
                    id: 'metodologi', label: 'Metodologi', sub: 'Metode & sistematika penulisan',
                    fields: 'metodologi'
                },
                {
                    id: 'konfigurasi', label: 'Referensi & Konfigurasi', sub: 'Pustaka & bab yang digunakan',
                    fields: 'konfigurasi_ta'
                },
            ]
        },
        freelancer: {
            name: 'Proposal Project',
            icon: '💼',
            color: '#c084fc',
            iconClass: 'pb-tpl-icon--freelancer',
            steps: [
                { id: 'proyek', label: 'Data Proyek', sub: 'Info klien & lingkup proyek', fields: 'data_proyek' },
                { id: 'scope', label: 'Scope of Work', sub: 'Deliverables & teknologi', fields: 'scope_work' },
                { id: 'timeline', label: 'Timeline & Anggaran', sub: 'Milestone & rincian biaya', fields: 'timeline_anggaran' },
                { id: 'profil', label: 'Profil & Syarat', sub: 'Profil tim & ketentuan', fields: 'profil_syarat' },
            ]
        },
        bisnis: {
            name: 'Proposal Bisnis',
            icon: '📈',
            color: '#fbbf24',
            iconClass: 'pb-tpl-icon--bisnis',
            steps: [
                { id: 'profil', label: 'Profil Bisnis', sub: 'Visi, misi & profil pendiri', fields: 'profil_bisnis' },
                { id: 'produk', label: 'Produk & Pasar', sub: 'Produk, target & nilai proposisi', fields: 'produk_pasar' },
                { id: 'analisis', label: 'Analisis & Strategi', sub: 'SWOT, marketing & kompetitor', fields: 'analisis_strategi' },
                { id: 'keuangan', label: 'Keuangan', sub: 'Modal, proyeksi & break even', fields: 'keuangan_bisnis' },
            ]
        },
        event: {
            name: 'Proposal Event',
            icon: '🗓',
            color: '#fb7185',
            iconClass: 'pb-tpl-icon--event',
            steps: [
                { id: 'info', label: 'Info Acara', sub: 'Nama, tanggal & lokasi', fields: 'info_acara' },
                { id: 'konsep', label: 'Konsep & Tujuan', sub: 'Latar belakang & konsep acara', fields: 'konsep_tujuan' },
                { id: 'panitia', label: 'Kepanitiaan & Rundown', sub: 'Struktur & susunan acara', fields: 'panitia_rundown' },
                { id: 'anggaran', label: 'Anggaran & Sponsorship', sub: 'Rincian biaya & paket sponsor', fields: 'anggaran_sponsor' },
            ]
        }
    };

    /* ══════════════════════════════════════
       VIEW SWITCHING
    ══════════════════════════════════════ */
    function showView(name) {
        state.view = name;
        document.querySelectorAll('.pb-view').forEach(el => el.classList.remove('active'));
        const el = document.getElementById('view-' + name);
        if (el) el.classList.add('active');
    }

    /* ══════════════════════════════════════
       LANDING — SELECT TEMPLATE
    ══════════════════════════════════════ */
    function selectTemplate(tplKey) {
        state.template = tplKey;
        state.currentStep = 0;
        state.formData = {};
        state.logoBase64 = null;
        buildWizard(tplKey);
        showView('wizard');
    }

    /* ══════════════════════════════════════
       WIZARD — BUILD
    ══════════════════════════════════════ */
    function buildWizard(tplKey) {
        const tpl = TEMPLATES[tplKey];
        state.templateName = tpl.name;

        // Sidebar header
        document.getElementById('sb-icon').textContent = tpl.icon;
        document.getElementById('sb-icon').style.background = tpl.color + '22';
        document.getElementById('sb-tpl-name').textContent = tpl.name;
        document.getElementById('sb-tpl-sub').textContent = tpl.steps.length + ' langkah pengisian';

        // Steps list
        const list = document.getElementById('sb-steps-list');
        list.innerHTML = tpl.steps.map((s, i) => `
            <li class="pb-step-item${i === 0 ? ' active' : ''}" data-step="${i}" onclick="window.PB.goToStep(${i})">
                <div class="pb-step-num"><span>${i + 1}</span></div>
                <div class="pb-step-info">
                    <div class="pb-step-label">${s.label}</div>
                    <div class="pb-step-sub">${s.sub}</div>
                </div>
            </li>
        `).join('');

        // Render step panels
        const panelsContainer = document.getElementById('pb-step-panels');
        panelsContainer.innerHTML = tpl.steps.map((s, i) =>
            renderStepPanel(s, i, tpl.steps.length, tplKey)
        ).join('');

        // Init interactions for step 0
        initStepInteractions();
        updateProgress();
        renderLogoUpload();
    }

    function renderStepPanel(step, idx, total, tplKey) {
        const isLast = idx === total - 1;
        const tpl = TEMPLATES[tplKey];

        return `
        <div class="pb-step-panel${idx === 0 ? ' active' : ''}" id="panel-${idx}">
            <div class="pb-step-panel-header">
                <div class="pb-step-eyebrow">
                    <span class="pb-step-eyebrow-dot"></span>
                    Langkah ${idx + 1} dari ${total}
                </div>
                <div class="pb-step-title">${step.label}</div>
                <div class="pb-step-hint">${getStepHint(step.fields)}</div>
            </div>
            ${getStepFields(step.fields, tplKey)}
        </div>
        `;
    }

    function getStepHint(fieldsKey) {
        const hints = {
            sampul: 'Isi identitas lengkap untuk halaman sampul proposal Anda. Upload logo kampus agar tampak lebih profesional.',
            pendahuluan: 'Jelaskan latar belakang masalah yang melatarbelakangi penelitian Anda.',
            rumusan: 'Tentukan rumusan masalah, tujuan dan manfaat penelitian secara spesifik.',
            metodologi: 'Jelaskan metode penelitian dan sistematika penulisan yang akan digunakan.',
            konfigurasi_ta: 'Tambahkan referensi awal dan pilih bab-bab yang akan disertakan dalam proposal Anda.',
            data_proyek: 'Isi informasi dasar proyek dan klien yang akan dikerjakan.',
            scope_work: 'Jabarkan deliverables, metodologi dan stack teknologi yang digunakan.',
            timeline_anggaran: 'Tentukan milestone, estimasi waktu dan rincian anggaran proyek.',
            profil_syarat: 'Tulis profil singkat dan syarat & ketentuan kerja sama.',
            profil_bisnis: 'Gambarkan identitas bisnis, visi dan misi serta profil pendiri.',
            produk_pasar: 'Deskripsikan produk/layanan, target pasar dan nilai keunggulan.',
            analisis_strategi: 'Lakukan analisis SWOT, strategi pemasaran dan peta kompetitor.',
            keuangan_bisnis: 'Cantumkan kebutuhan modal, proyeksi pendapatan dan break even point.',
            info_acara: 'Masukkan informasi dasar acara yang akan diselenggarakan.',
            konsep_tujuan: 'Uraikan latar belakang, tujuan dan konsep acara secara mendetail.',
            panitia_rundown: 'Cantumkan struktur kepanitiaan dan susunan acara / rundown.',
            anggaran_sponsor: 'Buat rincian anggaran dan paket-paket sponsorship yang tersedia.',
        };
        return hints[fieldsKey] || 'Lengkapi isian di bawah ini.';
    }

    /* ══════════════════════════════════════
       FIELD RENDERERS per step
    ══════════════════════════════════════ */
    function getStepFields(key, tplKey) {
        const map = {
            sampul:             fields_sampul,
            pendahuluan:        fields_pendahuluan,
            rumusan:            fields_rumusan,
            metodologi:         fields_metodologi,
            konfigurasi_ta:     fields_konfigurasi_ta,
            data_proyek:        fields_data_proyek,
            scope_work:         fields_scope_work,
            timeline_anggaran:  fields_timeline_anggaran,
            profil_syarat:      fields_profil_syarat,
            profil_bisnis:      fields_profil_bisnis,
            produk_pasar:       fields_produk_pasar,
            analisis_strategi:  fields_analisis_strategi,
            keuangan_bisnis:    fields_keuangan_bisnis,
            info_acara:         fields_info_acara,
            konsep_tujuan:      fields_konsep_tujuan,
            panitia_rundown:    fields_panitia_rundown,
            anggaran_sponsor:   fields_anggaran_sponsor,
        };
        return (map[key] || (() => '<div class="pb-info-box"><i class="fa-solid fa-circle-info"></i><span>Isian untuk langkah ini sedang disiapkan.</span></div>'))();
    }

    function inp(id, label, placeholder, type = 'text', opts = '') {
        return `<div class="pb-form-group">
            <label class="pb-label" for="${id}">${label}</label>
            <input type="${type}" id="${id}" name="${id}" class="pb-input" placeholder="${placeholder}" ${opts}>
        </div>`;
    }

    function txt(id, label, placeholder, rows = 4) {
        return `<div class="pb-form-group">
            <label class="pb-label" for="${id}">${label}</label>
            <textarea id="${id}" name="${id}" class="pb-textarea" rows="${rows}" placeholder="${placeholder}"></textarea>
        </div>`;
    }

    function sel(id, label, options) {
        const opts = options.map(o => `<option value="${o}">${o}</option>`).join('');
        return `<div class="pb-form-group">
            <label class="pb-label" for="${id}">${label}</label>
            <select id="${id}" name="${id}" class="pb-select"><option value="">-- Pilih --</option>${opts}</select>
        </div>`;
    }

    function sectionTitle(t) {
        return `<div class="pb-form-section-title">${t}</div>`;
    }

    /* ── SAMPUL ── */
    function fields_sampul() {
        return `<div class="pb-form-grid">
            <div class="pb-form-group">
                <label class="pb-label">Logo Institusi / Kampus <span class="pb-label-opt">(Opsional)</span></label>
                <div class="pb-logo-upload" id="logo-upload-zone">
                    <input type="file" id="logo-file-input" accept="image/*">
                    <div class="pb-logo-placeholder">
                        <i class="fa-solid fa-image"></i>
                        <p>Klik untuk upload logo kampus</p>
                        <span>PNG / JPG · Maks 5MB</span>
                    </div>
                    <img class="pb-logo-preview-img" id="logo-preview-img" alt="Logo preview">
                    <button type="button" class="pb-logo-remove" id="logo-remove-btn" onclick="window.PB.removeLogo(event)">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
            ${inp('judul_proposal', 'Judul Proposal / Penelitian', 'Contoh: Rancang Bangun Sistem Informasi Perpustakaan Berbasis Web...')}
            <div class="pb-form-grid-2">
                ${inp('nama_mahasiswa', 'Nama Mahasiswa', 'Nama lengkap sesuai KTP')}
                ${inp('nim', 'NIM / NPM', 'Nomor Induk Mahasiswa')}
            </div>
            <div class="pb-form-grid-2">
                ${inp('program_studi', 'Program Studi', 'Contoh: Teknik Informatika')}
                ${inp('jenjang', 'Jenjang Studi', 'Contoh: S1 / D3 / D4')}
            </div>
            <div class="pb-form-grid-2">
                ${inp('fakultas', 'Fakultas', 'Contoh: Fakultas Teknik')}
                ${inp('nama_kampus', 'Nama Universitas / Institusi', 'Contoh: Universitas Indonesia')}
            </div>
            <div class="pb-form-grid-2">
                ${inp('nama_dosen', 'Nama Dosen Pembimbing', 'Gelar dan nama lengkap')}
                ${inp('nip_dosen', 'NIP / NIDN Dosen', 'Nomor Induk Pegawai')}
            </div>
            <div class="pb-form-grid-2">
                ${inp('kota', 'Kota', 'Contoh: Jakarta')}
                ${inp('tahun', 'Tahun Akademik', new Date().getFullYear().toString())}
            </div>
            ${inp('kata_pengantar_penulis', 'Nama untuk Kata Pengantar', 'Nama yang tercantum di bagian akhir kata pengantar')}
        </div>`;
    }

    function fields_pendahuluan() {
        return `<div class="pb-form-grid">
            ${txt('latar_belakang', 'Latar Belakang Masalah', 'Uraikan fenomena, masalah atau kondisi yang melatarbelakangi penelitian Anda. Sertakan data atau fakta pendukung jika ada...', 6)}
            ${txt('identifikasi_masalah', 'Identifikasi Masalah', 'Tuliskan poin-poin masalah yang teridentifikasi dari latar belakang. Pisahkan tiap poin dengan enter baru.', 4)}
            ${txt('batasan_masalah', 'Batasan Masalah', 'Tuliskan batasan-batasan penelitian agar fokus dan terarah...', 3)}
        </div>`;
    }

    function fields_rumusan() {
        return `<div class="pb-form-grid">
            ${txt('rumusan_masalah', 'Rumusan Masalah', 'Tuliskan pertanyaan penelitian dalam bentuk kalimat tanya. Contoh:\n1. Bagaimana merancang sistem...\n2. Bagaimana tingkat efektivitas...', 4)}
            ${txt('tujuan_penelitian', 'Tujuan Penelitian', 'Tuliskan tujuan yang ingin dicapai dari penelitian ini (sesuaikan dengan rumusan masalah).', 4)}
            ${txt('manfaat_teoritis', 'Manfaat Teoritis', 'Jelaskan kontribusi penelitian terhadap perkembangan ilmu pengetahuan.', 3)}
            ${txt('manfaat_praktis', 'Manfaat Praktis', 'Jelaskan manfaat penelitian bagi instansi, masyarakat atau pemangku kepentingan lainnya.', 3)}
        </div>`;
    }

    function fields_metodologi() {
        return `<div class="pb-form-grid">
            ${sel('jenis_penelitian', 'Jenis / Metode Penelitian', ['Kuantitatif', 'Kualitatif', 'Campuran (Mixed Method)', 'Eksperimen', 'Penelitian Tindakan', 'Studi Kasus', 'Deskriptif', 'Literatur Review', 'Research and Development (R&D)'])}
            ${txt('metode_pengumpulan_data', 'Metode Pengumpulan Data', 'Contoh: observasi lapangan, wawancara mendalam, kuesioner, studi dokumentasi...', 3)}
            ${txt('teknik_analisis', 'Teknik Analisis Data', 'Jelaskan bagaimana data yang dikumpulkan akan dianalisis dan diinterpretasikan.', 3)}
            ${txt('sistematika_penulisan', 'Sistematika Penulisan', 'Contoh:\nBAB I: Pendahuluan\nBAB II: Landasan Teori\nBAB III: Metodologi Penelitian\nBAB IV: Hasil dan Pembahasan\nBAB V: Penutup', 5)}
            ${txt('jadwal_penelitian', 'Jadwal / Timeline Penelitian', 'Contoh:\nMinggu 1-2: Studi Literatur\nMinggu 3-4: Pengumpulan Data\nMinggu 5-8: Pengembangan Sistem\nMinggu 9-10: Pengujian & Evaluasi', 4)}
        </div>`;
    }

    function fields_konfigurasi_ta() {
        return `<div class="pb-form-grid">
            ${txt('daftar_pustaka', 'Referensi / Daftar Pustaka Awal', 'Tuliskan referensi yang sudah Anda miliki (format APA/IEEE). Contoh:\nSugiyono. (2019). Metode Penelitian Kuantitatif, Kualitatif dan R&D. Alfabeta.\nKotler, P., & Armstrong, G. (2021). Principles of Marketing. Pearson.', 5)}
            ${sectionTitle('Bab yang Disertakan dalam Proposal')}
            <div class="pb-check-list" id="bab-checklist">
                ${checkItem('bab1', 'BAB I — Pendahuluan', 'Latar belakang, rumusan, tujuan & manfaat', true)}
                ${checkItem('bab2', 'BAB II — Landasan Teori', 'Kajian pustaka dan kerangka teori', true)}
                ${checkItem('bab3', 'BAB III — Metodologi Penelitian', 'Metode, instrumen & teknik analisis', true)}
                ${checkItem('bab4', 'BAB IV — Hasil & Pembahasan', 'Hasil penelitian dan analisis data', true)}
                ${checkItem('bab5', 'BAB V — Penutup', 'Kesimpulan dan saran', true)}
            </div>
            <div class="pb-info-box">
                <i class="fa-solid fa-lightbulb"></i>
                <span>Unceklis bab yang tidak diperlukan sesuai ketentuan kampus Anda. AI akan menyesuaikan isi proposal secara otomatis.</span>
            </div>
        </div>`;
    }

    function fields_data_proyek() {
        return `<div class="pb-form-grid">
            <div class="pb-form-group">
                <label class="pb-label">Logo / Brand Anda <span class="pb-label-opt">(Opsional)</span></label>
                <div class="pb-logo-upload" id="logo-upload-zone">
                    <input type="file" id="logo-file-input" accept="image/*">
                    <div class="pb-logo-placeholder">
                        <i class="fa-solid fa-image"></i>
                        <p>Upload logo atau foto profil Anda</p>
                        <span>PNG / JPG · Maks 5MB</span>
                    </div>
                    <img class="pb-logo-preview-img" id="logo-preview-img" alt="Logo preview">
                    <button type="button" class="pb-logo-remove" id="logo-remove-btn" onclick="window.PB.removeLogo(event)">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
            ${inp('judul_proyek', 'Nama / Judul Proyek', 'Contoh: Pengembangan Website E-Commerce PT. Maju Bersama')}
            ${inp('nama_freelancer', 'Nama Freelancer / Tim', 'Nama Anda atau nama tim')}
            ${inp('nama_klien', 'Nama Klien / Perusahaan', 'Contoh: PT. Maju Bersama')}
            <div class="pb-form-grid-2">
                ${inp('tanggal_proposal', 'Tanggal Proposal', new Date().toLocaleDateString('id-ID'), 'text')}
                ${inp('durasi_proyek', 'Estimasi Durasi', 'Contoh: 3 bulan / 12 minggu')}
            </div>
            ${txt('deskripsi_proyek', 'Deskripsi Singkat Proyek', 'Gambarkan secara singkat tentang proyek ini, konteks bisnisnya dan apa yang ingin dicapai klien.', 4)}
            ${txt('latar_belakang_klien', 'Latar Belakang Klien / Kebutuhan', 'Ceritakan kondisi klien saat ini dan mengapa mereka membutuhkan proyek ini.', 3)}
        </div>`;
    }

    function fields_scope_work() {
        return `<div class="pb-form-grid">
            ${txt('deliverables', 'Deliverables / Output yang Diserahkan', 'Tuliskan apa saja yang akan Anda serahkan kepada klien. Contoh:\n1. Desain UI/UX (Figma)\n2. Kode sumber website (GitHub)\n3. Dokumentasi teknis\n4. Panduan penggunaan', 5)}
            ${txt('metodologi_kerja', 'Metodologi & Alur Kerja', 'Jelaskan bagaimana Anda akan menjalankan proyek ini (agile, waterfall, dll) dan alur komunikasinya.', 4)}
            ${txt('teknologi_stack', 'Teknologi / Tools yang Digunakan', 'Contoh:\nFrontend: React.js, Tailwind CSS\nBackend: Laravel, MySQL\nCloud: AWS EC2, S3\nTools: Figma, Postman, Git', 4)}
            ${txt('out_of_scope', 'Di Luar Scope (Opsional)', 'Tuliskan hal-hal yang TIDAK termasuk dalam proyek ini agar tidak ada miskomunikasi.', 3)}
        </div>`;
    }

    function fields_timeline_anggaran() {
        return `<div class="pb-form-grid">
            ${txt('milestone', 'Milestone & Timeline', 'Contoh:\nFase 1 (Minggu 1-2): Requirement Gathering & Desain\nFase 2 (Minggu 3-5): Development Frontend\nFase 3 (Minggu 6-8): Development Backend & Integrasi\nFase 4 (Minggu 9-10): Testing & Deployment', 6)}
            ${sectionTitle('Rincian Anggaran')}
            ${txt('rincian_biaya', 'Rincian Biaya / Quotation', 'Contoh:\nJasa Desain UI/UX: Rp 5.000.000\nJasa Development Frontend: Rp 8.000.000\nJasa Development Backend: Rp 10.000.000\nHosting & Domain (1 tahun): Rp 2.000.000\nRevisi & Support (3 bulan): Rp 3.000.000\nTotal: Rp 28.000.000', 6)}
            ${inp('total_anggaran', 'Total Anggaran', 'Contoh: Rp 28.000.000')}
            ${txt('skema_pembayaran', 'Skema Pembayaran', 'Contoh:\n50% di awal sebagai DP\n30% setelah development selesai\n20% setelah serah terima dan UAT', 3)}
        </div>`;
    }

    function fields_profil_syarat() {
        return `<div class="pb-form-grid">
            ${txt('profil_tim', 'Profil Singkat Freelancer / Tim', 'Ceritakan pengalaman, keahlian dan portofolio singkat Anda yang relevan dengan proyek ini.', 4)}
            ${txt('pengalaman_relevan', 'Pengalaman & Portofolio Relevan', 'Sebutkan proyek-proyek serupa yang pernah dikerjakan sebagai referensi.', 3)}
            ${sectionTitle('Syarat & Ketentuan')}
            ${txt('syarat_ketentuan', 'Syarat & Ketentuan', 'Cantumkan ketentuan penting seperti:\n- Revisi: maksimal 3x per fase\n- Kepemilikan source code setelah pelunasan\n- Garansi bug 30 hari setelah deployment\n- Force majeure\n- Klausul kerahasiaan', 5)}
            ${inp('kontak_freelancer', 'Kontak / Email Freelancer', 'Email atau WhatsApp untuk follow up')}
        </div>`;
    }

    function fields_profil_bisnis() {
        return `<div class="pb-form-grid">
            <div class="pb-form-group">
                <label class="pb-label">Logo Bisnis <span class="pb-label-opt">(Opsional)</span></label>
                <div class="pb-logo-upload" id="logo-upload-zone">
                    <input type="file" id="logo-file-input" accept="image/*">
                    <div class="pb-logo-placeholder">
                        <i class="fa-solid fa-image"></i>
                        <p>Upload logo bisnis Anda</p>
                        <span>PNG / JPG · Maks 5MB</span>
                    </div>
                    <img class="pb-logo-preview-img" id="logo-preview-img" alt="Logo preview">
                    <button type="button" class="pb-logo-remove" id="logo-remove-btn" onclick="window.PB.removeLogo(event)">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
            ${inp('nama_bisnis', 'Nama Bisnis / Startup', 'Contoh: GreenEco Indonesia')}
            ${inp('bidang_usaha', 'Bidang Usaha', 'Contoh: Teknologi Pertanian / Fintech / EdTech')}
            ${inp('tahun_berdiri', 'Tahun Berdiri', new Date().getFullYear().toString())}
            ${inp('lokasi_bisnis', 'Lokasi Utama', 'Contoh: Jakarta Selatan, DKI Jakarta')}
            ${txt('visi', 'Visi Bisnis', 'Gambaran besar tentang cita-cita jangka panjang bisnis Anda...', 2)}
            ${txt('misi', 'Misi Bisnis', 'Langkah-langkah konkret untuk mewujudkan visi. Pisahkan tiap poin dengan enter baru.', 3)}
            ${txt('profil_pendiri', 'Profil Pendiri / Tim Inti', 'Nama, latar belakang dan peran masing-masing pendiri dalam bisnis.', 4)}
        </div>`;
    }

    function fields_produk_pasar() {
        return `<div class="pb-form-grid">
            ${txt('deskripsi_produk', 'Deskripsi Produk / Layanan', 'Jelaskan secara detail produk atau layanan yang ditawarkan, bagaimana cara kerjanya dan apa yang membuatnya unik.', 4)}
            ${txt('nilai_proposisi', 'Nilai Proposisi (Value Proposition)', 'Apa keunggulan kompetitif utama yang membedakan bisnis Anda dari kompetitor?', 3)}
            ${txt('target_pasar', 'Target Pasar (Segmentasi)', 'Deskripsikan segmen pelanggan utama: usia, pekerjaan, kebiasaan, wilayah, dll. Sertakan estimasi jumlah target pasar jika ada.', 3)}
            ${txt('saluran_distribusi', 'Saluran Distribusi / Go-to-Market', 'Bagaimana produk/layanan Anda sampai ke pelanggan? (Online, offline, kemitraan, dll)', 3)}
        </div>`;
    }

    function fields_analisis_strategi() {
        return `<div class="pb-form-grid">
            ${sectionTitle('Analisis SWOT')}
            <div class="pb-form-grid-2">
                ${txt('strength', 'Kekuatan (Strengths)', 'Keunggulan internal bisnis Anda...', 3)}
                ${txt('weakness', 'Kelemahan (Weaknesses)', 'Keterbatasan internal yang perlu diperbaiki...', 3)}
            </div>
            <div class="pb-form-grid-2">
                ${txt('opportunity', 'Peluang (Opportunities)', 'Faktor eksternal yang bisa dimanfaatkan...', 3)}
                ${txt('threat', 'Ancaman (Threats)', 'Faktor eksternal yang bisa menghambat...', 3)}
            </div>
            ${sectionTitle('Strategi & Kompetitor')}
            ${txt('strategi_pemasaran', 'Strategi Pemasaran', 'Jelaskan strategi marketing mix (4P) atau strategi digital marketing yang akan dijalankan.', 4)}
            ${txt('analisis_kompetitor', 'Analisis Kompetitor', 'Sebutkan kompetitor utama dan bagaimana posisi bisnis Anda dibandingkan mereka.', 3)}
        </div>`;
    }

    function fields_keuangan_bisnis() {
        return `<div class="pb-form-grid">
            ${txt('modal_awal', 'Kebutuhan Modal Awal', 'Rincikan kebutuhan dana awal:\n- Biaya operasional: Rp ...\n- Pengembangan produk: Rp ...\n- Marketing & promosi: Rp ...\n- Infrastruktur: Rp ...\nTotal: Rp ...', 5)}
            ${inp('total_investasi', 'Total Investasi yang Dibutuhkan', 'Contoh: Rp 500.000.000')}
            ${txt('proyeksi_pendapatan', 'Proyeksi Pendapatan (3 Tahun)', 'Contoh:\nTahun 1: Rp 200.000.000 (dari 100 pelanggan)\nTahun 2: Rp 500.000.000 (dari 300 pelanggan)\nTahun 3: Rp 1.200.000.000 (dari 800 pelanggan)', 4)}
            ${txt('model_bisnis', 'Model Bisnis / Aliran Pendapatan', 'Jelaskan bagaimana bisnis menghasilkan uang: langganan, komisi, iklan, penjualan langsung, dll.', 3)}
            ${inp('break_even', 'Estimasi Break Even Point', 'Contoh: Bulan ke-18 / Tahun ke-2')}
        </div>`;
    }

    function fields_info_acara() {
        return `<div class="pb-form-grid">
            <div class="pb-form-group">
                <label class="pb-label">Logo / Banner Acara <span class="pb-label-opt">(Opsional)</span></label>
                <div class="pb-logo-upload" id="logo-upload-zone">
                    <input type="file" id="logo-file-input" accept="image/*">
                    <div class="pb-logo-placeholder">
                        <i class="fa-solid fa-image"></i>
                        <p>Upload logo atau banner acara</p>
                        <span>PNG / JPG · Maks 5MB</span>
                    </div>
                    <img class="pb-logo-preview-img" id="logo-preview-img" alt="Logo preview">
                    <button type="button" class="pb-logo-remove" id="logo-remove-btn" onclick="window.PB.removeLogo(event)">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
            ${inp('nama_acara', 'Nama Acara / Event', 'Contoh: Seminar Nasional Teknologi 2025')}
            ${inp('tema_acara', 'Tema Acara', 'Contoh: "Inovasi Digital Menuju Indonesia Maju"')}
            ${inp('penyelenggara', 'Penyelenggara / Organisasi', 'Contoh: BEM Fakultas Teknik Universitas Indonesia')}
            <div class="pb-form-grid-2">
                ${inp('tanggal_acara', 'Tanggal & Waktu Pelaksanaan', 'Contoh: 15 Agustus 2025, 08.00 – 17.00 WIB')}
                ${inp('lokasi_acara', 'Lokasi / Venue', 'Contoh: Auditorium Universitas Indonesia')}
            </div>
            <div class="pb-form-grid-2">
                ${inp('target_peserta', 'Target Jumlah Peserta', 'Contoh: 500 peserta')}
                ${inp('target_segmen', 'Segmen Peserta', 'Contoh: Mahasiswa, profesional IT')}
            </div>
            ${inp('narahubung', 'Narahubung / Contact Person', 'Nama dan nomor yang bisa dihubungi')}
        </div>`;
    }

    function fields_konsep_tujuan() {
        return `<div class="pb-form-grid">
            ${txt('latar_belakang_acara', 'Latar Belakang Penyelenggaraan', 'Mengapa acara ini penting untuk diselenggarakan? Kondisi apa yang melatarbelakanginya?', 5)}
            ${txt('tujuan_acara', 'Tujuan Acara', 'Tuliskan tujuan spesifik yang ingin dicapai dari penyelenggaraan acara ini. Pisahkan tiap poin.', 4)}
            ${txt('konsep_acara', 'Konsep & Format Acara', 'Jelaskan konsep dan format acara: seminar, konser, workshop, pameran, dll. Apa yang membuat acara ini berbeda?', 4)}
            ${txt('manfaat_acara', 'Manfaat yang Diharapkan', 'Apa manfaat yang akan diperoleh peserta dan masyarakat dari acara ini?', 3)}
        </div>`;
    }

    function fields_panitia_rundown() {
        return `<div class="pb-form-grid">
            ${txt('struktur_panitia', 'Struktur Kepanitiaan', 'Contoh:\nPelindung: Dekan Fakultas Teknik\nPenasehat: Wakil Dekan III\nKetua Panitia: Budi Santoso\nWakil Ketua: Ani Rahayu\nSekretaris: Citra Dewi\nBendahara: Dani Pratama\nDivisi Acara: ...\nDivisi Sponsorship: ...', 6)}
            ${txt('rundown_acara', 'Susunan Acara / Rundown', 'Contoh:\n07.30 – 08.00: Registrasi Peserta\n08.00 – 08.30: Pembukaan & Sambutan\n08.30 – 10.00: Keynote Speaker I\n10.00 – 10.15: Coffee Break\n10.15 – 11.45: Keynote Speaker II\n11.45 – 13.00: ISHOMA\n13.00 – 15.00: Panel Discussion\n15.00 – 15.30: Penutupan', 7)}
            ${txt('pembicara_tamu', 'Pembicara / Tamu Undangan', 'Sebutkan nama pembicara, jabatan dan topik yang akan dibawakan (jika sudah ada).', 3)}
        </div>`;
    }

    function fields_anggaran_sponsor() {
        return `<div class="pb-form-grid">
            ${txt('rincian_anggaran_event', 'Rincian Anggaran Acara', 'Contoh:\nVenue & Dekorasi: Rp 20.000.000\nKonsumsi (500 pax): Rp 25.000.000\nHonorarium Pembicara: Rp 15.000.000\nPublikasi & Marketing: Rp 8.000.000\nPerlengkapan: Rp 7.000.000\nOperasional Panitia: Rp 5.000.000\nTotal: Rp 80.000.000', 7)}
            ${inp('total_anggaran_event', 'Total Anggaran yang Dibutuhkan', 'Contoh: Rp 80.000.000')}
            ${sectionTitle('Paket Sponsorship')}
            ${txt('paket_sponsorship', 'Paket Sponsorship', 'Contoh:\n[PLATINUM – Rp 20.000.000]\n✓ Logo di semua media promosi\n✓ Booth display 3x3m\n✓ Slot presentasi 15 menit\n✓ 10 tiket VIP\n\n[GOLD – Rp 10.000.000]\n✓ Logo di backdrop\n✓ Booth display 2x2m\n✓ 5 tiket\n\n[SILVER – Rp 5.000.000]\n✓ Logo di backdrop\n✓ 2 tiket', 8)}
            ${txt('manfaat_sponsor', 'Benefit untuk Sponsor', 'Uraikan keuntungan yang didapatkan sponsor dari mendukung acara ini.', 3)}
            ${inp('deadline_sponsor', 'Batas Waktu Konfirmasi Sponsor', 'Contoh: 1 Juli 2025')}
        </div>`;
    }

    /* ── Helper: Checkbox item ── */
    function checkItem(id, label, sub, checked = false) {
        return `<label class="pb-check-item${checked ? ' checked' : ''}" for="${id}">
            <input type="checkbox" id="${id}" name="${id}" ${checked ? 'checked' : ''}>
            <div class="pb-check-box"><i class="fa-solid fa-check" style="font-size:9px;"></i></div>
            <div class="pb-check-info">
                <div class="pb-check-label">${label}</div>
                <div class="pb-check-sub">${sub}</div>
            </div>
        </label>`;
    }

    /* ══════════════════════════════════════
       LOGO UPLOAD HANDLING
    ══════════════════════════════════════ */
    function renderLogoUpload() {
        const zone = document.getElementById('logo-upload-zone');
        const fileInput = document.getElementById('logo-file-input');
        if (!zone || !fileInput) return;

        zone.addEventListener('click', (e) => {
            if (!e.target.closest('.pb-logo-remove')) fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) { showToast('File terlalu besar (maks 5MB)', 'error'); return; }
            const reader = new FileReader();
            reader.onload = (ev) => {
                state.logoBase64 = ev.target.result;
                const img = document.getElementById('logo-preview-img');
                if (img) { img.src = ev.target.result; }
                zone.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        });
    }

    window.PB = window.PB || {};
    window.PB.removeLogo = function (e) {
        e.stopPropagation();
        state.logoBase64 = null;
        const zone = document.getElementById('logo-upload-zone');
        const fileInput = document.getElementById('logo-file-input');
        if (zone) zone.classList.remove('has-image');
        if (fileInput) fileInput.value = '';
    };

    /* ══════════════════════════════════════
       STEP NAVIGATION
    ══════════════════════════════════════ */
    function initStepInteractions() {
        // Checkbox items
        document.querySelectorAll('.pb-check-item').forEach(item => {
            const chk = item.querySelector('input[type="checkbox"]');
            if (!chk) return;
            item.addEventListener('click', (e) => {
                if (e.target === chk) return;
                e.preventDefault();
                chk.checked = !chk.checked;
                item.classList.toggle('checked', chk.checked);
            });
        });
    }

    function collectCurrentStepData() {
        const panel = document.querySelector('.pb-step-panel.active');
        if (!panel) return;

        panel.querySelectorAll('input, textarea, select').forEach(el => {
            if (!el.id || el.type === 'file') return;
            if (el.type === 'checkbox') {
                state.formData[el.id] = el.checked;
            } else {
                state.formData[el.id] = el.value.trim();
            }
        });
    }

    function goToStep(step) {
        const tpl = TEMPLATES[state.template];
        if (!tpl) return;
        if (step < 0 || step >= tpl.steps.length) return;

        collectCurrentStepData();

        state.currentStep = step;

        // Update panels
        document.querySelectorAll('.pb-step-panel').forEach((p, i) => {
            p.classList.toggle('active', i === step);
        });

        // Update sidebar steps
        document.querySelectorAll('.pb-step-item').forEach((item, i) => {
            item.classList.remove('active', 'done');
            if (i === step) item.classList.add('active');
            else if (i < step) item.classList.add('done');
        });

        // Update nav buttons
        const backBtn = document.getElementById('wizard-back');
        const nextBtn = document.getElementById('wizard-next');
        const isLast = step === tpl.steps.length - 1;

        if (backBtn) backBtn.style.display = step === 0 ? 'none' : 'inline-flex';
        if (nextBtn) {
            nextBtn.innerHTML = isLast
                ? '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Proposal'
                : '<span>Lanjut</span><i class="fa-solid fa-arrow-right text-xs"></i>';
            nextBtn.className = 'pb-nav-next' + (isLast ? ' pb-nav-generate' : '');
        }

        // Re-init interactions for new panel
        initStepInteractions();
        renderLogoUpload();
        updateProgress();

        // Scroll to top of form
        document.getElementById('pb-main-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function nextStep() {
        const tpl = TEMPLATES[state.template];
        if (!tpl) return;
        const isLast = state.currentStep === tpl.steps.length - 1;
        if (isLast) {
            collectCurrentStepData();
            startGeneration();
        } else {
            goToStep(state.currentStep + 1);
        }
    }

    function prevStep() {
        if (state.currentStep > 0) goToStep(state.currentStep - 1);
    }

    window.PB.goToStep = goToStep;

    function updateProgress() {
        const tpl = TEMPLATES[state.template];
        if (!tpl) return;
        const pct = ((state.currentStep + 1) / tpl.steps.length) * 100;
        const fill = document.getElementById('pb-progress-fill');
        const label = document.getElementById('pb-progress-label');
        if (fill) fill.style.width = pct + '%';
        if (label) label.textContent = `Langkah ${state.currentStep + 1} dari ${tpl.steps.length}`;
    }

    /* ══════════════════════════════════════
       AI GENERATION
    ══════════════════════════════════════ */
    function startGeneration() {
        showView('generating');
        runGeneratingAnimation();
        generateProposal();
    }

    const GEN_STEPS = [
        { id: 'gs1', text: 'Memvalidasi data inputan…' },
        { id: 'gs2', text: 'Membangun konteks proposal…' },
        { id: 'gs3', text: 'AI sedang menulis konten…' },
        { id: 'gs4', text: 'Menyusun format & struktur…' },
        { id: 'gs5', text: 'Menyiapkan tampilan preview…' },
    ];

    function runGeneratingAnimation() {
        let i = 0;
        const progress = document.getElementById('pb-gen-progress-fill');
        const list = document.getElementById('pb-gen-steps');
        list.innerHTML = GEN_STEPS.map(s =>
            `<div class="pb-gen-step" id="${s.id}"><div class="pb-gen-step-dot"></div><span>${s.text}</span></div>`
        ).join('');

        function tick() {
            if (i >= GEN_STEPS.length) return;
            const el = document.getElementById(GEN_STEPS[i].id);
            if (el) el.classList.add('running');
            if (i > 0) {
                const prev = document.getElementById(GEN_STEPS[i-1].id);
                if (prev) { prev.classList.remove('running'); prev.classList.add('done'); }
            }
            const pct = Math.round(((i + 1) / GEN_STEPS.length) * 90);
            if (progress) progress.style.width = pct + '%';
            i++;
        }

        tick();
        window._genInterval = setInterval(tick, 2000);
    }

    function clearGeneratingAnimation() {
        clearInterval(window._genInterval);
        const progress = document.getElementById('pb-gen-progress-fill');
        if (progress) progress.style.width = '100%';
        document.querySelectorAll('.pb-gen-step').forEach(el => {
            el.classList.remove('running');
            el.classList.add('done');
        });
    }

    async function generateProposal() {
        try {
            const payload = buildPayload();
            const resp = await fetch(GENERATE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            if (!resp.ok) throw new Error('Server error: ' + resp.status);
            const data = await resp.json();

            if (!data.html) throw new Error('Respons tidak valid dari server.');

            clearGeneratingAnimation();
            await sleep(500);
            state.generatedHTML = data.html;
            state.cacheKey = data.cache_key || null;
            renderPreview(data.html);
            showView('preview');
            showToast('Proposal berhasil dibuat!');
        } catch (err) {
            clearGeneratingAnimation();
            showToast('Gagal generate: ' + err.message, 'error');
            showView('wizard');
            goToStep(state.currentStep);
        }
    }

    function buildPayload() {
        return {
            template: state.template,
            template_name: state.templateName,
            logo: state.logoBase64,
            form_data: state.formData,
        };
    }

    /* ══════════════════════════════════════
       PREVIEW
    ══════════════════════════════════════ */
    function renderPreview(html) {
        const frame = document.getElementById('pb-preview-frame');
        if (!frame) return;
        const doc = frame.contentDocument || frame.contentWindow.document;
        doc.open();
        doc.write(html);
        doc.close();

        const title = state.formData.judul_proposal
            || state.formData.judul_proyek
            || state.formData.nama_acara
            || state.formData.nama_bisnis
            || state.templateName;
        const el = document.getElementById('pb-preview-title-text');
        if (el) el.textContent = title;
    }

    /* ══════════════════════════════════════
       DOWNLOAD — server-side DOCX / PDF via LibreOffice
    ══════════════════════════════════════ */

    function setDlBtnLoading(btn, loading, label) {
        if (!btn) return;
        btn.disabled = loading;
        btn.innerHTML = loading
            ? '<i class="fa-solid fa-spinner fa-spin"></i> <span>' + label + '</span>'
            : btn._originalHTML;
    }

    async function triggerServerDownload(format) {
        if (!state.generatedHTML) return showToast('Belum ada proposal yang di-generate.', 'error');

        const btnId    = format === 'docx' ? 'pb-btn-dl-word' : 'pb-btn-dl-pdf';
        const btn      = document.getElementById(btnId);
        const label    = format === 'docx' ? 'Menyiapkan DOCX...' : 'Menyiapkan PDF...';

        if (btn && !btn._originalHTML) btn._originalHTML = btn.innerHTML;
        setDlBtnLoading(btn, true, label);

        try {
            const resp = await fetch(DOWNLOAD_URL, {
                method : 'POST',
                headers: {
                    'Content-Type' : 'application/json',
                    'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept'       : 'application/json',
                },
                body: JSON.stringify({
                    cache_key : state.cacheKey,
                    format    : format,
                    html      : state.generatedHTML,   // fallback if cache expired
                }),
            });

            const data = await resp.json();
            if (!resp.ok || !data.success) {
                throw new Error(data.error || 'Konversi gagal di server.');
            }

            // Immediately redirect browser to serve the file
            window.location.href = SERVE_URL + '/' + data.token;
            showToast('Berhasil! File sedang diunduh.');

        } catch (err) {
            showToast('Download gagal: ' + err.message, 'error');
        } finally {
            setTimeout(() => setDlBtnLoading(btn, false, ''), 2500);
        }
    }

    function downloadWord() { triggerServerDownload('docx'); }
    function downloadPDF()  { triggerServerDownload('pdf');  }

    function getFileName(ext) {
        const base = (state.formData.judul_proposal
            || state.formData.judul_proyek
            || state.formData.nama_acara
            || state.formData.nama_bisnis
            || 'proposal'
        ).replace(/[^a-zA-Z0-9\s]/g, '').trim().replace(/\s+/g, '_').substring(0, 40);
        return `Proposal_${base}.${ext}`;
    }

    /* ══════════════════════════════════════
       TOAST
    ══════════════════════════════════════ */
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('pb-toast');
        const msgEl = document.getElementById('pb-toast-msg');
        const ico = document.getElementById('pb-toast-ico');
        if (!toast) return;
        msgEl.textContent = msg;
        ico.className = type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check';
        ico.style.color = type === 'error' ? '#f87171' : 'var(--accent)';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    /* ── Utils ── */
    function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

    /* ══════════════════════════════════════
       INIT
    ══════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', () => {
        showView('landing');

        // Template card clicks
        document.querySelectorAll('.pb-tpl-card').forEach(card => {
            card.addEventListener('click', () => selectTemplate(card.dataset.template));
        });

        // Sidebar back
        document.getElementById('sb-back-btn')?.addEventListener('click', () => {
            state.template = null;
            showView('landing');
        });

        // Wizard navigation
        document.getElementById('wizard-next')?.addEventListener('click', nextStep);
        document.getElementById('wizard-back')?.addEventListener('click', prevStep);

        // Preview buttons
        document.getElementById('pb-btn-edit')?.addEventListener('click', () => {
            showView('wizard');
            goToStep(state.currentStep);
        });
        document.getElementById('pb-btn-new')?.addEventListener('click', () => {
            state.template = null;
            state.formData = {};
            state.logoBase64 = null;
            state.generatedHTML = '';
            showView('landing');
        });
        document.getElementById('pb-btn-dl-word')?.addEventListener('click', downloadWord);
        document.getElementById('pb-btn-dl-pdf')?.addEventListener('click', downloadPDF);

        // Expose for global use
        window.PB = Object.assign(window.PB, { selectTemplate, nextStep, prevStep, downloadWord, downloadPDF, triggerServerDownload, showToast });
    });
})();