// ── register.js ──

// Afficher / masquer le mot de passe
function togglePassword(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';

    // Mettre à jour l'icône selon l'état
    btn.querySelector('svg').innerHTML = isHidden
        ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`
        : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
}

// Force du mot de passe
const mdpInput    = document.getElementById('mdp');
const pwdBar      = document.getElementById('pwd-bar');
const pwdLabel    = document.getElementById('pwd-label');

function evalStrength(pwd) {
    let score = 0;
    if (pwd.length >= 8)  score++;
    if (pwd.length >= 12) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;
    return score;
}

mdpInput?.addEventListener('input', function () {
    const pwd   = this.value;
    const score = evalStrength(pwd);

    const levels = [
        { pct: '0%',   color: 'transparent', label: '' },
        { pct: '25%',  color: '#ef4444',      label: '🔴 Très faible' },
        { pct: '50%',  color: '#f97316',      label: '🟠 Faible' },
        { pct: '75%',  color: '#eab308',      label: '🟡 Correct' },
        { pct: '90%',  color: '#22c55e',      label: '🟢 Fort' },
        { pct: '100%', color: '#16a34a',      label: '✅ Très fort' },
    ];

    const lvl = pwd.length === 0 ? levels[0] : levels[Math.min(score, 5)];
    pwdBar.style.width      = lvl.pct;
    pwdBar.style.background = lvl.color;
    pwdLabel.textContent    = lvl.label;

    // Vérifier aussi la correspondance si mdp2 est déjà rempli
    checkMatch();
});

// Correspondance des mots de passe
const mdp2Input  = document.getElementById('mdp2');
const matchMsg   = document.getElementById('match-msg');

function checkMatch() {
    const v1 = mdpInput?.value  || '';
    const v2 = mdp2Input?.value || '';
    if (!v2) { matchMsg.textContent = ''; return; }

    if (v1 === v2) {
        matchMsg.textContent = '✓ Les mots de passe correspondent';
        matchMsg.className   = 'match-msg ok';
    } else {
        matchMsg.textContent = '✗ Les mots de passe ne correspondent pas';
        matchMsg.className   = 'match-msg err';
    }
}

mdp2Input?.addEventListener('input', checkMatch);

// Validation avant soumission
document.getElementById('form-register')?.addEventListener('submit', function (e) {
    const err  = document.getElementById('f-err');
    const mdp  = mdpInput.value;
    const mdp2 = mdp2Input.value;

    err.textContent = '';

    if (mdp.length < 8) {
        e.preventDefault();
        err.textContent = 'Le mot de passe doit contenir au moins 8 caractères.';
        mdpInput.focus();
        return;
    }

    if (mdp !== mdp2) {
        e.preventDefault();
        err.textContent = 'Les mots de passe ne correspondent pas.';
        mdp2Input.focus();
        return;
    }
});
/* ══════════════════════════════════════
   PATCH à fusionner dans JS/register.js
   Validation mot de passe : lettre + chiffre + caractère spécial
   ══════════════════════════════════════ */

const mdpInput   = document.getElementById('mdp');
const mdp2Input  = document.getElementById('mdp2');
const pwdBar     = document.getElementById('pwd-bar');
const pwdLabel   = document.getElementById('pwd-label');
const matchMsg   = document.getElementById('match-msg');
const fErr       = document.getElementById('f-err');
const form       = document.getElementById('form-register');

// Règles checklist (li dans le HTML)
const ruleLen     = document.getElementById('rule-len');
const ruleLetter  = document.getElementById('rule-letter');
const ruleDigit   = document.getElementById('rule-digit');
const ruleSpecial = document.getElementById('rule-special');

function checkPasswordRules(pwd) {
    return {
        len:     pwd.length >= 8,
        letter:  /[A-Za-z]/.test(pwd),
        digit:   /[0-9]/.test(pwd),
        special: /[^A-Za-z0-9]/.test(pwd),
    };
}

function updateRuleUI(li, ok) {
    li.classList.toggle('ok', ok);
    li.classList.toggle('pending', !ok);
}

function updatePasswordStrength() {
    const pwd   = mdpInput.value;
    const rules = checkPasswordRules(pwd);

    updateRuleUI(ruleLen, rules.len);
    updateRuleUI(ruleLetter, rules.letter);
    updateRuleUI(ruleDigit, rules.digit);
    updateRuleUI(ruleSpecial, rules.special);

    const score = Object.values(rules).filter(Boolean).length; // 0 à 4

    const levels = [
        { width: '0%',   color: '#e4e4f0', label: '' },
        { width: '25%',  color: '#dc2626', label: 'Très faible' },
        { width: '50%',  color: '#d97706', label: 'Faible' },
        { width: '75%',  color: '#2563eb', label: 'Bon' },
        { width: '100%', color: '#059669', label: 'Excellent' },
    ];
    const lvl = pwd.length === 0 ? levels[0] : levels[score];

    pwdBar.style.width = lvl.width;
    pwdBar.style.background = lvl.color;
    pwdLabel.textContent = lvl.label;
    pwdLabel.style.color = lvl.color;

    checkMatch();
}

function checkMatch() {
    if (mdp2Input.value === '') { matchMsg.textContent = ''; return; }
    if (mdpInput.value === mdp2Input.value) {
        matchMsg.textContent = '✓ Les mots de passe correspondent';
        matchMsg.className = 'match-msg ok';
    } else {
        matchMsg.textContent = '✗ Les mots de passe ne correspondent pas';
        matchMsg.className = 'match-msg err';
    }
}

mdpInput.addEventListener('input', updatePasswordStrength);
mdp2Input.addEventListener('input', checkMatch);

// Validation finale avant soumission
form.addEventListener('submit', function (e) {
    const pwd   = mdpInput.value;
    const rules = checkPasswordRules(pwd);
    fErr.textContent = '';

    if (!rules.len) {
        e.preventDefault();
        fErr.textContent = 'Le mot de passe doit contenir au moins 8 caractères.';
        return;
    }
    if (!rules.letter) {
        e.preventDefault();
        fErr.textContent = 'Le mot de passe doit contenir au moins une lettre.';
        return;
    }
    if (!rules.digit) {
        e.preventDefault();
        fErr.textContent = 'Le mot de passe doit contenir au moins un chiffre.';
        return;
    }
    if (!rules.special) {
        e.preventDefault();
        fErr.textContent = 'Le mot de passe doit contenir au moins un caractère spécial.';
        return;
    }
    if (pwd !== mdp2Input.value) {
        e.preventDefault();
        fErr.textContent = 'Les mots de passe ne correspondent pas.';
        return;
    }
});

// ── Affiche / masque le mot de passe (si pas déjà présent dans ton register.js) ──
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.classList.toggle('active');
}