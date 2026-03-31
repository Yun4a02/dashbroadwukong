<?php
// --- KONFIGURASI DATABASE ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "wukong_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// --- LOGIC PHP UNTUK AJAX REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_folder') {
        $name = strtoupper($conn->real_escape_string($_POST['name']));
        $conn->query("INSERT INTO folders (folder_name) VALUES ('$name')");
        echo $conn->insert_id;
    } 
    elseif ($action === 'add_report') {
        $f_id = $_POST['folder_id'];
        $title = strtoupper($conn->real_escape_string($_POST['title']));
        $content = $conn->real_escape_string($_POST['content']);
        $conn->query("INSERT INTO reports (folder_id, title, content) VALUES ('$f_id', '$title', '$content')");
    }
    elseif ($action === 'update_report') {
        $id = $_POST['id'];
        $content = $conn->real_escape_string($_POST['content']);
        $conn->query("UPDATE reports SET content = '$content' WHERE id = $id");
    }
    elseif ($action === 'update_btn_settings') {
        $id = $_POST['id'];
        $label = strtoupper($conn->real_escape_string($_POST['label']));
        $custom = $conn->real_escape_string($_POST['custom']);
        $conn->query("UPDATE reports SET btn_label = '$label', custom_report_text = '$custom' WHERE id = $id");
    }
    elseif ($action === 'delete_report') {
        $id = $_POST['id'];
        $conn->query("DELETE FROM reports WHERE id = $id");
    }
    elseif ($action === 'delete_folder') {
        $id = $_POST['id'];
        $conn->query("DELETE FROM folders WHERE id = $id");
    }
    elseif ($action === 'update_lc') {
        $incT = $conn->real_escape_string($_POST['incText']);
        $incLC = $conn->real_escape_string($_POST['incLinkCoding']);
        $incLL = $conn->real_escape_string($_POST['incLinkLivechat']);
        $incE = $conn->real_escape_string($_POST['incEmail']);
        $incP = $conn->real_escape_string($_POST['incPass']);
        
        $tawT = $conn->real_escape_string($_POST['tawkText']);
        $tawLC = $conn->real_escape_string($_POST['tawkLinkCoding']);
        $tawLL = $conn->real_escape_string($_POST['tawkLinkLivechat']);
        $tawE = $conn->real_escape_string($_POST['tawkEmail']);
        $tawP = $conn->real_escape_string($_POST['tawkPass']);
        
        $conn->query("UPDATE live_chat SET 
            inc_text='$incT', inc_link_coding='$incLC', inc_link_livechat='$incLL', inc_email='$incE', inc_pass='$incP', 
            tawk_text='$tawT', tawk_link_coding='$tawLC', tawk_link_livechat='$tawLL', tawk_email='$tawE', tawk_pass='$tawP' 
            WHERE id=1");
    }
    exit;
}

// --- AMBIL DATA AWAL ---
$folders_query = $conn->query("SELECT * FROM folders ORDER BY id DESC");
$dataStore = [];
while ($f = $folders_query->fetch_assoc()) {
    $f_id = $f['id'];
    $reports_query = $conn->query("SELECT * FROM reports WHERE folder_id = $f_id ORDER BY id DESC");
    $reports = [];
    while ($r = $reports_query->fetch_assoc()) {
        $reports[] = [
            'id' => $r['id'], 'title' => $r['title'], 'content' => $r['content'],
            'btnLabel' => $r['btn_label'], 'custom_report_text' => $r['custom_report_text']
        ];
    }
    $dataStore[] = ['db_id' => $f['id'], 'folderName' => $f['folder_name'], 'reports' => $reports];
}

