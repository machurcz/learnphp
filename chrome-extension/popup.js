// Získá doménu z URL
function extractDomain(url) {
  try {
    const urlObj = new URL(url);
    return urlObj.hostname;
  } catch (e) {
    return null;
  }
}

// Zkontroluje, zda MX záznam patří Google
function isGoogleMX(exchange) {
  const googlePatterns = [
    'google.com',
    'googlemail.com',
    'smtp.google.com',
    'aspmx.l.google.com',
    'alt1.aspmx.l.google.com',
    'alt2.aspmx.l.google.com',
    'alt3.aspmx.l.google.com',
    'alt4.aspmx.l.google.com'
  ];

  const lowerExchange = exchange.toLowerCase();
  return googlePatterns.some(pattern => lowerExchange.includes(pattern));
}

// Získá MX záznamy pomocí Google DNS-over-HTTPS API
async function getMXRecords(domain) {
  const url = `https://dns.google/resolve?name=${encodeURIComponent(domain)}&type=MX`;

  const response = await fetch(url);
  if (!response.ok) {
    throw new Error(`HTTP error: ${response.status}`);
  }

  const data = await response.json();

  if (data.Status !== 0) {
    // DNS error codes
    const errorMessages = {
      1: 'Chyba formátu dotazu',
      2: 'Chyba serveru',
      3: 'Doména neexistuje',
      4: 'Nepodporovaný typ dotazu',
      5: 'Dotaz odmítnut'
    };
    throw new Error(errorMessages[data.Status] || `DNS chyba: ${data.Status}`);
  }

  if (!data.Answer || data.Answer.length === 0) {
    return [];
  }

  // Parsování MX záznamů (formát: "priorita mailserver.")
  return data.Answer
    .filter(record => record.type === 15) // MX record type
    .map(record => {
      const parts = record.data.split(' ');
      return {
        priority: parseInt(parts[0], 10),
        exchange: parts[1].replace(/\.$/, '') // Odstraní tečku na konci
      };
    })
    .sort((a, b) => a.priority - b.priority);
}

// Aktualizuje UI se stavem
function updateUI(state, data = {}) {
  const statusIcon = document.getElementById('status-icon');
  const statusText = document.getElementById('status-text');
  const mxRecords = document.getElementById('mx-records');
  const mxList = document.getElementById('mx-list');

  // Reset classes
  statusIcon.className = 'status-icon';
  statusText.className = 'status-text';

  switch (state) {
    case 'loading':
      statusIcon.classList.add('loading');
      statusIcon.innerHTML = '<span class="spinner">&#8635;</span>';
      statusText.classList.add('loading');
      statusText.textContent = 'Kontroluji MX záznamy...';
      mxRecords.style.display = 'none';
      break;

    case 'google':
      statusIcon.classList.add('google');
      statusIcon.innerHTML = '&#10004;';
      statusText.classList.add('google');
      statusText.textContent = 'Používá Google';
      break;

    case 'not-google':
      statusIcon.classList.add('not-google');
      statusIcon.innerHTML = '&#10008;';
      statusText.classList.add('not-google');
      statusText.textContent = 'Nepoužívá Google';
      break;

    case 'no-mx':
      statusIcon.classList.add('not-google');
      statusIcon.innerHTML = '&#8709;';
      statusText.classList.add('not-google');
      statusText.textContent = 'Žádné MX záznamy';
      break;

    case 'error':
      statusIcon.classList.add('error');
      statusIcon.innerHTML = '&#9888;';
      statusText.classList.add('error');
      statusText.textContent = data.message || 'Chyba';
      break;
  }

  // Zobrazit MX záznamy pokud existují
  if (data.mxRecords && data.mxRecords.length > 0) {
    mxRecords.style.display = 'block';
    mxList.innerHTML = data.mxRecords
      .map(mx => {
        const isGoogle = isGoogleMX(mx.exchange);
        return `<div class="mx-record ${isGoogle ? 'google-mx' : ''}">${mx.priority} ${mx.exchange}</div>`;
      })
      .join('');
  }
}

// Hlavní funkce
async function checkMX() {
  const domainEl = document.getElementById('domain');

  try {
    // Získat aktuální tab
    const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

    if (!tab || !tab.url) {
      throw new Error('Nelze získat URL stránky');
    }

    const domain = extractDomain(tab.url);

    if (!domain) {
      throw new Error('Neplatná URL');
    }

    // Zobrazit doménu
    domainEl.textContent = domain;

    // Kontrola speciálních stránek (chrome://, about:, atd.)
    if (tab.url.startsWith('chrome://') || tab.url.startsWith('about:') || tab.url.startsWith('edge://')) {
      updateUI('error', { message: 'Systémová stránka' });
      return;
    }

    // Získat MX záznamy
    const mxRecords = await getMXRecords(domain);

    if (mxRecords.length === 0) {
      updateUI('no-mx');
      return;
    }

    // Zkontrolovat, zda některý MX záznam patří Google
    const hasGoogle = mxRecords.some(mx => isGoogleMX(mx.exchange));

    if (hasGoogle) {
      updateUI('google', { mxRecords });
    } else {
      updateUI('not-google', { mxRecords });
    }

  } catch (error) {
    console.error('Error:', error);
    updateUI('error', { message: error.message });
  }
}

// Spustit při načtení popup
document.addEventListener('DOMContentLoaded', checkMX);
