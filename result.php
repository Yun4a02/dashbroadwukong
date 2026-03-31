<?php
// KONFIGURASI DATABASE
$host = "localhost";
$user = "root";
$pass = "";
$db   = "wukong_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// Ambil data dari database
$sql = "SELECT * FROM pasaran ORDER BY id ASC";
$result = $conn->query($sql);
$markets_data = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $markets_data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wukong Dashboard RESULT </title>
    <style>
        :root {
            --gold-primary: #d4af37;
            --gold-light: #f9e29c;
            --gold-dark: #aa8439;
            --gold-glow: rgba(212, 175, 55, 0.5);
            --bg-site: #0a0a0a;
            --card-bg: #141414;
            --text-white: #ffffff;
            --border-color: #2c2c2c;
            --biru-dope: #1e3a8a; 
            --neon-green: #39ff14;
        }

        * { box-sizing: border-box; }

        body {
            background-color: var(--bg-site);
            color: var(--text-white);
            font-family: 'Segoe UI', serif;
            font-weight: bold;
            margin: 0;
            padding: 15px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #wukong-toast {
            position: fixed; top: -100px; left: 50%; transform: translateX(-50%);
            background: rgba(20, 20, 20, 0.95); border: 2px solid var(--gold-primary);
            color: var(--gold-light); padding: 12px 35px; border-radius: 50px;
            font-size: 14px; font-weight: 900; text-transform: uppercase;
            box-shadow: 0 0 20px var(--gold-glow); z-index: 10000;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        #wukong-toast.show { top: 30px; }

        .neon-title {
            font-size: 1.8rem; text-transform: uppercase; letter-spacing: 5px;
            color: var(--gold-primary); 
            text-align: center; margin-bottom: 15px;
            border-bottom: none; 
            display: inline-block; padding-bottom: 5px;
        }

        .flicker-char {
            display: inline-block;
            animation: neonFlicker 3s linear infinite;
        }

        @keyframes neonFlicker {
            0%, 19.999%, 22%, 62.999%, 64%, 64.999%, 70%, 100% {
                opacity: 1;
                text-shadow: 0 0 10px var(--gold-primary), 0 0 20px var(--gold-dark);
            }
            20%, 21.999%, 63%, 63.999%, 65%, 69.999% {
                opacity: 0.4;
                text-shadow: none;
            }
        }

        .title-container { text-align: center; width: 100%; }

        .nav-area {
            display: flex; align-items: center; gap: 15px;
            margin-bottom: 15px; background: rgba(255,255,255,0.05);
            padding: 10px; border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .nav-menu { display: flex; gap: 6px; }
        .btn-nav {
            background: #1a1a1a; color: #888; border: 1px solid var(--border-color);
            padding: 6px 12px; cursor: pointer; font-weight: bold;
            border-radius: 5px; font-size: 10px; text-transform: uppercase;
        }
        .btn-nav.active { 
            background: linear-gradient(145deg, var(--gold-dark), var(--gold-primary)); 
            color: black; box-shadow: 0 0 10px var(--gold-glow); border-color: var(--gold-light);
        }

        .official-links-toolbar { display: flex; gap: 8px; }
        .btn-link-quick {
            padding: 8px 15px; border-radius: 5px; font-size: 10px; font-weight: 900;
            cursor: pointer; text-transform: uppercase; border: 1px solid var(--gold-dark);
        }
        .btn-link-quick.merah { background: #4a0000; color: #ff9999; border-color: #7a0000; }
        .btn-link-quick.biru { 
            background: var(--biru-dope); 
            color: white; 
            border: 1px solid #3b82f6; 
            box-shadow: 0 4px 0 #0f172a;
        }

        .main-container-grid {
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 15px;
            flex: 1;
            min-height: 0;
        }

        .table-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px;
            overflow-y: auto;
        }
        table { width: 100%; border-collapse: collapse; font-size: 13px; } 
        
        /* UPDATE: FONT RATA TENGAH SEMUA PADA TABLE */
        th { 
            color: var(--gold-primary); text-align: center; padding: 12px 8px; 
            border-bottom: 2px solid var(--gold-dark); background: #0a0a0a; 
            position: sticky; top: 0; z-index: 10; 
        }
        td { padding: 8px 8px; border-bottom: 1px solid var(--border-color); text-align: center; }

        .row-done { 
            color: var(--neon-green) !important; 
            text-shadow: 0 0 8px var(--neon-green);
            background: rgba(57, 255, 20, 0.1) !important; 
            border-left: 4px solid var(--neon-green); 
            transition: all 0.3s ease;
        }

        .row-hokidraw-waiting { color: #ff4444 !important; animation: blinkRed 0.8s infinite alternate; font-weight: 900; }
        @keyframes blinkRed { from { text-shadow: 0 0 2px #ff4444; opacity: 1; } to { text-shadow: 0 0 15px #ff4444; opacity: 0.8; } }
        .row-next-up { color: var(--gold-light) !important; background: rgba(212, 175, 55, 0.1) !important; animation: blinkGold 1s infinite alternate; font-weight: 900; }
        @keyframes blinkGold { from { text-shadow: 0 0 2px var(--gold-primary); opacity: 1; } to { text-shadow: 0 0 18px var(--gold-light); opacity: 0.7; } }

        .sidebar-menu { display: flex; flex-direction: column; gap: 10px; overflow-y: auto; padding-right: 5px; }
        .result-input-card { background: #000; border: 2px solid var(--gold-dark); padding: 12px; border-radius: 8px; }
        
        .label-float { 
            display: inline-block; border-bottom: 3px solid var(--gold-primary); 
            margin-bottom: 8px; animation: floatingLabel 2s ease-in-out infinite;
        }
        @keyframes floatingLabel { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .label-text { color: var(--gold-primary); font-size: 15px; font-weight: 900; text-transform: uppercase; }

        .main-input-field { 
            width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--gold-dark); 
            color: var(--gold-light); padding: 10px; border-radius: 5px; outline: none; font-size: 13px; font-weight: 900; 
        }

        .post-card { background: var(--card-bg); border: 2px solid var(--gold-dark); border-radius: 8px; padding: 12px; }
        .post-display-box { 
            background: #000; color: #ddd; padding: 10px; border-radius: 5px; 
            font-family: 'Courier New', monospace; font-size: 12px; height: 140px; 
            overflow-y: auto; border: 1px dashed var(--gold-dark); white-space: pre-wrap; line-height: 1.4;
        }
        .btn-post-copy { 
            width: 100%; margin-top: 8px; background: linear-gradient(to bottom, var(--gold-primary), var(--gold-dark)); 
            color: black; border: none; padding: 10px; border-radius: 5px; font-weight: 900; cursor: pointer; text-transform: uppercase; font-size: 11px; 
        }

        .sweep-card { background: var(--card-bg); border: 2px solid #555; border-radius: 8px; padding: 12px; }
        .sweep-title { color: var(--gold-primary); font-size: 11px; margin-bottom: 8px; text-align: center; text-transform: uppercase; border-bottom: 1px solid var(--border-color); padding-bottom: 4px; }
        .sweep-input { width: 100%; background: #000; border: 1px solid var(--gold-dark); color: var(--gold-light); padding: 8px; border-radius: 5px; font-size: 12px; outline: none; margin-bottom: 10px; text-align: center; font-weight: 900; }
        .ball-container { display: flex; justify-content: center; align-items: center; margin-bottom: 10px; }
        .ball-sm { width: 32px; height: 32px; background: #222; border: 1px solid var(--gold-dark); color: var(--gold-light); font-size: 11px; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin: 0 2px; }
        .ball-add { margin-left: 15px; border: 1px solid var(--gold-light); color: var(--gold-light); background: rgba(212, 175, 55, 0.1); }
        .sweep-res-box { background: #000; border: 2px solid var(--gold-primary); padding: 8px; text-align: center; border-radius: 5px; }
        .sweep-4d-out { font-size: 26px; color: var(--gold-primary); text-shadow: 0 0 10px var(--gold-primary); letter-spacing: 5px; font-weight: 900; }

        .emoji-link {
            cursor: pointer;
            font-size: 18px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        .emoji-link:hover { transform: scale(1.3); }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--gold-dark); border-radius: 10px; }
    </style>
</head>
<body>  

    <div id="wukong-toast">WUKONG NOTIFICATION</div>
    <div class="title-container">
        <h1 class="neon-title" id="main-title">ANGKA RESULT</h1>
    </div>

    <div class="nav-area">
        <div class="nav-menu">
            <button class="btn-nav active" onclick="filterTab('ALL', this)">ALL PASARAN</button>
            <button class="btn-nav" onclick="filterTab('PAGI', this)">PAGI</button>
            <button class="btn-nav" onclick="filterTab('SORE', this)">SORE</button>
            <button class="btn-nav" onclick="filterTab('MALAM', this)">MALAM</button>
        </div>
        <div class="official-links-toolbar">
            <button class="btn-link-quick merah" onclick="quickCopy('https://w6.ceriawlatogel88.net/wap', 'LINK MERAH')">LINK MERAH</button>
            <button class="btn-link-quick biru" onclick="quickCopy('https://www.vegastogel.net/wap', 'LINK BIRU')">LINK BIRU</button>
            <button class="btn-nav" style="color:#ff4444; border-color:#550000;" onclick="resetAll()">RESET ALL</button>
        </div>
    </div>

    <div class="main-container-grid">
        <div class="table-section">
            <table>
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>NAMA PASARAN</th>
                        <th>JADWAL</th>
                        <!-- UPDATE: JUDUL ACUAN -->
                        <th>ACUAN</th>
                        <th>BUKA</th>
                        <th>TUTUP</th>
                    </tr>
                </thead>
                <tbody id="table-body"></tbody>
            </table>
        </div>

        <div class="sidebar-menu">
            <div class="result-input-card">
                <div class="label-float">
                    <span class="label-text" id="label-hasil">HASIL RESULT :</span>
                </div>
                <input type="text" id="main-input" class="main-input-field" placeholder="Tempel Periode & Pasar..." oninput="autoProcess(this.value)">
            </div>

            <div class="post-card">
                <div style="color:var(--gold-primary); font-size: 10px; margin-bottom: 5px; text-transform: uppercase;">FORMAT POSTINGAN:</div>
                <div class="post-display-box" id="post-display"></div>
                <button class="btn-post-copy" onclick="copyPost()">SALIN & POST RESULT</button>
            </div>

            <div class="sweep-card">
                <div class="sweep-title">RUMUS SINGAPORE SWEEP</div>
                <input type="text" class="sweep-input" id="sweep-input" placeholder="TEMPEL 7 ANGKA RESULT..." oninput="calcSweep(this.value)">
                <div class="ball-container">
                    <div class="ball-sm" id="b1">00</div><div class="ball-sm" id="b2">00</div><div class="ball-sm" id="b3">00</div>
                    <div class="ball-sm" id="b4">00</div><div class="ball-sm" id="b5">00</div><div class="ball-sm" id="b6">00</div>
                    <div class="ball-sm ball-add" id="badd">00</div>
                </div>
                <div class="sweep-res-box">
                    <div style="font-size: 9px; color: #888;">ANGKA KELUAR (4D)</div>
                    <div class="sweep-4d-out" id="sweep-4d-out">0000</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // DATA DARI PHP DATABASE
        const markets = <?php echo json_encode($markets_data); ?>;

        function applyNeonFlicker(elementId) {
            const el = document.getElementById(elementId);
            if(!el) return;
            const text = el.innerText;
            el.innerHTML = '';
            text.split('').forEach(char => {
                const span = document.createElement('span');
                if (char === ' ') {
                    span.innerHTML = '&nbsp;';
                } else {
                    span.innerText = char;
                }
                span.className = 'flicker-char';
                span.style.animationDelay = (Math.random() * 5) + 's';
                el.appendChild(span);
            });
        }

        window.addEventListener('DOMContentLoaded', () => {
            applyNeonFlicker('main-title');
            applyNeonFlicker('label-hasil');
            updateClock(); 
            render();
        });

        const shioTable = {
            "Kuda": ["01","13","25","37","49","61","73","85","97"], "Ular": ["02","14","26","38","50","62","74","86","98"],
            "Naga": ["03","15","27","39","51","63","75","87","99"], "Kelinci": ["04","16","28","40","52","64","76","88","00"],
            "Harimau": ["05","17","29","41","53","65","77","89"], "Kerbau": ["06","18","30","42","54","66","78","90"],
            "Tikus": ["07","19","31","43","55","67","79","91"], "Babi": ["08","20","32","44","56","68","80","92"],
            "Anjing": ["09","21","33","45","57","69","81","93"], "Ayam": ["10","22","34","46","58","70","82","94"],
            "Monyet": ["11","23","35","47","59","71","83","95"], "Kambing": ["12","24","36","48","60","72","84","96"]
        };

        let doneIDs = JSON.parse(localStorage.getItem('autoDoneIDs')) || [];
        let activeTab = "ALL";

        function getShio(num) {
            if(!num) return "...";
            let tail = num.slice(-2);
            for(let key in shioTable) if(shioTable[key].includes(tail)) return key;
            return "...";
        }

        // UPDATE: FUNGSI BUKA MULTIPLE LINK
        function openAcuan(mId) {
            const m = markets.find(x => x.id == mId);
            if(!m) return;
            const links = [m.link1, m.link2, m.link3].filter(l => l && l.trim() !== "");
            if(links.length === 0) {
                wukongAlert("TIDAK ADA LINK DATABASE");
                return;
            }
            links.forEach(url => {
                window.open(url, '_blank');
            });
        }

        function calcSweep(val) {
            const nums = val.match(/\d+/g);
            if (!nums || nums.length < 7) return;
            const b = nums.slice(0, 6).map(n => parseInt(n));
            const add = parseInt(nums[6]);
            for(let i=0; i<6; i++) document.getElementById(`b${i+1}`).innerText = b[i].toString().padStart(2,'0');
            document.getElementById('badd').innerText = add.toString().padStart(2,'0');
            const d1 = (b[1] + b[2]).toString().slice(-1);
            const d2 = (b[3] + b[4]).toString().slice(-1);
            const sumB = b.reduce((a, b) => a + b, 0);
            const last2 = ((sumB * 2) - b[0] - b[5] + add).toString().slice(-2).padStart(2,'0');
            document.getElementById('sweep-4d-out').innerText = d1 + d2 + last2;
        }

        function updateClock() {
            const now = new Date();
            const h = now.getHours();
            const m = now.getMinutes();
            let lastResetHour = localStorage.getItem('lastHokiResetHour');
            if (m >= 50 && lastResetHour != h) {
                doneIDs = doneIDs.filter(id => id !== 1);
                localStorage.setItem('autoDoneIDs', JSON.stringify(doneIDs));
                localStorage.setItem('lastHokiResetHour', h);
                render();
            }
        }

        function autoProcess(val) {
            const display = document.getElementById('post-display');
            if(!val) { display.innerText = ""; return; }
            const upperText = val.toUpperCase();
            let matched = markets.find(m => upperText.replace(/\s+/g, '').includes(m.nama.toUpperCase().replace(/\s+/g, '')));
            if (matched) {
                let tempText = upperText.replace(matched.nama.toUpperCase(), "");
                const nums = tempText.match(/\d{4,5}/g);
                if (nums && nums.length > 0) {
                    if (!doneIDs.includes(matched.id)) {
                        doneIDs.push(matched.id);
                        localStorage.setItem('autoDoneIDs', JSON.stringify(doneIDs));
                    }
                    const now = new Date();
                    const dayStr = now.toLocaleDateString('id-ID', { weekday: 'long' }).toUpperCase();
                    const dateStr = now.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                    let post = `Hasil Pengeluaran ${matched.nama}\n`;
                    post += `Hari Ini ${dayStr} , ${dateStr}\n`;
                    if (nums.length >= 3) {
                        post += `Prize 1 : ${nums[0]}, SHIO : ${getShio(nums[0])}\nPrize 2 : ${nums[1]}\nPrize 3 : ${nums[2]}\n`;
                    } else {
                        post += `Result : ${nums[0]}\nSHIO : ${getShio(nums[0])}\n`;
                    }
                    post += `Selamat Kepada Pemenang, Salam JP Hanya di TogelUP`;
                    display.innerText = post;
                    render();
                }
            }
        }

        function render() {
            const tbody = document.getElementById('table-body');
            const filtered = activeTab === "ALL" ? markets : markets.filter(m => m.cat === activeTab || m.id == 1);
            
            // Cari pasar selanjutnya untuk animasi gold
            const nextUpMarket = markets.find(m => m.id != 1 && !doneIDs.includes(m.id.toString()) && !doneIDs.includes(Number(m.id)));
            const nextUpID = nextUpMarket ? nextUpMarket.id : null;

            tbody.innerHTML = filtered.map(m => {
                let cls = "";
                // Cek status done
                if (doneIDs.includes(m.id.toString()) || doneIDs.includes(Number(m.id))) cls = "row-done";
                else if (m.id == 1) cls = "row-hokidraw-waiting";
                else if (m.id == nextUpID) cls = "row-next-up";

                return `<tr class="${cls}">
                    <td>${m.id}</td>
                    <td style="font-weight:900;">${m.nama}</td>
                    <td>${m.jadwal}</td>
                    <td><span class="emoji-link" onclick="openAcuan(${m.id})">🔗</span></td>
                    <td>${m.buka}</td>
                    <td>${m.tutup}</td>
                </tr>`;
            }).join('');
        }

        function quickCopy(url, label) { navigator.clipboard.writeText(url).then(() => wukongAlert(label + " COPIED!")); }
        function wukongAlert(msg) { const t = document.getElementById('wukong-toast'); t.innerText = msg; t.classList.add('show'); setTimeout(() => t.classList.remove('show'), 2000); }
        function filterTab(tab, el) { activeTab = tab; document.querySelectorAll('.btn-nav').forEach(b => b.classList.remove('active')); el.classList.add('active'); render(); }

        function resetAll() { 
            localStorage.clear(); 
            doneIDs = []; 
            render(); 
            document.getElementById('main-input').value = ""; 
            document.getElementById('post-display').innerText = ""; 
            wukongAlert("ALL DATA RESET"); 
        }

        function copyPost() { const txt = document.getElementById('post-display').innerText; if(!txt) return; navigator.clipboard.writeText(txt).then(() => wukongAlert("TELAH MENYALIN")); }

        setInterval(updateClock, 1000);
    </script>
</body>
</html>