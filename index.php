<?php
include 'koneksi.php';

// --- API SERVER SIDE ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    // Ambil Data Alarm
    if ($_GET['action'] == 'get_alarms') {
        $result = $conn->query("SELECT * FROM alarms ORDER BY time_val ASC");
        $data = [];
        while($row = $result->fetch_assoc()) {
            $row['days'] = array_map('intval', explode(',', $row['days']));
            $row['time'] = substr($row['time_val'], 0, 5); // Ambil HH:mm saja
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
    }

    // Simpan atau Edit Alarm
    if ($_GET['action'] == 'save_alarm') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $time = $_POST['time'];
        $sound = $_POST['sound'];
        $days = $_POST['days'];

        if ($id != "") {
            $stmt = $conn->prepare("UPDATE alarms SET name=?, time_val=?, sound=?, days=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $time, $sound, $days, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO alarms (name, time_val, sound, days) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $time, $sound, $days);
        }
        $stmt->execute();
        echo json_encode(["status" => "success"]);
        exit;
    }

    // Hapus Alarm
    if ($_GET['action'] == 'delete_alarm') {
        $id = $_POST['id'];
        $conn->query("DELETE FROM alarms WHERE id = $id");
        echo json_encode(["status" => "deleted"]);
        exit;
    }

    // Ambil Data Tanggal Spesial
    if ($_GET['action'] == 'get_dates') {
        $result = $conn->query("SELECT * FROM special_dates");
        $dates = [];
        while($row = $result->fetch_assoc()) {
            $dates[$row['id_name']] = $row['date_val'];
        }
        echo json_encode($dates);
        exit;
    }

    // Simpan Tanggal Spesial
    if ($_GET['action'] == 'save_date') {
        $type = $_POST['type'];
        $date = $_POST['date'];
        $conn->query("INSERT INTO special_dates (id_name, date_val) VALUES ('$type', '$date') ON DUPLICATE KEY UPDATE date_val='$date'");
        echo json_encode(["status" => "success"]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard WUKONG </title>
    <link rel="icon" type="image/png" href="https://i.imgur.com/4DSIJr3.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 300px;
            --bg-dark: #0f0f0f; 
            --sidebar-bg: #151515; 
            --neon-gold: #d4af37; 
            --gold-glow: rgba(212, 175, 55, 0.6); 
            --neon-red: #ff3c00;
            --red-glow: rgba(255, 60, 0, 0.5);
            --border-ui: rgba(212, 175, 55, 0.15);
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --luxury-gold: #d4af37;
        }

        /* --- CUSTOM SCROLLBAR --- */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0a0a0a; }
        ::-webkit-scrollbar-thumb { background: var(--luxury-gold); border-radius: 10px; border: 2px solid #0a0a0a; }
        ::-webkit-scrollbar-thumb:hover { background: #fff; box-shadow: 0 0 10px var(--gold-glow); }
        * { scrollbar-width: thin; scrollbar-color: var(--luxury-gold) #0a0a0a; }

        /* --- CORE RESET --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-weight: 900 !important; -webkit-font-smoothing: antialiased; }

        body {
            display: flex; width: 100vw; height: 100vh;
            background-color: var(--bg-dark);
            background-image: radial-gradient(circle at 50% 50%, #1e1e1e 0%, #0f0f0f 100%);
            font-family: 'Rajdhani', sans-serif;
            overflow: hidden; color: white;
        }

        /* --- ANIMATIONS --- */
        @keyframes letterFlicker {
            0%, 18%, 22%, 25%, 53%, 57%, 100% {
                text-shadow: 0 0 10px var(--gold-glow), 0 0 20px var(--neon-gold), 0 0 30px var(--luxury-gold);
                color: var(--luxury-gold); opacity: 1;
            }
            20%, 24%, 55% { text-shadow: none; color: #333; opacity: 0.3; }
        }

        @keyframes floatingEffect {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-10px) translateX(2px); }
        }

        @keyframes pulseGlow {
            0% { text-shadow: 0 0 10px var(--gold-glow); transform: scale(1); }
            50% { text-shadow: 0 0 30px var(--luxury-gold), 0 0 50px var(--neon-gold); transform: scale(1.05); }
            100% { text-shadow: 0 0 10px var(--gold-glow); transform: scale(1); }
        }

        /* --- TOAST --- */
        #toastBox { 
            position: fixed; top: -100px; left: 50%; transform: translateX(-50%); 
            z-index: 100001; transition: top 0.5s; 
            background: #000; border: 2px solid var(--luxury-gold); padding: 12px 35px; 
            border-radius: 50px; box-shadow: 0 0 30px var(--gold-glow); 
            color: var(--luxury-gold); font-family: 'Orbitron'; font-size: 13px;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width); height: 100vh;
            background-color: var(--sidebar-bg); border-right: 2px solid var(--neon-gold);
            display: flex; flex-direction: column; z-index: 1000;
            box-shadow: 10px 0 50px rgba(0, 0, 0, 0.8); flex-shrink: 0;
        }

        .header-title { padding: 30px 10px 20px; text-align: center; border-bottom: 1px solid var(--border-ui); }

        .dashboard-text { 
            font-family: 'Orbitron', sans-serif; font-size: 24px; text-transform: uppercase; 
            letter-spacing: 5px; line-height: 1.4; margin-bottom: 15px; min-height: 70px;
        }
        .dashboard-text span { display: inline-block; animation: letterFlicker 4s infinite; }

        .upcoming-alarm-info {
            margin: 15px auto; padding: 15px;
            background: linear-gradient(180deg, rgba(212, 175, 55, 0.1), transparent);
            border: 1px solid var(--border-ui); border-top: 2px solid var(--luxury-gold);
            border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center;
            width: 90%; cursor: pointer; transition: 0.3s;
        }
        .upcoming-label { 
            font-family: 'Orbitron'; font-size: 14px; color: var(--luxury-gold); 
            letter-spacing: 2px; text-transform: uppercase; text-align: center; text-shadow: 0 0 10px var(--gold-glow); 
        }
        .upcoming-time { font-family: 'Orbitron'; font-size: 16px; color: var(--luxury-gold); margin-top: 8px; text-align: center; }
        .realtime-clock { margin-top: 10px; font-size: 14px; color: #fff; letter-spacing: 1px; font-family: Orbitron; opacity: 0.8; }

        /* --- FLOATING POPUP COUNTDOWN --- */
        .floating-countdown-container {
            position: fixed; top: 15px; right: 15px; z-index: 9998;
            display: flex; flex-direction: column; gap: 8px;
        }
        
        .countdown-item { 
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            padding: 8px 12px; background: rgba(0, 0, 0, 0.9); 
            border: 2px solid var(--luxury-gold); border-radius: 10px;
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.4), inset 0 0 10px var(--gold-glow);
            backdrop-filter: blur(10px); min-width: 220px;
            animation: floatingEffect 5s ease-in-out infinite;
        }
        .countdown-item:nth-child(2) { animation-delay: 1.2s; }
        .countdown-item:nth-child(3) { animation-delay: 2.4s; }

        .countdown-text { 
            font-family: 'Orbitron'; font-size: 10px; color: var(--luxury-gold); 
            text-transform: uppercase; letter-spacing: 1px; flex-grow: 1; text-align: left;
            display: flex; flex-wrap: wrap; gap: 1px;
        }
        .countdown-text span { display: inline-block; animation: letterFlicker 3s infinite; }
        .btn-emoji-edit { background: transparent; border: none; cursor: pointer; font-size: 14px; filter: drop-shadow(0 0 5px var(--neon-gold)); transition: 0.2s; }
        .btn-emoji-edit:hover { transform: scale(1.3) rotate(15deg); }

        /* --- CALLING POPUP --- */
        .neon-popup-overlay { 
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); 
            width: 500px; background: rgba(5, 5, 5, 0.95); 
            border: 2px solid var(--luxury-gold); border-radius: 20px; 
            z-index: 100000; display: none; flex-direction: column; 
            text-align: center; box-shadow: 0 0 50px rgba(0,0,0,1), 0 0 30px var(--gold-glow); 
            backdrop-filter: blur(20px); overflow: hidden;
        }
        .pop-head { 
            padding: 25px; background: linear-gradient(to bottom, rgba(212,175,55,0.1), transparent);
            border-bottom: 1px solid var(--border-ui);
        }
        .pop-head h1 { 
            font-family: 'Orbitron'; font-size: 18px; color: var(--luxury-gold); 
            letter-spacing: 4px; text-transform: uppercase; margin: 0;
        }
        .pop-act-text { 
            font-family: 'Orbitron'; font-size: 48px; color: #fff; padding: 60px 20px; 
            text-transform: uppercase; letter-spacing: 2px;
            animation: pulseGlow 2s infinite ease-in-out;
        }
        .pop-foot { 
            display: grid; grid-template-columns: 1fr 1fr; gap: 1px; background: var(--border-ui);
        }
        .btn-pop { 
            padding: 25px; border: none; font-family: 'Orbitron'; font-size: 14px; 
            font-weight: 900; cursor: pointer; text-transform: uppercase; letter-spacing: 2px;
            transition: 0.3s;
        }
        .btn-pop-ok { background: var(--luxury-gold); color: #000; }
        .btn-pop-ok:hover { background: #fff; box-shadow: inset 0 0 20px var(--gold-glow); }
        .btn-pop-freeze { background: #000; color: var(--luxury-gold); }
        .btn-pop-freeze:hover { background: #111; color: #fff; }

        /* --- MENU --- */
        .menu-container { list-style: none; padding: 15px; overflow-y: auto; flex-grow: 1; }
        .menu-item { margin-bottom: 12px; }
        .menu-link {
            text-decoration: none; color: #fff; display: flex; align-items: center; padding: 16px 20px;
            border-radius: 12px; font-size: 15px; letter-spacing: 1.2px; text-transform: uppercase;
            background: rgba(255,255,255,0.03); border: 1px solid var(--border-ui); transition: var(--transition); cursor: pointer;
        }
        .menu-link:hover, .menu-link.active { color: #000; background: var(--luxury-gold); border-color: #fff; box-shadow: 0 0 20px var(--gold-glow); transform: translateX(10px); }
        .menu-link .icon { margin-right: 15px; font-size: 20px; }
        .arrow { margin-left: auto; font-size: 12px; transition: 0.3s; color: var(--luxury-gold); }
        .has-submenu.open .arrow { transform: rotate(180deg); color: #000; }
        .submenu { list-style: none; max-height: 0; overflow: hidden; transition: max-height 0.4s ease; background: rgba(0, 0, 0, 0.4); border-left: 3px solid var(--luxury-gold); margin-left: 25px; border-radius: 0 0 12px 12px; }
        .has-submenu.open .submenu { max-height: 1200px; padding: 10px 0; }
        .submenu-item a { text-decoration: none; color: #777; display: block; padding: 12px 25px; font-size: 14px; transition: 0.2s; text-transform: uppercase; }
        .submenu-item a:hover, .submenu-item a.active { color: var(--luxury-gold); padding-left: 35px; }

        /* --- PANELS --- */
        .floating-panel { position: fixed; top: 60px; width: 380px; background: #0a0a0a; border: 2px solid var(--luxury-gold); border-radius: 15px; z-index: 9999; display: none; flex-direction: column; box-shadow: 0 10px 50px rgba(0,0,0,0.9); }
        #setupPanel { left: 320px; }
        #registryPanel { left: 720px; width: 350px; }
        .date-setup-panel { right: 300px; left: auto; top: 20px; width: 320px; }
        .panel-header { padding: 15px 20px; border-bottom: 1px solid var(--border-ui); display: flex; justify-content: space-between; align-items: center; }
        .panel-header h2 { font-family: 'Orbitron'; font-size: 12px; color: var(--luxury-gold); letter-spacing: 2px; }
        .close-btn { cursor: pointer; color: var(--neon-red); font-size: 22px; }
        .panel-body { padding: 20px; }
        .cyber-input { background: #000; border: 1px solid #333; border-radius: 8px; padding: 15px; color: #fff; font-family: Orbitron; width: 100%; text-align: center; outline: none; margin-bottom: 15px; border-left: 5px solid var(--luxury-gold); font-size: 12px; }
        .btn-neon-main { width: 100%; padding: 18px; border-radius: 10px; border: none; background: var(--luxury-gold); color: #000; font-family: Orbitron; font-size: 13px; cursor: pointer; margin-top: 10px; text-transform: uppercase; }

        .time-box-container { background: #000; border: 1px solid #222; border-radius: 12px; padding: 20px; margin-bottom: 15px; display: flex; flex-direction: column; align-items: center; }
        .unit-box { width: 75px; height: 65px; background: #111; border: 1px solid #333; border-radius: 10px; color: var(--luxury-gold); font-family: 'Orbitron'; font-size: 35px; text-align: center; outline: none; text-shadow: 0 0 15px var(--gold-glow); }
        .ap-btn { padding: 8px 18px; border: none; background: transparent; color: #444; font-family: 'Orbitron'; font-size: 11px; cursor: pointer; border-radius: 6px; }
        .ap-btn.active { background: var(--luxury-gold); color: #000; box-shadow: 0 0 10px var(--gold-glow); }
        .day-bt { padding: 10px 0; background: #000; border: 1px solid #222; color: #555; font-family: Orbitron; font-size: 9px; text-align: center; border-radius: 6px; cursor: pointer; }
        .day-bt.active { background: #111; color: var(--luxury-gold); border-color: var(--luxury-gold); box-shadow: 0 0 10px var(--gold-glow); }
        .btn-allday { width: 100%; padding: 8px; background: transparent; border: 1px solid var(--luxury-gold); color: var(--luxury-gold); font-family: Orbitron; font-size: 10px; border-radius: 5px; cursor: pointer; margin-bottom: 10px; transition: 0.3s; }
        .btn-allday.active { background: var(--luxury-gold); color: #000; }

        .content-area { flex-grow: 1; height: 100vh; background: #000; position: relative; }
        iframe { width: 100%; height: 100%; border: none; }
        .footer-tag { padding: 15px 20px; text-align: left; background: #000; border-top: 1px solid var(--border-ui); font-family: Orbitron; font-size: 9px; color: #666; display: flex; align-items: center; gap: 5px; }
    </style>
</head>
<body>

    <div id="toastBox">WUKONG SYSTEM ONLINE</div>
    <audio id="audioAlarm"></audio>
    <input type="file" id="audioExplorer" accept="audio/*" style="display:none">

    <!-- FLOATING COUNTDOWN -->
    <div class="floating-countdown-container">
        <div class="countdown-item"><div id="leaveText" class="countdown-text"></div><button onclick="toggleDateSetup('leavePanel')" class="btn-emoji-edit">⚙️</button></div>
        <div class="countdown-item"><div id="wifeText" class="countdown-text"></div><button onclick="toggleDateSetup('wifePanel')" class="btn-emoji-edit">⚙️</button></div>
        <div class="countdown-item"><div id="childText" class="countdown-text"></div><button onclick="toggleDateSetup('childPanel')" class="btn-emoji-edit">⚙️</button></div>
    </div>

    <!-- CALLING POPUP -->
    <div id="callingPopup" class="neon-popup-overlay">
        <div class="pop-head"><h1>WUKONG STATUS: ACTIVE</h1></div>
        <div id="callingActionName" class="pop-act-text">ACTION</div>
        <div class="pop-foot">
            <button onclick="stopCurrentAlarm()" class="btn-pop btn-pop-ok">CONFIRM (OK)</button>
            <button onclick="stopCurrentAlarm()" class="btn-pop btn-pop-freeze">FREEZE SYSTEM</button>
        </div>
    </div>

    <!-- COMMAND CENTER PANEL -->
    <div id="setupPanel" class="floating-panel">
        <div class="panel-header"><h2 id="setupPanelTitle">COMMAND CENTER</h2><span onclick="toggleSetup()" class="close-btn">×</span></div>
        <div class="panel-body">
            <input type="hidden" id="editId" value="">
            <input type="text" id="inpName" placeholder="ENTER ACTION NAME..." class="cyber-input">
            <div class="time-box-container">
                <div style="display:flex; align-items:center; gap:10px;">
                    <input type="text" id="hh" class="unit-box" placeholder="00" maxlength="2" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                    <span style="font-family:Orbitron; font-size:30px; color:#fff;">:</span>
                    <input type="text" id="mm" class="unit-box" placeholder="00" maxlength="2" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                </div>
                <div class="ampm-switch" style="margin-top:10px;">
                    <button id="am" class="ap-btn active" onclick="setAMPM('AM')">AM</button>
                    <button id="pm" class="ap-btn" onclick="setAMPM('PM')">PM</button>
                </div>
            </div>
            <select id="inpSound" onchange="handleSoundSelect()" style="background:#000; color:#fff; padding:15px; border:1px solid #333; border-radius:8px; width:100%; font-family:Rajdhani; margin-bottom:15px; outline:none; cursor:pointer;">
                <option value="https://actions.google.com/sounds/v1/alarms/digital_watch_alarm_long.ogg">Classic Gold Tone</option>
                <optgroup label="Registry (Saved)" id="customSoundsGroup"></optgroup>
                <option value="ADD_CUSTOM">[ + ] UPLOAD AUDIO FILE</option>
            </select>
            
            <button id="btnAllDay" onclick="selectAllDays()" class="btn-allday">ALL DAY (SEN - MGG)</button>
            
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:5px; margin-bottom:5px;">
                <div class="day-bt" data-day="1">SEN</div><div class="day-bt" data-day="2">SEL</div><div class="day-bt" data-day="3">RAB</div><div class="day-bt" data-day="4">KAM</div>
            </div>
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:5px; width:75%; margin:0 auto 10px;">
                <div class="day-bt" data-day="5">JUM</div><div class="day-bt" data-day="6">SAB</div><div class="day-bt" data-day="0">MGG</div>
            </div>
            
            <button onclick="saveAlarm()" class="btn-neon-main" id="btnMainSave">INITIALIZE COMMAND</button>
            <button id="btnCancelEdit" onclick="cancelEdit()" style="display:none; background:transparent; border:1px solid #555; color:#777; width:100%; padding:10px; font-family:Orbitron; font-size:10px; border-radius:8px; cursor:pointer; margin-top:10px;">BATAL EDIT</button>
            <button onclick="toggleRegistry()" style="background:transparent; border:none; color:#fff; font-family:Orbitron; cursor:pointer; font-size:11px; text-decoration:underline; margin-top:15px; display:block; width:100%;">OPEN ALARM REGISTRY DATABASE</button>
        </div>
    </div>

    <div id="registryPanel" class="floating-panel">
        <div class="panel-header"><h2>ALARM REGISTRY DATABASE</h2><span onclick="toggleRegistry()" class="close-btn">×</span></div>
        <div class="panel-body" id="regBody" style="max-height: 400px; overflow-y: auto;"></div>
    </div>

    <!-- DATE SETUP PANELS -->
    <div id="leavePanel" class="floating-panel date-setup-panel"><div class="panel-header"><h2>SET JADWAL CUTI</h2><span onclick="toggleDateSetup('leavePanel')" class="close-btn">×</span></div><div class="panel-body"><input type="date" id="leaveDateInput" class="cyber-input"><button onclick="saveSpecialDate('leave')" class="btn-neon-main">SAVE</button></div></div>
    <div id="wifePanel" class="floating-panel date-setup-panel"><div class="panel-header"><h2>SET ULTAH ISTRI</h2><span onclick="toggleDateSetup('wifePanel')" class="close-btn">×</span></div><div class="panel-body"><input type="date" id="wifeDateInput" class="cyber-input"><button onclick="saveSpecialDate('wife')" class="btn-neon-main">SAVE</button></div></div>
    <div id="childPanel" class="floating-panel date-setup-panel"><div class="panel-header"><h2>SET ULTAH ANAK</h2><span onclick="toggleDateSetup('childPanel')" class="close-btn">×</span></div><div class="panel-body"><input type="date" id="childDateInput" class="cyber-input"><button onclick="saveSpecialDate('child')" class="btn-neon-main">SAVE</button></div></div>

    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="header-title">
            <div class="dashboard-text" id="mainDashboardTitle"></div>
            <div class="upcoming-alarm-info" onclick="toggleSetup()">
                <div class="upcoming-label">WUKONG SYSTEM</div>
                <div id="nextAlarmDisplay" class="upcoming-time">ALARM</div>
            </div>
            <div id="clock" class="realtime-clock">...</div>
        </div>

        <ul class="menu-container">
            <li class="menu-item"><a href="personal_data.php" target="mainFrame" class="menu-link nav-link-item"><span class="icon">👤</span> PERSONAL DATA</a></li>
            <li class="menu-item"><a href="result.php" target="mainFrame" class="menu-link nav-link-item"><span class="icon">🏆</span> DASHBOARD RESULT</a></li>
            <li class="menu-item has-submenu">
                <div class="menu-link" onclick="toggleSubmenu(this)"><span class="icon">📁</span> POSTINGAN <span class="arrow">▼</span></div>
                <ul class="submenu">
                    <li class="submenu-item"><a href="prediksi.html" target="mainFrame" class="nav-link-item">🔥 PREDIKSI UP</a></li>
                    <li class="submenu-item"><a href="katakata.php" target="mainFrame" class="nav-link-item">💬 KATA KATA</a></li>
                    <li class="submenu-item"><a href="syair.html" target="mainFrame" class="nav-link-item">📜 SYAIR</a></li>
                    <li class="submenu-item"><a href="reportan_007.php" target="mainFrame" class="nav-link-item">📝 REPORTAN 007</a></li>
                </ul>
            </li>
            <li class="menu-item has-submenu">
                <div class="menu-link" onclick="toggleSubmenu(this)"><span class="icon">📊</span> DATA REPORTAN <span class="arrow">▼</span></div>
                <ul class="submenu">
                    <li class="submenu-item"><a href="rate_usdt.html" target="mainFrame" class="nav-link-item">💵 RATE USDT</a></li>
                    <li class="submenu-item"><a href="reportan_slot.html" target="mainFrame" class="nav-link-item">🎰 REPORTAN SLOT</a></li>
                    <li class="submenu-item"><a href="daily_report.html" target="mainFrame" class="nav-link-item">🤖 DAILY REPORT</a></li>
                    <li class="submenu-item"><a href="pencairan_vip.html" target="mainFrame" class="nav-link-item">💰 PENCAIRAN VIP</a></li>
                    <li class="submenu-item"><a href="report_vip.html" target="mainFrame" class="nav-link-item">📊 REPORT DAILY QRIS</a></li>
                    <li class="submenu-item"><a href="sc_peminjaman.html" target="mainFrame" class="nav-link-item">🏦 DATA PEMINJAMAN</a></li>
                    <li class="submenu-item"><a href="minera_cair.html" target="mainFrame" class="nav-link-item">🏦 PENCAIRAN MINERAPAY</a></li>
                    <li class="submenu-item"><a href="sc_selisih_vip.html" target="mainFrame" class="nav-link-item">🔍 SC SELISIH VIP</a></li>
                    <li class="submenu-item"><a href="sc-vip.html" target="mainFrame" class="nav-link-item">⚡ SHORTCUT SELISIH </a></li>
                    <li class="submenu-item"><a href="bank_scanner.html" target="mainFrame" class="nav-link-item">🔍 VALIDASI BANK</a></li>
                </ul>
            </li>
        </ul>
        <div class="footer-tag">PROFESIONAL DASHBROAD <span style="color:var(--luxury-gold)"> BY WUKONG</span></div>
    </nav>

    <main class="content-area"><iframe id="mainFrame" src="prediksi.html" name="mainFrame"></iframe></main>

    <script>
        // --- STORAGE PERMANEN (INTEGRASI PHP SQL) ---
        let alarmList = [];
        let specialDates = {};
        let activeDays = []; 
        let selAMPM = 'AM';

        // DATABASE UNTUK AUDIO (INDEXEDDB)
        const dbName = "WukongAudioDB";
        function initDB() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open(dbName, 1);
                request.onupgradeneeded = (e) => {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains("sounds")) db.createObjectStore("sounds");
                };
                request.onsuccess = (e) => resolve(e.target.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async function saveAudioToDB(file) {
            const db = await initDB();
            return new Promise((resolve, reject) => {
                const transaction = db.transaction("sounds", "readwrite");
                const store = transaction.objectStore("sounds");
                const key = "custom_" + file.name;
                store.put(file, key);
                transaction.oncomplete = () => resolve(key);
            });
        }

        async function getAudioFromDB(key) {
            const db = await initDB();
            return new Promise((resolve) => {
                const transaction = db.transaction("sounds", "readonly");
                const store = transaction.objectStore("sounds");
                const request = store.get(key);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => resolve(null);
            });
        }

        async function loadCustomSoundsList() {
            const db = await initDB();
            const transaction = db.transaction("sounds", "readonly");
            const store = transaction.objectStore("sounds");
            const request = store.getAllKeys();
            request.onsuccess = () => {
                const group = document.getElementById('customSoundsGroup');
                group.innerHTML = "";
                request.result.forEach(key => {
                    const opt = document.createElement('option');
                    opt.value = "DB:" + key;
                    opt.innerText = key.replace("custom_", "").substring(0, 20).toUpperCase();
                    group.appendChild(opt);
                });
            };
        }

        // --- CORE LOGIC PHP INTEGRATION ---
        async function fetchAlarms() {
            const res = await fetch('?action=get_alarms');
            alarmList = await res.json();
            updateNextAlarmDisplay();
        }

        async function fetchDates() {
            const res = await fetch('?action=get_dates');
            specialDates = await res.json();
            updateAllCountdowns();
        }

        function flickerify(text) {
            return Array.from(text).map(char => {
                let delay = (Math.random() * 2.5).toFixed(1);
                return `<span style="animation-delay: ${delay}s;">${char === ' ' ? '&nbsp;' : char}</span>`;
            }).join('');
        }

        function toggleSetup() { 
            const s = document.getElementById('setupPanel'); 
            if(s.style.display !== 'flex') {
                if(!document.getElementById('editId').value) resetSetupForm();
            }
            s.style.display = s.style.display === 'flex' ? 'none' : 'flex'; 
        }
        
        function resetSetupForm() {
            document.getElementById('editId').value = "";
            document.getElementById('inpName').value = "";
            document.getElementById('hh').value = "";
            document.getElementById('mm').value = "";
            document.getElementById('btnMainSave').innerText = "INITIALIZE COMMAND";
            document.getElementById('setupPanelTitle').innerText = "COMMAND CENTER";
            document.getElementById('btnCancelEdit').style.display = "none";
            activeDays = [];
            document.querySelectorAll('.day-bt').forEach(b => b.classList.remove('active'));
            document.getElementById('btnAllDay').classList.remove('active');
        }

        function cancelEdit() { resetSetupForm(); toggleSetup(); }
        function toggleRegistry() { const r = document.getElementById('registryPanel'); r.style.display = r.style.display === 'flex' ? 'none' : 'flex'; if(r.style.display === 'flex') renderRegistry(); }
        
        function toggleDateSetup(id) { 
            document.querySelectorAll('.date-setup-panel').forEach(p => { if(p.id !== id) p.style.display = 'none'; }); 
            const el = document.getElementById(id); 
            const isOpening = el.style.display !== 'flex';
            el.style.display = isOpening ? 'flex' : 'none'; 
            if(isOpening) {
                const type = id.replace('Panel', ''); 
                if(specialDates[type]) document.getElementById(type + 'DateInput').value = specialDates[type];
            }
        }
        
        async function saveSpecialDate(type) { 
            const input = document.getElementById(type + 'DateInput'); 
            if(!input.value) return; 
            const fd = new FormData();
            fd.append('type', type);
            fd.append('date', input.value);
            await fetch('?action=save_date', { method: 'POST', body: fd });
            await fetchDates();
            toggleDateSetup(type + 'Panel'); 
            showToast("DATE SECURED!"); 
        }
        
        function updateAllCountdowns() {
            const types = ['leave', 'wife', 'child'], labels = { leave: 'HARI LAGI CUTI 🏖️', wife: 'HARI ULTAH ISTRI ❤️', child: 'HARI ULTAH ANAK 👼' };
            types.forEach(t => {
                const dateStr = specialDates[t];
                const el = document.getElementById(t + 'Text');
                if(!dateStr) { el.innerHTML = flickerify("NOT SET"); return; }
                const target = new Date(dateStr); target.setHours(0,0,0,0);
                const now = new Date(); now.setHours(0,0,0,0);
                const diff = Math.ceil((target - now) / (1000 * 60 * 60 * 24));
                let finalStr = diff > 0 ? `${diff} ${labels[t]}` : (diff === 0 ? "HARI INI!" : "BERLALU");
                el.innerHTML = flickerify(finalStr);
            });
        }

        async function saveAlarm() {
            const editId = document.getElementById('editId').value;
            let hVal = parseInt(document.getElementById('hh').value) || 0;
            let mVal = parseInt(document.getElementById('mm').value) || 0;
            if(selAMPM === 'PM' && hVal < 12) hVal += 12;
            if(selAMPM === 'AM' && hVal === 12) hVal = 0;
            const time24 = `${hVal.toString().padStart(2,'0')}:${mVal.toString().padStart(2,'0')}`;
            const fd = new FormData();
            fd.append('id', editId);
            fd.append('name', document.getElementById('inpName').value.toUpperCase() || "TASK");
            fd.append('time', time24);
            fd.append('sound', document.getElementById('inpSound').value);
            fd.append('days', activeDays.length ? activeDays.join(',') : "0,1,2,3,4,5,6");
            await fetch('?action=save_alarm', { method: 'POST', body: fd });
            showToast("DATABASE SYNCED"); 
            resetSetupForm();
            await fetchAlarms();
            document.getElementById('setupPanel').style.display = 'none';
        }

        function renderRegistry() {
            const container = document.getElementById('regBody'); container.innerHTML = "";
            if(alarmList.length === 0) { container.innerHTML = "<p style='text-align:center; opacity:0.5; font-size:12px;'>DATABASE EMPTY</p>"; return; }
            alarmList.forEach(a => { 
                container.innerHTML += `<div style="border-bottom:1px solid #222; padding:12px 0;"><div style="font-size:12px; color:var(--luxury-gold); letter-spacing:1px;">${a.name}</div><div style="font-size:18px; font-family:Orbitron;">${a.time}</div><div style="display:flex; gap:8px; margin-top:8px;"><button onclick="editAlarm(${a.id})" style="background:var(--luxury-gold); color:black; border:none; padding:6px 15px; cursor:pointer; font-size:10px; border-radius:4px;">EDIT</button><button onclick="deleteAlarm(${a.id})" style="background:#330000; color:red; border:1px solid red; padding:6px 15px; cursor:pointer; font-size:10px; border-radius:4px;">HAPUS</button></div></div>`; 
            });
        }

        function editAlarm(id) {
            const a = alarmList.find(x => x.id == id);
            if(!a) return;
            resetSetupForm(); 
            document.getElementById('editId').value = a.id;
            document.getElementById('inpName').value = a.name;
            document.getElementById('inpSound').value = a.sound;
            let [h, m] = a.time.split(':');
            let hour24 = parseInt(h);
            let displayHour = hour24;
            if(hour24 >= 12) { setAMPM('PM'); if(hour24 > 12) displayHour = hour24 - 12; } else { setAMPM('AM'); if(hour24 === 0) displayHour = 12; }
            document.getElementById('hh').value = displayHour.toString().padStart(2, '0');
            document.getElementById('mm').value = m;
            activeDays = [...a.days];
            document.querySelectorAll('.day-bt').forEach(b => { if(activeDays.includes(parseInt(b.dataset.day))) b.classList.add('active'); });
            document.getElementById('btnMainSave').innerText = "UPDATE COMMAND";
            document.getElementById('setupPanelTitle').innerText = "EDIT ALARM";
            document.getElementById('btnCancelEdit').style.display = "block";
            document.getElementById('registryPanel').style.display = 'none';
            document.getElementById('setupPanel').style.display = 'flex';
        }

        async function deleteAlarm(id) { 
            if(confirm("DELETE THIS COMMAND?")) { 
                const fd = new FormData();
                fd.append('id', id);
                await fetch('?action=delete_alarm', { method: 'POST', body: fd });
                await fetchAlarms();
                renderRegistry(); 
            } 
        }
        
        async function triggerPop(n, s) { 
            const audio = document.getElementById('audioAlarm'); 
            let finalSrc = s;
            if(s.startsWith("DB:")) {
                const blob = await getAudioFromDB(s.replace("DB:", ""));
                if(blob) finalSrc = URL.createObjectURL(blob);
            }
            audio.src = finalSrc; audio.loop = true; audio.play().catch(()=>{});
            
            // --- UPDATE: BROWSER NOTIFICATION ---
            if (Notification.permission === "granted" && document.visibilityState !== 'visible') {
                const notif = new Notification("WUKONG CALLING!", {
                    body: "ACTION REQUIRED: " + n,
                    icon: "https://i.imgur.com/4DSIJr3.png",
                    tag: "wukong-alarm",
                    requireInteraction: true // Notif tetap ada sampai diklik
                });
                notif.onclick = function() {
                    window.focus();
                    this.close();
                };
            }

            document.getElementById('callingActionName').innerText = n; 
            document.getElementById('callingPopup').style.display = 'flex'; 
        }

        function stopCurrentAlarm() { document.getElementById('audioAlarm').pause(); document.getElementById('callingPopup').style.display = 'none'; }
        function selectAllDays() { activeDays = [0,1,2,3,4,5,6]; document.querySelectorAll('.day-bt').forEach(b => b.classList.add('active')); document.getElementById('btnAllDay').classList.add('active'); }
        function showToast(m) { const t = document.getElementById('toastBox'); t.innerText = m; t.style.top = "30px"; setTimeout(() => t.style.top = "-100px", 3000); }
        function setAMPM(m) { selAMPM = m; document.getElementById('am').classList.toggle('active', m==='AM'); document.getElementById('pm').classList.toggle('active', m==='PM'); }
        function toggleSubmenu(e) { e.parentElement.classList.toggle('open'); }
        
        function updateClock() {
            const n = new Date();
            const ds = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"], ms = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"];
            document.getElementById('clock').innerHTML = `${ds[n.getDay()]}, ${n.getDate()} ${ms[n.getMonth()]}<br>${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}:${String(n.getSeconds()).padStart(2,'0')}`;
            const ct = `${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}`;
            alarmList.forEach(a => {
                if(a.time === ct && a.days.includes(n.getDay()) && window.lastTrig !== ct + a.id) { triggerPop(a.name, a.sound); window.lastTrig = ct + a.id; }
            });
        }

        function updateNextAlarmDisplay() {
            if (alarmList.length === 0) { document.getElementById('nextAlarmDisplay').innerText = "NO ALARM"; return; }
            const sorted = [...alarmList].sort((a, b) => a.time.localeCompare(b.time));
            document.getElementById('nextAlarmDisplay').innerText = sorted[0].time + " - " + sorted[0].name;
        }

        document.querySelectorAll('.day-bt').forEach(b => {
            b.onclick = function() {
                this.classList.toggle('active');
                const d = parseInt(this.dataset.day);
                if(this.classList.contains('active')) { if(!activeDays.includes(d)) activeDays.push(d); } else { activeDays = activeDays.filter(x => x !== d); }
            }
        });

        function handleSoundSelect() { if(document.getElementById('inpSound').value === 'ADD_CUSTOM') document.getElementById('audioExplorer').click(); }
        
        document.getElementById('audioExplorer').onchange = async function(e) {
            const file = e.target.files[0];
            if(file) {
                const key = await saveAudioToDB(file);
                await loadCustomSoundsList();
                document.getElementById('inpSound').value = "DB:" + key;
                showToast("SOUND SECURED");
            }
        };

        function setActiveMenu(url) {
            document.querySelectorAll('.nav-link-item').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === url) {
                    link.classList.add('active');
                    const parentSubmenu = link.closest('.submenu');
                    if(parentSubmenu) {
                        parentSubmenu.parentElement.classList.add('open');
                    }
                }
            });
        }

        document.querySelectorAll('.nav-link-item').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                localStorage.setItem('wukong_active_page', href);
                setActiveMenu(href);
            });
        });

        window.onload = async () => { 
            document.getElementById('mainDashboardTitle').innerHTML = flickerify("DASHBOARD") + "<br>" + flickerify("WUKONG");
            
            // Minta Izin Notifikasi Saat Load
            if ("Notification" in window) {
                Notification.requestPermission();
            }

            const lastPage = localStorage.getItem('wukong_active_page');
            if(lastPage) {
                document.getElementById('mainFrame').src = lastPage;
                setActiveMenu(lastPage);
            } else {
                setActiveMenu('prediksi.html');
            }
            await loadCustomSoundsList(); 
            await fetchAlarms();
            await fetchDates();
            setInterval(updateClock, 1000); 
            setInterval(updateAllCountdowns, 30000); 
            showToast("WUKONG SYSTEM ONLINE"); 
        };
    </script>
</body>
</html>