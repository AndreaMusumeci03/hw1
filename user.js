document.addEventListener('DOMContentLoaded', function() {
    const loginContainer = document.getElementById('login1');
    const authDropdown = document.getElementById('dropdownLogin');
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const authForm = document.getElementById('authForm');
    const authMessage = document.getElementById('authMessage');
    const authSubmit = document.getElementById('authSubmit');
    const actionInput = document.getElementById('actionType');

    let isLogin = true;
    let currentTimeout;
    let usernameCheckTimeout;
    let isUsernameAvailable = true;

    function setupLoginDropdown() {
        if (!loginContainer || !authDropdown) return;
        
        loginContainer.addEventListener('click', function(e) {
            e.stopPropagation();
            authDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function() {
            authDropdown.classList.remove('show');
        });

        authDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // 2. Gestione tab login/register
    function setupAuthTabs() {
        if (!loginTab || !registerTab) return;
        
        loginTab.addEventListener('click', function() {
            isLogin = true;
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            authSubmit.textContent = 'Accedi';
            actionInput.value = 'login';
            authMessage.textContent = '';
            
            // Nascondi indicatori di validazione quando si passa al login
            hidePasswordValidation();
            hideUsernameValidation();
        });

        registerTab.addEventListener('click', function() {
            isLogin = false;
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            authSubmit.textContent = 'Registrati';
            actionInput.value = 'register';
            authMessage.textContent = '';
            
            // Mostra indicatori di validazione per la registrazione
            setupPasswordValidation();
            setupUsernameValidation();
        });
    }

    // 3. NUOVA FUNZIONE: Validazione password avanzata lato client
    function setupPasswordValidation() {
        const passwordInput = document.getElementById('password');
        if (!passwordInput) return;

        // Crea o mostra l'indicatore di validazione password
        let passwordStrength = document.getElementById('password-strength');
        if (!passwordStrength) {
            passwordStrength = document.createElement('div');
            passwordStrength.id = 'password-strength';
            passwordStrength.innerHTML = `
                <div class="password-requirements">
                    <div class="requirement" id="length-req">
                        <span class="req-icon">✗</span>
                        <span>Almeno 8 caratteri</span>
                    </div>
                    <div class="requirement" id="uppercase-req">
                        <span class="req-icon">✗</span>
                        <span>Una lettera maiuscola</span>
                    </div>
                    <div class="requirement" id="lowercase-req">
                        <span class="req-icon">✗</span>
                        <span>Una lettera minuscola</span>
                    </div>
                    <div class="requirement" id="number-req">
                        <span class="req-icon">✗</span>
                        <span>Un numero</span>
                    </div>
                    <div class="requirement" id="special-req">
                        <span class="req-icon">✗</span>
                        <span>Un carattere speciale (!@#$%^&*)</span>
                    </div>
                </div>
                <div class="strength-meter">
                    <div class="strength-bar" id="strength-bar"></div>
                    <span class="strength-text" id="strength-text">Debole</span>
                </div>
            `;
            passwordInput.parentNode.appendChild(passwordStrength);
        }
        passwordStrength.style.display = 'block';

        // Event listener per la validazione in tempo reale
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            validatePasswordStrength(password);
        });
    }

    function validatePasswordStrength(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };

        // Aggiorna indicatori visivi
        updateRequirement('length-req', requirements.length);
        updateRequirement('uppercase-req', requirements.uppercase);
        updateRequirement('lowercase-req', requirements.lowercase);
        updateRequirement('number-req', requirements.number);
        updateRequirement('special-req', requirements.special);

        // Calcola forza password
        const score = Object.values(requirements).filter(Boolean).length;
        updateStrengthMeter(score, password.length);

        return score >= 4; // Richiede almeno 4 criteri su 5
    }

    function updateRequirement(id, met) {
        const element = document.getElementById(id);
        if (!element) return;
        
        const icon = element.querySelector('.req-icon');
        if (met) {
            element.classList.add('met');
            icon.textContent = '✓';
        } else {
            element.classList.remove('met');
            icon.textContent = '✗';
        }
    }

    function updateStrengthMeter(score, length) {
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        if (!strengthBar || !strengthText) return;

        let strength, color, width;
        
        if (length === 0) {
            strength = '';
            color = '#ddd';
            width = '0%';
        } else if (score <= 2) {
            strength = 'Debole';
            color = '#ff4757';
            width = '25%';
        } else if (score <= 3) {
            strength = 'Media';
            color = '#ffa502';
            width = '50%';
        } else if (score <= 4) {
            strength = 'Forte';
            color = '#3742fa';
            width = '75%';
        } else {
            strength = 'Molto Forte';
            color = '#2ed573';
            width = '100%';
        }

        strengthBar.style.width = width;
        strengthBar.style.backgroundColor = color;
        strengthText.textContent = strength;
        strengthText.style.color = color;
    }

    function hidePasswordValidation() {
        const passwordStrength = document.getElementById('password-strength');
        if (passwordStrength) {
            passwordStrength.style.display = 'none';
        }
    }

    // 4. NUOVA FUNZIONE: Check disponibilità username in tempo reale
    function setupUsernameValidation() {
        const usernameInput = document.getElementById('username');
        if (!usernameInput) return;

        // Crea o mostra l'indicatore di disponibilità username
        let usernameStatus = document.getElementById('username-status');
        if (!usernameStatus) {
            usernameStatus = document.createElement('div');
            usernameStatus.id = 'username-status';
            usernameStatus.className = 'username-status';
            usernameInput.parentNode.appendChild(usernameStatus);
        }
        usernameStatus.style.display = 'block';

        // Event listener per controllo disponibilità
        usernameInput.addEventListener('input', function() {
            const username = this.value.trim();
            
            // Clear previous timeout
            clearTimeout(usernameCheckTimeout);
            
            if (username.length === 0) {
                usernameStatus.textContent = '';
                usernameStatus.className = 'username-status';
                isUsernameAvailable = true;
                return;
            }

            // Validazione email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(username)) {
                usernameStatus.textContent = 'Inserisci un indirizzo email valido';
                usernameStatus.className = 'username-status error';
                isUsernameAvailable = false;
                return;
            }

            // Mostra stato di caricamento
            usernameStatus.textContent = 'Controllo disponibilità...';
            usernameStatus.className = 'username-status checking';

            // Debounce della richiesta
            usernameCheckTimeout = setTimeout(() => {
                checkUsernameAvailability(username);
            }, 500);
        });
    }

    function checkUsernameAvailability(username) {
        const formData = new FormData();
        formData.append('action', 'check_username');
        formData.append('username', username);

        fetch('log.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            const usernameStatus = document.getElementById('username-status');
            if (!usernameStatus) return;

            if (data.available) {
                usernameStatus.textContent = '✓ Email disponibile';
                usernameStatus.className = 'username-status success';
                isUsernameAvailable = true;
            } else {
                usernameStatus.textContent = '✗ Email già registrata';
                usernameStatus.className = 'username-status error';
                isUsernameAvailable = false;
            }
        })
        .catch(error => {
            console.error('Errore controllo username:', error);
            const usernameStatus = document.getElementById('username-status');
            if (usernameStatus) {
                usernameStatus.textContent = 'Errore nel controllo disponibilità';
                usernameStatus.className = 'username-status error';
            }
            isUsernameAvailable = false;
        });
    }

    function hideUsernameValidation() {
        const usernameStatus = document.getElementById('username-status');
        if (usernameStatus) {
            usernameStatus.style.display = 'none';
        }
    }

    // 5. Validazione form aggiornata
    function validateAuthForm(username, password) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(username)) {
            return { valid: false, message: 'Inserisci un indirizzo email valido' };
        }
        
        if (!isLogin) {
            // Per la registrazione, controlla la disponibilità username
            if (!isUsernameAvailable) {
                return { valid: false, message: 'Email non disponibile o non valida' };
            }
            
            // Validazione password avanzata
            if (!validatePasswordStrength(password)) {
                return { valid: false, message: 'La password non soddisfa i requisiti di sicurezza' };
            }
        }
        
        return { valid: true };
    }

    // 6. Gestione submit form aggiornata
    function setupAuthForm() {
        if (!authForm) return;
        
        authForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            const validation = validateAuthForm(username, password);
            if (!validation.valid) {
                authMessage.textContent = validation.message;
                authMessage.style.color = 'red';
                return;
            }
            
            authSubmit.disabled = true;
            authSubmit.textContent = 'Caricamento...';
            
            const formData = new FormData();
            formData.append('action', isLogin ? 'login' : 'register');
            formData.append('username', username);
            formData.append('password', password);
            
            fetch('log.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    authMessage.textContent = data.message;
                    authMessage.style.color = 'green';
                    updateUserUI(true);
                    
                    setTimeout(() => {
                        if (typeof loadCart === 'function') {
                            loadCart();
                        }
                    }, 100);
                    
                    currentTimeout = setTimeout(function() {
                        authDropdown.classList.remove('show');
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Operazione fallita');
                }
            })
            .catch(function(error) {
                authMessage.textContent = error.message;
                authMessage.style.color = 'red';
            })
            .finally(function() {
                authSubmit.disabled = false;
                authSubmit.textContent = isLogin ? 'Accedi' : 'Registrati';
            });
        });
    }

    function logoutUser() {
        const logoutBtn = document.getElementById('logoutBtn');
        if (!logoutBtn) return;

        logoutBtn.disabled = true;

        fetch('log.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=logout',
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateUserUI(false);
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            } else {
                throw new Error(data.message || 'Logout failed');
            }
        })
        .catch(error => {
            console.error('Logout error:', error);
        });
    }

    // 7. Aggiornamento UI dopo login/logout
    function updateUserUI(isLoggedIn) {
        const loginContainer = document.getElementById('login1');
        
        if (isLoggedIn) {
            loginContainer.innerHTML = `
                <div class="login user-menu">
                    <button class="profile profile-btn">
                        <img src="https://img.icons8.com/?size=100&id=ABBSjQJK83zf&format=png&color=000000" alt="user">
                    </button>
                    <ul class="user-dropdown" id="userDropdown">
                        <li><a href="profile.php">Profilo</a></li>
                        <li><a href="./favorite_list.php">preferiti❤️</a></li>
                        <li><button id="logoutBtn" class="logout-btn">Logout</button></li>
                    </ul>
                </div>
            `;
            setupUserDropdown();
        } else {
            // Ripristina il form di login originale
            loginContainer.innerHTML = `
                <div class="login" id="login1">
                    <!-- Contenuto originale del form di login -->
                </div>
            `;
        }
        
        // RI-inizializza gli event listeners solo se necessario
        setupLoginDropdown();
        setupAuthTabs();
        setupAuthForm();
    }   

    // 8. Gestione dropdown utente e logout
    function setupUserDropdown() {
        const profileBtn = document.querySelector('.profile-btn');
        const userDropdown = document.getElementById('userDropdown');
        const logoutBtn = document.getElementById('logoutBtn');
        
        if (profileBtn && userDropdown) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
            });
        }
        
        document.addEventListener('click', function() {
            if (userDropdown) {
                userDropdown.classList.remove('show');
            }
        });
        
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                logoutUser();
            });
        }
    }

    // 9. Funzione per verificare lo stato di login
    function checkLoginStatus() {
        return fetch('log.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check_status',
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            return data.logged_in || false;
        })
        .catch(error => {
            console.error('Errore nel controllo dello stato di login:', error);
            return false;
        });
    }

    // Inizializzazione
    function init() {
        setupLoginDropdown();
        setupAuthTabs();
        setupAuthForm();
        
        checkLoginStatus().then(isLoggedIn => {
            if (isLoggedIn) {
                updateUserUI(true);
                if (typeof loadCart === 'function') {
                    loadCart();
                }
            }
        });
    }

    init();
});