$lc_query = $conn->query("SELECT * FROM live_chat WHERE id=1");
$lc = $lc_query->fetch_assoc();
$liveChatData = [
    'incText' => $lc['inc_text'] ?? '', 
    'incLinkCoding' => $lc['inc_link_coding'] ?? '', 
    'incLinkLivechat' => $lc['inc_link_livechat'] ?? '', 
    'incEmail' => $lc['inc_email'] ?? '', 
    'incPass' => $lc['inc_pass'] ?? '',
    'tawkText' => $lc['tawk_text'] ?? '', 
    'tawkLinkCoding' => $lc['tawk_link_coding'] ?? '', 
    'tawkLinkLivechat' => $lc['tawk_link_livechat'] ?? '', 
    'tawkEmail' => $lc['tawk_email'] ?? '', 
    'tawkPass' => $lc['tawk_pass'] ?? ''
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORT 007 - WUKONG SYSTEM</title>
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@400;700&family=Chakra+Petch:wght@300;400;500;600;700&family=Michroma&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #1a1a1a; 
            --card-bg: #0f0f0f;
            --luxury-gold: #d4af37;
            --gold-glow: rgba(212, 175, 55, 0.6);
            --border-ui: rgba(212, 175, 55, 0.15);
            --neon-red: #ff3c00;
            --red-glow: rgba(255, 60, 0, 0.5);
            --neon-green: #00ff00;
            --font-main: 'Syncopate', sans-serif;
            --font-ui: 'Chakra Petch', sans-serif;
        }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--luxury-gold); border-radius: 10px; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-weight: 700 !important; -webkit-font-smoothing: antialiased; }
        body {
            background-color: var(--bg-dark);
            background-image: radial-gradient(circle at 50% 50%, #252525 0%, #121212 100%);
            color: white; font-family: var(--font-ui); min-height: 100vh; padding: 20px 40px; overflow-x: hidden;
        }
        @keyframes letterFlicker {
            0%, 18%, 22%, 25%, 53%, 57%, 100% { text-shadow: 0 0 10px var(--gold-glow), 0 0 20px var(--luxury-gold); color: var(--luxury-gold); opacity: 1; }
            20%, 24%, 55% { text-shadow: none; color: #333; opacity: 0.3; }
        }
        @keyframes modalPop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .header { position: relative; display: flex; justify-content: center; align-items: center; padding: 5px 0 35px; margin-bottom: 15px; }
        .header h1 { font-family: var(--font-main); font-size: 24px; letter-spacing: 10px; text-transform: uppercase; }
        .header h1 span { display: inline-block; animation: letterFlicker 4s infinite; }
        .header-left-btns { position: absolute; top: 0; left: 0; display: flex; gap: 10px; z-index: 100; }
        .btn-add-folder { background: var(--luxury-gold); color: #000; border: none; width: 40px; height: 40px; border-radius: 8px; font-size: 22px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 15px var(--gold-glow); }
        .btn-add-folder:hover { transform: scale(1.05) rotate(90deg); background: #fff; }
        .btn-live-chat-toggle { background: #000; color: var(--luxury-gold); border: 1px solid var(--luxury-gold); padding: 0 15px; height: 40px; border-radius: 8px; font-family: var(--font-main); font-size: 9px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-live-chat-toggle::before { content: ''; width: 8px; height: 8px; background: var(--neon-green); border-radius: 50%; box-shadow: 0 0 8px var(--neon-green); }
        .folder-tabs-container { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid var(--border-ui); }
        .tab-item { background: rgba(0,0,0,0.4); border: 1px solid var(--border-ui); padding: 7px 15px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s; }
        .tab-item.active { background: var(--luxury-gold); border-color: #fff; color: #000; box-shadow: 0 0 15px var(--gold-glow); }
        .tab-text { font-size: 10px; font-weight: 900 !important; text-transform: uppercase; letter-spacing: 1px; }
        .report-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; }
        .report-card { background: var(--card-bg); border: 1px solid var(--border-ui); border-radius: 15px; padding: 15px; transition: 0.3s; position: relative; }
        .report-card:hover { border-color: var(--luxury-gold); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .card-header-hud { text-align: center; margin-bottom: 10px; }
        .card-title-display { color: var(--luxury-gold); font-size: 13px; font-family: var(--font-main); text-transform: uppercase; font-weight: 900 !important; }
        .card-content { background: #0a0a0a; border: 1px solid #1a1a1a; border-radius: 8px; padding: 10px; width: 100%; min-height: 110px; color: #ccc; font-family: var(--font-ui); font-size: 12px; resize: none; outline: none; margin-bottom: 12px; line-height: 1.5; }
        .card-footer { display: flex; gap: 6px; }
        .btn-card { padding: 10px 5px; font-family: var(--font-ui); font-size: 9px; cursor: pointer; border-radius: 6px; text-transform: uppercase; border: none; transition: 0.2s; outline: none; }
        .btn-copy-main { flex: 1; background: var(--luxury-gold); color: #000; font-weight: 900 !important; }
        .btn-copy-main:hover { background: #fff; }
        .report-btn-wrapper { position: relative; flex: 1; display: flex; }
        .btn-report { width: 100%; background: #000; color: var(--luxury-gold); border: 1px solid var(--luxury-gold) !important; font-weight: 900 !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 20px; }
        .btn-report:hover { background: var(--luxury-gold); color: #000; box-shadow: 0 0 10px var(--gold-glow); }
        .btn-dots-option { position: absolute; top: 50%; right: 5px; transform: translateY(-50%); background: transparent; border: none; color: rgba(212, 175, 55, 0.6); cursor: pointer; font-size: 14px; z-index: 5; padding: 5px; transition: 0.2s; }
        .btn-dots-option:hover { color: #fff; transform: translateY(-50%) scale(1.3); }
        .btn-edit-main { flex: 1; background: #111; border: 1px solid #333 !important; color: #777; }
        .btn-edit-main.active { background: var(--neon-green) !important; color: #000 !important; border-color: #fff !important; box-shadow: 0 0 10px var(--neon-green); }
        .btn-del-card { flex: 0.3; background: #111; border: 1px solid #400 !important; color: var(--neon-red); font-size: 14px; display: flex; align-items: center; justify-content: center; }
        .btn-del-card:hover { background: var(--neon-red); color: #fff; box-shadow: 0 0 10px var(--red-glow); }
        
        /* MODAL STYLES */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(10px); display: none; justify-content: center; align-items: center; z-index: 10000; }
        .modal-box { background: rgba(20, 20, 20, 0.85); border: 1px solid var(--luxury-gold); border-radius: 20px; padding: 30px; width: 450px; text-align: center; box-shadow: 0 0 50px rgba(0,0,0,0.8); animation: modalPop 0.2s ease-out; }
        .lc-modal-box { width: 1100px; max-width: 95vw; padding: 35px; border: 1px solid var(--luxury-gold); box-shadow: 0 0 30px rgba(212, 175, 55, 0.3); }
        .modal-input { width: 100%; background: rgba(0, 0, 0, 0.6); border: 1px solid var(--border-ui); border-left: 4px solid var(--luxury-gold); padding: 12px; color: white; font-family: var(--font-ui); font-size: 14px; outline: none; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        
        /* LIVE CHAT SPECIFIC NEON */
        .lc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .lc-table-container { 
            background: rgba(0,0,0,0.5); 
            border: 1px solid var(--luxury-gold); 
            border-radius: 12px; 
            padding: 20px; 
            text-align: center; 
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.2); /* Neon Effect */
        }
        .lc-row-link { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            background: #000; 
            border: 1px solid var(--luxury-gold); 
            border-left: 6px solid var(--luxury-gold); 
            padding: 8px 12px; 
            border-radius: 8px; 
            margin-bottom: 10px; 
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.1); /* Neon Effect Row */
        }
        .lc-label { color: var(--luxury-gold); font-size: 8px; font-family: var(--font-main); width: 100px; text-align: left; }
        .lc-input-link { background: transparent !important; border: none; color: #fff; width: 100%; font-size: 11px; outline: none; }
        
        /* TEXTAREA WRAPPER FOR COPY BUTTON */
        .textarea-container { position: relative; width: 100%; margin-top: 10px; }
        .btn-copy-corner { 
            position: absolute; 
            top: 5px; 
            right: 5px; 
            background: var(--luxury-gold); 
            color: #000; 
            border: none; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 8px; 
            cursor: pointer; 
            z-index: 10; 
            font-weight: 900 !important;
            transition: 0.2s;
        }
        .btn-copy-corner:hover { background: #fff; transform: scale(1.1); }

        /* NOTIF KEREN */
        #toast { 
            position: fixed; bottom: -100px; left: 50%; transform: translateX(-50%); 
            background: linear-gradient(135deg, var(--luxury-gold), #fff); 
            color: #000; padding: 12px 45px; border-radius: 10px; 
            font-family: var(--font-main); font-size: 10px; font-weight: 900 !important; 
            z-index: 100001; transition: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 0 30px var(--gold-glow); display: flex; align-items: center; gap: 10px;
        }
        #toast.show { bottom: 40px; }
    </style>
</head>
<body>

    <!-- MODAL INITIALIZE -->
    <div id="modalOverlay" class="modal-overlay">
        <div class="modal-box">
            <h2 style="color:var(--luxury-gold); font-family:Syncopate; margin-bottom:20px; font-size:12px; letter-spacing:2px;">INITIALIZE</h2>
            <input type="text" id="modalInput" class="modal-input" placeholder="NAMA FOLDER / JUDUL...">
            <textarea id="modalTextarea" class="modal-input" style="min-height:120px; text-align:left;" placeholder="DESKRIPSI / DATA..."></textarea>
            <div style="display:flex; gap:15px;">
                <button onclick="confirmModal()" class="btn-card" style="flex:1; background:var(--luxury-gold); color:#000; height:45px; font-family:Syncopate;">OK / CONFIRM</button>
                <button onclick="closeModal()" class="btn-card" style="flex:1; background:transparent; border:1px solid var(--neon-red); color:var(--neon-red); height:45px; font-family:Syncopate;">BATALKAN</button>
            </div>
        </div>
    </div>

    <!-- MODAL SETTING TOMBOL REPORT -->
    <div id="btnLabelModal" class="modal-overlay">
        <div class="modal-box" style="width: 420px;">
            <h2 style="color:var(--luxury-gold); font-family:Syncopate; margin-bottom:20px; font-size:10px; letter-spacing:2px;">CUSTOM REPORT BUTTON</h2>
            <label style="color:var(--luxury-gold); font-size:9px; display:block; text-align:left; margin-bottom:5px; font-family:Syncopate;">NAMA PADA TOMBOL :</label>
            <input type="text" id="newBtnLabel" class="modal-input" placeholder="Contoh: ONLINE / NORMAL">
            <label style="color:var(--luxury-gold); font-size:9px; display:block; text-align:left; margin-bottom:5px; font-family:Syncopate;">TEKS SAAT DI COPY :</label>
            <textarea id="newCustomReportText" class="modal-input" style="min-height:80px; text-align:left;" placeholder="Kosongkan jika ingin deteksi otomatis..."></textarea>
            <div style="display:flex; gap:15px;">
                <button onclick="saveBtnSettings()" class="btn-card" style="flex:1; background:var(--luxury-gold); color:#000; height:40px; font-family:Syncopate;">SAVE COMMAND</button>
                <button onclick="closeBtnLabelModal()" class="btn-card" style="flex:1; background:transparent; border:1px solid #444!important; color:#888; height:40px; font-family:Syncopate;">BATAL</button>
            </div>
        </div>
    </div>

    <!-- MODAL DELETE -->
    <div id="deleteModalOverlay" class="modal-overlay">
        <div class="modal-box" style="border-color:var(--neon-red); width:380px;">
            <h2 style="color:var(--neon-red); font-family:Syncopate; margin-bottom:15px; font-size:12px;">PERINGATAN</h2>
            <p id="deleteMsg" style="font-size:11px; margin-bottom:25px; color:#fff; font-family:Syncopate;">Hapus data secara permanen?</p>
            <div style="display:flex; gap:10px;">
                <button id="btnConfirmDelete" onclick="executeDelete()" class="btn-card" style="flex:1; background:var(--neon-red); color:#fff; height:45px; font-family:Syncopate;">YA, HAPUS</button>
                <button id="btnCancelDelete" onclick="closeDeleteModal()" class="btn-card" style="flex:1; background:transparent; border:1px solid #444!important; color:#888; height:45px; font-family:Syncopate;">BATAL</button>
            </div>
        </div>
    </div>

    <!-- MODAL LIVE CHAT -->
    <div id="liveChatModal" class="modal-overlay">
        <div class="modal-box lc-modal-box">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                <h2 style="color:var(--luxury-gold); font-family:Syncopate; font-size:13px; letter-spacing:3px;">LIVE CHAT LOGIN CENTER</h2>
                <button onclick="toggleLiveChat()" style="background:transparent; border:none; color:#555; font-size:32px; cursor:pointer;">&times;</button>
            </div>
            <div class="lc-grid">
                <!-- LC INC -->
                <div class="lc-table-container">
                    <div style="color:var(--luxury-gold); font-family:Syncopate; font-size:11px; margin-bottom:15px;">LIVE CHAT INC</div>
                    
                    <div class="lc-row-link"><span class="lc-label">Link Coding :</span><input type="text" id="lc-inc-link-coding" class="lc-input-link" readonly><button onclick="copySpecific('lc-inc-link-coding')" style="background:var(--luxury-gold); border:none; padding:4px 8px; border-radius:4px; font-size:8px; cursor:pointer; color:#000;">COPY</button></div>
                    
                    <!-- TEXTAREA DENGAN TOMBOL COPY DI POJOK -->
                    <div class="textarea-container">
                        <button class="btn-copy-corner" onclick="copySpecific('lc-inc-text')">COPY</button>
                        <textarea id="lc-inc-text" class="card-content" style="min-height:100px; padding-top:25px;" readonly placeholder="Description..."></textarea>
                    </div>
                    
                    <div class="lc-row-link"><span class="lc-label">Link livechat :</span><input type="text" id="lc-inc-link-livechat" class="lc-input-link" readonly><button onclick="copySpecific('lc-inc-link-livechat')" style="background:var(--luxury-gold); border:none; padding:4px 8px; border-radius:4px; font-size:8px; cursor:pointer; color:#000;">COPY</button></div>
                    <div class="lc-row-link"><span class="lc-label">EMAIL :</span><input type="text" id="lc-inc-email" class="lc-input-link" readonly><button onclick="copySpecific('lc-inc-email')" style="background:var(--luxury-gold); border:none; padding:4px 8px; border-radius:4px; font-size:8px; cursor:pointer; color:#000;">COPY</button></div>
                    <div class="lc-row-link"><span class="lc-label">PASS :</span><input type="text" id="lc-inc-pass" class="lc-input-link" readonly><button onclick="copySpecific('lc-inc-pass')" style="background:var(--luxury-gold); border:none; padding:4px 8px; border-radius:4px; font-size:8px; cursor:pointer; color:#000;">COPY</button></div>
                    
                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <button class="btn-card btn-copy-main" style="flex:1; height:40px;" onclick="copySpecific('lc-inc-text')">COPY DESC</button>
                        <button class="btn-card btn-edit-main" id="btn-edit-inc" style="flex:1; height:40px;" onclick="editLC('inc')">EDIT DATA</button>
                    </div>
                </div>

                <!-- LC TAWKTO -->
                <div class="lc-table-container">
                    <div style="color:var(--luxury-gold); font-family:Syncopate; font-size:11px; margin-bottom:15px;">LIVE CHAT TAWKTO</div>
                    
                    <div class="lc-row-link"><span class="lc-label">Link Coding :</span><input type="text" id="lc-tawk-link-coding" class="lc-input-link" readonly><button onclick="copySpecific('lc-tawk-link-coding')" style="background:var(--luxury-gold); border:none; padding:4px 8px; border-radius:4px; font-size:8px; cursor:pointer; color:#000;">COPY</button></div>
                    
                    <!-- TEXTAREA DENGAN TOMBOL COPY DI POJOK -->
                    <div class="textarea-container">
                        <button class="btn-copy-corner" onclick="copySpecific('lc-tawk-text')">COPY</button>
                        <textarea id="lc-tawk-text" class="card-content" style="min-height:100px; padding-top:25px;" readonly placeholder="Description..."></textarea>
                    </div>
                    
                    <div class="lc-row-link"><span class="lc-label">Link livechat :</span><input type="text" id="lc-tawk-link-livechat" class="lc-input-link" readonly><button onclick="copySpecific('lc-tawk-link-livechat')" style="background:var(--luxury-gold); border:none; padding:4px 8px; border-radius:4px; font-size:8px; cursor:pointer; color:#000;">COPY</button></div>
                    <div class="lc-row-link"><span class="lc-label">EMAIL :</span><input type="text" id="lc-tawk-email" class="lc-input-link" readonly><button onclick="copySpecific('lc-tawk-email')" style="background:var(--luxury-gold); border:none; padding:4px 8px; border-radius:4px; font-size:8px; cursor:pointer; color:#000;">COPY</button></div>
                    <div class="lc-row-link"><span class="lc-label">PASS :</span><input type="text" id="lc-tawk-pass" class="lc-input-link" readonly><button onclick="copySpecific('lc-tawk-pass')" style="background:var(--luxury-gold); border:none; padding:4px 8px; border-radius:4px; font-size:8px; cursor:pointer; color:#000;">COPY</button></div>
                    
                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <button class="btn-card btn-copy-main" style="flex:1; height:40px;" onclick="copySpecific('lc-tawk-text')">COPY DESC</button>
                        <button class="btn-card btn-edit-main" id="btn-edit-tawk" style="flex:1; height:40px;" onclick="editLC('tawk')">EDIT DATA</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HEADER -->
    <div class="header">
        <div class="header-left-btns">
            <button class="btn-add-folder" onclick="openFolderModal()">+</button>
            <button class="btn-live-chat-toggle" onclick="toggleLiveChat()">LIVE CHAT</button>
        </div>
        <h1 id="mainTitle"></h1>
    </div>

    <div id="folderTabs" class="folder-tabs-container"></div>
    <div id="contentSection" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; background:rgba(212,175,55,0.05); padding:10px 20px; border-radius:10px; border-left:4px solid var(--luxury-gold);">
            <div id="activeFolderName" style="font-family:Syncopate; font-size:11px; color:var(--luxury-gold);">DIRECTORY</div>
            <div style="display:flex; gap:10px; align-items:center;">
                <button style="background:var(--luxury-gold); border:none; padding:8px 15px; border-radius:6px; font-family:Syncopate; font-size:9px; cursor:pointer; color:#000;" onclick="openReportModal()">+ NEW REPORT</button>
                <button onclick="reqDeleteFolder()" style="background:transparent; border:none; color:var(--neon-red); cursor:pointer; font-size:18px;">🗑</button>
            </div>
        </div>
        <div id="reportGrid" class="report-grid"></div>
    </div>

    <!-- NOTIF KEREN -->
    <div id="toast">
        <span style="font-size:20px">✨</span>
        <span id="toastMsg">SYSTEM NOTIFIED</span>
    </div>

    <script>
        let dataStore = <?php echo json_encode($dataStore); ?>;
        let liveChatData = <?php echo json_encode($liveChatData); ?>;
        let activeIndex = null;
        let currentMode = '';
        let deleteTarget = { type: '', index: null };
        let currentEditBtnIdx = null; 

        function flickerify(text) {
            return Array.from(text).map(char => {
                let delay = (Math.random() * 2.5).toFixed(1);
                return `<span style="animation-delay: ${delay}s;">${char === ' ' ? '&nbsp;' : char}</span>`;
            }).join('');
        }

        async function phpAction(data) {
            let formData = new FormData();
            for (let key in data) formData.append(key, data[key]);
            await fetch(window.location.href, { method: 'POST', body: formData });
            location.reload();
        }

        function toggleLiveChat() {
            const m = document.getElementById('liveChatModal');
            m.style.display = (m.style.display === 'flex') ? 'none' : 'flex';
            if (m.style.display === 'flex') renderLiveChat();
        }

        function renderLiveChat() {
            document.getElementById('lc-inc-text').value = liveChatData.incText;
            document.getElementById('lc-inc-link-coding').value = liveChatData.incLinkCoding;
            document.getElementById('lc-inc-link-livechat').value = liveChatData.incLinkLivechat;
            document.getElementById('lc-inc-email').value = liveChatData.incEmail;
            document.getElementById('lc-inc-pass').value = liveChatData.incPass;
            
            document.getElementById('lc-tawk-text').value = liveChatData.tawkText;
            document.getElementById('lc-tawk-link-coding').value = liveChatData.tawkLinkCoding;
            document.getElementById('lc-tawk-link-livechat').value = liveChatData.tawkLinkLivechat;
            document.getElementById('lc-tawk-email').value = liveChatData.tawkEmail;
            document.getElementById('lc-tawk-pass').value = liveChatData.tawkPass;
        }

        function editLC(t) {
            const txt = document.getElementById(`lc-${t}-text`), 
                  lncC = document.getElementById(`lc-${t}-link-coding`), 
                  lncL = document.getElementById(`lc-${t}-link-livechat`), 
                  eml = document.getElementById(`lc-${t}-email`), 
                  pas = document.getElementById(`lc-${t}-pass`), 
                  btn = document.getElementById(`btn-edit-${t}`);
            
            if (txt.readOnly) {
                txt.readOnly = false; lncC.readOnly = false; lncL.readOnly = false; eml.readOnly = false; pas.readOnly = false;
                btn.innerText = "SAVE DATA"; btn.classList.add('active'); txt.focus();
            } else {
                phpAction({
                    action: 'update_lc',
                    incText: document.getElementById('lc-inc-text').value,
                    incLinkCoding: document.getElementById('lc-inc-link-coding').value,
                    incLinkLivechat: document.getElementById('lc-inc-link-livechat').value,
                    incEmail: document.getElementById('lc-inc-email').value,
                    incPass: document.getElementById('lc-inc-pass').value,
                    tawkText: document.getElementById('lc-tawk-text').value,
                    tawkLinkCoding: document.getElementById('lc-tawk-link-coding').value,
                    tawkLinkLivechat: document.getElementById('lc-tawk-link-livechat').value,
                    tawkEmail: document.getElementById('lc-tawk-email').value,
                    tawkPass: document.getElementById('lc-tawk-pass').value
                });
                showToast("DATA SAVED TO SERVER");
            }
        }

        function selectFolder(index) {
            activeIndex = index;
            localStorage.setItem('wukong_last_active_index', index);
            renderTabs(); renderContent();
        }

        function renderTabs() {
            const container = document.getElementById('folderTabs');
            container.innerHTML = '';
            dataStore.forEach((folder, index) => {
                const tab = document.createElement('div');
                tab.className = `tab-item ${activeIndex === index ? 'active' : ''}`;
                tab.onclick = () => { selectFolder(index); };
                tab.innerHTML = `<span style="font-size:14px;">📁</span><span class="tab-text">${folder.folderName}</span>`;
                container.appendChild(tab);
            });
        }

        function renderContent() {
            const section = document.getElementById('contentSection'), grid = document.getElementById('reportGrid'), nameDisp = document.getElementById('activeFolderName');
            if (activeIndex === null || !dataStore[activeIndex]) { section.style.display = 'none'; return; }
            section.style.display = 'block'; nameDisp.innerText = dataStore[activeIndex].folderName; grid.innerHTML = '';
            dataStore[activeIndex].reports.forEach((repo, rIndex) => {
                const card = document.createElement('div');
                card.className = 'report-card';
                card.innerHTML = `
                    <div class="card-header-hud"><div class="card-title-display">${repo.title}</div></div>
                    <textarea class="card-content" id="content-${rIndex}" readonly>${repo.content}</textarea>
                    <div class="card-footer">
                        <button class="btn-card btn-copy-main" onclick="copyCard(${rIndex})">COPY</button>
                        <div class="report-btn-wrapper">
                            <button class="btn-card btn-report" onclick="smartReport(${rIndex})">${repo.btnLabel || 'REPORT'}</button>
                            <button class="btn-dots-option" onclick="openBtnSettings(${rIndex})">⋮</button>
                        </div>
                        <button class="btn-card btn-edit-main" id="btn-edit-${rIndex}" onclick="toggleEditCard(${rIndex})">EDIT</button>
                        <button class="btn-del-card" onclick="reqDeleteCard(${rIndex})">🗑</button>
                    </div>`;
                grid.appendChild(card);
            });
        }

        function openBtnSettings(idx) {
            currentEditBtnIdx = idx;
            const repo = dataStore[activeIndex].reports[idx];
            document.getElementById('newBtnLabel').value = repo.btnLabel || "REPORT";
            document.getElementById('newCustomReportText').value = repo.customReportText || "";
            document.getElementById('btnLabelModal').style.display = 'flex';
        }

        function closeBtnLabelModal() { document.getElementById('btnLabelModal').style.display = 'none'; }
        function saveBtnSettings() {
            phpAction({ action: 'update_btn_settings', id: dataStore[activeIndex].reports[currentEditBtnIdx].id, label: document.getElementById('newBtnLabel').value, custom: document.getElementById('newCustomReportText').value });
        }

        async function smartReport(rIndex) {
            const repo = dataStore[activeIndex].reports[rIndex];
            let res = repo.customReportText && repo.customReportText.trim() !== "" ? repo.customReportText : `saat ini ${repo.title.toUpperCase()} NORMAL `;
            navigator.clipboard.writeText(res); showToast("REPORT COPIED");
        }

        function openFolderModal() { currentMode = 'folder'; document.getElementById('modalInput').value = ""; document.getElementById('modalTextarea').style.display = 'none'; document.getElementById('modalOverlay').style.display = 'flex'; }
        function openReportModal() { currentMode = 'report'; document.getElementById('modalInput').value = ""; document.getElementById('modalTextarea').value = ""; document.getElementById('modalTextarea').style.display = 'block'; document.getElementById('modalOverlay').style.display = 'flex'; }
        function closeModal() { document.getElementById('modalOverlay').style.display = 'none'; }
        function confirmModal() {
            const t = document.getElementById('modalInput').value, c = document.getElementById('modalTextarea').value;
            if(!t) return;
            if(currentMode === 'folder') phpAction({ action: 'add_folder', name: t });
            else phpAction({ action: 'add_report', folder_id: dataStore[activeIndex].db_id, title: t, content: c });
        }

        function reqDeleteCard(idx) { deleteTarget = { type: 'card', index: idx }; document.getElementById('deleteMsg').innerText = "HAPUS REPORT INI?"; document.getElementById('deleteModalOverlay').style.display = 'flex'; }
        function reqDeleteFolder() { deleteTarget = { type: 'folder', index: activeIndex }; document.getElementById('deleteMsg').innerText = "HAPUS SELURUH FOLDER?"; document.getElementById('deleteModalOverlay').style.display = 'flex'; }
        function closeDeleteModal() { document.getElementById('deleteModalOverlay').style.display = 'none'; }
        function executeDelete() {
            if (deleteTarget.type === 'card') phpAction({ action: 'delete_report', id: dataStore[activeIndex].reports[deleteTarget.index].id });
            else phpAction({ action: 'delete_folder', id: dataStore[activeIndex].db_id });
        }

        function toggleEditCard(rIndex) {
            const cInp = document.getElementById(`content-${rIndex}`), btn = document.getElementById(`btn-edit-${rIndex}`);
            if (cInp.readOnly) { cInp.readOnly = false; btn.innerText = "SAVE"; btn.classList.add('active'); } 
            else phpAction({ action: 'update_report', id: dataStore[activeIndex].reports[rIndex].id, content: cInp.value });
        }

        function copyCard(idx) { navigator.clipboard.writeText(dataStore[activeIndex].reports[idx].content); showToast("CONTENT COPIED"); }
        function copySpecific(id) { 
            const val = document.getElementById(id).value;
            if(!val) return;
            navigator.clipboard.writeText(val); 
            showToast("COPIED TO CLIPBOARD"); 
        }

        function showToast(m) { 
            const t = document.getElementById('toast'), msg = document.getElementById('toastMsg'); 
            msg.innerText = m; t.classList.add('show'); 
            setTimeout(() => t.classList.remove('show'), 2500); 
        }

        window.onload = () => { 
            document.getElementById('mainTitle').innerHTML = flickerify("REPORT 007"); 
            const lastIdx = localStorage.getItem('wukong_last_active_index');
            if(lastIdx !== null && dataStore[lastIdx]) activeIndex = parseInt(lastIdx);
            renderTabs(); renderContent();
        };
    </script>
</body>
</html>