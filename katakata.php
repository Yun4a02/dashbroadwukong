<?php
// --- KONEKSI DATABASE ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "wukong_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// --- HANDLER AJAX UNTUK SIMPAN DATA ---
if (isset($_POST['action']) && $_POST['action'] == 'update_db') {
    $key = $_POST['key'];
    $val = $_POST['val'];
    
    $stmt = $conn->prepare("INSERT INTO quotes_settings (q_key, q_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE q_value = ?");
    $stmt->bind_param("sss", $key, $val, $val);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

// --- FUNGSI AMBIL DATA ---
function getQuote($key, $conn) {
    $sql = "SELECT q_value FROM quotes_settings WHERE q_key = '$key'";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        return $row['q_value'];
    }
    return "";
}

$val_sosmed = getQuote('box-sosmed', $conn);
$val_prediksi = getQuote('box-prediksi', $conn);
$val_link = getQuote('box-link', $conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QOUTES POSTINGAN </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Rajdhani:wght@500;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f0f0f;
            --card-bg: #1a1a1a; 
            --neon-gold: #d4af37;
            --gold-glow: rgba(212, 175, 55, 0.6);
            --neon-red: #ff3c00;
            --red-glow: rgba(255, 60, 0, 0.5);
            --neon-blue: #00d2ff;
            --neon-green: #10b981;
            --border-ui: rgba(212, 175, 55, 0.2);
            --text-main: #e0e6ed;
        }

        * { 
            margin: 0; padding: 0; box-sizing: border-box; 
            font-weight: 900 !important; 
            -webkit-font-smoothing: antialiased;
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background-color: var(--bg-dark);
            background-image: radial-gradient(circle at 50% 50%, #1e1e1e 0%, #0f0f0f 100%);
            color: #fff;
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
        }

        @keyframes charFlicker {
            0%, 19.999%, 22%, 62.999%, 64%, 64.999%, 70%, 100% {
                opacity: 1; text-shadow: 0 0 10px var(--gold-glow), 0 0 20px var(--neon-gold);
            }
            20%, 21.999%, 63%, 63.999%, 65%, 69.999% {
                opacity: 0.4; text-shadow: none;
            }
        }

        .moving-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            color: var(--neon-gold);
            text-transform: uppercase;
            letter-spacing: 8px;
            margin-bottom: 40px;
            text-align: center;
        }

        .moving-title span { display: inline-block; animation: charFlicker 5s infinite; }

        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 35px;
            width: 100%;
            max-width: 1100px;
        }

        @media (max-width: 900px) {
            .dashboard-container { grid-template-columns: 1fr; }
        }

        .glass-card {
            background: var(--card-bg);
            border: 2px solid var(--border-ui);
            border-radius: 20px;
            padding: 30px 20px 20px 20px;
            position: relative;
            display: flex;
            flex-direction: column;
            transition: 0.4s;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
        }

        .glass-card:hover {
            border-color: var(--neon-gold);
            box-shadow: 0 0 30px var(--gold-glow);
            transform: translateY(-8px);
        }

        .card-header-label {
            position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
            background: var(--bg-dark); padding: 0 15px; color: var(--neon-gold);
            font-family: 'Orbitron', sans-serif; font-size: 0.9rem; letter-spacing: 2px;
            text-transform: uppercase; display: flex; align-items: center; gap: 8px;
            border: 1px solid var(--border-ui); border-radius: 4px; white-space: nowrap;
        }

        .field-label { font-size: 0.75rem; color: var(--neon-gold); margin-bottom: 8px; text-transform: uppercase; }
        
        .modern-input {
            width: 100%; background: #000; border: 1px solid #333; border-radius: 8px;
            padding: 12px; color: #fff; margin-bottom: 15px; outline: none; border-left: 4px solid var(--neon-gold);
        }

        .btn-main {
            background: var(--neon-gold); color: #000; width: 100%; padding: 14px;
            border-radius: 10px; font-family: 'Orbitron', sans-serif; cursor: pointer;
            text-transform: uppercase; border: none; transition: 0.3s;
        }

        .btn-main:hover { filter: brightness(1.2); box-shadow: 0 0 25px var(--gold-glow); }

        .action-group { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 10px; }

        .btn-icon {
            padding: 8px 15px; font-size: 0.7rem; border-radius: 6px; cursor: pointer;
            background: rgba(0,0,0,0.3); color: #fff; border: 1px solid var(--border-ui); font-family: 'Orbitron', sans-serif;
        }

        .btn-edit { color: #f59e0b; }
        .btn-copy { color: var(--neon-gold); }
        .btn-icon:hover { background: var(--neon-gold); color: #000; }

        .output-container {
            background: #000; border: 1px dashed var(--neon-gold); border-radius: 12px;
            padding: 15px; min-height: 120px; font-size: 0.85rem; line-height: 1.6;
            color: #fff; white-space: pre-wrap; outline: none;
        }

        .btn-reset {
            background: transparent; color: var(--neon-red); border: 1px solid var(--neon-red);
            padding: 8px 15px; font-size: 0.7rem; border-radius: 6px; align-self: flex-end;
            margin-top: 15px; cursor: pointer; font-family: 'Orbitron', sans-serif;
        }

        .btn-reset:hover { background: var(--neon-red); color: white; }

        /* --- NOTIFIKASI TENGAH (TOAST) --- */
        #toast-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .toast {
            background: rgba(26, 26, 26, 0.95);
            color: #fff;
            padding: 20px 40px;
            border-radius: 15px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 2px solid var(--neon-gold);
            box-shadow: 0 0 40px rgba(0,0,0,0.8), 0 0 20px var(--gold-glow);
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
        }

        .toast.show { opacity: 1; transform: scale(1); }
        .toast i { font-size: 1.5rem; }
        
        .toast.save { border-color: var(--neon-gold); color: var(--neon-gold); }
        .toast.edit { border-color: var(--neon-blue); color: var(--neon-blue); }
        .toast.copy { border-color: var(--neon-green); color: var(--neon-green); }
        .toast.delete { border-color: var(--neon-red); color: var(--neon-red); }

    </style>
</head>
<body>

    <h1 class="moving-title">
        <span>Q</span><span>O</span><span>U</span><span>T</span><span>E</span><span>S</span> &nbsp; 
        <span>P</span><span>O</span><span>S</span><span>T</span><span>I</span><span>N</span><span>G</span><span>A</span><span>N</span>
    </h1>

    <div id="toast-container"></div>

    <div class="dashboard-container">
        
        <!-- CARD 1 -->
        <div class="glass-card">
            <div class="card-header-label"><i class="fa-solid fa-circle-plus"></i> Input Data Strategis</div>
            <label class="field-label">USER ID PEMENANG</label>
            <input type="text" id="in-user" class="modern-input" placeholder="ID..." oninput="saveInputs()">
            <label class="field-label">NAMA PERMAINAN</label>
            <input type="text" id="in-game" class="modern-input" placeholder="Nama game..." oninput="saveInputs()">
            <label class="field-label">NOMINAL KEMENANGAN</label>
            <input type="text" id="in-nominal" class="modern-input" placeholder="0" onkeyup="formatNumber(this); saveInputs()">
            <button class="btn-main" onclick="generateReport()">
                <i class="fa-solid fa-bolt"></i> GENERATE LAPORAN
            </button>
        </div>

        <!-- CARD 2 -->
        <div class="glass-card">
            <div class="card-header-label"><i class="fa-solid fa-trophy"></i> Postingan Kemenangan</div>
            <div class="action-group">
                <button class="btn-icon btn-edit" onclick="toggleEdit('box-post', this)"><i class="fa-solid fa-pen"></i> Edit</button>
                <button class="btn-icon btn-copy" onclick="copyText('box-post')"><i class="fa-solid fa-copy"></i> Copy</button>
            </div>
            <div id="box-post" class="output-container">Generate data untuk hasil...</div>
            <button class="btn-reset" onclick="resetForm()">
                <i class="fa-solid fa-trash-can"></i> RESET DATA
            </button>
        </div>

        <!-- CARD 3 -->
        <div class="glass-card">
            <div class="card-header-label"><i class="fa-brands fa-facebook"></i> Twitter & Facebook</div>
            <div class="action-group">
                <button class="btn-icon btn-edit" onclick="toggleEdit('box-sosmed', this)"><i class="fa-solid fa-pen"></i> Edit</button>
                <button class="btn-icon btn-copy" onclick="copyText('box-sosmed')"><i class="fa-solid fa-copy"></i> Copy</button>
            </div>
            <div id="box-sosmed" class="output-container"><?php echo $val_sosmed; ?></div>
        </div>

        <!-- CARD 4 -->
        <div class="glass-card">
            <div class="card-header-label"><i class="fa-solid fa-wand-magic-sparkles"></i> Kata Prediksi</div>
            <div class="action-group">
                <button class="btn-icon btn-edit" onclick="toggleEdit('box-prediksi', this)"><i class="fa-solid fa-pen"></i> Edit</button>
                <button class="btn-icon btn-copy" onclick="copyText('box-prediksi')"><i class="fa-solid fa-copy"></i> Copy</button>
            </div>
            <div id="box-prediksi" class="output-container"><?php echo $val_prediksi; ?></div>
        </div>

        <!-- CARD 5 -->
        <div class="glass-card">
            <div class="card-header-label"><i class="fa-solid fa-link"></i> Link Alternatif Aman</div>
            <div class="action-group">
                <button class="btn-icon btn-edit" onclick="toggleEdit('box-link', this)"><i class="fa-solid fa-pen"></i> Edit</button>
                <button class="btn-icon btn-copy" onclick="copyText('box-link')"><i class="fa-solid fa-copy"></i> Copy</button>
            </div>
            <div id="box-link" class="output-container"><?php echo $val_link; ?></div>
        </div>

    </div>

    <script>
        function showToast(msg, type) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            let icon = 'fa-check-circle';
            if(type === 'edit') icon = 'fa-pen-to-square';
            if(type === 'copy') icon = 'fa-copy';
            if(type === 'delete') icon = 'fa-trash-can';

            toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${msg}</span>`;
            container.appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, 2000);
        }

        window.onload = function() {
            const savedPost = localStorage.getItem('box-post');
            if(savedPost) document.getElementById('box-post').innerText = savedPost;
            loadInputs();
        };

        function saveInputs() {
            localStorage.setItem('in-user', document.getElementById('in-user').value);
            localStorage.setItem('in-game', document.getElementById('in-game').value);
            localStorage.setItem('in-nominal', document.getElementById('in-nominal').value);
        }

        function loadInputs() {
            document.getElementById('in-user').value = localStorage.getItem('in-user') || '';
            document.getElementById('in-game').value = localStorage.getItem('in-game') || '';
            document.getElementById('in-nominal').value = localStorage.getItem('in-nominal') || '';
        }

        function formatNumber(input) {
            let value = input.value.replace(/\D/g, "");
            input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function generateReport() {
            const uid = document.getElementById('in-user').value || '---';
            const game = document.getElementById('in-game').value || '---';
            const nom = document.getElementById('in-nominal').value || '0';

            const template = `🎉 WINNER ALERT! 🎉\n🎰 User ID : ${uid} 🎰\n💥 Menang : ${game} 💥\n💸 Nominal Rp. ${nom} 💸 CASH OUT 💸\n🔥 Main tenang, MENANG = BAYAR! 🔥\n🚀 Jangan cuma lihat, ayo ikutan! 🚀\n📩 Giliran kamu selanjutnya! 📩`;

            document.getElementById('box-post').innerText = template;
            localStorage.setItem('box-post', template);
            showToast("LAPORAN BERHASIL DISIMPAN!", "save");
        }

        function copyText(id) {
            const rawText = document.getElementById(id).innerText;
            navigator.clipboard.writeText(rawText).then(() => {
                showToast("TEKS BERHASIL DISALIN!", "copy");
            });
        }

        function toggleEdit(id, btn) {
            const el = document.getElementById(id);
            const isEditing = el.contentEditable === "true";

            if (!isEditing) {
                el.contentEditable = "true";
                el.focus();
                btn.innerHTML = '<i class="fa-solid fa-check"></i> OK';
                btn.style.color = "#10b981";
                showToast("MODE EDIT DIAKTIFKAN", "edit");
            } else {
                el.contentEditable = "false";
                btn.innerHTML = '<i class="fa-solid fa-pen"></i> Edit';
                btn.style.color = "#f59e0b";
                
                if(id !== 'box-post') {
                    const formData = new FormData();
                    formData.append('action', 'update_db');
                    formData.append('key', id);
                    formData.append('val', el.innerText);

                    fetch(window.location.href, { method: 'POST', body: formData })
                    .then(r => r.text())
                    .then(data => {
                        if(data.trim() === 'success') showToast("DATABASE DIPERBARUI!", "save");
                    });
                } else {
                    localStorage.setItem(id, el.innerText);
                    showToast("DATA DISIMPAN!", "save");
                }
            }
        }

        function resetForm() {
            if(confirm("Hapus semua data?")) {
                localStorage.clear();
                showToast("DATA BERHASIL DIHAPUS!", "delete");
                setTimeout(() => location.reload(), 1000);
            }
        }
    </script>
</body>
</html>