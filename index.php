<?php require __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Battlefield 6 — Cross-Platform Stats</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
  <header class="bf-header">
    <div class="brand">BATTLEFIELD<span>6</span> STATS</div>
    <div class="subtitle">Cross-platform • EA ID–centric</div>
  </header>

  <main class="container">
    <section class="card add-card">
      <h2>Add Player</h2>
      <form id="addForm">
        <div class="row">
          <label>Platform</label>
          <select name="platform" required>
            <option value="">Choose…</option>
            <option value="origin">PC (Origin/EA)</option>
            <option value="psn">PlayStation</option>
            <option value="xbl">Xbox</option>
            <option value="steam">Steam</option>
            <option value="epic">Epic</option>
          </select>
        </div>
        <div class="row">
          <label>Username / Handle</label>
          <input type="text" name="username" placeholder="Player handle" required />
        </div>
        <button type="submit" class="btn">Resolve & Save</button>
        <div id="addMsg" class="msg"></div>
      </form>
    </section>

    <section class="card table-card">
      <div class="table-header">
        <h2>Leaderboard</h2>
        <button id="refreshAll" class="btn btn-ghost">Refresh All</button>
      </div>
      <div class="table-wrap">
        <table id="playersTable">
          <thead>
            <tr>
              <th>EA ID</th>
              <th>Handle</th>
              <th>Platform</th>
              <th>Kills</th>
              <th>Deaths</th>
              <th>Wins</th>
              <th>Losses</th>
              <th>Score</th>
              <th>Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </section>
  </main>

  <footer class="bf-footer">
    <span>Built on LAMP • Single-table, EA-ID centric</span>
  </footer>

<script>
async function fetchJSON(url, options={}) {
  const res = await fetch(url, options);
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}

async function loadPlayers() {
  try {
    const data = await fetchJSON('api/list_players.php');
    const tbody = document.querySelector('#playersTable tbody');
    tbody.innerHTML = '';
    (data.players || []).forEach(p => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${p.ea_id}</td>
        <td>${escapeHtml(p.handle)}</td>
        <td>${p.platform || '-'}</td>
        <td>${p.kills}</td>
        <td>${p.deaths}</td>
        <td>${p.wins}</td>
        <td>${p.losses}</td>
        <td>${p.score}</td>
        <td>${p.last_updated}</td>
        <td class="actions">
          <button class="mini" data-act="refresh" data-eaid="${p.ea_id}">↻</button>
          <button class="mini danger" data-act="delete" data-eaid="${p.ea_id}">✕</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  } catch(e) {
    console.error(e);
  }
}

function escapeHtml(s){return (s??'').toString().replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));}

document.getElementById('addForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);
  const msg = document.getElementById('addMsg');
  msg.textContent = 'Resolving…';
  try {
    const res = await fetch('api/add_player.php', { method: 'POST', body: data });
    const json = await res.json();
    if (!res.ok || json.error) throw new Error(json.error || 'Add failed');
    msg.textContent = `Added: ${json.player.handle} (EA ${json.player.ea_id})`;
    form.reset();
    await loadPlayers();
  } catch(err) {
    msg.textContent = 'Error: ' + err.message;
  }
});

document.getElementById('playersTable').addEventListener('click', async (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const eaId = btn.dataset.eaid;
  const act  = btn.dataset.act;
  if (act === 'refresh') {
    btn.disabled = true; btn.textContent = '…';
    await fetch('api/refresh_player.php', { method: 'POST', body: new URLSearchParams({ ea_id: eaId }) });
    await loadPlayers();
    btn.disabled = false; btn.textContent = '↻';
  } else if (act === 'delete') {
    if (!confirm('Remove this player?')) return;
    await fetch('api/delete_player.php', { method: 'POST', body: new URLSearchParams({ ea_id: eaId }) });
    await loadPlayers();
  }
});

document.getElementById('refreshAll').addEventListener('click', async () => {
  const rows = [...document.querySelectorAll('#playersTable tbody tr')];
  for (const r of rows) {
    const btn = r.querySelector('button[data-act="refresh"]');
    if (btn) {
      btn.click(); // serial refresh keeps rate-use reasonable
      await new Promise(res => setTimeout(res, 500));
    }
  }
});

// initial + periodic refresh
loadPlayers();
setInterval(loadPlayers, 5000);
</script>
</body>
</html>
