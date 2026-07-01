document.addEventListener("DOMContentLoaded", () => {

    const BASE = window.BASE_URL || ''; // si tu as une variable globale BASE_URL en JS, sinon laisse vide pour chemin relatif

    // ══════════════════════════════════════
    // 1) FORMULAIRE INFOS (nom + email)
    // ══════════════════════════════════════
    const formInfos  = document.getElementById("form-infos");
    const infosFlash = document.getElementById("infos-flash");
    const pNom       = document.getElementById("p-nom");
    const pEmail     = document.getElementById("p-email");

    formInfos.addEventListener("submit", async (e) => {
        e.preventDefault();
        infosFlash.className = "flash";

        const nom   = pNom.value.trim();
        const email = pEmail.value.trim();

        if (!nom || !email) {
            showFlash(infosFlash, "Le nom et l'email sont obligatoires.", "err");
            return;
        }

        const btn = formInfos.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = "Enregistrement…";

        try {
            const res = await fetch("update_profil.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action: "update_infos", nom, email })
            });

            const data = await res.json();

            if (data.success) {
                showFlash(infosFlash, data.message, "ok");
            } else {
                showFlash(infosFlash, data.message, "err");
            }
        } catch (err) {
            showFlash(infosFlash, "Erreur réseau. Veuillez réessayer.", "err");
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // ══════════════════════════════════════
    // 2) FORMULAIRE MOT DE PASSE
    // ══════════════════════════════════════
    const formPassword = document.getElementById("form-password");
    const oldPwd        = document.getElementById("p-oldpwd");
    const newPwd         = document.getElementById("p-newpwd");
    const confirmPwd     = document.getElementById("p-confirmpwd");
    const pwdBar         = document.getElementById("pwd-bar");
    const pwdHint        = document.getElementById("pwd-hint");
    const pwdFlash       = document.getElementById("pwd-flash");

    // Force du mot de passe (affichage en direct)
    newPwd.addEventListener("input", () => {
        const pwd = newPwd.value;
        let score = 0;
        if (pwd.length >= 8) score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[a-z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;

        switch (score) {
            case 0:
            case 1:
                pwdBar.style.width = "20%";
                pwdBar.style.background = "#dc2626";
                pwdHint.textContent = "Mot de passe très faible";
                break;
            case 2:
                pwdBar.style.width = "40%";
                pwdBar.style.background = "#ea580c";
                pwdHint.textContent = "Mot de passe faible";
                break;
            case 3:
                pwdBar.style.width = "60%";
                pwdBar.style.background = "#eab308";
                pwdHint.textContent = "Mot de passe moyen";
                break;
            case 4:
                pwdBar.style.width = "80%";
                pwdBar.style.background = "#2563eb";
                pwdHint.textContent = "Mot de passe fort";
                break;
            case 5:
                pwdBar.style.width = "100%";
                pwdBar.style.background = "#16a34a";
                pwdHint.textContent = "Mot de passe très fort";
                break;
        }
    });

    // Soumission réelle vers le serveur
    formPassword.addEventListener("submit", async (e) => {
        e.preventDefault();
        pwdFlash.className = "flash";

        if (!oldPwd.value) {
            showFlash(pwdFlash, "Veuillez saisir votre mot de passe actuel.", "err");
            return;
        }
        if (newPwd.value.length < 8) {
            showFlash(pwdFlash, "Le mot de passe doit contenir au moins 8 caractères.", "err");
            return;
        }
        if (!/[A-Za-z]/.test(newPwd.value)) {
            showFlash(pwdFlash, "Le mot de passe doit contenir au moins une lettre.", "err");
            return;
        }
        if (!/[0-9]/.test(newPwd.value)) {
            showFlash(pwdFlash, "Le mot de passe doit contenir au moins un chiffre.", "err");
            return;
        }
        if (!/[^A-Za-z0-9]/.test(newPwd.value)) {
            showFlash(pwdFlash, "Le mot de passe doit contenir au moins un caractère spécial.", "err");
            return;
        }
        if (newPwd.value !== confirmPwd.value) {
            showFlash(pwdFlash, "Les mots de passe ne correspondent pas.", "err");
            return;
        }

        const btn = formPassword.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = "Mise à jour…";

        try {
            const res = await fetch("update_profil.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    action: "update_password",
                    old_pwd: oldPwd.value,
                    new_pwd: newPwd.value,
                    confirm_pwd: confirmPwd.value
                })
            });

            const data = await res.json();

            if (data.success) {
                showFlash(pwdFlash, data.message, "ok");
                formPassword.reset();
                pwdBar.style.width = "0%";
                pwdHint.textContent = "";
            } else {
                showFlash(pwdFlash, data.message, "err");
            }
        } catch (err) {
            showFlash(pwdFlash, "Erreur réseau. Veuillez réessayer.", "err");
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // ══════════════════════════════════════
    // Utilitaire affichage flash
    // ══════════════════════════════════════
    function showFlash(el, message, type) {
        el.textContent = message;
        el.className = "flash show " + type;
    }

});
document.getElementById('logo-input').addEventListener('change', async function () {
    if (!this.files[0]) return;
 
    const formData = new FormData();
    formData.append('logo', this.files[0]);
 
    const preview = document.getElementById('logo-preview');
    preview.innerHTML = '<span>Envoi en cours…</span>';
 
    try {
        const res = await fetch('uploadlogo.php', { method: 'POST', body: formData });
        const data = await res.json();
 
        if (data.success) {
            preview.innerHTML = `<img src="${data.logo_url}" alt="Logo">`;
        } else {
            preview.innerHTML = '<span>Aucun logo</span>';
            alert(data.message);
        }
    } catch (err) {
        preview.innerHTML = '<span>Aucun logo</span>';
        alert('Erreur réseau lors de l\'upload.');
    }
});

//KessyEloish@1234