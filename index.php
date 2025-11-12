<?php
// index.php — Battlefield 6 Stats Tracker (frontend)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Battlefield 6 Stats Tracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #0d1117;
      color: #e0e6ed;
      text-align: center;
      margin: 0;
      padding: 0;
    }
    h1 {
      color: #ff9f1c;
      margin-top: 40px;
      text-shadow: 0 0 10px #ff9f1c;
    }
    form {
      margin: 30px auto;
      background: #161b22;
      border: 1px solid #30363d;
      border-radius: 10px;
      padding: 20px;
      max-width: 400px;
      box-shadow: 0 0 10px #0d1117;
    }
    input, select, button {
      margin: 8px;
      padding: 10px;
      font-size: 1rem;
      border-radius: 5px;
      border: 1px solid #30363d;
      background: #0d1117;
      color: #e0e6ed;
      width: calc(100% - 24px);
    }
    button {
      background: #ff9f1c;
      color: #000;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background: #ffb347;
    }
    pre {
      text-align: left;
      margin: 20px auto;
      background: #161b22;
      padding: 15px;
      border-radius: 10px;
      max-width: 600px;
      overflow-x: auto;
      white-space: pre-wrap;
    }
  </style>
</head>
<body>
  <h1>Battlefield 6 Stats Tracker</h1>

  <form id="lookupForm">
    <select id="platform" required>
      <option value="">Select Platform</option>
      <option value="psn">PlayStation Network</option>
      <option value="xbl">Xbox Live</option>
      <option value="pc">EA / Origin PC</option>
    </select>
    <input type="text" id="username" placeholder="Enter player name / EA ID" required />
    <button type="submit">Look Up Stats</button>
  </form>

  <div id="status"></div>
  <pre id="result">Results will appear here...</pre>

  <script>
  document.getElementById('lookupForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const platform = document.getElementById('platform').value.trim();
    const username = document.getElementById('username').value.trim();
    const statusEl = document.getElementById('status');
    const resultEl = document.getElementById('result');

    if (!platform || !username) {
      alert('Please select a platform and enter a username.');
      return;
    }

    statusEl.textContent = 'Fetching stats...';
    resultEl.textContent = '';

    try {
      const res = await fetch('api/add_player.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `platform=${encodeURIComponent(platform)}&username=${encodeURIComponent(username)}`
      });

      const data = await res.json();
      statusEl.textContent = '';

      if (data.error) {
        resultEl.textContent = `❌ Error: ${data.error}\n${data.detail ? data.detail : ''}`;
      } else {
        resultEl.textContent = JSON.stringify(data.player, null, 2);
      }

    } catch (err) {
      statusEl.textContent = '';
      resultEl.textContent = '❌ Request failed: ' + err.message;
    }
  });
  </script>
</body>
</html>
