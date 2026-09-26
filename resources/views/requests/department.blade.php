<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QM-SEM-2NAVI ｜ {{ $departmentName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        :root {
            /* プランナーアプリ風の、青みがかったソフトなグレー配色。
               形（レイアウト）はそのまま、色調だけを寒色寄りのグラス＋濃紺インクに変更。 */
            --glass: rgba(233,240,248,0.75);
            --glass-strong: rgba(238,244,250,0.95);
            --glass-border: rgba(210,222,238,0.95);
            --glass-sheen: rgba(255,255,255,0.95);
            --glass-sheen-soft: rgba(255,255,255,0.3);
            --ink-1: #1a2540;
            --ink-2: #5c6b81;
            --ink-3: #93a0b2;
            --accent: #3f5fe0;
            --accent-soft: #dde6fb;
            --waiting-bg: #fbe3e3; --waiting-ink: #c1453f;
            --progress-bg: #dce7fb; --progress-ink: #35509c;
            --sent-bg: #dff3e6; --sent-ink: #237a4f;
            --assign-bg: #fbe1e1; --assign-ink: #bd433c;
            --row-line: rgba(70,90,120,0.16);
            --shadow-out: 0 16px 34px rgba(35,48,78,0.16), 0 2px 6px rgba(35,48,78,0.08);
            --shadow-in: inset 1px 1px 2px rgba(255,255,255,0.95), inset -1px -1px 2px rgba(120,140,170,0.14);
            --radius-lg: 24px;
            --radius-pill: 999px;
            --zebra-bg: rgba(120,140,170,0.1);
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro JP", "Hiragino Kaku Gothic ProN", "Noto Sans JP", "Segoe UI", Roboto, sans-serif;
            font-weight: 400;
            color: var(--ink-1);
            /* 参考画像のような、ぼんやりした光のムラがある背景。
               1つの中心グラデーションだけだと平坦に見えるため、
               明るい光だまりを複数の位置に重ねてぼかし写真のような質感にする。 */
            background-color: #c2cdda;
            background-image:
                radial-gradient(650px 550px at 12% 10%, rgba(255,255,255,0.65), transparent 60%),
                radial-gradient(750px 620px at 78% 22%, rgba(255,255,255,0.5), transparent 62%),
                radial-gradient(900px 700px at 65% 85%, rgba(150,168,195,0.35), transparent 65%),
                radial-gradient(800px 650px at 8% 95%, rgba(120,140,170,0.4), transparent 65%),
                linear-gradient(135deg, #d3dce8 0%, #aebccf 100%);
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }

        .dashboard-shell { width: 100%; max-width: 1520px; margin: 0 auto; padding: 16px 24px 80px; }

        /* --- ヘッダー --- */
        .dash-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
        .dash-header-left { display: flex; align-items: center; gap: 12px; }
        .dash-logo-icon {
            width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(145deg, #2c3a5e, var(--ink-1));
            color: #fff;
            box-shadow: 0 0 18px rgba(26,37,64,0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        }
        .dash-brand { font-size: 17px; font-weight: 500; color: var(--ink-1); letter-spacing: 0.06em; }

        /* --- メインナビ（Project / Dashboard / Calendar 切替タブ） --- */
        .main-tabs {
            display: flex; gap: 30px;
            border-bottom: 1px solid var(--row-line);
            margin-bottom: 20px;
        }
        .main-tab {
            position: relative;
            display: flex; align-items: center; gap: 7px;
            cursor: pointer; font-family: inherit;
            background: none; border: none; padding: 0 1px 12px;
            font-size: 15px; font-weight: 600; color: var(--ink-3);
            transition: color 0.15s;
        }
        .main-tab:hover { color: var(--ink-2); }
        .main-tab.active { color: var(--ink-1); }
        .main-tab::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: -1px; height: 2px;
            background: transparent; border-radius: 2px; transition: background 0.15s;
        }
        .main-tab.active::after { background: var(--accent); }
        .main-panel { display: none; }
        .main-panel.active { display: block; }

        /* --- 汎用ガラスカード（うっすら水色を含んだガラス。上端にごく薄いハイライト線を足して立体感を出す） --- */
        .glass-card, .cards-section {
            position: relative;
            /* 真っ白に寄せすぎず、背景の光のムラが透けて見える程度の半透明に。
               大枠がガラスとして背景と呼応することで、内側の案件カードとの立体差も出る。 */
            background: linear-gradient(165deg, rgba(255,255,255,0.62), rgba(255,255,255,0.4) 55%, rgba(220,230,244,0.42));
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(28px) saturate(150%);
            -webkit-backdrop-filter: blur(28px) saturate(150%);
            box-shadow: var(--shadow-out), inset 0 1px 0 rgba(255,255,255,0.75);
            padding: 22px 24px;
            overflow: hidden;
        }
        .cards-section { padding: 24px; margin-top: 0; }
        .card-title { font-size: 14px; font-weight: 600; letter-spacing: 0.03em; color: var(--ink-1); margin: 0 0 14px; }
        .card-title.cal-title { display: flex; align-items: center; gap: 7px; }
        .panel-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }

        /* --- Dashboard（月次推移／今月の内訳／メンバー別件数を3枚のカードで横並び） --- */
        .dashboard-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: stretch; }
        .dashboard-mini-card { display: flex; flex-direction: column; min-width: 0; }
        .dashboard-mini-body { flex: 1; display: flex; align-items: center; justify-content: center; min-height: 220px; position: relative; }
        .dashboard-mini-body canvas { max-width: 100%; }
        .card-subtitle { font-size: 12px; font-weight: 700; color: var(--ink-2); margin: 0 0 10px; }
        /* 列の中に2枚のカードを縦に積んで、他の列（単体カード）と高さを揃える */
        .dashboard-stack { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
        .dashboard-stack .dashboard-mini-card { flex: 1; }
        .dashboard-mini-card-half .dashboard-mini-body { flex: 0 0 130px; height: 130px; min-height: 0; }
        /* ドーナツ2枚のカードは、キャンバス固定サイズ＋独自HTML凡例で構成を完全に揃える
           （Chart.js内蔵の凡例だと項目数の違いでリング位置がズレるため使わない）。
           グラフ・凡例ともカード内で中央揃えにし、凡例はグラフの下に横並びで配置する。 */
        .donut-body { flex-direction: column; justify-content: center; align-items: center; gap: 8px; }
        .donut-canvas-wrap { width: 76px; height: 76px; flex-shrink: 0; position: relative; margin: 0 auto; }
        .donut-canvas-wrap canvas { width: 76px !important; height: 76px !important; }
        .donut-legend {
            list-style: none; margin: 0; padding: 0;
            display: flex; flex-direction: row; flex-wrap: wrap;
            justify-content: center; align-items: center;
            gap: 4px 12px;
        }
        .donut-legend li { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 500; color: var(--ink-2); white-space: nowrap; }
        .donut-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .member-avatar {
            width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--accent), #7c88ff);
            color: #fff; font-size: 12px; font-weight: 800;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .member-avatar.small { width: 20px; height: 20px; font-size: 10px; }
        .member-avatar.unassigned { background: var(--ink-3); }

        /* --- カレンダーカード（開始日〜納期日をバーで表示） --- */
        .calendar-card { margin-bottom: 18px; }
        .calendar-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .cal-title { display: flex; align-items: center; gap: 7px; }
        .calendar-nav { display: flex; align-items: center; gap: 10px; }
        .cal-nav-btn {
            width: 26px; height: 26px; border-radius: 50%; border: 1px solid var(--glass-border);
            background: var(--glass); color: var(--ink-2); cursor: pointer; font-size: 14px; line-height: 1;
        }
        .cal-nav-btn:hover { color: var(--ink-1); }
        .cal-month-label { font-size: 13px; font-weight: 500; letter-spacing: 0.02em; color: var(--ink-1); min-width: 50px; text-align: center; white-space: nowrap; }

        /* --- Notionのタイムラインビューのような横スクロール式カレンダー --- */
        .calendar-timeline-outer {
            flex: 1; display: flex; flex-direction: column;
            overflow-x: auto; overflow-y: hidden;
            border-radius: 12px;
            min-width: 0;
            max-width: 100%;
            /* 横スクロールバーは常時非表示（‹›ボタン・ドラッグ・トラックパッドでの操作は引き続き可能） */
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .calendar-timeline-outer::-webkit-scrollbar { display: none; }
        .calendar-tl-grid {
            position: relative;
            display: grid;
            grid-auto-rows: min-content;
            row-gap: 4px;
            flex: 1;
        }
        .cal-tl-daynum {
            grid-row: 1;
            font-size: 11px; font-weight: 700; color: var(--ink-2);
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px;
            padding: 4px 0 10px;
            border-right: 1px solid var(--row-line);
            position: sticky; top: 0;
            background: transparent;
        }
        .cal-tl-weekday {
            font-size: 9.5px; font-weight: 700; letter-spacing: 0.02em;
            color: var(--ink-3);
        }
        .cal-tl-weekday.is-sun { color: var(--waiting-ink); }
        .cal-tl-weekday.is-sat { color: var(--progress-ink); }
        .cal-tl-daynum-num { font-size: 11px; font-weight: 700; color: var(--ink-2); }
        .cal-tl-daynum.cal-today .cal-tl-daynum-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 20px; height: 20px; border-radius: 50%;
            background: var(--ink-1); color: #fff;
            box-shadow: 0 0 8px rgba(26,37,64,0.4);
        }
        .cal-tl-today-line {
            position: absolute; top: 34px; bottom: 0; width: 2px;
            background: rgba(76,111,224,0.4);
            pointer-events: none;
        }
        /* ステータスタブと同じ「透明な薄い背景＋色つきドット」の見た目に統一（真っ赤/真っ青の強い色は使わない） */
        .cal-bar {
            font-size: 10.5px; font-weight: 700; padding: 3px 10px 3px 8px;
            margin: 0 2px;
            border-radius: 999px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            display: flex; align-items: center; gap: 5px;
        }
        .cal-bar::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: currentColor; flex-shrink: 0;
        }
        .cal-bar.waiting    { background: var(--waiting-bg);  color: var(--waiting-ink); }
        .cal-bar.inprogress { background: var(--progress-bg); color: var(--progress-ink); }

        /* --- 検索バー（Projectカードのヘッダー右側に配置） --- */
        .filter-bar {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .search-box {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 16px;
            background: var(--glass-strong);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-pill);
            box-shadow: var(--shadow-in);
            width: 280px;
        }
        .search-box svg { flex-shrink: 0; color: var(--ink-3); }
        .search-box input {
            border: none; background: transparent; outline: none;
            font-size: 13px; color: var(--ink-1); width: 100%; font-family: inherit;
        }
        .search-box input::placeholder { color: var(--ink-3); }

        /* --- ステータス切替タブ（カードではなく下線タイプのラインタブ） --- */
        .status-tabs {
            display: flex; gap: 30px;
            border-bottom: 1px solid var(--row-line);
            margin-bottom: 20px;
        }
        .status-tab {
            position: relative;
            display: flex; align-items: center; gap: 7px; cursor: pointer; font-family: inherit;
            background: none; border: none; padding: 0 1px 12px;
            font-size: 14px; font-weight: 600; color: var(--ink-3);
            transition: color 0.15s;
        }
        /* ステータスごとの色ドットを常時表示して、タブに彩りを持たせる */
        .status-tab-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--tab-color, var(--accent)); flex-shrink: 0; }
        .status-tab:hover { color: var(--ink-2); }
        .status-tab.active { color: var(--ink-1); }
        .status-tab::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: -1px; height: 2px;
            background: transparent; border-radius: 2px; transition: background 0.15s;
        }
        .status-tab.active::after { background: var(--tab-color, var(--accent)); }
        .status-tab-count {
            font-size: 11.5px; font-weight: 700; color: var(--ink-3);
        }
        .status-tab.active .status-tab-count { color: var(--ink-2); }
        .status-panel {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px;
        }
        .status-panel.active { display: grid; }

        /* --- カレンダーはProjectと同じカード内、下部に常設表示 --- */
        .project-calendar-inner {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--row-line);
        }

        /* --- Researchタブ：媒体切替タブ・フィルタ用セレクト・一覧テーブル --- */
        .research-select {
            padding: 9px 14px;
            background: var(--glass-strong);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-pill);
            box-shadow: var(--shadow-in);
            font-size: 12.5px; font-weight: 600; color: var(--ink-2);
            font-family: inherit; cursor: pointer; outline: none;
        }
        .research-media-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
        .research-media-tab {
            cursor: pointer; font-family: inherit;
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: var(--radius-pill);
            padding: 7px 16px;
            font-size: 12.5px; font-weight: 700; color: var(--ink-2);
            transition: background 0.15s, color 0.15s, box-shadow 0.15s;
        }
        .research-media-tab:hover { color: var(--ink-1); }
        .research-media-tab.active {
            background: var(--accent); color: #fff; border-color: var(--accent);
            box-shadow: 0 6px 14px rgba(63,95,224,0.3);
        }
        .research-platform-panel { display: none; }
        .research-platform-panel.active { display: block; }
        .research-table-wrap { overflow-x: auto; }
        table.research-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; font-size: 12.5px; }
        table.research-table th {
            text-align: left; font-size: 11px; font-weight: 700; color: var(--ink-3);
            padding: 0 14px 6px; white-space: nowrap;
        }
        table.research-table td {
            background: var(--glass); padding: 12px 14px; color: var(--ink-1);
            white-space: nowrap; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border);
        }
        table.research-table td:first-child { border-radius: 10px 0 0 10px; border-left: 1px solid var(--glass-border); font-weight: 600; white-space: normal; }
        table.research-table td:last-child { border-radius: 0 10px 10px 0; border-right: 1px solid var(--glass-border); color: var(--ink-2); }

        /* --- Excel風オートフィルター：見出しの▾から一覧選択でフィルタ --- */
        .rf-th-wrap { display: flex; align-items: center; gap: 4px; position: relative; }
        .rf-caret {
            cursor: pointer; font-family: inherit; border: none; background: none;
            font-size: 9px; color: var(--ink-3); padding: 2px 3px; border-radius: 4px; line-height: 1;
        }
        .rf-caret:hover { background: var(--glass-strong); color: var(--ink-1); }
        .rf-caret.rf-active { color: var(--accent); }
        .rf-dropdown {
            display: none;
            position: absolute; top: calc(100% + 6px); left: 0; z-index: 40;
            width: 220px;
            background: linear-gradient(180deg, rgba(255,255,255,0.98), var(--glass-strong));
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            box-shadow: var(--shadow-out);
            padding: 10px;
            white-space: normal;
        }
        .rf-dropdown.open { display: block; }
        .rf-dropdown-search {
            width: 100%; padding: 6px 10px; margin-bottom: 8px;
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 8px; font-size: 12px; color: var(--ink-1);
            font-family: inherit; outline: none; box-sizing: border-box;
        }
        .rf-dropdown-list { max-height: 220px; overflow-y: auto; margin-bottom: 8px; }
        .rf-dropdown-item {
            display: flex; align-items: center; gap: 7px;
            padding: 4px 2px; font-size: 12px; font-weight: 500; color: var(--ink-1);
            cursor: pointer;
        }
        .rf-dropdown-item input { flex-shrink: 0; cursor: pointer; }
        .rf-dropdown-item.rf-all-item { font-weight: 700; border-bottom: 1px solid var(--row-line); margin-bottom: 4px; padding-bottom: 7px; }
        .rf-dropdown-actions { display: flex; gap: 8px; }
        .rf-dropdown-actions button {
            flex: 1; font-family: inherit; cursor: pointer;
            padding: 7px 0; border-radius: 8px; font-size: 12px; font-weight: 700;
            border: 1px solid var(--glass-border);
        }
        .rf-dropdown-ok { background: var(--accent); color: #fff; border-color: var(--accent); }
        .rf-dropdown-cancel { background: var(--glass); color: var(--ink-2); }

        .mini-card {
            position: relative;
            text-align: left; cursor: pointer; font-family: inherit;
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 14px; padding: 14px 16px 14px 20px;
            box-shadow: var(--shadow-in);
            display: flex; flex-direction: column; gap: 8px;
            transition: transform 0.15s, box-shadow 0.15s;
            overflow: hidden;
        }
        /* ステータス色の細いアクセントバーを左端に添えて、一覧に彩りを出す */
        .mini-card::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            background: var(--card-accent, var(--accent));
        }
        .mini-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-out); }
        .mini-card-title { font-size: 13px; font-weight: 800; color: var(--ink-1); }
        .mini-card-project { font-weight: 500; color: var(--ink-2); margin-left: 2px; }
        .mini-card-platforms { display: flex; gap: 4px; flex-wrap: wrap; }
        .platform-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; background: var(--accent-soft); color: var(--accent); white-space: nowrap; }
        .platform-badge.red    { background: #fde3e1; color: #c8402f; }
        .platform-badge.purple { background: #ece3fb; color: #6b3fc4; }
        .platform-badge.blue   { background: #dde7fb; color: #2f57c8; }
        .platform-badge.teal   { background: #dcf3f0; color: #1f8577; }
        .platform-badge.slate  { background: #e6e9ee; color: #4a5568; }
        .platform-badge.green  { background: #e0f5e6; color: #2a8a4c; }
        /* 納期だけが右側にぽつんと浮くと不自然なので、担当者と同じ行に並べてペアで見せる */
        .mini-card-meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
        .mini-card-due { display: flex; align-items: center; gap: 4px; font-size: 11px; color: var(--ink-3); white-space: nowrap; }
        .mini-card-due svg { flex-shrink: 0; }
        .mini-card-assignee { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--ink-2); }
        .unassigned-text { color: var(--ink-3); }

        /* --- 案件詳細ポップアップ --- */
        .campaign-modal-overlay {
            position: fixed; inset: 0; z-index: 900;
            background: rgba(4,7,15,0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            display: none; align-items: flex-start; justify-content: center;
            padding: 40px 20px; overflow-y: auto;
        }
        .campaign-modal-overlay.open { display: flex; }
        .campaign-modal {
            width: min(1400px, 96vw);
            background:
                radial-gradient(120% 50% at 12% -10%, var(--glass-sheen-soft), transparent 55%),
                var(--glass-strong);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            backdrop-filter: blur(24px) saturate(120%);
            -webkit-backdrop-filter: blur(24px) saturate(120%);
            box-shadow: var(--shadow-out), inset 0 1px 0 var(--glass-sheen);
            position: relative;
            padding: 16px 4px 24px;
        }
        .campaign-modal-close {
            position: absolute; top: 14px; right: 14px; z-index: 2;
            width: 34px; height: 34px; border-radius: 50%;
            border: none; background: var(--glass);
            box-shadow: var(--shadow-in); color: var(--ink-2); font-size: 18px; cursor: pointer;
        }
        .campaign-modal-close:hover { color: var(--ink-1); }
        .campaign-modal-scroll { max-height: 84vh; overflow-y: auto; padding: 14px 22px 0; }
        #detail-store .client-group { display: none; margin-bottom: 0; }
        #detail-store .client-group.modal-active { display: block; }
        body.modal-open-lock { overflow: hidden; }

        @media (max-width: 900px) {
            .dashboard-row { grid-template-columns: 1fr; }
        }

        /* --- クライアントグループ --- */
        .client-group { margin-bottom: 24px; }

        /* campaign-blockは案件詳細モーダル(.campaign-modal)の中身であり、
           それ自体を独立したガラスカードにすると「モーダルの中にモーダル」に見えてしまうため、
           背景・枠・影は持たせずモーダルの地の上にそのまま乗せる。 */
        .campaign-block {
            position: relative;
            overflow: hidden;
            margin-bottom: 8px;
        }

        /* --- 要約バー（常時展開） --- */
        .summary-bar {
            padding: 18px 24px;
        }
        .summary-top-row {
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap;
        }
        .summary-title-group { display: flex; align-items: center; gap: 12px; min-width: 0; flex-wrap: wrap; }
        .summary-left-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; flex-shrink: 0; }
        .group-title {
            font-size: 15px; font-weight: 800; color: var(--ink-1);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .group-title .project-name { color: var(--ink-2); font-weight: 500; margin-left: 2px; }

        /* --- ステータス（編集不可・色付きバッジ） --- */
        .status-pill {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 5px 12px;
            border-radius: var(--radius-pill);
            font-size: 12px; font-weight: 700;
            box-shadow: var(--shadow-in);
        }
        .status-pill .dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
        .status-pill.waiting    { background: var(--waiting-bg); color: var(--waiting-ink); }
        .status-pill.inprogress { background: var(--progress-bg); color: var(--progress-ink); }
        .status-pill.sent       { background: var(--sent-bg); color: var(--sent-ink); }

        /* --- 依頼者名・納期・備考（ミニテーブル） --- */
        table.mini-info-table {
            width: 100%; margin-top: 16px;
            border-collapse: collapse;
        }
        table.mini-info-table th {
            text-align: left;
            font-size: 10.5px; font-weight: 700; color: var(--ink-3);
            padding: 0 12px 6px;
        }
        table.mini-info-table td {
            padding: 10px 12px;
            font-size: 12.5px; color: var(--ink-1);
            background: var(--glass);
            box-shadow: var(--shadow-in);
        }
        table.mini-info-table td:first-child { border-radius: 10px 0 0 10px; font-weight: 600; }
        table.mini-info-table td:last-child { border-radius: 0 10px 10px 0; color: var(--ink-2); }

        .mail-btn {
            display: inline-flex; align-items: center; gap: 6px;
            height: 30px; padding: 0 14px 0 10px; border-radius: var(--radius-pill);
            background: var(--glass);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-in);
            color: var(--ink-2);
            font-size: 12px; font-weight: 700; font-family: inherit;
            cursor: pointer;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .mail-btn:hover { color: var(--accent); }
        .mail-btn svg { display: block; flex-shrink: 0; }

        /* --- 対応します（Assign待ち→対応中） --- */
        .assign-btn {
            display: inline-flex; align-items: center; gap: 5px;
            height: 30px; padding: 0 14px; border-radius: var(--radius-pill);
            background: var(--assign-bg);
            color: var(--assign-ink);
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: var(--shadow-in);
            font-size: 12px; font-weight: 700; font-family: inherit;
            cursor: pointer; white-space: nowrap;
        }
        .assign-btn:hover { filter: brightness(0.97); }


        /* --- 詳細（開閉部） --- */
        .detail-body { display: block; padding: 0 24px 36px; }

        .info-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 19px; height: 19px; border-radius: 50%;
            background: linear-gradient(135deg, #f6f7fb 0%, #dadfeb 55%, #eef0f6 100%);
            box-shadow: inset 0 1px 1px rgba(255,255,255,0.85), inset 0 -1px 1px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.08);
            color: #7a8194;
            margin-right: 6px;
            flex-shrink: 0;
        }
        .info-icon svg { display: block; width: 10px; height: 10px; }

        /* --- 媒体切替タブ --- */
        .group-tabs {
            display: flex; gap: 20px; flex-wrap: wrap;
            border-bottom: 1px solid var(--row-line);
            margin-bottom: 4px;
        }
        .group-tab {
            display: inline-flex; align-items: center; gap: 6px;
            border: none; background: transparent;
            color: var(--ink-3);
            padding: 0 2px 10px;
            font-size: 13px; font-weight: 700;
            cursor: pointer; font-family: inherit;
            position: relative;
        }
        .group-tab.active { color: var(--ink-1); }
        .group-tab.active::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: -1px; height: 2.5px;
            background: linear-gradient(90deg, #565c68, #2b2f38);
            border-radius: 3px 3px 0 0;
        }
        .tab-lottie-canvas { display: block; flex-shrink: 0; width: 22px; height: 22px; }

        /* --- YouTube: 詳細指標トグル --- */
        .yt-table-toolbar {
            display: flex; justify-content: flex-end; gap: 8px;
            margin-top: 12px;
        }
        .yt-detail-toggle {
            border: 1px solid var(--row-line);
            border-radius: 999px;
            background: var(--glass);
            color: var(--accent);
            font-size: 12px; font-weight: 700;
            padding: 6px 14px;
            cursor: pointer; font-family: inherit;
        }
        .yt-detail-toggle:hover { background: var(--glass-strong); }
        .yt-detail-toggle.active {
            background: var(--accent-soft);
            color: var(--accent);
            border-color: var(--accent);
        }
        .pattern-table .yt-detail-cv,
        .pattern-table .yt-detail-fq { display: none; }
        .pattern-table.show-yt-cv .yt-detail-cv { display: table-cell; }
        .pattern-table.show-yt-fq .yt-detail-fq { display: table-cell; }

        /* --- パターンテーブル --- */
        .pattern-table-wrap {
            overflow-x: auto; margin-top: 12px; padding-bottom: 34px;
            scrollbar-width: thin;
            scrollbar-color: rgba(60,70,100,0.35) rgba(60,70,100,0.08);
        }
        /* 白いガラスの上に乗るので、白系ではなくダーク系の色でコントラストを付ける。
           ネイティブの矢印ボタンは非表示にする。 */
        .pattern-table-wrap::-webkit-scrollbar { height: 12px; }
        .pattern-table-wrap::-webkit-scrollbar-button { display: none; width: 0; height: 0; }
        .pattern-table-wrap::-webkit-scrollbar-track {
            background: rgba(60,70,100,0.08);
            border-radius: 999px;
            margin: 0 2px;
        }
        .pattern-table-wrap::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, rgba(90,100,130,0.55), rgba(90,100,130,0.3));
            border-radius: 999px;
            border: 3px solid transparent;
            background-clip: padding-box;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15), inset 0 1px 1px rgba(255,255,255,0.4);
        }
        .pattern-table-wrap::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, rgba(90,100,130,0.7), rgba(90,100,130,0.42));
        }
        table.pattern-table {
            border-collapse: collapse;
            table-layout: auto;
            width: 100%;
            background: transparent;
        }
        table.pattern-table th {
            background: transparent;
            color: var(--ink-3);
            font-size: 10.5px; font-weight: 700;
            height: 34px; line-height: 34px; padding: 0 10px;
            text-align: left; vertical-align: middle;
            white-space: nowrap;
            border-bottom: 1px solid var(--row-line);
            box-shadow: 0 1px 0 rgba(255,255,255,0.55);
        }
        table.pattern-table td {
            padding: 10px 10px;
            border-bottom: 1px solid var(--row-line);
            font-size: 12px; font-weight: 400;
            color: var(--ink-1);
            vertical-align: middle;
            white-space: nowrap;
        }
        table.pattern-table tbody tr:nth-child(odd) td { background: var(--zebra-bg); }
        table.pattern-table tbody tr:hover td { background: var(--glass); }
        .plain-tag { display: inline-block; margin: 1px 6px 1px 0; font-weight: 400; color: var(--ink-1); }

        /* --- 見積指標セル --- */
        .estimate-input {
            width: auto;
            min-width: 48px;
            field-sizing: content;
            border: 1px solid var(--row-line);
            border-radius: 6px;
            padding: 5px 7px;
            font-size: 12px;
            font-weight: 700;
            color: #111111;
            background: var(--glass);
            font-family: inherit;
        }
        .estimate-input::-webkit-outer-spin-button,
        .estimate-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .estimate-input[type="number"] {
            -moz-appearance: textfield;
        }
        .estimate-input:focus { outline: none; border-color: var(--accent); }
        .estimate-input.is-empty {
            border: 2px solid #e03131;
        }
        .mail-input.is-empty, .mail-textarea.is-empty {
            border: 2px solid #e03131;
        }
        .estimate-input.max-budget-input {
            min-width: 132px;
            text-align: right;
        }
        .max-budget-cell { min-width: 160px; }
        .max-budget-toggle { display: flex; gap: 4px; margin-bottom: 5px; }
        .max-budget-mode-btn {
            border: 1px solid var(--row-line);
            border-radius: 999px;
            background: var(--glass);
            color: var(--ink-2);
            font-size: 10.5px; font-weight: 700;
            padding: 3px 9px;
            cursor: pointer; font-family: inherit;
            white-space: nowrap;
        }
        .max-budget-mode-btn:hover { color: var(--ink-1); }
        .max-budget-mode-btn.active {
            background: var(--accent-soft);
            color: var(--accent);
            border-color: var(--accent);
        }
        .estimate-input-wrap { display: flex; align-items: center; gap: 2px; }
        .estimate-input-wrap .unit { font-size: 11px; color: var(--ink-3); flex-shrink: 0; }
        .estimate-computed { color: var(--ink-1); text-align: right; font-weight: 600; }
        .estimate-na { color: var(--ink-3); text-align: center; }

        .no-data-text { color: var(--ink-3); font-size: 13px; padding-top: 8px; }
        .back-link {
            display: inline-block; margin-top: 20px;
            color: var(--accent); text-decoration: none;
            font-size: 14px; font-weight: 700;
        }
        .back-link:hover { text-decoration: underline; }

        @media (max-width: 720px) {
            .panel-header { flex-direction: column; align-items: stretch; }
            .search-box { width: 100%; }
            .summary-top-row { flex-direction: column; align-items: flex-start; }
            .summary-left-actions { width: 100%; }
        }

        /* --- メール送信モーダル --- */
        .mail-modal-overlay {
            position: fixed; inset: 0; z-index: 1000;
            background: rgba(20,22,30,0.45);
            backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            padding: 20px;
        }
        .mail-modal {
            position: relative;
            width: 100%; max-width: 520px;
            background:
                radial-gradient(120% 70% at 12% -10%, var(--glass-sheen-soft), transparent 55%),
                var(--glass-strong);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            backdrop-filter: blur(20px) saturate(120%);
            -webkit-backdrop-filter: blur(20px) saturate(120%);
            box-shadow: var(--shadow-out), inset 0 1px 0 var(--glass-sheen);
            padding: 24px;
        }
        .mail-modal-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 18px;
        }
        .mail-modal-header h3 { margin: 0; font-size: 16px; font-weight: 800; color: var(--ink-1); }
        .mail-modal-close {
            width: 28px; height: 28px; border-radius: 50%;
            border: none; background: var(--glass);
            box-shadow: var(--shadow-in);
            color: var(--ink-2); font-size: 16px; line-height: 1;
            cursor: pointer;
        }
        .mail-modal-body label {
            display: block; font-size: 11.5px; font-weight: 700; color: var(--ink-3);
            margin: 14px 0 6px;
        }
        .mail-modal-body label:first-child { margin-top: 0; }
        .mail-input, .mail-textarea {
            width: 100%; box-sizing: border-box;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px; color: var(--ink-1); font-family: inherit;
            box-shadow: var(--shadow-in);
        }
        .mail-input:focus, .mail-textarea:focus { outline: none; border-color: var(--accent); }
        .mail-textarea { resize: vertical; }
        .mail-modal-footer {
            display: flex; justify-content: flex-end; gap: 10px;
            margin-top: 20px;
        }
        .mail-btn-secondary, .mail-btn-primary {
            padding: 9px 18px; border-radius: var(--radius-pill);
            font-size: 13px; font-weight: 700; font-family: inherit;
            cursor: pointer; border: none;
        }
        .mail-btn-secondary { background: var(--glass); color: var(--ink-2); box-shadow: var(--shadow-in); }
        .mail-btn-primary { background: linear-gradient(145deg, var(--accent), #7c88ff); color: #fff; box-shadow: 0 8px 18px rgba(91,108,240,0.35); }

        /* --- メール送信中／送信完了の演出 --- */
        .mail-status-overlay {
            position: fixed; inset: 0; z-index: 1100;
            background: rgba(20,22,30,0.55);
            backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
        }
        .mail-status-card {
            display: flex; flex-direction: column; align-items: center; gap: 14px;
            padding: 32px 40px;
            background: var(--glass-strong);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--shadow-out), inset 0 1px 0 var(--glass-sheen);
        }
        .mail-status-spinner {
            width: 36px; height: 36px;
            border: 3px solid rgba(28,34,51,0.15);
            border-top-color: var(--ink-1);
            border-radius: 50%;
            animation: mailStatusSpin 0.8s linear infinite;
        }
        @keyframes mailStatusSpin { to { transform: rotate(360deg); } }
        .mail-status-text { font-size: 14px; font-weight: 700; color: var(--ink-1); }
        #mailSentLottie { width: 140px; height: 140px; }
    </style>
</head>
<body>
@php
    // --- 新ダッシュボード用の集計データ ---
    // group_key は $groups のインデックス＝コントローラーが分けた「依頼」単位そのもの。
    // クライアント名・案件名が同じでも別の依頼（別のグループ）なら混ぜない。
    $allCampaignItems = collect();
    foreach ($groups as $gIndex => $g) {
        foreach ($g['campaigns'] as $b) {
            $allCampaignItems->push([
                'campaign' => $b['campaign'],
                'client_name' => $g['client_name'],
                'project_name' => $g['project_name'],
                'platform' => $b['campaign']->platform,
                'group_key' => $gIndex,
            ]);
        }
    }

    // 月ごとの件数推移（直近6ヶ月、依頼月＝campaigns.created_at）
    $monthlyLabels = [];
    $monthlyCounts = [];
    for ($mIdx = 5; $mIdx >= 0; $mIdx--) {
        $mDate = \Carbon\Carbon::now()->subMonths($mIdx);
        $mKey = $mDate->format('Y-m');
        $monthlyLabels[] = $mDate->format('n月');
        $monthlyCounts[$mKey] = 0;
    }
    foreach ($allCampaignItems as $item) {
        $c = $item['campaign'];
        if (!$c->created_at) continue;
        $key = \Carbon\Carbon::parse($c->created_at)->format('Y-m');
        if (array_key_exists($key, $monthlyCounts)) $monthlyCounts[$key]++;
    }
    $monthlyCountsValues = array_values($monthlyCounts);

    // メンバーごとの件数（今月、既存のassigneeテキストで集計）
    $thisMonthKey = \Carbon\Carbon::now()->format('Y-m');
    $memberCounts = [];
    foreach ($allCampaignItems as $item) {
        $c = $item['campaign'];
        if (!$c->assignee || !$c->created_at) continue;
        if (\Carbon\Carbon::parse($c->created_at)->format('Y-m') !== $thisMonthKey) continue;
        $memberCounts[$c->assignee] = ($memberCounts[$c->assignee] ?? 0) + 1;
    }
    arsort($memberCounts);

    // 今月件数のステータス内訳（ドーナツグラフ用）
    $monthlyStatusOrder = ['Assign待ち' => 'waiting', '対応中' => 'inprogress', '送付済み' => 'sent'];
    $monthlyStatusCounts = ['Assign待ち' => 0, '対応中' => 0, '送付済み' => 0];
    $monthlyStatusColors = ['Assign待ち' => 'var(--waiting-ink)', '対応中' => 'var(--progress-ink)', '送付済み' => 'var(--sent-ink)'];
    foreach ($allCampaignItems as $item) {
        $c = $item['campaign'];
        if (!$c->created_at) continue;
        if (\Carbon\Carbon::parse($c->created_at)->format('Y-m') !== $thisMonthKey) continue;
        if (array_key_exists($c->status, $monthlyStatusCounts)) $monthlyStatusCounts[$c->status]++;
    }

    // 今月件数の媒体内訳（ドーナツグラフ用）
    $platformOrderForChart = ['youtube', 'yg', 'meta', 'listing', 'x', 'line'];
    $platformChartColors = [
        'youtube' => '#c8402f',
        'yg'      => '#6b3fc4',
        'meta'    => '#2f57c8',
        'listing' => '#1f8577',
        'x'       => '#4a5568',
        'line'    => '#2a8a4c',
    ];
    $monthlyPlatformCounts = [];
    foreach ($allCampaignItems as $item) {
        $c = $item['campaign'];
        if (!$c->created_at) continue;
        if (\Carbon\Carbon::parse($c->created_at)->format('Y-m') !== $thisMonthKey) continue;
        $monthlyPlatformCounts[$item['platform']] = ($monthlyPlatformCounts[$item['platform']] ?? 0) + 1;
    }
    $monthlyPlatformLabels = [];
    $monthlyPlatformValues = [];
    $monthlyPlatformChartColorList = [];
    foreach ($platformOrderForChart as $plat) {
        if (empty($monthlyPlatformCounts[$plat])) continue;
        $monthlyPlatformLabels[] = $platformLabels[$plat] ?? $plat;
        $monthlyPlatformValues[] = $monthlyPlatformCounts[$plat];
        $monthlyPlatformChartColorList[] = $platformChartColors[$plat] ?? '#93a0b2';
    }

    // カレンダービュー用イベント（依頼日＝campaigns.created_at ～ 納期日＝entry_due_date）
    $calendarEvents = $allCampaignItems->map(function ($item) use ($platformLabels) {
        $c = $item['campaign'];
        $start = $c->created_at ? \Carbon\Carbon::parse($c->created_at)->format('Y-m-d') : null;
        $end = $c->entry_due_date ? \Carbon\Carbon::parse($c->entry_due_date)->format('Y-m-d') : $start;
        return [
            'campaign_id' => $c->id,
            'client'   => $item['client_name'],
            'project'  => $item['project_name'],
            'platform' => $platformLabels[$item['platform']] ?? $item['platform'],
            'status'   => $c->status,
            'start'    => $start,
            'end'      => $end,
        ];
    })->filter(fn($e) => $e['start'])->values();

    // 案件カードは「依頼（$groups の1件）」ごとに1枚。同じクライアント名・案件名でも別の依頼なら別カードにする。
    // 1つの依頼の中で複数媒体が含まれる場合のみ、そのカードの中でタブ切替になる。
    $statusPriority = ['Assign待ち', '対応中', '送付済み'];
    $projectCards = $allCampaignItems
        ->groupBy(fn($item) => $item['group_key'])
        ->map(function ($items) use ($statusPriority, $platformLabels) {
            $first = $items->first();
            $statuses = $items->map(fn($it) => $it['campaign']->status)->values()->all();
            $overallStatus = $statusPriority[0];
            foreach ($statusPriority as $sp) {
                if (in_array($sp, $statuses, true)) { $overallStatus = $sp; break; }
            }
            $platforms = $items->map(fn($it) => $it['platform'])->unique()->values();
            $dueDates = $items->map(fn($it) => $it['campaign']->entry_due_date)->filter()->values();
            $minDue = null;
            foreach ($dueDates as $dd) {
                if ($minDue === null || $dd < $minDue) $minDue = $dd;
            }
            $assignee = null;
            foreach ($items as $it) {
                if ($it['campaign']->assignee) { $assignee = $it['campaign']->assignee; break; }
            }
            return [
                'group_key'    => $first['group_key'],
                'client_name'  => $first['client_name'],
                'project_name' => $first['project_name'],
                'platforms'    => $platforms,
                'status'       => $overallStatus,
                'due_date'     => $minDue,
                'assignee'     => $assignee,
            ];
        })
        ->values();
    $cardsByStatus = $projectCards->groupBy('status');
    $cardStatusOrder = ['Assign待ち' => 'waiting', '対応中' => 'inprogress', '送付済み' => 'sent'];
    $statusInkVar = ['waiting' => 'waiting', 'inprogress' => 'progress', 'sent' => 'sent'];
    $statusClassMap = ['Assign待ち' => 'waiting', '対応中' => 'inprogress', '送付済み' => 'sent'];
    // 媒体ごとに色を分けて、案件カードのバッジに彩りを出す
    $platformColors = [
        'youtube' => 'red',
        'yg'      => 'purple',
        'meta'    => 'blue',
        'listing' => 'teal',
        'x'       => 'slate',
        'line'    => 'green',
    ];

    // --- Researchタブ用：案件カードを開いた時と同じ「メニュー～指標」までの内容を、
    //     媒体ごとに全案件横断で一覧化する（表示専用。編集用の入力欄は持たない）。
    $rsFormatGender = function ($g) {
        $g = trim((string) $g);
        $map = ['male' => '男性', 'MALE' => '男性', 'Male' => '男性',
                'female' => '女性', 'FEMALE' => '女性', 'Female' => '女性',
                'all' => 'ALL', 'ALL' => 'ALL', 'All' => 'ALL'];
        return $map[$g] ?? $g;
    };
    $rsFormatDevice = function ($d) { return strtoupper(trim((string) $d)); };
    $rsFormatTargetingValues = function ($values) {
        if (!is_array($values) || empty($values)) return '';
        $labels = array_map(function ($v) {
            if (is_array($v)) return $v['label'] ?? $v['name'] ?? $v['text'] ?? $v['value'] ?? '';
            if (is_object($v)) { $arr = (array) $v; return $arr['label'] ?? $arr['name'] ?? $arr['text'] ?? $arr['value'] ?? ''; }
            return (string) $v;
        }, $values);
        return implode('・', array_filter($labels, fn($l) => $l !== ''));
    };
    $rsFormatArea = function ($p) {
        $names = is_array($p->pref_names ?? null) ? $p->pref_names : [];
        $area = implode('・', $names);
        return $area . ($p->city ? '　' . $p->city : '') ?: '—';
    };
    $rsFormatTargeting = function ($p) use ($rsFormatTargetingValues) {
        $parts = [];
        foreach ([1, 2, 3] as $n) {
            $type = $p->{"targeting{$n}_type"} ?? null;
            $values = $p->{"targeting{$n}_values"} ?? [];
            if ($type) $parts[] = $type . '：' . $rsFormatTargetingValues((array) $values);
        }
        return $parts ? implode(' / ', $parts) : '—';
    };
    $rsMetric = function ($est, $key, $percentKeys, $currencyKeys) {
        if (!$est || !isset($est->$key)) return '—';
        $v = $est->$key;
        if (in_array($key, $percentKeys)) return number_format($v * 100, 2) . '%';
        if (in_array($key, $currencyKeys)) return '¥' . number_format($v);
        return number_format($v, 0);
    };
    $youtubeBillingMap = ['VRC2.0（リーチ）' => 'CPM課金', 'スキップ不可' => 'CPM課金', 'VVC（視聴）' => 'CPV課金'];
    $ytPercentKeys = ['ctr', 'cvr', 'view_rate', 'view_complete_rate'];
    $ytCurrencyKeys = ['cpm', 'cpv', 'cpc', 'cpa'];
    $ytMetricKeys = ['imp' => 'IMP', 'cpm' => 'CPM', 'cts' => 'CTs', 'cpc' => 'CPC',
                      'view_rate' => '視聴率', 'view_count' => '視聴数', 'cpv' => 'CPV',
                      'view_complete_rate' => '視聴完了率', 'view_complete' => '視聴完了数',
                      'cvr' => 'CVR', 'cv' => 'CVs', 'cpa' => 'CPA', 'fq' => '想定FQ', 'reach' => 'リーチ数'];
    $metaPercentKeys = ['ctr', 'cvr', 'vimp_rate'];
    $metaCurrencyKeys = ['vcpm', 'cpc', 'cpa'];
    $metaMetricKeys = ['vcpm' => 'vCPM', 'vimp_rate' => '推定vimp率', 'ctr' => 'CTR', 'cpc' => 'CPC', 'cvr' => 'CVR',
                        'vimp' => 'vIMP(想定)', 'imp' => 'IMP(想定)', 'cts' => 'CTs', 'cv' => 'CV', 'cpa' => 'CPA'];

    // --- YG-Display&DGC：課金・素材形態でケース分けし、ケースごとに該当する指標だけ値を出す（他は—）
    $ygPercentKeys = ['ctr', 'cvr', 'view_rate', 'vimp_rate'];
    $ygCurrencyKeys = ['vcpm', 'cpm', 'cpc', 'cpa'];
    $ygMetricKeys = [
        'imp' => 'IMP', 'vimp' => 'vIMP', 'vcpm' => 'vCPM', 'cpm' => 'CPM', 'cts' => 'CTs',
        'ctr' => 'CTR', 'cpc' => 'CPC', 'view_rate' => '視聴率', 'view_count' => '視聴数',
        'cvr' => 'CVR', 'cv' => 'CVs', 'cpa' => 'CPA', 'vimp_rate' => '推定vimp率',
    ];
    $ygCaseKeys = [
        'cpc'       => ['imp', 'cts', 'ctr', 'cpc', 'cvr', 'cv', 'cpa'],
        'vcpm'      => ['imp', 'vimp', 'vcpm', 'cts', 'ctr', 'cpc', 'cvr', 'cv', 'cpa', 'vimp_rate'],
        'dgc_image' => ['imp', 'cpm', 'cts', 'ctr', 'cpc', 'cvr', 'cv', 'cpa'],
        'dgc_video' => ['imp', 'cpm', 'cts', 'ctr', 'cpc', 'view_rate', 'view_count', 'cvr', 'cv', 'cpa'],
    ];
    // CVR・CVs・CPAは「①GDA/YDA CPC課金」「④Listing」と同じ考え方で、CTR/CPCから逆算する
    // ベースとなるCTs・IMPを出したうえでCVRを掛けてCV/CPAを計算するため、CVRは全ケース共通で入力項目とする
    $ygCaseInputKeys = [
        'cpc'       => ['ctr', 'cpc', 'cvr'],
        'vcpm'      => ['vcpm', 'ctr', 'cvr', 'vimp_rate'],
        'dgc_image' => ['ctr', 'cpc', 'cvr'],
        'dgc_video' => ['ctr', 'cpc', 'view_rate', 'cvr'],
    ];
    $resolveYgCase = function ($p) {
        $menu = (string) ($p->menu ?? '');
        if (stripos($menu, 'DGC') !== false) {
            return (stripos($menu, '動画') !== false) ? 'dgc_video' : 'dgc_image';
        }
        if (($p->billing ?? null) === 'vCPM課金') return 'vcpm';
        if (($p->billing ?? null) === 'CPC課金') return 'cpc';
        return null;
    };

    // --- Listing
    $listingPercentKeys = ['ctr', 'cvr'];
    $listingCurrencyKeys = ['cpc', 'cpa'];
    $listingMetricKeys = ['imp' => 'IMP', 'cts' => 'CTs', 'ctr' => 'CTR', 'cpc' => 'CPC', 'cvr' => 'CVR', 'cv' => 'CVs', 'cpa' => 'CPA'];
    $listingInputKeys = ['ctr', 'cpc', 'cvr'];

    $researchYoutubeRows = collect();
    $researchMetaRows = collect();
    $researchYgRows = collect();
    $researchListingRows = collect();
    $researchOtherRows = collect();
    $researchAssignees = collect();

    foreach ($groups as $g) {
        foreach ($g['campaigns'] as $b) {
            $c = $g['client_name'];
            $proj = $g['project_name'];
            $campaign = $b['campaign'];
            if ($campaign->assignee) $researchAssignees->push($campaign->assignee);
            foreach ($b['patterns'] as $p) {
                $base = [
                    'client_name' => $c, 'project_name' => $proj,
                    'status' => $campaign->status, 'assignee' => $campaign->assignee,
                ];
                if ($campaign->platform === 'youtube') {
                    $ytBilling = $youtubeBillingMap[$p->menu ?? ''] ?? null;
                    $est = $p->estimateItem ?? null;
                    $metrics = [];
                    foreach ($ytMetricKeys as $key => $label) {
                        $metrics[$label] = $key === 'ctr' ? '0.04%' : $rsMetric($est, $key, $ytPercentKeys, $ytCurrencyKeys);
                    }
                    $researchYoutubeRows->push($base + [
                        'menu' => $p->menu ?? '—',
                        'placement' => implode('・', (array) ($p->placement ?? [])) ?: '—',
                        'creative' => '縦' . ($p->vertical_creative ?? '—') . ' / 横' . ($p->horizontal_creative ?? '—'),
                        'device' => implode('/', array_map($rsFormatDevice, (array) ($p->device ?? []))) ?: '—',
                        'age' => trim(($p->age1 ?? '') . ($p->age2 ?? '')) ?: '—',
                        'gender' => implode('/', array_map($rsFormatGender, (array) ($p->gender ?? []))) ?: '—',
                        'targeting' => $rsFormatTargeting($p),
                        'area' => $rsFormatArea($p),
                        'period' => trim(($p->period_number ?? '') . ($p->period_unit ?? '')) ?: '—',
                        'budget' => $p->budget ?? null,
                        'billing' => $ytBilling ?? '判定不可',
                        'metrics' => $metrics,
                        'notes' => $p->notes ?: '—',
                    ]);
                } elseif ($campaign->platform === 'meta') {
                    $est = $p->estimateItem ?? null;
                    $metrics = [];
                    foreach ($metaMetricKeys as $key => $label) {
                        $metrics[$label] = $rsMetric($est, $key, $metaPercentKeys, $metaCurrencyKeys);
                    }
                    $researchMetaRows->push($base + [
                        'objective' => $p->campaign_objective ?? '—',
                        'kpi' => $p->kpi ?? '—',
                        'menu' => $p->menu ?? '—',
                        'placement' => implode('・', (array) ($p->placement ?? [])) ?: '—',
                        'period' => trim(($p->period_number ?? '') . ($p->period_unit ?? '')) ?: '—',
                        'age' => ($p->age1 ?? '') . '〜' . ($p->age2 ?? '') . '歳',
                        'gender' => $p->gender ?? '—',
                        'device' => $p->device ?? '—',
                        'area' => $rsFormatArea($p),
                        'budget' => $p->budget ?? null,
                        'billing' => $p->billing ?? '—',
                        'metrics' => $metrics,
                        'notes' => $p->remarks ?: '—',
                    ]);
                } elseif ($campaign->platform === 'yg') {
                    $ygCase = $resolveYgCase($p);
                    $est = $p->estimateItem ?? null;
                    $ygApplicable = $ygCaseKeys[$ygCase] ?? [];
                    $metrics = [];
                    foreach ($ygMetricKeys as $key => $label) {
                        $metrics[$label] = in_array($key, $ygApplicable) ? $rsMetric($est, $key, $ygPercentKeys, $ygCurrencyKeys) : '—';
                    }
                    $researchYgRows->push($base + [
                        'menu' => $p->menu ?? '—',
                        'duration' => $p->video_duration ? $p->video_duration . '秒' : '—',
                        'billing' => $p->billing ?? '—',
                        'period' => trim(($p->period_number ?? '') . ($p->period_unit ?? '')) ?: '—',
                        'budget' => $p->budget ?? null,
                        'area' => $rsFormatArea($p),
                        'gender' => $p->gender ?? '—',
                        'age' => trim(($p->age1 ?? '') . ($p->age2 ?? '')) ?: '—',
                        'targeting' => $rsFormatTargeting($p),
                        'device' => implode('/', (array) ($p->device ?? [])) ?: '—',
                        'metrics' => $metrics,
                        'notes' => $p->remarks ?: '—',
                    ]);
                } elseif ($campaign->platform === 'listing') {
                    $est = $p->estimateItem ?? null;
                    $metrics = [];
                    foreach ($listingMetricKeys as $key => $label) {
                        $metrics[$label] = $rsMetric($est, $key, $listingPercentKeys, $listingCurrencyKeys);
                    }
                    $researchListingRows->push($base + [
                        'menu' => $p->menu ?? '—',
                        'period' => trim(($p->period_number ?? '') . ($p->period_unit ?? '')) ?: '—',
                        'budget' => $p->budget ?? null,
                        'area' => $rsFormatArea($p),
                        'gender' => $p->gender ?? '—',
                        'age' => trim(($p->age1 ?? '') . ($p->age2 ?? '') . ($p->age3 ? '（' . $p->age3 . '）' : '')) ?: '—',
                        'device' => implode('/', (array) ($p->device ?? [])) ?: '—',
                        'metrics' => $metrics,
                        'notes' => $p->remarks ?: '—',
                    ]);
                } else {
                    $rawPlacement = $p->placement ?? null;
                    $researchOtherRows->push($base + [
                        'platform' => $campaign->platform,
                        'menu' => $p->menu ?? '—',
                        'placement' => is_array($rawPlacement) ? implode('・', $rawPlacement) : ($rawPlacement ?: '—'),
                        'budget' => $p->budget ?? null,
                        'period' => trim(($p->period_number ?? '') . ($p->period_unit ?? '')) ?: '—',
                    ]);
                }
            }
        }
    }
    $researchAssignees = $researchAssignees->unique()->values();
    $researchPlatformTabs = collect();
    if ($researchYoutubeRows->isNotEmpty()) $researchPlatformTabs->push('youtube');
    if ($researchMetaRows->isNotEmpty()) $researchPlatformTabs->push('meta');
    if ($researchYgRows->isNotEmpty()) $researchPlatformTabs->push('yg');
    if ($researchListingRows->isNotEmpty()) $researchPlatformTabs->push('listing');
    foreach ($researchOtherRows->pluck('platform')->unique() as $op) {
        $researchPlatformTabs->push($op);
    }
@endphp
<div class="dashboard-shell">

    <header class="dash-header">
        <div class="dash-header-left">
            <span class="dash-logo-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="8" height="10" rx="2.2" fill="currentColor"/>
                    <rect x="13" y="3" width="8" height="6" rx="2.2" fill="currentColor" opacity="0.55"/>
                    <rect x="13" y="11" width="8" height="10" rx="2.2" fill="currentColor" opacity="0.8"/>
                    <rect x="3" y="15" width="8" height="6" rx="2.2" fill="currentColor" opacity="0.55"/>
                </svg>
            </span>
            <div class="dash-brand">QM-SEM-2NAVI</div>
        </div>
    </header>

    <div class="main-tabs">
        <button type="button" class="main-tab active" data-panel="project">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/></svg>
            Project
        </button>
        <button type="button" class="main-tab" data-panel="dashboard">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><rect x="7" y="12" width="3" height="6" rx="1"/><rect x="12" y="8" width="3" height="10" rx="1"/><rect x="17" y="5" width="3" height="13" rx="1"/></svg>
            Dashboard
        </button>
        <button type="button" class="main-tab" data-panel="research">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
            Research
        </button>
    </div>

    <div class="main-panel active" data-panel="project">
    <section class="cards-section">
        <div class="panel-header">
            <h2 class="card-title cal-title" style="margin:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/></svg>
                Project
            </h2>
            <div class="filter-bar">
                <div class="search-box">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
                    <input id="searchInput" placeholder="クライアント名・案件名で検索">
                </div>
            </div>
        </div>

        <div class="status-tabs">
            @foreach ($cardStatusOrder as $statusName => $statusClass)
                @php $statusItems = $cardsByStatus->get($statusName, collect()); @endphp
                <button type="button" class="status-tab{{ $loop->first ? ' active' : '' }}" data-status="{{ $statusName }}" style="--tab-color: var(--{{ $statusInkVar[$statusClass] }}-ink);">
                    <span class="status-tab-dot"></span>
                    <span>{{ $statusName }}</span>
                    <span class="status-tab-count">{{ $statusItems->count() }}</span>
                </button>
            @endforeach
        </div>

        @foreach ($cardStatusOrder as $statusName => $statusClass)
            @php $statusItems = $cardsByStatus->get($statusName, collect()); @endphp
            <div class="status-panel{{ $loop->first ? ' active' : '' }}" data-status="{{ $statusName }}">
                @forelse ($statusItems as $card)
                    <button type="button" class="mini-card"
                            data-group-key="{{ $card['group_key'] }}"
                            data-client-name="{{ $card['client_name'] }}"
                            data-project-name="{{ $card['project_name'] }}"
                            style="--card-accent: var(--{{ $statusInkVar[$statusClass] }}-ink);">
                        <div class="mini-card-title">{{ $card['client_name'] }}<span class="mini-card-project">（{{ $card['project_name'] }}）</span></div>
                        <div class="mini-card-platforms">
                            @foreach ($card['platforms'] as $plat)
                                <span class="platform-badge {{ $platformColors[$plat] ?? 'blue' }}">{{ $platformLabels[$plat] ?? $plat }}</span>
                            @endforeach
                        </div>
                        <div class="mini-card-meta">
                            <div class="mini-card-assignee">
                                @if ($card['assignee'])
                                    <span class="member-avatar small">{{ mb_substr($card['assignee'], 0, 1) }}</span>
                                    <span>{{ $card['assignee'] }}</span>
                                @else
                                    <span class="member-avatar small unassigned">?</span>
                                    <span class="unassigned-text">未アサイン</span>
                                @endif
                            </div>
                            @if ($card['due_date'])
                                <span class="mini-card-due">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
                                    {{ \Carbon\Carbon::parse($card['due_date'])->format('n/j') }}
                                </span>
                            @endif
                        </div>
                    </button>
                @empty
                    <p class="no-data-text">該当する案件はありません。</p>
                @endforelse
            </div>
        @endforeach

        <div class="project-calendar-inner" id="projectCalendarInner">
            <div class="calendar-card-header">
                <h2 class="card-title cal-title" style="margin:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
                    Calendar
                </h2>
                <div class="calendar-nav">
                    <button type="button" id="calPrevBtn" class="cal-nav-btn">‹</button>
                    <span id="calMonthLabel" class="cal-month-label"></span>
                    <button type="button" id="calNextBtn" class="cal-nav-btn">›</button>
                    <button type="button" id="calTodayBtn" class="cal-nav-btn" style="font-size:11px; width:auto; padding:0 10px;">今日</button>
                </div>
            </div>
            <div class="calendar-timeline-outer" id="calendarTimelineOuter">
                <div class="calendar-tl-grid" id="calendarTlGrid"></div>
            </div>
        </div>
    </section>
    </div>

    <div class="main-panel" data-panel="dashboard">
    <div class="dashboard-row">
        <section class="glass-card dashboard-mini-card">
            <h2 class="card-title cal-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M6 16l4-5 4 3 5-8"/></svg>
                月ごとの件数推移
            </h2>
            <div class="dashboard-mini-body">
                <canvas id="monthlyTrendChart" height="200"></canvas>
            </div>
        </section>

        <div class="dashboard-stack">
            <section class="glass-card dashboard-mini-card dashboard-mini-card-half">
                <h2 class="card-title cal-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/></svg>
                    今月件数の内訳
                </h2>
                <div class="dashboard-mini-body donut-body">
                    @if (array_sum($monthlyStatusCounts) > 0)
                        <div class="donut-canvas-wrap"><canvas id="monthlyStatusDonutChart"></canvas></div>
                        <ul class="donut-legend">
                            @foreach ($monthlyStatusCounts as $stName => $stValue)
                                <li><span class="donut-dot" style="background: {{ $monthlyStatusColors[$stName] }};"></span>{{ $stName }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="no-data-text">今月の依頼はまだありません。</p>
                    @endif
                </div>
            </section>

            <section class="glass-card dashboard-mini-card dashboard-mini-card-half">
                <h2 class="card-title cal-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/></svg>
                    今月件数の媒体内訳
                </h2>
                <div class="dashboard-mini-body donut-body">
                    @if (count($monthlyPlatformValues) > 0)
                        <div class="donut-canvas-wrap"><canvas id="monthlyPlatformDonutChart"></canvas></div>
                        <ul class="donut-legend">
                            @foreach ($monthlyPlatformLabels as $mpi => $mpLabel)
                                <li><span class="donut-dot" style="background: {{ $monthlyPlatformChartColorList[$mpi] }};"></span>{{ $mpLabel }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="no-data-text">今月の依頼はまだありません。</p>
                    @endif
                </div>
            </section>
        </div>

        <section class="glass-card dashboard-mini-card">
            <h2 class="card-title cal-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><rect x="7" y="8" width="10" height="3" rx="1"/><rect x="7" y="13" width="14" height="3" rx="1"/></svg>
                メンバーごとの件数
            </h2>
            <div class="dashboard-mini-body">
                @if (count($memberCounts) > 0)
                    <canvas id="memberCountChart" height="200"></canvas>
                @else
                    <p class="no-data-text">今月アサインされた案件はまだありません。</p>
                @endif
            </div>
        </section>
    </div>
    </div>

    <div class="main-panel" data-panel="research">
    <section class="cards-section">
        <div class="panel-header">
            <h2 class="card-title cal-title" style="margin:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
                Research
            </h2>
            <div class="filter-bar">
                <select id="researchAssigneeFilter" class="research-select">
                    <option value="">担当者：すべて</option>
                    @foreach ($researchAssignees as $ra)
                        <option value="{{ $ra }}">{{ $ra }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="research-media-tabs">
            @foreach ($researchPlatformTabs as $rp)
                <button type="button" class="research-media-tab{{ $loop->first ? ' active' : '' }}" data-platform="{{ $rp }}">{{ $platformLabels[$rp] ?? $rp }}</button>
            @endforeach
        </div>

        {{-- 案件カードを開いた時と同じ「メニュー～指標」の内容を、媒体ごとに全案件横断で一覧化（表示専用） --}}
        @if ($researchYoutubeRows->isNotEmpty())
        <div class="research-platform-panel" data-platform="youtube">
            <div class="research-table-wrap">
                <table class="research-table">
                    <thead>
                        <tr>
                            <th>クライアント / 案件</th><th>メニュー</th><th>配信面</th><th>広告素材</th>
                            <th>デバイス</th><th>年齢</th><th>性別</th><th>ターゲティング</th>
                            <th>エリア</th><th>配信期間</th><th>予算</th><th>課金形態</th>
                            @foreach ($ytMetricKeys as $ytLabel)
                                <th>{{ $ytLabel }}</th>
                            @endforeach
                            <th>備考</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($researchYoutubeRows as $row)
                            <tr class="research-row" data-assignee="{{ $row['assignee'] }}">
                                <td>{{ $row['client_name'] }}<span class="mini-card-project">（{{ $row['project_name'] }}）</span></td>
                                <td>{{ $row['menu'] }}</td>
                                <td>{{ $row['placement'] }}</td>
                                <td>{{ $row['creative'] }}</td>
                                <td>{{ $row['device'] }}</td>
                                <td>{{ $row['age'] }}</td>
                                <td>{{ $row['gender'] }}</td>
                                <td>{{ $row['targeting'] }}</td>
                                <td>{{ $row['area'] }}</td>
                                <td>{{ $row['period'] }}</td>
                                <td>{{ $row['budget'] !== null ? '¥'.number_format($row['budget']) : '—' }}</td>
                                <td>{{ $row['billing'] }}</td>
                                @foreach ($ytMetricKeys as $ytLabel)
                                    <td>{{ $row['metrics'][$ytLabel] }}</td>
                                @endforeach
                                <td>{{ $row['notes'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if ($researchMetaRows->isNotEmpty())
        <div class="research-platform-panel" data-platform="meta">
            <div class="research-table-wrap">
                <table class="research-table">
                    <thead>
                        <tr>
                            <th>クライアント / 案件</th><th>キャンペーン目的</th><th>KPI</th><th>メニュー</th><th>配信面</th>
                            <th>配信期間</th><th>年齢</th><th>性別</th><th>デバイス</th><th>エリア</th>
                            <th>予算</th><th>課金形態</th>
                            @foreach ($metaMetricKeys as $metaLabel)
                                <th>{{ $metaLabel }}</th>
                            @endforeach
                            <th>備考</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($researchMetaRows as $row)
                            <tr class="research-row" data-assignee="{{ $row['assignee'] }}">
                                <td>{{ $row['client_name'] }}<span class="mini-card-project">（{{ $row['project_name'] }}）</span></td>
                                <td>{{ $row['objective'] }}</td>
                                <td>{{ $row['kpi'] }}</td>
                                <td>{{ $row['menu'] }}</td>
                                <td>{{ $row['placement'] }}</td>
                                <td>{{ $row['period'] }}</td>
                                <td>{{ $row['age'] }}</td>
                                <td>{{ $row['gender'] }}</td>
                                <td>{{ $row['device'] }}</td>
                                <td>{{ $row['area'] }}</td>
                                <td>{{ $row['budget'] !== null ? '¥'.number_format($row['budget']) : '—' }}</td>
                                <td>{{ $row['billing'] }}</td>
                                @foreach ($metaMetricKeys as $metaLabel)
                                    <td>{{ $row['metrics'][$metaLabel] }}</td>
                                @endforeach
                                <td>{{ $row['notes'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if ($researchYgRows->isNotEmpty())
        <div class="research-platform-panel" data-platform="yg">
            <div class="research-table-wrap">
                <table class="research-table">
                    <thead>
                        <tr>
                            <th>クライアント / 案件</th><th>メニュー</th><th>動画尺</th><th>課金形態</th><th>配信期間</th>
                            <th>予算</th><th>エリア</th><th>性別</th><th>年齢</th>
                            <th>ターゲティング</th><th>デバイス</th>
                            @foreach ($ygMetricKeys as $ygLabel)
                                <th>{{ $ygLabel }}</th>
                            @endforeach
                            <th>備考</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($researchYgRows as $row)
                            <tr class="research-row" data-assignee="{{ $row['assignee'] }}">
                                <td>{{ $row['client_name'] }}<span class="mini-card-project">（{{ $row['project_name'] }}）</span></td>
                                <td>{{ $row['menu'] }}</td>
                                <td>{{ $row['duration'] }}</td>
                                <td>{{ $row['billing'] }}</td>
                                <td>{{ $row['period'] }}</td>
                                <td>{{ $row['budget'] !== null ? '¥'.number_format($row['budget']) : '—' }}</td>
                                <td>{{ $row['area'] }}</td>
                                <td>{{ $row['gender'] }}</td>
                                <td>{{ $row['age'] }}</td>
                                <td>{{ $row['targeting'] }}</td>
                                <td>{{ $row['device'] }}</td>
                                @foreach ($ygMetricKeys as $ygLabel)
                                    <td>{{ $row['metrics'][$ygLabel] }}</td>
                                @endforeach
                                <td>{{ $row['notes'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if ($researchListingRows->isNotEmpty())
        <div class="research-platform-panel" data-platform="listing">
            <div class="research-table-wrap">
                <table class="research-table">
                    <thead>
                        <tr>
                            <th>クライアント / 案件</th><th>メニュー</th><th>配信期間</th><th>予算</th>
                            <th>エリア</th><th>性別</th><th>年齢</th><th>デバイス</th>
                            @foreach ($listingMetricKeys as $lsLabel)
                                <th>{{ $lsLabel }}</th>
                            @endforeach
                            <th>備考</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($researchListingRows as $row)
                            <tr class="research-row" data-assignee="{{ $row['assignee'] }}">
                                <td>{{ $row['client_name'] }}<span class="mini-card-project">（{{ $row['project_name'] }}）</span></td>
                                <td>{{ $row['menu'] }}</td>
                                <td>{{ $row['period'] }}</td>
                                <td>{{ $row['budget'] !== null ? '¥'.number_format($row['budget']) : '—' }}</td>
                                <td>{{ $row['area'] }}</td>
                                <td>{{ $row['gender'] }}</td>
                                <td>{{ $row['age'] }}</td>
                                <td>{{ $row['device'] }}</td>
                                @foreach ($listingMetricKeys as $lsLabel)
                                    <td>{{ $row['metrics'][$lsLabel] }}</td>
                                @endforeach
                                <td>{{ $row['notes'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @foreach ($researchPlatformTabs as $rp)
            @if (!in_array($rp, ['youtube', 'meta', 'yg', 'listing']))
            <div class="research-platform-panel" data-platform="{{ $rp }}">
                <div class="research-table-wrap">
                    <table class="research-table">
                        <thead>
                            <tr>
                                <th>クライアント / 案件</th><th>メニュー</th><th>配信面</th><th>予算</th><th>期間</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($researchOtherRows->where('platform', $rp) as $row)
                                <tr class="research-row" data-assignee="{{ $row['assignee'] }}">
                                    <td>{{ $row['client_name'] }}<span class="mini-card-project">（{{ $row['project_name'] }}）</span></td>
                                    <td>{{ $row['menu'] }}</td>
                                    <td>{{ $row['placement'] }}</td>
                                    <td>{{ $row['budget'] !== null ? '¥'.number_format($row['budget']) : '—' }}</td>
                                    <td>{{ $row['period'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach

        @if ($researchPlatformTabs->isEmpty())
            <p class="no-data-text">該当するパターンはありません。</p>
        @endif
    </section>
    </div>
</div>

<div class="campaign-modal-overlay" id="campaignDetailModal">
    <div class="campaign-modal">
        <button type="button" class="campaign-modal-close" id="campaignModalClose">×</button>
        <div class="campaign-modal-scroll" id="detail-store">
    @forelse ($groups as $group)
        @php
            $platformOrder = ['youtube', 'yg', 'listing', 'meta', 'x', 'line'];
            $existingPlatformsInGroup = collect($group['campaigns'])->pluck('campaign.platform')->unique();
            $uniquePlatforms = collect($platformOrder)->filter(function ($p) use ($existingPlatformsInGroup) {
                return $existingPlatformsInGroup->contains($p);
            })->values();
            $groupPlatformLabels = $uniquePlatforms->map(function ($p) use ($platformLabels) {
                return $platformLabels[$p] ?? $p;
            })->implode('/');

            // タブは媒体ごとに1つ。同じ媒体の案件（campaign行）が1グループ内に複数あっても
            // タブは増やさず、それらのパターンを1つのテーブルに結合して連番で表示する。
            $statusPriority = ['Assign待ち', '対応中', '送付済み'];
            $statusClassMap = [
                'Assign待ち' => 'waiting',
                '対応中'    => 'inprogress',
                '送付済み'  => 'sent',
            ];

            // クライアント名×案件名＝1つの「案件」という単位に合わせて、
            // 対応します／メール送信ボタン・ステータス表示は「媒体ごと」ではなく「案件（グループ）全体」で1つにする。
            // ステータスは、グループ内の全campaign行のうち最も手前の段階（Assign待ち＞対応中＞送付済み）を採用。
            $groupCampaignObjs = collect($group['campaigns'])->pluck('campaign');
            $groupStatus = collect($statusPriority)->first(function ($sp) use ($groupCampaignObjs) {
                return $groupCampaignObjs->contains(fn ($c) => $c->status === $sp);
            }) ?? 'Assign待ち';
            // 「対応します」「メール送信」は、この案件に紐づく全媒体のcampaign行に対して一斉に行う
            $groupAllIds = $groupCampaignObjs->pluck('id')->implode(',');
            $groupRepCampaign = $groupCampaignObjs->first();
            $groupAssigneeName = $groupCampaignObjs->pluck('assignee')->filter()->first();
            $groupCurrentStatusClass = $statusClassMap[$groupStatus] ?? 'waiting';

            $mergedBlocks = $uniquePlatforms->map(function ($plat) use ($group, $statusPriority) {
                $items = collect($group['campaigns'])->filter(fn($b) => $b['campaign']->platform === $plat)->values();

                // 代表となるcampaign行：ステータスが最も手前の段階のものを採用（通常は全て同じ値のはず）
                $repCampaign = $items->first()['campaign'];
                foreach ($statusPriority as $sp) {
                    $match = $items->first(fn($it) => $it['campaign']->status === $sp);
                    if ($match) { $repCampaign = $match['campaign']; break; }
                }
                // 対応します／送付ボタン・カレンダー連動が、この媒体の全campaign行に対して行われるように
                // カンマ区切りのIDリストを代表campaignに付与しておく（1件だけなら自身のIDのみ）
                $repCampaign->merged_ids = $items->map(fn($it) => $it['campaign']->id)->implode(',');

                // 全campaign行のパターンを1つに結合し、連番を振り直す
                $mergedPatterns = collect();
                foreach ($items as $it) {
                    foreach ($it['patterns'] as $p) { $mergedPatterns->push($p); }
                }

                return [
                    'campaign' => $repCampaign,
                    'patterns' => $mergedPatterns->values(),
                ];
            })->values();
        @endphp
        <div class="client-group" data-group-key="{{ $loop->index }}" data-client-name="{{ $group['client_name'] }}" data-project-name="{{ $group['project_name'] }}" data-status="{{ $groupStatus }}" data-assignee="{{ $groupAssigneeName }}">
            {{-- 対応します／メール送信・ステータス表示は「案件（クライアント×案件名）」につき1つだけ。
                 媒体が複数あっても、ここでまとめて全campaign行（$groupAllIds）に対して操作する。 --}}
            <div class="summary-bar">
                <div class="summary-top-row">
                    <div class="summary-title-group">
                        <span class="group-title">{{ $group['client_name'] }}<span class="project-name">（{{ $group['project_name'] }}）</span></span>
                    </div>
                    <div class="summary-left-actions">
                        <button
                            type="button"
                            class="assign-btn"
                            data-campaign-id="{{ $groupAllIds }}"
                            style="{{ $groupStatus === 'Assign待ち' ? '' : 'display:none;' }}"
                        >🔥対応します</button>
                        <button
                            type="button"
                            class="mail-btn"
                            title="メールを送信"
                            data-campaign-id="{{ $groupAllIds }}"
                            data-email="{{ $groupRepCampaign->entry_email ?? '' }}"
                            data-client="{{ $group['client_name'] }}"
                            data-project="{{ $group['project_name'] }}"
                            data-platforms="{{ $groupPlatformLabels }}"
                            data-applicant="{{ $groupRepCampaign->applicant_name ?? '' }}"
                            style="{{ $groupStatus === '対応中' ? '' : 'display:none;' }}"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 6l10 7L22 6"/></svg>
                            <span>メール送信</span>
                        </button>
                    </div>
                </div>

                <table class="mini-info-table">
                    <thead>
                        <tr><th>ステータス</th><th>依頼者名</th><th>納期</th><th>備考</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="status-pill {{ $groupCurrentStatusClass }}">
                                    <span class="dot"></span>
                                    <span class="status-text">{{ $groupStatus }}@if ($groupAssigneeName && $groupStatus !== 'Assign待ち')（{{ $groupAssigneeName }}）@endif</span>
                                </span>
                            </td>
                            <td>{{ $groupRepCampaign->applicant_name ?? '' }}</td>
                            <td>{{ $groupRepCampaign->entry_due_date ?? '' ?: '未設定' }}</td>
                            <td>{{ $groupRepCampaign->entry_remarks ?? '' ?: 'なし' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @foreach ($mergedBlocks as $block)
                @php
                    $campaign = $block['campaign'];
                    $platform = $campaign->platform;
                @endphp

                <div class="campaign-block" data-campaign-id="{{ $campaign->merged_ids }}" data-status="{{ $groupStatus }}" data-platform="{{ $platform }}" data-assignee="{{ $groupAssigneeName }}">
                    <div class="detail-body">
                        <div class="group-tabs">
                            @foreach ($uniquePlatforms as $tabPlatform)
                                <button
                                    type="button"
                                    class="group-tab {{ $tabPlatform === $platform ? 'active' : '' }}"
                                    data-platform="{{ $tabPlatform }}"
                                >
                                    @if (in_array($tabPlatform, ['youtube', 'yg', 'meta', 'listing']))
                                        <canvas class="tab-lottie-canvas" data-platform="{{ $tabPlatform }}" width="132" height="132"></canvas>
                                    @endif
                                    {{ $platformLabels[$tabPlatform] ?? $tabPlatform }}
                                </button>
                            @endforeach
                        </div>

                        @if ($platform === 'youtube')
                            <div class="yt-table-toolbar">
                                <button type="button" class="yt-detail-toggle" data-group="cv">CV指標を表示</button>
                                <button type="button" class="yt-detail-toggle" data-group="fq">FQ指標を表示</button>
                            </div>
                            <div class="pattern-table-wrap">
                                @php
                                    // ターゲティング①～③のうち、この案件群で実際に入力されている枠だけ列を出す
                                    $ytHasTargeting1 = false; $ytHasTargeting2 = false; $ytHasTargeting3 = false;
                                    foreach ($block['patterns'] as $pp) {
                                        if (!empty($pp->targeting1_type) || !empty((array) ($pp->targeting1_values ?? []))) $ytHasTargeting1 = true;
                                        if (!empty($pp->targeting2_type) || !empty((array) ($pp->targeting2_values ?? []))) $ytHasTargeting2 = true;
                                        if (!empty($pp->targeting3_type) || !empty((array) ($pp->targeting3_values ?? []))) $ytHasTargeting3 = true;
                                    }
                                    $ytHasAnyTargeting = $ytHasTargeting1 || $ytHasTargeting2 || $ytHasTargeting3;
                                @endphp
                                <table class="pattern-table" style="min-width: 1900px;">
                                    <colgroup>
                                        <col style="width:30px;">
                                        <col style="width:150px;">
                                        <col style="width:160px;">
                                        <col style="width:130px;">
                                        <col style="width:70px;">
                                        <col style="width:80px;">
                                        <col style="width:60px;">
                                        @if ($ytHasAnyTargeting)
                                            @if ($ytHasTargeting1)<col style="width:160px;">@endif
                                            @if ($ytHasTargeting2)<col style="width:160px;">@endif
                                            @if ($ytHasTargeting3)<col style="width:160px;">@endif
                                        @else
                                            <col style="width:160px;">
                                        @endif
                                        <col style="width:170px;">
                                        <col style="width:80px;">
                                        <col style="width:110px;">
                                        <col style="width:70px;">
                                        <col style="width:90px;">
                                        <col style="width:70px;">
                                        <col style="width:90px;">
                                        <col style="width:70px;">
                                        <col style="width:90px;">
                                        <col style="width:80px;">
                                        <col style="width:90px;">
                                        <col style="width:70px;">
                                        <col style="width:80px;">
                                        <col style="width:70px;">
                                        <col style="width:70px;">
                                        <col style="width:80px;">
                                        <col style="width:90px;">
                                        <col style="width:160px;">
                                        <col style="width:180px;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>#</th><th>メニュー</th><th>配信面</th><th>広告素材</th>
                                            <th>デバイス</th><th>年齢</th><th>性別</th>
                                            @if ($ytHasAnyTargeting)
                                                @if ($ytHasTargeting1)<th>ターゲティング①</th>@endif
                                                @if ($ytHasTargeting2)<th>ターゲティング②</th>@endif
                                                @if ($ytHasTargeting3)<th>ターゲティング③</th>@endif
                                            @else
                                                <th>ターゲティング</th>
                                            @endif
                                            <th>エリア</th><th>配信期間</th>
                                            <th>予算</th><th>課金形態</th>
                                            <th>IMP</th><th>CPM</th><th>CTs</th><th>CTR</th><th>CPC</th>
                                            <th>視聴率</th><th>視聴数</th>
                                            <th>CPV</th><th>視聴完了率</th><th>視聴完了数</th>
                                            <th class="yt-detail-cv">CVR</th><th class="yt-detail-cv">CVs</th><th class="yt-detail-cv">CPA</th>
                                            <th class="yt-detail-fq">想定FQ</th><th class="yt-detail-fq">リーチ数</th>
                                            <th>備考</th><th>MAX出稿金額</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // 性別・デバイスの表記ゆれ（英語のまま届く場合）をVue側の日本語表記に揃える
                                            $formatGender = function ($g) {
                                                $g = trim((string) $g);
                                                $map = ['male' => '男性', 'MALE' => '男性', 'Male' => '男性',
                                                        'female' => '女性', 'FEMALE' => '女性', 'Female' => '女性',
                                                        'all' => 'ALL', 'ALL' => 'ALL', 'All' => 'ALL'];
                                                return $map[$g] ?? $g;
                                            };
                                            $formatDevice = function ($d) {
                                                return strtoupper(trim((string) $d));
                                            };
                                            // ターゲティングの選択値（文字列配列/オブジェクト配列どちらでも）を表示用文字列に整形
                                            $formatTargetingValues = function ($values) {
                                                if (!is_array($values) || empty($values)) return '';
                                                $labels = array_map(function ($v) {
                                                    if (is_array($v)) {
                                                        return $v['label'] ?? $v['name'] ?? $v['text'] ?? $v['value'] ?? '';
                                                    }
                                                    if (is_object($v)) {
                                                        $arr = (array) $v;
                                                        return $arr['label'] ?? $arr['name'] ?? $arr['text'] ?? $arr['value'] ?? '';
                                                    }
                                                    return (string) $v;
                                                }, $values);
                                                $labels = array_filter($labels, fn($l) => $l !== '');
                                                return implode('・', $labels);
                                            };
                                            // メニュー名から課金形態を判定するマッピング
                                            $youtubeBillingMap = [
                                                'VRC2.0（リーチ）' => 'CPM課金',
                                                'スキップ不可' => 'CPM課金',
                                                'VVC（視聴）' => 'CPV課金',
                                                // 目標FQ系（マルチフォーマット／スキップ可／スキップ不可）は要確認
                                            ];
                                            $resolveYoutubeBilling = function ($menu) use ($youtubeBillingMap) {
                                                return $youtubeBillingMap[$menu] ?? null;
                                            };
                                            $ytChargeInputKeys = [
                                                'CPV課金' => ['cpv', 'view_rate', 'view_complete_rate', 'cvr'],
                                                'CPM課金' => ['cpm', 'view_rate', 'view_complete_rate', 'cvr'],
                                            ];
                                            // 課金形態に関わらず手入力する指標（広告コンソール等の実績値を転記する想定FQ・MAX出稿金額）
                                            $alwaysInputKeys = ['fq'];
                                            // 「CV案件の指標を表示」「FQ案件の指標を表示」ボタンで開閉するグループ（両方同時にONも可）
                                            $detailKeysCv = ['cvr', 'cv', 'cpa'];
                                            $detailKeysFq = ['fq', 'reach'];
                                            $ytMetricKeys = ['imp', 'cpm', 'cts', 'ctr', 'cpc', 'view_rate', 'view_count', 'cpv', 'view_complete_rate', 'view_complete', 'cvr', 'cv', 'cpa', 'fq', 'reach'];
                                            $percentKeys = ['ctr', 'cvr', 'view_rate', 'view_complete_rate', 'vimp_rate'];
                                            $currencyKeys = ['cpm', 'cpv', 'vcpm', 'cpc', 'cpa', 'max_budget']; // 入力・自動計算どちらでも金額として扱うキー
                                            $decimalKeys = ['fq']; // 小数第1位まで許容する指標（想定FQ）
                                            $ytFixedCtr = 0.0004; // CTRは0.04%で固定
                                        @endphp
                                        @foreach ($block['patterns'] as $i => $p)
                                        @php
                                            $ytBilling = $resolveYoutubeBilling($p->menu);
                                            $est = $p->estimateItem;
                                            $ytInputKeys = $ytChargeInputKeys[$ytBilling] ?? [];
                                        @endphp
                                        <tr data-pattern-id="{{ $p->id }}" data-platform="youtube" data-billing="{{ $ytBilling }}" data-budget="{{ $p->budget }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $p->menu }}</td>
                                            <td>@foreach ($p->placement as $v)<span class="plain-tag">{{ $v }}</span>@endforeach</td>
                                            <td>縦{{ $p->vertical_creative }} / 横{{ $p->horizontal_creative }}</td>
                                            <td>{{ implode('/', array_map($formatDevice, (array) $p->device)) }}</td>
                                            <td>{{ $p->age1 }}{{ $p->age2 }}</td>
                                            <td>{{ implode('/', array_map($formatGender, (array) $p->gender)) }}</td>
                                            @if ($ytHasAnyTargeting)
                                                @if ($ytHasTargeting1)
                                                    <td>{{ $p->targeting1_type ? $p->targeting1_type . '：' . $formatTargetingValues($p->targeting1_values ?? []) : '—' }}</td>
                                                @endif
                                                @if ($ytHasTargeting2)
                                                    <td>{{ $p->targeting2_type ? $p->targeting2_type . '：' . $formatTargetingValues($p->targeting2_values ?? []) : '—' }}</td>
                                                @endif
                                                @if ($ytHasTargeting3)
                                                    <td>{{ $p->targeting3_type ? $p->targeting3_type . '：' . $formatTargetingValues($p->targeting3_values ?? []) : '—' }}</td>
                                                @endif
                                            @else
                                                <td>なし</td>
                                            @endif
                                            <td>@foreach ($p->pref_names as $v)<span class="plain-tag">{{ $v }}</span>@endforeach @if($p->city)<span class="plain-tag">&lt;{{ $p->city }}</span>@endif</td>
                                            <td>{{ $p->period_number }}{{ $p->period_unit }}</td>
                                            <td>¥{{ number_format($p->budget) }}</td>
                                            <td>{{ $ytBilling ?? '判定不可' }}</td>
                                            @foreach ($ytMetricKeys as $key)
                                                @php
                                                    $detailClass = in_array($key, $detailKeysCv) ? ' yt-detail-cv' : (in_array($key, $detailKeysFq) ? ' yt-detail-fq' : '');
                                                @endphp
                                                @if ($key === 'ctr')
                                                    <td class="estimate-cell estimate-computed" data-key="ctr">{{ number_format($ytFixedCtr * 100, 2) }}%</td>
                                                @elseif ($key === 'reach')
                                                    <td class="estimate-cell estimate-computed{{ $detailClass }}" data-key="reach">
                                                        {{ $est && isset($est->reach) ? number_format($est->reach) : '—' }}
                                                    </td>
                                                @elseif (in_array($key, $ytInputKeys) || in_array($key, $alwaysInputKeys))
                                                    <td class="estimate-cell{{ $detailClass }}">
                                                        <div class="estimate-input-wrap">
                                                            @if (in_array($key, $currencyKeys))<span class="unit">¥</span>@endif
                                                            <input type="number" step="{{ in_array($key, $percentKeys) ? '0.01' : (in_array($key, $decimalKeys) ? '0.1' : '1') }}" class="estimate-input"
                                                                   data-key="{{ $key }}"
                                                                   data-format="{{ in_array($key, $percentKeys) ? 'percent' : (in_array($key, $currencyKeys) ? 'currency' : (in_array($key, $decimalKeys) ? 'decimal' : 'count')) }}"
                                                                   value="{{ $est && isset($est->$key) ? (in_array($key, $percentKeys) ? $est->$key * 100 : (in_array($key, $currencyKeys) ? round($est->$key) : $est->$key)) : '' }}">
                                                            @if (in_array($key, $percentKeys))<span class="unit">%</span>@endif
                                                            @if ($key === 'fq')<span class="unit">回</span>@endif
                                                        </div>
                                                    </td>
                                                @elseif ($ytBilling)
                                                    <td class="estimate-cell estimate-computed{{ $detailClass }}" data-key="{{ $key }}">
                                                        {{ $est && isset($est->$key) ? (in_array($key, $currencyKeys) ? '¥'.number_format($est->$key) : (in_array($key, ['ctr', 'cvr']) ? number_format($est->$key, 2) : number_format($est->$key, 0))) : '—' }}
                                                    </td>
                                                @else
                                                    <td class="estimate-cell estimate-na{{ $detailClass }}">—</td>
                                                @endif
                                            @endforeach
                                            <td>{{ $p->notes ?: '—' }}</td>
                                            @php
                                                $maxBudgetMode = ($est && isset($est->max_budget_mode) && $est->max_budget_mode === 'unlimited') ? 'unlimited' : 'amount';
                                            @endphp
                                            <td class="estimate-cell max-budget-cell">
                                                <div class="max-budget-toggle" data-mode="{{ $maxBudgetMode }}">
                                                    <button type="button" class="max-budget-mode-btn{{ $maxBudgetMode === 'amount' ? ' active' : '' }}" data-mode="amount">¥金額</button>
                                                    <button type="button" class="max-budget-mode-btn{{ $maxBudgetMode === 'unlimited' ? ' active' : '' }}" data-mode="unlimited">億単位で出稿可</button>
                                                </div>
                                                <div class="estimate-input-wrap max-budget-input-wrap"{{ $maxBudgetMode === 'unlimited' ? ' style="display:none;"' : '' }}>
                                                    <span class="unit">¥</span>
                                                    <input type="text" inputmode="numeric" class="estimate-input comma-input max-budget-input"
                                                           data-key="max_budget"
                                                           data-format="currency"
                                                           value="{{ $est && isset($est->max_budget) ? number_format($est->max_budget) : '' }}">
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($platform === 'meta')
                            <div class="pattern-table-wrap">
                                <table class="pattern-table" style="min-width: 1370px;">
                                    <colgroup>
                                        <col style="width:30px;">
                                        <col style="width:130px;">
                                        <col style="width:70px;">
                                        <col style="width:140px;">
                                        <col style="width:160px;">
                                        <col style="width:100px;">
                                        <col style="width:60px;">
                                        <col style="width:90px;">
                                        <col style="width:80px;">
                                        <col style="width:160px;">
                                        <col style="width:110px;">
                                        <col style="width:90px;">
                                        <col style="width:80px;">
                                        <col style="width:90px;">
                                        <col style="width:70px;">
                                        <col style="width:70px;">
                                        <col style="width:70px;">
                                        <col style="width:100px;">
                                        <col style="width:100px;">
                                        <col style="width:80px;">
                                        <col style="width:80px;">
                                        <col style="width:80px;">
                                        <col style="width:150px;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>#</th><th>キャンペーン目的</th><th>KPI</th><th>メニュー</th><th>配信面</th>
                                            <th>配信期間</th><th>年齢</th><th>性別</th><th>デバイス</th><th>エリア</th>
                                            <th>予算</th><th>課金形態</th>
                                            <th>vCPM</th><th>推定vimp率</th><th>CTR</th><th>CPC</th><th>CVR</th>
                                            <th>vIMP(想定)</th><th>IMP(想定)</th><th>CTs</th><th>CV</th><th>CPA</th>
                                            <th>備考</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // 課金形態ごとに「入力欄にする指標キー」を定義（それ以外は自動計算 or 対象外）
                                            $chargeInputKeys = [
                                                'CPC課金'  => ['ctr', 'cpc', 'cvr'],
                                                'vCPM課金' => ['vcpm', 'ctr', 'cvr', 'vimp_rate'],
                                            ];
                                            $metricKeys = ['vcpm', 'vimp_rate', 'ctr', 'cpc', 'cvr', 'vimp', 'imp', 'cts', 'cv', 'cpa'];
                                            $percentKeys = ['ctr', 'cvr', 'view_rate', 'view_complete_rate', 'vimp_rate'];
                                            $currencyKeys = ['cpm', 'cpv', 'vcpm', 'cpc', 'cpa'];
                                        @endphp
                                        @foreach ($block['patterns'] as $i => $p)
                                        @php
                                            $est = $p->estimateItem; // estimate_itemsとの1:1リレーション（未保存ならnull）
                                            $inputKeys = $chargeInputKeys[$p->billing] ?? [];
                                        @endphp
                                        <tr data-pattern-id="{{ $p->id }}" data-platform="meta" data-billing="{{ $p->billing }}" data-budget="{{ $p->budget }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $p->campaign_objective }}</td>
                                            <td>{{ $p->kpi }}</td>
                                            <td>{{ $p->menu }}</td>
                                            <td>@foreach ($p->placement as $v)<span class="plain-tag">{{ $v }}</span>@endforeach</td>
                                            <td>{{ $p->period_number }}{{ $p->period_unit }}</td>
                                            <td>{{ $p->age1 }}〜{{ $p->age2 }}歳</td>
                                            <td>{{ $p->gender }}</td>
                                            <td>{{ $p->device }}</td>
                                            <td>@foreach ($p->pref_names as $v)<span class="plain-tag">{{ $v }}</span>@endforeach @if($p->city) ・{{ $p->city }} @endif</td>
                                            <td>¥{{ number_format($p->budget) }}</td>
                                            <td>{{ $p->billing }}</td>
                                            @foreach ($metricKeys as $key)
                                                @if (in_array($key, $inputKeys))
                                                    <td class="estimate-cell">
                                                        <div class="estimate-input-wrap">
                                                            @if (in_array($key, $currencyKeys))<span class="unit">¥</span>@endif
                                                            <input type="number" step="{{ in_array($key, $percentKeys) ? '0.01' : '1' }}" class="estimate-input"
                                                                   data-key="{{ $key }}"
                                                                   data-format="{{ in_array($key, $percentKeys) ? 'percent' : (in_array($key, $currencyKeys) ? 'currency' : 'count') }}"
                                                                   value="{{ $est && isset($est->$key) ? (in_array($key, $percentKeys) ? $est->$key * 100 : $est->$key) : '' }}">
                                                            @if (in_array($key, $percentKeys))<span class="unit">%</span>@endif
                                                        </div>
                                                    </td>
                                                @elseif (array_key_exists($p->billing, $chargeInputKeys))
                                                    <td class="estimate-cell estimate-computed" data-key="{{ $key }}">
                                                        {{ $est && isset($est->$key) ? (in_array($key, $currencyKeys) ? '¥'.number_format($est->$key) : (in_array($key, ['ctr', 'cvr']) ? number_format($est->$key, 2) : number_format($est->$key, 0))) : '—' }}
                                                    </td>
                                                @else
                                                    <td class="estimate-cell estimate-na">—</td>
                                                @endif
                                            @endforeach
                                            <td>{{ $p->remarks ?: '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($platform === 'yg')
                            <div class="pattern-table-wrap">
                                @php
                                    $ygPercentKeys = ['ctr', 'cvr', 'view_rate', 'vimp_rate'];
                                    $ygCurrencyKeys = ['vcpm', 'cpm', 'cpc', 'cpa'];
                                    $ygMetricKeys = [
                                        'imp' => 'IMP', 'vimp' => 'vIMP', 'vcpm' => 'vCPM', 'cpm' => 'CPM', 'cts' => 'CTs',
                                        'ctr' => 'CTR', 'cpc' => 'CPC', 'view_rate' => '視聴率', 'view_count' => '視聴数',
                                        'cvr' => 'CVR', 'cv' => 'CVs', 'cpa' => 'CPA', 'vimp_rate' => '推定vimp率',
                                    ];
                                    $ygCaseKeys = [
                                        'cpc'       => ['imp', 'cts', 'ctr', 'cpc', 'cvr', 'cv', 'cpa'],
                                        'vcpm'      => ['imp', 'vimp', 'vcpm', 'cts', 'ctr', 'cpc', 'cvr', 'cv', 'cpa', 'vimp_rate'],
                                        'dgc_image' => ['imp', 'cpm', 'cts', 'ctr', 'cpc', 'cvr', 'cv', 'cpa'],
                                        'dgc_video' => ['imp', 'cpm', 'cts', 'ctr', 'cpc', 'view_rate', 'view_count', 'cvr', 'cv', 'cpa'],
                                    ];
                                    $ygCaseInputKeys = [
                                        'cpc'       => ['ctr', 'cpc', 'cvr'],
                                        'vcpm'      => ['vcpm', 'ctr', 'cvr', 'vimp_rate'],
                                        'dgc_image' => ['ctr', 'cpc', 'cvr'],
                                        'dgc_video' => ['ctr', 'cpc', 'view_rate', 'cvr'],
                                    ];
                                    $resolveYgCase = function ($p) {
                                        $menu = (string) ($p->menu ?? '');
                                        if (stripos($menu, 'DGC') !== false) {
                                            return (stripos($menu, '動画') !== false) ? 'dgc_video' : 'dgc_image';
                                        }
                                        if (($p->billing ?? null) === 'vCPM課金') return 'vcpm';
                                        if (($p->billing ?? null) === 'CPC課金') return 'cpc';
                                        return null;
                                    };
                                @endphp
                                <table class="pattern-table" style="min-width: 2050px;">
                                    <colgroup>
                                        <col style="width:30px;">
                                        <col style="width:150px;">
                                        <col style="width:70px;">
                                        <col style="width:90px;">
                                        <col style="width:90px;">
                                        <col style="width:110px;">
                                        <col style="width:160px;">
                                        <col style="width:60px;">
                                        <col style="width:80px;">
                                        <col style="width:220px;">
                                        <col style="width:80px;">
                                        @foreach ($ygMetricKeys as $ygKey => $ygLabel)
                                            <col style="width:80px;">
                                        @endforeach
                                        <col style="width:150px;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>#</th><th>メニュー</th><th>動画尺</th><th>課金形態</th><th>配信期間</th>
                                            <th>予算</th><th>エリア</th><th>性別</th><th>年齢</th>
                                            <th>ターゲティング①②③</th><th>デバイス</th>
                                            @foreach ($ygMetricKeys as $ygKey => $ygLabel)
                                                <th>{{ $ygLabel }}</th>
                                            @endforeach
                                            <th>備考</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($block['patterns'] as $i => $p)
                                        @php
                                            $ygCase = $resolveYgCase($p);
                                            $est = $p->estimateItem;
                                            $ygInputKeys = $ygCaseInputKeys[$ygCase] ?? [];
                                            $ygApplicableKeys = $ygCaseKeys[$ygCase] ?? [];
                                        @endphp
                                        <tr data-pattern-id="{{ $p->id }}" data-platform="yg" data-billing="{{ $ygCase }}" data-budget="{{ $p->budget }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $p->menu }}</td>
                                            <td>{{ $p->video_duration ? $p->video_duration.'秒' : '—' }}</td>
                                            <td>{{ $p->billing ?: ($ygCase && str_starts_with($ygCase, 'dgc') ? 'DGC' : '—') }}</td>
                                            <td>{{ $p->period_number }}{{ $p->period_unit }}</td>
                                            <td>¥{{ number_format($p->budget) }}</td>
                                            <td>@foreach ($p->pref_names as $v)<span class="plain-tag">{{ $v }}</span>@endforeach @if($p->city) ・{{ $p->city }} @endif</td>
                                            <td>{{ $p->gender }}</td>
                                            <td>{{ $p->age1 }}{{ $p->age2 }}</td>
                                            <td>
                                                {{ $p->targeting1_type }}: {{ implode('/', (array) $p->targeting1_values) }}<br>
                                                {{ $p->targeting2_type }}: {{ implode('/', (array) $p->targeting2_values) }}<br>
                                                {{ $p->targeting3_type }}: {{ implode('/', (array) $p->targeting3_values) }}
                                            </td>
                                            <td>{{ implode('/', (array) $p->device) }}</td>
                                            @foreach ($ygMetricKeys as $key => $ygLabel)
                                                @if (!in_array($key, $ygApplicableKeys))
                                                    <td class="estimate-cell estimate-na">—</td>
                                                @elseif (in_array($key, $ygInputKeys))
                                                    <td class="estimate-cell">
                                                        <div class="estimate-input-wrap">
                                                            @if (in_array($key, $ygCurrencyKeys))<span class="unit">¥</span>@endif
                                                            <input type="number" step="{{ in_array($key, $ygPercentKeys) ? '0.01' : '1' }}" class="estimate-input"
                                                                   data-key="{{ $key }}"
                                                                   data-format="{{ in_array($key, $ygPercentKeys) ? 'percent' : (in_array($key, $ygCurrencyKeys) ? 'currency' : 'count') }}"
                                                                   value="{{ $est && isset($est->$key) ? (in_array($key, $ygPercentKeys) ? $est->$key * 100 : $est->$key) : '' }}">
                                                            @if (in_array($key, $ygPercentKeys))<span class="unit">%</span>@endif
                                                        </div>
                                                    </td>
                                                @else
                                                    <td class="estimate-cell estimate-computed" data-key="{{ $key }}">
                                                        {{ $est && isset($est->$key) ? (in_array($key, $ygCurrencyKeys) ? '¥'.number_format($est->$key) : (in_array($key, $ygPercentKeys) ? number_format($est->$key, 2) : number_format($est->$key, 0))) : '—' }}
                                                    </td>
                                                @endif
                                            @endforeach
                                            <td>{{ $p->remarks ?: '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($platform === 'listing')
                            @php
                                $listingRequestTypes = json_decode($campaign->listing_request_types ?? '[]', true) ?: [];
                            @endphp
                            @if (!empty($listingRequestTypes) || $campaign->listing_lp || $campaign->listing_kw_broad || $campaign->listing_kw_phrase || $campaign->listing_kw_exact)
                                <div class="listing-info-box" style="margin-bottom:12px; padding:12px 16px; border-radius:10px; background:var(--glass); border:1px solid var(--glass-border); font-size:13px; line-height:1.7;">
                                    @if (!empty($listingRequestTypes))
                                        <div><strong>依頼種別：</strong>{{ implode('、', $listingRequestTypes) }}</div>
                                    @endif
                                    @if ($campaign->listing_lp)
                                        <div><strong>LP：</strong>{{ $campaign->listing_lp }}</div>
                                    @endif
                                    @if ($campaign->listing_kw_broad)
                                        <div><strong>部分一致（インテントマッチ）KW：</strong>{{ $campaign->listing_kw_broad }}</div>
                                    @endif
                                    @if ($campaign->listing_kw_phrase)
                                        <div><strong>フレーズ一致KW：</strong>{{ $campaign->listing_kw_phrase }}</div>
                                    @endif
                                    @if ($campaign->listing_kw_exact)
                                        <div><strong>完全一致KW：</strong>{{ $campaign->listing_kw_exact }}</div>
                                    @endif
                                </div>
                            @endif
                            <div class="pattern-table-wrap">
                                @php
                                    $listingPercentKeys = ['ctr', 'cvr'];
                                    $listingCurrencyKeys = ['cpc', 'cpa'];
                                    $listingMetricKeys = ['imp' => 'IMP', 'cts' => 'CTs', 'ctr' => 'CTR', 'cpc' => 'CPC', 'cvr' => 'CVR', 'cv' => 'CVs', 'cpa' => 'CPA'];
                                    $listingInputKeys = ['ctr', 'cpc', 'cvr'];
                                @endphp
                                <table class="pattern-table" style="min-width: 1350px;">
                                    <colgroup>
                                        <col style="width:30px;">
                                        <col style="width:70px;">
                                        <col style="width:110px;">
                                        <col style="width:90px;">
                                        <col style="width:150px;">
                                        <col style="width:70px;">
                                        <col style="width:90px;">
                                        <col style="width:110px;">
                                        @foreach ($listingMetricKeys as $lsKey => $lsLabel)
                                            <col style="width:80px;">
                                        @endforeach
                                        <col style="width:160px;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>#</th><th>メニュー</th><th>配信期間</th><th>予算</th>
                                            <th>エリア</th><th>性別</th><th>年齢</th><th>デバイス</th>
                                            @foreach ($listingMetricKeys as $lsKey => $lsLabel)
                                                <th>{{ $lsLabel }}</th>
                                            @endforeach
                                            <th>備考</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($block['patterns'] as $i => $p)
                                        @php $est = $p->estimateItem; @endphp
                                        <tr data-pattern-id="{{ $p->id }}" data-platform="listing" data-billing="listing" data-budget="{{ $p->budget }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $p->menu ?? '—' }}</td>
                                            <td>{{ $p->period_number }}{{ $p->period_unit }}</td>
                                            <td>¥{{ number_format($p->budget) }}</td>
                                            <td>@foreach ((array) $p->pref_names as $v)<span class="plain-tag">{{ $v }}</span>@endforeach @if($p->city) ・{{ $p->city }} @endif</td>
                                            <td>{{ $p->gender }}</td>
                                            <td>{{ $p->age1 }}{{ $p->age2 }}{{ $p->age3 ? '（'.$p->age3.'）' : '' }}</td>
                                            <td>{{ implode('/', (array) $p->device) }}</td>
                                            @foreach ($listingMetricKeys as $key => $lsLabel)
                                                @if (in_array($key, $listingInputKeys))
                                                    <td class="estimate-cell">
                                                        <div class="estimate-input-wrap">
                                                            @if (in_array($key, $listingCurrencyKeys))<span class="unit">¥</span>@endif
                                                            <input type="number" step="{{ in_array($key, $listingPercentKeys) ? '0.01' : '1' }}" class="estimate-input"
                                                                   data-key="{{ $key }}"
                                                                   data-format="{{ in_array($key, $listingPercentKeys) ? 'percent' : (in_array($key, $listingCurrencyKeys) ? 'currency' : 'count') }}"
                                                                   value="{{ $est && isset($est->$key) ? (in_array($key, $listingPercentKeys) ? $est->$key * 100 : $est->$key) : '' }}">
                                                            @if (in_array($key, $listingPercentKeys))<span class="unit">%</span>@endif
                                                        </div>
                                                    </td>
                                                @else
                                                    <td class="estimate-cell estimate-computed" data-key="{{ $key }}">
                                                        {{ $est && isset($est->$key) ? (in_array($key, $listingCurrencyKeys) ? '¥'.number_format($est->$key) : (in_array($key, $listingPercentKeys) ? number_format($est->$key, 2) : number_format($est->$key, 0))) : '—' }}
                                                    </td>
                                                @endif
                                            @endforeach
                                            <td>{{ $p->remarks ?: '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="no-data-text">この媒体のパターン詳細はまだ準備中です。</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <p class="no-data-text">このデータはまだありません。</p>
    @endforelse
        </div>
    </div>
</div>

<div id="mailModalOverlay" class="mail-modal-overlay">
    <div class="mail-modal">
        <div class="mail-modal-header">
            <h3>メールを送信</h3>
            <button type="button" class="mail-modal-close" id="mailModalClose">×</button>
        </div>
        <div class="mail-modal-body">
            <label>宛先</label>
            <input type="email" id="mailTo" class="mail-input">
            <label>件名</label>
            <input type="text" id="mailSubject" class="mail-input">
            <label>本文</label>
            <textarea id="mailBody" class="mail-textarea" rows="8"></textarea>
        </div>
        <div class="mail-modal-footer">
            <button type="button" class="mail-btn-secondary" id="mailCancelBtn">キャンセル</button>
            <button type="button" class="mail-btn-primary" id="mailSendBtn">送信する</button>
        </div>
    </div>
</div>

<div id="mailStatusOverlay" class="mail-status-overlay">
    <div class="mail-status-card">
        <div id="mailSendingSpinner" class="mail-status-spinner"></div>
        <div id="mailSentLottie"></div>
        <div class="mail-status-text" id="mailStatusText">送信中…</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/lottie-web@5.12.2/build/player/lottie.min.js"></script>
<script>
$(function () {
    const statusClassMap = {
        'Assign待ち': 'waiting',
        '対応中': 'inprogress',
        '送付済み': 'sent',
    };

    // ================= 月ごとの件数推移（折れ線グラフ） =================
    // Dashboardタブは初期状態で非表示（display:none）のため、Chart.jsがcanvasのサイズを
    // 正しく取得できない。タブが表示されたタイミングで resize() を呼べるようにインスタンスを外に保持しておく。
    let monthlyTrendChartInstance = null;
    (function () {
        const canvas = document.getElementById('monthlyTrendChart');
        if (!canvas || typeof Chart === 'undefined') return;
        const rootStyles = getComputedStyle(document.documentElement);
        const inkColor = (rootStyles.getPropertyValue('--ink-2') || '#566079').trim();
        const ink1Color = (rootStyles.getPropertyValue('--ink-1') || '#1c2233').trim();
        const lineColor = (rootStyles.getPropertyValue('--row-line') || 'rgba(60,70,100,0.14)').trim();
        const valueLabelPlugin = {
            id: 'valueLabelPlugin',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                chart.data.datasets.forEach((dataset, di) => {
                    const meta = chart.getDatasetMeta(di);
                    meta.data.forEach((point, i) => {
                        const value = dataset.data[i];
                        ctx.save();
                        ctx.fillStyle = ink1Color;
                        ctx.font = '600 11px -apple-system, BlinkMacSystemFont, "Noto Sans JP", sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillText(value, point.x, point.y - 8);
                        ctx.restore();
                    });
                });
            }
        };
        monthlyTrendChartInstance = new Chart(canvas, {
            type: 'line',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    label: '依頼件数',
                    data: @json($monthlyCountsValues),
                    borderColor: '#7ecbff',
                    backgroundColor: 'rgba(126,203,255,0.18)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#7ecbff',
                }]
            },
            options: {
                responsive: true,
                layout: { padding: { top: 18 } },
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: inkColor } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: inkColor }, grid: { color: lineColor } }
                }
            },
            plugins: [valueLabelPlugin]
        });
    })();

    // ================= ドーナツグラフ中央に合計件数を表示する共通プラグイン =================
    function makeDonutCenterTextPlugin(ink1Color, inkColor, numberSize) {
        numberSize = numberSize || 26;
        return {
            id: 'donutCenterText',
            afterDraw(chart) {
                const { ctx, chartArea } = chart;
                const total = chart.data.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
                const cx = (chartArea.left + chartArea.right) / 2;
                const cy = (chartArea.top + chartArea.bottom) / 2;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = ink1Color;
                ctx.font = '700 ' + numberSize + 'px -apple-system, BlinkMacSystemFont, "Noto Sans JP", sans-serif';
                ctx.fillText(String(total), cx, cy);
                ctx.restore();
            }
        };
    }

    // ================= 今月件数のステータス内訳（ドーナツグラフ） =================
    let monthlyStatusDonutChartInstance = null;
    (function () {
        const canvas = document.getElementById('monthlyStatusDonutChart');
        if (!canvas || typeof Chart === 'undefined') return;
        const rootStyles = getComputedStyle(document.documentElement);
        const waitingColor = (rootStyles.getPropertyValue('--waiting-ink') || '#c1453f').trim();
        const progressColor = (rootStyles.getPropertyValue('--progress-ink') || '#35509c').trim();
        const sentColor = (rootStyles.getPropertyValue('--sent-ink') || '#237a4f').trim();
        const inkColor = (rootStyles.getPropertyValue('--ink-2') || '#566079').trim();
        const ink1Color = (rootStyles.getPropertyValue('--ink-1') || '#1c2233').trim();
        monthlyStatusDonutChartInstance = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($monthlyStatusCounts)),
                datasets: [{
                    data: @json(array_values($monthlyStatusCounts)),
                    backgroundColor: [waitingColor, progressColor, sentColor],
                    borderColor: 'rgba(255,255,255,0.9)',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                layout: { padding: 0 },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                }
            },
            plugins: [makeDonutCenterTextPlugin(ink1Color, inkColor, 15)]
        });
    })();

    // ================= 今月件数の媒体内訳（ドーナツグラフ） =================
    let monthlyPlatformDonutChartInstance = null;
    (function () {
        const canvas = document.getElementById('monthlyPlatformDonutChart');
        if (!canvas || typeof Chart === 'undefined') return;
        const rootStyles = getComputedStyle(document.documentElement);
        const inkColor = (rootStyles.getPropertyValue('--ink-2') || '#566079').trim();
        const ink1Color = (rootStyles.getPropertyValue('--ink-1') || '#1c2233').trim();
        monthlyPlatformDonutChartInstance = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: @json($monthlyPlatformLabels),
                datasets: [{
                    data: @json($monthlyPlatformValues),
                    backgroundColor: @json($monthlyPlatformChartColorList),
                    borderColor: 'rgba(255,255,255,0.9)',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                layout: { padding: 0 },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                }
            },
            plugins: [makeDonutCenterTextPlugin(ink1Color, inkColor, 15)]
        });
    })();

    // ================= メンバーごとの件数（横向き棒グラフ） =================
    let memberCountChartInstance = null;
    (function () {
        const canvas = document.getElementById('memberCountChart');
        if (!canvas || typeof Chart === 'undefined') return;
        const rootStyles = getComputedStyle(document.documentElement);
        const inkColor = (rootStyles.getPropertyValue('--ink-2') || '#566079').trim();
        const lineColor = (rootStyles.getPropertyValue('--row-line') || 'rgba(60,70,100,0.14)').trim();
        const accentColor = (rootStyles.getPropertyValue('--accent') || '#3f5fe0').trim();
        const memberCounts = @json($memberCounts);
        const memberNames = Object.keys(memberCounts);
        memberCountChartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: memberNames,
                datasets: [{
                    label: '件数',
                    data: memberNames.map(function (n) { return memberCounts[n]; }),
                    backgroundColor: accentColor,
                    borderRadius: 6,
                    maxBarThickness: 22,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0, color: inkColor }, grid: { color: lineColor } },
                    y: { ticks: { color: inkColor }, grid: { display: false } }
                }
            }
        });
    })();

    // ================= Calendar（Notionのタイムラインビューのような横スクロール式） =================
    // カレンダーはステータス切替タブと連動させ、そのタブのステータスの案件だけを表示する。
    // 送付済みタブの時はカレンダー自体を非表示にする。
    // 対応します／送付ボタンでステータスが変わったら calendarUpdateEventStatus() 経由でその場で再描画する。
    let calendarUpdateEventStatus = function () {};
    // Projectタブが非表示の間は横スクロール位置が正しく計算できないため、表示された瞬間に合わせ直せるようにしておく。
    let calendarRefreshOnShow = function () {};
    let calendarSetStatus = function () {};
    (function () {
        const $outer = $('#calendarTimelineOuter');
        const $grid = $('#calendarTlGrid');
        const $inner = $('#projectCalendarInner');
        if (!$grid.length) return;
        const allEvents = @json($calendarEvents);
        let currentStatus = $('.status-tab.active').data('status') || 'Assign待ち';
        const DAY_W = 46; // 1日あたりの幅(px)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayKey = fmtYMD(today);

        function fmtYMD(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        // タイムラインの表示範囲：全イベント（ステータス問わず）＋今日の前後に余白を持たせて算出（タブ切替で幅が変わらないように固定）
        let rangeStart = new Date(today);
        rangeStart.setDate(rangeStart.getDate() - 7);
        let rangeEnd = new Date(today);
        rangeEnd.setDate(rangeEnd.getDate() + 30);
        allEvents.forEach(function (ev) {
            if (!ev.start) return;
            const s = new Date(ev.start + 'T00:00:00');
            const e = new Date((ev.end || ev.start) + 'T00:00:00');
            if (s < rangeStart) rangeStart = new Date(s);
            if (e > rangeEnd) rangeEnd = new Date(e);
        });
        rangeStart.setDate(rangeStart.getDate() - 3);
        rangeEnd.setDate(rangeEnd.getDate() + 7);
        const totalDays = Math.round((rangeEnd - rangeStart) / 86400000) + 1;

        // 重なる期間のイベントを段（レーン）に振り分ける
        function layoutEvents(events) {
            const segs = [];
            events.forEach(function (ev) {
                if (!ev.start) return;
                const s = new Date(ev.start + 'T00:00:00');
                const e = new Date((ev.end || ev.start) + 'T00:00:00');
                const startCol = Math.round((s - rangeStart) / 86400000);
                const endCol = Math.round((e - rangeStart) / 86400000);
                segs.push({
                    campaignId: ev.campaign_id,
                    client: ev.client,
                    project: ev.project,
                    status: ev.status,
                    startCol: Math.max(0, startCol),
                    endCol: Math.min(totalDays - 1, endCol),
                });
            });
            segs.sort(function (a, b) { return a.startCol - b.startCol; });
            const laneEnds = [];
            segs.forEach(function (seg) {
                let lane = laneEnds.findIndex(function (endCol) { return endCol < seg.startCol; });
                if (lane === -1) { lane = laneEnds.length; laneEnds.push(seg.endCol); }
                else { laneEnds[lane] = seg.endCol; }
                seg.lane = lane;
            });
            return segs;
        }

        function renderCalendar() {
            $('#calMonthLabel').text((today.getMonth() + 1) + '月');
            $grid.css('grid-template-columns', 'repeat(' + totalDays + ', ' + DAY_W + 'px)');
            $grid.empty();

            const weekdayLabels = ['日', '月', '火', '水', '木', '金', '土'];
            for (let i = 0; i < totalDays; i++) {
                const d = new Date(rangeStart);
                d.setDate(rangeStart.getDate() + i);
                const isToday = fmtYMD(d) === todayKey;
                const dow = d.getDay();
                const dayLabel = (d.getDate() === 1) ? (d.getMonth() + 1) + '/' + d.getDate() : String(d.getDate());
                const $num = $('<div class="cal-tl-daynum"></div>')
                    .toggleClass('cal-today', isToday)
                    .css('grid-column', i + 1);
                const $weekday = $('<span class="cal-tl-weekday"></span>').text(weekdayLabels[dow]);
                if (dow === 0) $weekday.addClass('is-sun');
                if (dow === 6) $weekday.addClass('is-sat');
                $num.append($weekday);
                if (isToday) {
                    $num.append($('<span class="cal-tl-daynum-badge"></span>').text(d.getDate()));
                } else {
                    $num.append($('<span class="cal-tl-daynum-num"></span>').text(dayLabel));
                }
                $grid.append($num);
            }

            const events = allEvents.filter(function (ev) { return ev.status === currentStatus; });
            const segs = layoutEvents(events);
            segs.forEach(function (seg) {
                const statusClass = statusClassMap[seg.status] || 'waiting';
                const label = seg.client + '（' + seg.project + '）';
                const $bar = $('<div class="cal-bar"></div>')
                    .addClass(statusClass)
                    .text(label)
                    .css({
                        'grid-column': (seg.startCol + 1) + ' / ' + (seg.endCol + 2),
                        'grid-row': seg.lane + 2,
                    });
                $grid.append($bar);
            });

            // 今日を示す縦線
            const todayCol = Math.round((today - rangeStart) / 86400000);
            if (todayCol >= 0 && todayCol < totalDays) {
                $('<div class="cal-tl-today-line"></div>').css('left', (todayCol * DAY_W + DAY_W / 2) + 'px').appendTo($grid);
            }
        }

        function scrollToToday(smooth) {
            const todayCol = Math.round((today - rangeStart) / 86400000);
            const target = Math.max(0, todayCol * DAY_W - 120);
            if ($outer.length) $outer.stop().animate({ scrollLeft: target }, smooth ? 300 : 0);
        }

        $('#calPrevBtn').on('click', function () { $outer.stop().animate({ scrollLeft: '-=' + (DAY_W * 7) }, 200); });
        $('#calNextBtn').on('click', function () { $outer.stop().animate({ scrollLeft: '+=' + (DAY_W * 7) }, 200); });
        $('#calTodayBtn').on('click', function () { scrollToToday(true); });

        // 案件カードのステータス変更（対応します／送付ボタン）が起きたら、その案件のイベントデータを
        // 更新して即座に再描画する（タブ切替を挟まずいちいち手動更新しなくてよいように連動させる）
        calendarUpdateEventStatus = function (campaignId, newStatus) {
            // campaignId は "1,2" のようにカンマ区切りの場合がある（同一媒体の複数案件行をまとめて更新）
            const ids = String(campaignId).split(',').map(function (s) { return s.trim(); });
            let changed = false;
            allEvents.forEach(function (ev) {
                if (ids.includes(String(ev.campaign_id))) {
                    ev.status = newStatus;
                    changed = true;
                }
            });
            if (changed) renderCalendar();
        };

        calendarRefreshOnShow = function () {
            scrollToToday(false);
        };

        calendarSetStatus = function (status) {
            currentStatus = status;
            if (status === '送付済み') {
                $inner.hide();
                return;
            }
            $inner.show();
            renderCalendar();
            scrollToToday(false);
        };

        if (currentStatus === '送付済み') {
            $inner.hide();
        } else {
            renderCalendar();
            scrollToToday(false);
        }
    })();

    // ================= メインタブ（Project / Dashboard / Research） =================
    $(document).on('click', '.main-tab', function () {
        const panel = $(this).data('panel');
        $('.main-tab').removeClass('active');
        $(this).addClass('active');
        $('.main-panel').removeClass('active');
        $('.main-panel[data-panel="' + panel + '"]').addClass('active');
        // Dashboard/Projectは非表示の間 canvas や横スクロール位置が正しく計算できないため、
        // 表示された瞬間に再計算させる（カレンダーはProjectタブの下部に常設）。
        if (panel === 'dashboard') {
            if (monthlyTrendChartInstance) monthlyTrendChartInstance.resize();
            if (monthlyStatusDonutChartInstance) monthlyStatusDonutChartInstance.resize();
            if (monthlyPlatformDonutChartInstance) monthlyPlatformDonutChartInstance.resize();
            if (memberCountChartInstance) memberCountChartInstance.resize();
        }
        if (panel === 'project') {
            calendarRefreshOnShow();
        }
    });

    // ================= Researchタブ：媒体切替タブ＋担当者フィルタ＋Excel風の列オートフィルター =================
    // 媒体ごとに列構成が異なる（メニュー～指標の内容がそのまま出る）ため、媒体タブは
    // テーブルそのものの切替に使う。各列見出しの▾から、Excelのオートフィルターのように
    // その列に実際に登場する値のチェックリストで絞り込める（ドロップダウン内の検索欄で
    // 選択肢自体を絞ることもできる）。
    function syncResearchPanel() {
        const platform = $('.research-media-tab.active').data('platform');
        $('.research-platform-panel').removeClass('active');
        $('.research-platform-panel[data-platform="' + platform + '"]').addClass('active');
    }

    function applyExcelFilters($table) {
        const assignee = $('#researchAssigneeFilter').val();
        const colFilters = $table.data('rfColFilters') || {};
        $table.find('tbody tr').each(function () {
            const $row = $(this);
            const matchAssignee = !assignee || String($row.data('assignee')) === assignee;
            let matchCols = true;
            if (matchAssignee) {
                const $cells = $row.find('td');
                Object.keys(colFilters).forEach(function (idx) {
                    if (!matchCols) return;
                    const allowed = colFilters[idx];
                    const val = ($cells.eq(+idx).text() || '').trim();
                    if (!allowed.has(val)) matchCols = false;
                });
            }
            $row.toggle(matchAssignee && matchCols);
        });
        $table.find('.rf-caret').each(function () {
            const idx = $(this).data('col-index');
            $(this).toggleClass('rf-active', !!colFilters[idx]);
        });
    }

    function closeAllResearchDropdowns() {
        $('.rf-dropdown').removeClass('open');
    }

    function openResearchDropdown($table, colIndex, $caret) {
        const isOpen = $caret.next('.rf-dropdown').hasClass('open');
        closeAllResearchDropdowns();
        if (isOpen) return;

        // 現在このテーブルに実際に出現している値を、表示中の行から重複なく取得する
        const values = [];
        const seen = {};
        $table.find('tbody tr').each(function () {
            const val = ($(this).find('td').eq(colIndex).text() || '').trim();
            if (!seen[val]) { seen[val] = true; values.push(val); }
        });
        values.sort(function (a, b) { return a.localeCompare(b, 'ja'); });

        const colFilters = $table.data('rfColFilters') || {};
        const activeSet = colFilters[colIndex] || new Set(values);

        const $dropdown = $caret.next('.rf-dropdown');
        const $search = $dropdown.find('.rf-dropdown-search');
        const $list = $dropdown.find('.rf-dropdown-list');
        const $allCb = $dropdown.find('.rf-all-checkbox');
        $search.val('');
        $list.empty();
        values.forEach(function (val) {
            const checked = activeSet.has(val);
            const $item = $('<label class="rf-dropdown-item"></label>');
            const $cb = $('<input type="checkbox" class="rf-val-checkbox">').prop('checked', checked).data('val', val);
            $item.append($cb).append($('<span></span>').text(val || '（空白）'));
            $list.append($item);
        });
        $allCb.prop('checked', values.length > 0 && values.every(function (v) { return activeSet.has(v); }));
        $dropdown.data('col-index', colIndex);
        $dropdown.addClass('open');
    }

    $(document).on('click', '.research-media-tab', function () {
        $('.research-media-tab').removeClass('active');
        $(this).addClass('active');
        syncResearchPanel();
    });

    $(document).on('click', '.rf-caret', function (e) {
        e.stopPropagation();
        const $caret = $(this);
        openResearchDropdown($caret.closest('table'), $caret.data('col-index'), $caret);
    });
    $(document).on('click', '.rf-dropdown', function (e) { e.stopPropagation(); });
    $(document).on('input', '.rf-dropdown-search', function () {
        const term = $(this).val().trim().toLowerCase();
        $(this).closest('.rf-dropdown').find('.rf-dropdown-item:not(.rf-all-item)').each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(!term || text.includes(term));
        });
    });
    $(document).on('change', '.rf-all-checkbox', function () {
        const checked = $(this).prop('checked');
        $(this).closest('.rf-dropdown').find('.rf-val-checkbox').prop('checked', checked);
    });
    $(document).on('click', '.rf-dropdown-cancel', function () {
        closeAllResearchDropdowns();
    });
    $(document).on('click', '.rf-dropdown-ok', function () {
        const $dropdown = $(this).closest('.rf-dropdown');
        const colIndex = $dropdown.data('col-index');
        const $table = $dropdown.closest('table');
        const selected = new Set();
        $dropdown.find('.rf-val-checkbox:checked').each(function () { selected.add($(this).data('val')); });
        const colFilters = $table.data('rfColFilters') || {};
        // 全選択状態ならフィルタなし扱いにして解除する
        const totalCount = $dropdown.find('.rf-val-checkbox').length;
        if (selected.size >= totalCount) {
            delete colFilters[colIndex];
        } else {
            colFilters[colIndex] = selected;
        }
        $table.data('rfColFilters', colFilters);
        closeAllResearchDropdowns();
        applyExcelFilters($table);
    });
    $(document).on('click', function () { closeAllResearchDropdowns(); });

    $(document).on('change', '#researchAssigneeFilter', function () {
        $('.research-table').each(function () { applyExcelFilters($(this)); });
    });

    // 各テーブルの見出しに▾ボタンとドロップダウンを組み込む（媒体ごとに列構成が違うため汎用的に処理）
    $('table.research-table').each(function () {
        const $table = $(this);
        $table.find('thead tr').first().find('th').each(function (colIndex) {
            const $th = $(this);
            const label = $th.text();
            $th.empty();
            const $wrap = $('<div class="rf-th-wrap"></div>');
            $wrap.append($('<span></span>').text(label));
            const $caret = $('<button type="button" class="rf-caret">▾</button>').data('col-index', colIndex);
            const $dropdown = $(
                '<div class="rf-dropdown">' +
                    '<input type="text" class="rf-dropdown-search" placeholder="検索">' +
                    '<label class="rf-dropdown-item rf-all-item"><input type="checkbox" class="rf-all-checkbox" checked><span>(すべて選択)</span></label>' +
                    '<div class="rf-dropdown-list"></div>' +
                    '<div class="rf-dropdown-actions">' +
                        '<button type="button" class="rf-dropdown-ok">OK</button>' +
                        '<button type="button" class="rf-dropdown-cancel">キャンセル</button>' +
                    '</div>' +
                '</div>'
            );
            $wrap.append($caret).append($dropdown);
            $th.append($wrap);
        });
    });

    syncResearchPanel();

    // ================= ステータス切替タブ（案件カード一覧とカレンダーを連動して切り替え） =================
    $(document).on('click', '.status-tab', function () {
        const status = $(this).data('status');
        $('.status-tab').removeClass('active');
        $(this).addClass('active');
        $('.status-panel').removeClass('active');
        $('.status-panel[data-status="' + status + '"]').addClass('active');
        calendarSetStatus(status);
    });

    // ================= 案件カードの検索フィルター =================
    function applyCardFilters() {
        const term = $('#searchInput').val().trim().toLowerCase();
        $('.mini-card').each(function () {
            const hay = (String($(this).data('client-name')) + String($(this).data('project-name'))).toLowerCase();
            $(this).toggle(!term || hay.includes(term));
        });
    }
    $('#searchInput').on('input', applyCardFilters);

    // ================= 案件カード → 詳細ポップアップ =================
    // タブは「媒体」単位。同じ媒体の案件（campaign行）が1グループ内に複数あっても
    // サーバー側で1つのブロックに統合済みなので、媒体ごとの表示切替だけでOK。
    function applyDetailPlatform($group) {
        const $blocks = $group.find('.campaign-block');
        const platforms = $blocks.map(function () { return String($(this).data('platform')); }).get();
        let selected = $group.data('selected-platform');
        if (!selected || !platforms.includes(String(selected))) {
            selected = platforms[0];
        }
        $blocks.each(function () {
            $(this).toggle(String($(this).data('platform')) === String(selected));
        });
        $group.find('.group-tab').removeClass('active');
        $group.find('.group-tab').filter(function () {
            return String($(this).data('platform')) === String(selected);
        }).addClass('active');
    }

    // group-key（=依頼単位）で厳密に一致するグループだけを開く。
    // クライアント名・案件名が同じでも別の依頼（別のgroup-key）なら混ざらない。
    function openCampaignModal(groupKey) {
        const $store = $('#detail-store');
        $store.find('.client-group').removeClass('modal-active');
        const $target = $store.find('.client-group[data-group-key="' + groupKey + '"]').first();
        if (!$target.length) return;
        $target.addClass('modal-active');
        applyDetailPlatform($target);
        $('#campaignDetailModal').addClass('open');
        $('body').addClass('modal-open-lock');
    }

    function closeCampaignModal() {
        $('#campaignDetailModal').removeClass('open');
        $('body').removeClass('modal-open-lock');
    }

    $(document).on('click', '.mini-card', function () {
        openCampaignModal($(this).data('group-key'));
    });
    $('#campaignModalClose').on('click', closeCampaignModal);
    $('#campaignDetailModal').on('click', function (e) {
        if (e.target === this) closeCampaignModal();
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeCampaignModal();
    });

    $(document).on('click', '.group-tab', function () {
        const $group = $(this).closest('.client-group');
        $group.data('selected-platform', $(this).data('platform'));
        applyDetailPlatform($group);
    });

    // ================= ステータス件数・カードの並び替え =================
    function updateStatusGroupCounts() {
        $('.status-panel').each(function () {
            const status = $(this).data('status');
            const n = $(this).find('.mini-card').length;
            $('.status-tab[data-status="' + status + '"] .status-tab-count').text(n);
        });
    }

    // 案件カードは「クライアント×案件」で1枚のため、ステータスは同じ案件内の全媒体のうち
    // 最も手前の段階（Assign待ち＞対応中＞送付済み）を採用する
    function computeProjectStatus($group) {
        const order = ['Assign待ち', '対応中', '送付済み'];
        const statuses = $group.find('.campaign-block').map(function () { return $(this).data('status'); }).get();
        for (let i = 0; i < order.length; i++) {
            if (statuses.includes(order[i])) return order[i];
        }
        return order[0];
    }

    function moveProjectCard($group, assigneeName) {
        const groupKey = $group.data('group-key');
        const $card = $('.mini-card[data-group-key="' + groupKey + '"]');
        if (!$card.length) return;
        if (assigneeName) {
            const $assigneeBox = $card.find('.mini-card-assignee');
            $assigneeBox.find('.member-avatar').removeClass('unassigned').text(assigneeName.charAt(0));
            $assigneeBox.find('span:last').removeClass('unassigned-text').text(assigneeName);
        }
        const newStatus = computeProjectStatus($group);
        const $targetPanel = $('.status-panel[data-status="' + newStatus + '"]');
        $targetPanel.find('.no-data-text').remove();
        $targetPanel.append($card);
        updateStatusGroupCounts();
        applyCardFilters();
    }

    // ステータス変更をUIに反映する共通処理（ピルの表示・ボタンの出し分け・カード配置を更新）
    // クライアント名×案件名＝1つの「案件」のため、$group（.client-group）に対して1回だけ行う。
    // 媒体ごとの .campaign-block は表示上のタブ切替にしか使わないが、
    // データの整合性のため data-status はすべての .campaign-block にも反映しておく。
    function applyStatusChange($group, newStatus) {
        const $pill = $group.find('.summary-bar .status-pill');
        $pill.removeClass('waiting inprogress sent').addClass(statusClassMap[newStatus]);

        const assignee = $group.data('assignee') || '';
        let text = newStatus;
        if (assignee && newStatus !== 'Assign待ち') {
            text += '（' + assignee + '）';
        }
        $pill.find('.status-text').text(text);

        $group.data('status', newStatus);
        $group.find('.summary-bar .assign-btn').toggle(newStatus === 'Assign待ち');
        $group.find('.summary-bar .mail-btn').toggle(newStatus === '対応中');

        const $blocks = $group.find('.campaign-block');
        $blocks.data('status', newStatus).attr('data-status', newStatus);
        $blocks.data('assignee', assignee).attr('data-assignee', assignee);

        moveProjectCard($group, assignee);

        // カレンダーもこの場で連動させる（タブ操作なしで自動反映）。全媒体分のIDをまとめて渡す。
        const allIds = $blocks.map(function () { return String($(this).data('campaign-id')); }).get().join(',');
        calendarUpdateEventStatus(allIds, newStatus);
    }

    // --- 見積指標の自動計算 ---
    const YT_FIXED_CTR = 0.0004; // YouTubeはCTR0.04%で固定
    // 課金形態ごとの計算ルール（入力キーと計算式）。テンプレートの数式をそのまま移植したもの。
    const chargeRules = {
        'CPC課金': {
            inputs: ['ctr', 'cpc', 'cvr'],
            calc: v => {
                const cts = v.budget / v.cpc;
                const imp = cts / v.ctr;
                const cv = cts * v.cvr;
                const cpa = cv > 0 ? v.budget / cv : null;
                return { cts, imp, cv, cpa };
            }
        },
        'vCPM課金': {
            inputs: ['vcpm', 'ctr', 'cvr', 'vimp_rate'],
            calc: v => {
                const vimp = v.budget / v.vcpm * 1000;
                const imp = vimp / v.vimp_rate;
                const cts = vimp * v.ctr;
                const cpc = cts > 0 ? v.budget / cts : null;
                const cv = cts * v.cvr;
                const cpa = cv > 0 ? v.budget / cv : null;
                return { vimp, imp, cts, cpc, cv, cpa };
            }
        },
        'CPV課金': {
            // テンプレート「動画」シートの数式：視聴数=予算/CPV、IMP=視聴数/視聴率、CTs=IMP×CTR…の順で計算
            // CTRはYouTubeでは0.04%固定（入力欄なし）。リーチ数＝IMP÷想定FQ（FQ案件のみ）
            inputs: ['cpv', 'view_rate', 'view_complete_rate', 'cvr'],
            calc: v => {
                const view_count = v.budget / v.cpv;
                const imp = view_count / v.view_rate;
                const cts = imp * YT_FIXED_CTR;
                const cpc = cts > 0 ? v.budget / cts : null;
                const view_complete = imp * v.view_complete_rate;
                const cv = cts * v.cvr;
                const cpa = cv > 0 ? v.budget / cv : null;
                const reach = v.fq > 0 ? imp / v.fq : null;
                return { view_count, cts, cpc, view_complete, cv, cpa, reach };
            }
        },
        'CPM課金': {
            // CTRはYouTubeでは0.04%固定（入力欄なし）。リーチ数＝IMP÷想定FQ（FQ案件のみ）
            inputs: ['cpm', 'view_rate', 'view_complete_rate', 'cvr'],
            calc: v => {
                const imp = v.budget / v.cpm * 1000;
                const cts = imp * YT_FIXED_CTR;
                const cpc = cts > 0 ? v.budget / cts : null;
                const view_count = imp * v.view_rate;
                const view_complete = imp * v.view_complete_rate;
                const cv = cts * v.cvr;
                const cpa = cv > 0 ? v.budget / cv : null;
                const reach = v.fq > 0 ? imp / v.fq : null;
                return { imp, cts, cpc, view_count, view_complete, cv, cpa, reach };
            }
        },
        // --- YG-Display&DGC / Listing 用（'CPC課金'・'vCPM課金'はMetaと同じ数式のため上のルールをそのまま流用し、
        //     data-billing にはケース名（cpc / vcpm / dgc_image / dgc_video / listing）を入れて選択する）
        'cpc': {
            inputs: ['ctr', 'cpc', 'cvr'],
            calc: v => {
                const cts = v.budget / v.cpc;
                const imp = cts / v.ctr;
                const cv = cts * v.cvr;
                const cpa = cv > 0 ? v.budget / cv : null;
                return { cts, imp, cv, cpa };
            }
        },
        'vcpm': {
            inputs: ['vcpm', 'ctr', 'cvr', 'vimp_rate'],
            calc: v => {
                const vimp = v.budget / v.vcpm * 1000;
                const imp = vimp / v.vimp_rate;
                const cts = vimp * v.ctr;
                const cpc = cts > 0 ? v.budget / cts : null;
                const cv = cts * v.cvr;
                const cpa = cv > 0 ? v.budget / cv : null;
                return { vimp, imp, cts, cpc, cv, cpa };
            }
        },
        'dgc_image': {
            // CTs=予算/CPC、IMP=CTs/CTR、CPM=予算/IMP×1000（IMPからの逆算）
            inputs: ['ctr', 'cpc', 'cvr'],
            calc: v => {
                const cts = v.budget / v.cpc;
                const imp = cts / v.ctr;
                const cpm = imp > 0 ? v.budget / imp * 1000 : null;
                const cv = cts * v.cvr;
                const cpa = cv > 0 ? v.budget / cv : null;
                return { cts, imp, cpm, cv, cpa };
            }
        },
        'dgc_video': {
            // dgc_imageと同じ基礎計算に加えて、視聴率からの視聴数（IMP×視聴率）を追加
            inputs: ['ctr', 'cpc', 'view_rate', 'cvr'],
            calc: v => {
                const cts = v.budget / v.cpc;
                const imp = cts / v.ctr;
                const cpm = imp > 0 ? v.budget / imp * 1000 : null;
                const view_count = imp * v.view_rate;
                const cv = cts * v.cvr;
                const cpa = cv > 0 ? v.budget / cv : null;
                return { cts, imp, cpm, view_count, cv, cpa };
            }
        },
        'listing': {
            inputs: ['ctr', 'cpc', 'cvr'],
            calc: v => {
                const cts = v.budget / v.cpc;
                const imp = cts / v.ctr;
                const cv = cts * v.cvr;
                const cpa = cv > 0 ? v.budget / cv : null;
                return { cts, imp, cv, cpa };
            }
        }
    };

    const CURRENCY_KEYS = ['cpm', 'cpv', 'vcpm', 'cpc', 'cpa', 'max_budget'];
    const PERCENT_KEYS = ['ctr', 'cvr', 'view_rate', 'view_complete_rate', 'vimp_rate'];
    const DECIMAL_KEYS = ['fq'];

    function readRowValues($row) {
        const budget = parseFloat($row.data('budget')) || 0;
        const v = { budget };
        $row.find('.estimate-input').each(function () {
            const key = $(this).data('key');
            // カンマ区切り表示の欄（MAX出稿金額など）はカンマを取り除いてから数値化
            let val = parseFloat(String($(this).val()).replace(/,/g, '')) || 0;
            // %表示の入力欄は「40」を40%として扱う → 内部の計算式には0.4として渡す
            if (PERCENT_KEYS.includes(key)) val = val / 100;
            v[key] = val;
        });
        // MAX出稿金額：●¥金額 / ●億単位で出稿可 の2択トグル
        const $maxBudgetToggle = $row.find('.max-budget-toggle');
        if ($maxBudgetToggle.length) {
            const mode = $maxBudgetToggle.attr('data-mode') || 'amount';
            v.max_budget_mode = mode;
            if (mode === 'unlimited') v.max_budget = ''; // 金額は保存しない（NULL化）
        }
        return v;
    }

    function formatMetric(key, value) {
        if (value === null || !isFinite(value)) return '—';
        if (CURRENCY_KEYS.includes(key)) {
            return '¥' + Math.round(value).toLocaleString();
        }
        if (key === 'ctr' || key === 'cvr') {
            return Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 });
        }
        return Math.round(value).toLocaleString();
    }

    function buildSavePayload($row) {
        const billing = $row.data('billing');
        const rule = chargeRules[billing];
        const values = readRowValues($row);
        // 課金形態が未確定（判定不可）の行でも、fq/reach/MAX出稿金額など単純な手入力欄は保存できるようにする
        const result = rule ? rule.calc(values) : {};
        return { billing, values, result };
    }

    function recalcRow($row) {
        const payload = buildSavePayload($row);

        Object.keys(payload.result).forEach(key => {
            const $cell = $row.find('.estimate-computed[data-key="' + key + '"]');
            if ($cell.length) {
                $cell.text(formatMetric(key, payload.result[key]));
            }
        });

        return payload;
    }

    function saveRow($row, payload) {
        $.post('/api/patterns/' + $row.data('pattern-id') + '/estimate', {
            platform: $row.data('platform'),
            billing: payload.billing,
            ...payload.values,
            ...payload.result,
            _token: $('meta[name="csrf-token"]').attr('content')
        });
    }

    function toggleEmptyState($input) {
        // MAX出稿金額が「億単位で出稿可」モードのときは、金額欄は未入力のままでよい（必須にしない）
        if ($input.hasClass('max-budget-input')) {
            const mode = $input.closest('.max-budget-cell').find('.max-budget-toggle').attr('data-mode');
            if (mode === 'unlimited') {
                $input.removeClass('is-empty');
                return;
            }
        }
        $input.toggleClass('is-empty', $input.val() === '');
    }

    // --- YouTube: 「CV案件の指標(CVR/CVs/CPA)」「FQ案件の指標(想定FQ/リーチ数)」を個別に開閉（両方ONも可） ---
    $(document).on('click', '.yt-detail-toggle', function () {
        const $btn = $(this);
        const group = $btn.data('group'); // 'cv' or 'fq'
        const $table = $btn.closest('.yt-table-toolbar').next('.pattern-table-wrap').find('.pattern-table');
        const cls = 'show-yt-' + group;
        const shown = $table.toggleClass(cls).hasClass(cls);
        $btn.toggleClass('active', shown);
    });

    // --- カンマ区切り表示の欄（MAX出稿金額など）: 入力中も¥1,000,000のようにカンマを自動挿入 ---
    $(document).on('input', '.comma-input', function () {
        const $input = $(this);
        const cursorFromEnd = $input.val().length - $input[0].selectionStart;
        const digits = $input.val().replace(/[^0-9]/g, '');
        const formatted = digits ? Number(digits).toLocaleString() : '';
        $input.val(formatted);
        const pos = Math.max(formatted.length - cursorFromEnd, 0);
        $input[0].setSelectionRange(pos, pos);
    });

    let saveTimer = null;
    $(document).on('input', '.estimate-input', function () {
        toggleEmptyState($(this));
        const $row = $(this).closest('tr');
        const payload = recalcRow($row);

        // 入力の都度ではなく、少し待ってからまとめて保存（連続入力での過剰なAjaxを防ぐ）
        clearTimeout(saveTimer);
        saveTimer = setTimeout(function () {
            saveRow($row, payload);
        }, 600);
    });

    // --- MAX出稿金額：¥金額 / 億単位で出稿可 の切り替え ---
    $(document).on('click', '.max-budget-mode-btn', function () {
        const $btn = $(this);
        const mode = $btn.data('mode');
        const $row = $btn.closest('tr');
        const $toggle = $btn.closest('.max-budget-toggle');
        const $wrap = $row.find('.max-budget-input-wrap');
        const $input = $wrap.find('.max-budget-input');

        $toggle.attr('data-mode', mode);
        $toggle.find('.max-budget-mode-btn').removeClass('active');
        $btn.addClass('active');

        if (mode === 'unlimited') {
            $input.val('');
            $wrap.hide();
        } else {
            $wrap.show();
        }
        toggleEmptyState($input);

        clearTimeout(saveTimer);
        saveRow($row, recalcRow($row));
    });

    // 初期表示時に一度計算しておく（保存済みの入力値がある場合の再計算）
    $('tr[data-pattern-id]').each(function () {
        recalcRow($(this));
    });

    // 初期表示時に未入力の見積セルへ赤枠を付ける
    $('.estimate-input').each(function () {
        toggleEmptyState($(this));
    });

    // 同一媒体で複数のcampaign行が統合されている場合、data-campaign-idは "1,2" のような
    // カンマ区切りになっているため、それぞれのIDに対して個別にPOSTし、全て完了を待つ。
    function postAllCampaigns(idsStr, path, data) {
        const ids = String(idsStr).split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        const requests = ids.map(function (id) {
            return $.post('/api/campaigns/' + id + path, data);
        });
        return $.when.apply($, requests);
    }

    // --- 対応します（担当者を保存し、ステータスをAssign待ち→対応中に自動変更） ---
    $(document).on('click', '.assign-btn', function (e) {
        e.stopPropagation();
        const $btn = $(this);
        // クライアント名×案件名＝1つの「案件」のため、この案件に紐づく全媒体のcampaign行に対して一斉に反映する
        const $group = $btn.closest('.client-group');
        const campaignId = $btn.data('campaign-id');
        const name = prompt('担当者の名前を入力してください');
        if (!name || !name.trim()) return;
        const trimmed = name.trim();

        postAllCampaigns(campaignId, '/assignee', { assignee: trimmed })
            .then(function () {
                return postAllCampaigns(campaignId, '/status', { status: '対応中' });
            })
            .then(function () {
                $group.data('assignee', trimmed);
                applyStatusChange($group, '対応中');
            })
            .fail(function () {
                alert('担当者の保存、またはステータス更新に失敗しました。');
            });
    });

    // --- メール送信モーダル ---
    // 「送信する」を押すと、実際にメールを送信したうえでステータスが送付済みに変わります。
    // 宛先・件名・本文のいずれかが空欄のときは、赤枠を付けて送信をブロックします。

    // 未入力の欄に赤枠を付ける／外す（見積り入力欄と同じ仕組み）
    function toggleMailEmptyState($input) {
        $input.toggleClass('is-empty', $input.val().trim() === '');
    }

    // 宛先・件名・本文をすべてチェックし、1つでも空欄があれば false を返す
    function validateMailFields() {
        let valid = true;
        $('#mailTo, #mailSubject, #mailBody').each(function () {
            const $field = $(this);
            toggleMailEmptyState($field);
            if ($field.hasClass('is-empty')) {
                valid = false;
            }
        });
        return valid;
    }

    // 入力するたびに、その場で赤枠を外す（直しながら気づけるように）
    $(document).on('input', '#mailTo, #mailSubject, #mailBody', function () {
        toggleMailEmptyState($(this));
    });

    $(document).on('click', '.mail-btn', function (e) {
        e.stopPropagation();
        // クライアント名×案件名＝1つの「案件」のため、この案件に紐づく全媒体分をまとめて送信する
        const $group = $(this).closest('.client-group');
        const email = $(this).data('email') || '';
        const client = $(this).data('client') || '';
        const project = $(this).data('project') || '';
        const platforms = $(this).data('platforms') || '';
        const applicant = $(this).data('applicant') || '';

        $('#mailTo').val(email);
        $('#mailSubject').val('【見積送付】 ' + client + '（' + project + '/' + platforms + '）');
        $('#mailBody').val(
            applicant + '　さま\n' +
            'いつもお世話になっております。ONEの○○です。\n\n' +
            'ご依頼いただきました見積を送付させていただきます。\n\n\n' +
            'ご不明点等ございましたらご連絡いただけますと幸いです。\n' +
            'ご確認のほど、何卒よろしくお願い申し上げます。'
        );
        // モーダルを開き直すたびに、赤枠の状態も入力内容に合わせてリセットする
        $('#mailTo, #mailSubject, #mailBody').each(function () {
            toggleMailEmptyState($(this));
        });
        $('#mailModalOverlay').data('block', $group).data('campaign-id', $(this).data('campaign-id'));
        $('#mailModalOverlay').css('display', 'flex');
    });
    $('#mailModalClose, #mailCancelBtn').on('click', function () {
        $('#mailModalOverlay').css('display', 'none');
    });
    $('#mailModalOverlay').on('click', function (e) {
        if (e.target === this) $(this).css('display', 'none');
    });
    // 送信中→送信完了（Lottieアニメーション）の演出をまとめて面倒みる小さなヘルパー
    let mailSentAnim = null; // 前回のアニメーションインスタンスを覚えておいて、破棄してから作り直す
    function showMailSendingOverlay() {
        $('#mailStatusText').text('送信中…');
        $('#mailSendingSpinner').show();
        $('#mailSentLottie').hide().empty();
        $('#mailStatusOverlay').css('display', 'flex');
    }
    function showMailSentAnimation(onFinished) {
        $('#mailSendingSpinner').hide();
        $('#mailStatusText').text('送信しました！');
        const $lottieEl = $('#mailSentLottie').show();

        if (mailSentAnim) {
            mailSentAnim.destroy();
            mailSentAnim = null;
        }

        if (typeof lottie === 'undefined') {
            // ライブラリ読み込みに失敗した場合も、演出なしで完了扱いにして処理を止めない
            setTimeout(onFinished, 900);
            return;
        }

        mailSentAnim = lottie.loadAnimation({
            container: $lottieEl[0],
            renderer: 'svg',
            loop: false,
            autoplay: true,
            path: '{{ asset("images_json/EmailSent.json") }}',
        });
        mailSentAnim.addEventListener('complete', function () {
            onFinished();
        });
    }
    function hideMailStatusOverlay() {
        $('#mailStatusOverlay').css('display', 'none');
        if (mailSentAnim) {
            mailSentAnim.destroy();
            mailSentAnim = null;
        }
    }

    $('#mailSendBtn').on('click', function () {
        // 空欄があれば、赤枠を付けた状態で処理を止める
        if (!validateMailFields()) {
            alert('未入力の項目があります（赤枠の欄を入力してください）。');
            return;
        }

        // 誤送信防止の確認ダイアログ
        if (!confirm('このメールを送信しますか？')) {
            return;
        }

        const $overlay = $('#mailModalOverlay');
        const campaignId = $overlay.data('campaign-id');
        const $block = $overlay.data('block');

        const to = $('#mailTo').val();
        const subject = $('#mailSubject').val();
        const body = $('#mailBody').val();

        showMailSendingOverlay();

        $.post('/api/send-mail', {
            to: to,
            subject: subject,
            body: body,
            campaign_id: campaignId,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
            .done(function () {
                postAllCampaigns(campaignId, '/status', { status: '送付済み' })
                    .done(function () {
                        if ($block) applyStatusChange($block, '送付済み');
                    })
                    .fail(function () {
                        alert('メール送信は完了しましたが、ステータス更新に失敗しました。');
                    });

                // 送信中スピナー → 送信完了アニメーションの順で見せてから、両方のモーダルを閉じる
                showMailSentAnimation(function () {
                    hideMailStatusOverlay();
                    $overlay.css('display', 'none');
                });
            })
            .fail(function () {
                hideMailStatusOverlay();
                alert('メールの送信に失敗しました。');
            });
    });
});
</script>
<script type="module">
    // SimForm.vueで使っているものと同じ .lottie ファイルを、媒体タブのアイコンとして表示する
    // 前提：public/images_json/ 配下に各.lottieファイルが置かれていること（Vue側と同じパス）
    import { DotLottie } from 'https://cdn.jsdelivr.net/npm/@lottiefiles/dotlottie-web@latest/+esm';

    const platformLottieMap = {
        youtube: '/images_json/YouTube.lottie',
        yg: '/images_json/YG.lottie',
        meta: '/images_json/Meta.lottie',
        listing: '/images_json/Listing.lottie',
    };

    document.querySelectorAll('.tab-lottie-canvas').forEach(function (canvas) {
        const src = platformLottieMap[canvas.dataset.platform];
        if (!src) return;
        // 高DPI(Retinaなど)でも粗くならないよう、画面のピクセル比に合わせて内部解像度を引き上げる
        // （CSS表示サイズは22pxのまま、実ピクセルだけ最大4倍相当まで確保）
        const displaySize = 22;
        const dpr = window.devicePixelRatio || 1;
        const renderSize = Math.round(displaySize * Math.min(dpr, 2) * 3);
        canvas.width = renderSize;
        canvas.height = renderSize;
        try {
            new DotLottie({ canvas: canvas, src: src, loop: true, autoplay: true });
        } catch (e) {
            // ファイルが見つからない等の場合も、タブ自体は文字だけで問題なく機能する
            console.warn('Lottieロゴの読み込みに失敗しました:', src, e);
        }
    });
</script>
</body>
</html>