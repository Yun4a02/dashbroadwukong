<?php
include 'koneksi.php';

// --- API HANDLER ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    // Ambil Semua Data
    if ($_GET['action'] == 'fetch') {
        $search = isset($_GET['query']) ? "%" . $_GET['query'] . "%" : "%%";
        $stmt = $conn->prepare("SELECT * FROM personal_data WHERE judul LIKE ? OR email LIKE ? ORDER BY id DESC");
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
    }

    // Simpan atau Update Data
    if ($_GET['action'] == 'save') {
        $id = $_POST['id'];
        $judul = strtoupper($_POST['judul']);
        $link = $_POST['link'];
        $email = $_POST['email'];
        $pass = $_POST['pass'];

        if ($id == "-1") {
            // Insert Baru
            $stmt = $conn->prepare("INSERT INTO personal_data (judul, link, email, pass) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $judul, $link, $email, $pass);
        } else {
            // Update
            $stmt = $conn->prepare("UPDATE personal_data SET judul=?, link=?, email=?, pass=? WHERE id=?");
            $stmt->bind_param("ssssi", $judul, $link, $email, $pass, $id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
        exit;
    }

    // Hapus Data
    if ($_GET['action'] == 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM personal_data WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "deleted"]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PERSONAL DATA - WUKONG</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f0f0f; 
            --card-bg: rgba(20, 20, 20, 0.95);
            --luxury-gold: #d4af37;
            --gold-glow: rgba(212, 175, 55, 0.4);
            --border-ui: rgba(212, 175, 55, 0.15);
            --neon-red: #ff3131;
            --red-glow: rgba(255, 49, 49, 0.6);
        }

        /* --- RESET --- */
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-font-smoothing: antialiased; font-weight: 900 !important; }

        body {
            background-color: var(--bg-dark);
            background-image: radial-gradient(circle at 2px 2px, rgba(212, 175, 55, 0.05) 1px, transparent 0);
            background-size: 30px 30px;
            color: white;
            font-family: 'Rajdhani', sans-serif;
            padding: 40px 20px;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* --- ANIMASI BERKILAU (SHIMMER) --- */
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        @keyframes flicker {
            0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
                text-shadow: 0 0 10px var(--gold-glow), 0 0 20px var(--luxury-gold);
                color: var(--luxury-gold);
            }
            20%, 22%, 24%, 55% { text-shadow: none; color: #333; }
        }

        /* --- HEADER SECTION --- */
        .header-container {
            max-width: 1400px;
            margin: 0 auto 40px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .title-wrapper { width: 100%; text-align: center; }
        .flicker-main-title {
            font-family: 'Orbitron';
            font-size: 35px;
            letter-spacing: 12px;
            text-transform: uppercase;
            display: inline-block;
        }
        .flicker-main-title span { display: inline-block; animation: flicker 4s infinite; }

        .controls-wrapper {
            display: flex;
            justify-content: flex-start; 
            align-items: center;
            gap: 15px;
            padding-bottom: 15px;
        }

        .search-container { 
            width: 350px; 
            position: relative; 
            border-radius: 50px;
            padding: 2px;
            background: linear-gradient(90deg, transparent, var(--luxury-gold), transparent, var(--luxury-gold), transparent);
            background-size: 200% 100%;
            animation: shimmer 3s infinite linear;
            box-shadow: 0 0 15px var(--gold-glow);
        }

        .search-input {
            width: 100%;
            background: #000;
            border: none;
            padding: 10px 15px 10px 40px;
            border-radius: 50px;
            color: white;
            font-family: 'Rajdhani';
            font-size: 15px;
            outline: none;
            display: block;
        }

        .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--luxury-gold); z-index: 5; }

        .btn-add-pro {
            background: transparent;
            border: 1px solid var(--luxury-gold);
            color: var(--luxury-gold);
            padding: 10px 20px;
            font-family: 'Orbitron';
            font-size: 12px;
            cursor: pointer;
            border-radius: 50px;
            transition: 0.3s;
        }
        .btn-add-pro:hover { background: var(--luxury-gold); color: black; box-shadow: 0 0 15px var(--gold-glow); }

        .header-divider {
            width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--luxury-gold), transparent);
            box-shadow: 0 0 15px var(--gold-glow);
            margin-bottom: 10px;
        }

        /* --- CARD GRID --- */
        #dataContainer {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 450px)); 
            justify-content: center;
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* --- CARD STYLE --- */
        .data-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-ui);
            border-radius: 15px;
            padding: 25px;
            position: relative;
            transition: 0.4s;
            border-top: 3px solid var(--luxury-gold);
        }
        .data-card:hover { transform: translateY(-8px); border-color: var(--luxury-gold); }

        .card-actions-top {
            position: absolute; top: 15px; right: 15px;
            display: flex; gap: 10px;
        }

        .btn-action-hud {
            width: 38px; height: 38px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.3s; border: none;
        }

        .btn-edit-hud {
            background-color: #8b6d00;
            box-shadow: 0 2px 5px rgba(0,0,0,0.5);
        }
        .btn-edit-hud:hover { background-color: var(--luxury-gold); transform: scale(1.1); }
        .btn-edit-hud svg { width: 20px; height: 20px; stroke: white; fill: none; }

        .btn-delete-hud {
            background: rgba(20, 0, 0, 0.8);
            border: 1px solid var(--neon-red);
        }
        .btn-delete-hud:hover { background: var(--neon-red); transform: scale(1.1); }

        .card-header-title {
            font-family: 'Orbitron';
            color: var(--luxury-gold);
            font-size: 18px;
            margin-bottom: 25px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .info-block {
            background: #000; border: 1px solid #222; border-radius: 8px;
            margin-bottom: 12px; overflow: hidden;
        }
        .block-label {
            background: rgba(212, 175, 55, 0.1); color: var(--luxury-gold);
            font-family: 'Orbitron'; font-size: 10px; padding: 6px 15px;
            border-bottom: 1px solid var(--border-ui); text-align: center;
        }
        .block-body {
            padding: 10px 15px; display: flex; align-items: center;
            justify-content: space-between; gap: 15px;
        }
        .info-value { color: #fff; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .btn-copy-hud {
            background: var(--luxury-gold); color: #000; border: none;
            padding: 5px 12px; border-radius: 4px; font-family: 'Orbitron'; font-size: 9px; cursor: pointer;
        }

        /* --- MODALS --- */
        .modal-overlay, .confirm-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(10px);
            display: none; align-items: center; justify-content: center; z-index: 10000;
        }
        .modal-box {
            background: rgba(20, 20, 20, 0.95); border: 2px solid var(--luxury-gold);
            width: 450px; padding: 35px; border-radius: 20px; box-shadow: 0 0 50px var(--gold-glow); text-align: center;
        }
        .cyber-input {
            width: 100%; background: #000; border: 1px solid #333; padding: 12px;
            color: white; border-radius: 8px; margin-bottom: 15px; outline: none;
            border-left: 5px solid var(--luxury-gold); font-family: 'Rajdhani'; font-size: 16px; text-align: center;
        }
        .modal-footer { display: flex; gap: 10px; }
        .m-btn { flex: 1; padding: 12px; border-radius: 8px; font-family: 'Orbitron'; font-size: 13px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        .m-save { background: var(--luxury-gold); color: black; border: none; }
        .m-cancel { background: transparent; border: 1px solid #444; color: #777; }

        #toast {
            position: fixed; top: 30px; left: 50%; transform: translateX(-50%);
            background: var(--luxury-gold); color: black; padding: 10px 40px;
            border-radius: 50px; font-family: 'Orbitron'; font-size: 13px;
            display: none; z-index: 20000;
        }
    </style>
</head>
<body>

    <div id="toast">SYSTEM SYNCED</div>

    <header class="header-container">
        <div class="title-wrapper"><div class="flicker-main-title" id="pageTitle"></div></div>
        <div class="controls-wrapper">
            <div class="search-container">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" class="search-input" placeholder="SEARCH" onkeyup="filterData()">
            </div>
            <button class="btn-add-pro" onclick="openModal()">+ NEW DATA</button>
        </div>
        <div class="header-divider"></div>
    </header>

    <div id="dataContainer"></div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-box">
            <h2 style="color:var(--luxury-gold); font-family:Orbitron; margin-bottom:25px;">TAMBAH DATA </h2>
            <input type="hidden" id="editIndex" value="-1">
            <input type="text" id="inpJudul" class="cyber-input" placeholder="JUDUL">
            <input type="text" id="inpLink" class="cyber-input" placeholder="LINK / URL">
            <input type="text" id="inpEmail" class="cyber-input" placeholder="USERNAME / EMAIL">
            <input type="text" id="inpPass" class="cyber-input" placeholder="PASSWORD">
            <div class="modal-footer">
                <button class="m-btn m-save" onclick="saveData()">SAVE COMMAND</button>
                <button class="m-btn m-cancel" onclick="closeModal()">CANCEL</button>
            </div>
        </div>
    </div>

    <div class="confirm-overlay" id="confirmOverlay">
        <div class="modal-box" style="border-color: var(--neon-red); box-shadow: 0 0 40px var(--red-glow);">
            <h2 style="color:var(--neon-red); font-family:Orbitron; font-size:18px; margin-bottom:20px;">YAKIN DELETE ?</h2>
            <p style="color:#bbb; font-size:12px; margin-bottom:30px; line-height: 1.6;">INFORMASI ! JIKA DI HAPUS MAKA AKAN HILANG SECARA PERMANENT , APAKAH ANDA YAKIN ?</p>
            <div class="modal-footer">
                <button onclick="executeDelete()" class="m-btn m-save" style="background:var(--neon-red); color:white;">OK,HAPUS</button>
                <button onclick="closeConfirm()" class="m-btn m-cancel">CANCEL</button>
            </div>
        </div>
    </div>

    <script>
        let vaultData = [];
        let deleteId = null;

        async function fetchData(query = "") {
            const res = await fetch(`personal_data.php?action=fetch&query=${query}`);
            vaultData = await res.json();
            renderUI();
        }

        function flickerText(text) {
            return Array.from(text).map(char => {
                let delay = (Math.random() * 2).toFixed(1);
                return `<span style="animation-delay: ${delay}s">${char === ' ' ? '&nbsp;' : char}</span>`;
            }).join('');
        }

        function showToast(m) {
            const t = document.getElementById('toast');
            t.innerText = m; t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', 2000);
        }

        function copy(text) { navigator.clipboard.writeText(text).then(() => showToast("DATA COPIED")); }

        function filterData() { fetchData(document.getElementById('searchInput').value.toLowerCase()); }

        function openModal(id = -1) {
            const modal = document.getElementById('modalOverlay');
            if(id > -1) {
                const d = vaultData.find(x => x.id == id);
                document.getElementById('inpJudul').value = d.judul;
                document.getElementById('inpLink').value = d.link;
                document.getElementById('inpEmail').value = d.email;
                document.getElementById('inpPass').value = d.pass;
                document.getElementById('editIndex').value = id;
            } else {
                document.getElementById('editIndex').value = "-1";
                document.getElementById('inpJudul').value = ""; document.getElementById('inpLink').value = "";
                document.getElementById('inpEmail').value = ""; document.getElementById('inpPass').value = "";
            }
            modal.style.display = 'flex';
        }

        function closeModal() { document.getElementById('modalOverlay').style.display = 'none'; }
        function askDelete(id) { deleteId = id; document.getElementById('confirmOverlay').style.display = 'flex'; }
        function closeConfirm() { document.getElementById('confirmOverlay').style.display = 'none'; }

        async function executeDelete() {
            if(deleteId !== null) {
                const fd = new FormData();
                fd.append('id', deleteId);
                await fetch('personal_data.php?action=delete', { method: 'POST', body: fd });
                fetchData(); showToast("DATA DELETED"); closeConfirm();
            }
        }

        async function saveData() {
            const judul = document.getElementById('inpJudul').value;
            if(!judul) return;
            
            const fd = new FormData();
            fd.append('id', document.getElementById('editIndex').value);
            fd.append('judul', judul);
            fd.append('link', document.getElementById('inpLink').value);
            fd.append('email', document.getElementById('inpEmail').value);
            fd.append('pass', document.getElementById('inpPass').value);

            await fetch('personal_data.php?action=save', { method: 'POST', body: fd });
            closeModal(); fetchData(); showToast("DATA SECURED");
        }

        function renderUI() {
            const container = document.getElementById('dataContainer');
            container.innerHTML = "";
            vaultData.forEach((d) => {
                container.innerHTML += `
                <div class="data-card">
                    <div class="card-actions-top">
                        <button class="btn-action-hud btn-edit-hud" onclick="openModal(${d.id})">
                            <svg viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="btn-action-hud btn-delete-hud" onclick="askDelete(${d.id})">🗑️</button>
                    </div>
                    <div class="card-header-title">${d.judul}</div>
                    <div class="info-block"><div class="block-label">LINK ACCESS</div><div class="block-body"><div class="info-value">${d.link || '-'}</div><button class="btn-copy-hud" onclick="copy('${d.link}')">COPY</button></div></div>
                    <div class="info-block"><div class="block-label">USERNAME</div><div class="block-body"><div class="info-value">${d.email || '-'}</div><button class="btn-copy-hud" onclick="copy('${d.email}')">COPY</button></div></div>
                    <div class="info-block"><div class="block-label">PASSWORD</div><div class="block-body"><div class="info-value">${d.pass || '-'}</div><button class="btn-copy-hud" onclick="copy('${d.pass}')">COPY</button></div></div>
                </div>`;
            });
        }

        window.onload = () => { 
            document.getElementById('pageTitle').innerHTML = flickerText("DATA PRIBADI"); 
            fetchData(); 
        };
    </script>
</body>
</html>