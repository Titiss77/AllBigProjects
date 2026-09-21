document.addEventListener("DOMContentLoaded", () => {
    // Récupération des dates et sécurité anti-NaN
    const targetDateStr = window.timerData.targetDate;
    let targetDate = targetDateStr ? new Date(targetDateStr) : null;
    if (targetDate && isNaN(targetDate.getTime())) targetDate = null; 
    
    const pastDateStr = window.timerData.pastDate;
    let pastDate = pastDateStr ? new Date(pastDateStr) : null;
    if (pastDate && isNaN(pastDate.getTime())) pastDate = null; 

    // Calcul de la date de départ pour la barre (exactement 1 an avant la date cible)
    let progressStartDate = null;
    if (targetDate) {
        progressStartDate = new Date(targetDate.getTime());
        progressStartDate.setFullYear(progressStartDate.getFullYear() - 1);
    }
    
    // Formateur d'heure pour le fuseau de Paris
    const frTimeFormatter = new Intl.DateTimeFormat('fr-FR', {
        timeZone: 'Europe/Paris',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    // Fonction précise pour calculer la différence incluant le texte complet et les MS
    function formatTimeDiff(startDate, endDate) {
        let years = endDate.getFullYear() - startDate.getFullYear();
        let months = endDate.getMonth() - startDate.getMonth();
        let days = endDate.getDate() - startDate.getDate();
        let hours = endDate.getHours() - startDate.getHours();
        let minutes = endDate.getMinutes() - startDate.getMinutes();
        let seconds = endDate.getSeconds() - startDate.getSeconds();
        let ms = endDate.getMilliseconds() - startDate.getMilliseconds();

        // Gestion des retenues (soustraction)
        if (ms < 0) { seconds--; ms += 1000; }
        if (seconds < 0) { minutes--; seconds += 60; }
        if (minutes < 0) { hours--; minutes += 60; }
        if (hours < 0) { days--; hours += 24; }
        if (days < 0) {
            months--;
            let prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0);
            days += prevMonth.getDate();
        }
        if (months < 0) { years--; months += 12; }

        let result = "";
        
        // Singulier / Pluriel textuel
        if (years > 0) result += years + (years > 1 ? " ans, " : " an, ");
        if (months > 0) result += months + " mois, ";
        if (days > 0 || years > 0 || months > 0) result += days + (days > 1 ? " jours, " : " jour, ");
        
        result += hours + (hours > 1 ? " heures, " : " heure, ");
        result += minutes + (minutes > 1 ? " minutes, " : " minute, ");
        result += seconds + (seconds > 1 ? " secondes" : " seconde");
        
        // Formatage des millisecondes sur 3 chiffres avec un petit style
        let msFormatted = ms.toString().padStart(3, '0');
        result += ` <span class="ms-text">et ${msFormatted} ms</span>`;
        
        return result;
    }

    function updateTimers() {
        const nowObj = new Date();
        const now = nowObj.getTime();
        
        // Mise à jour de l'heure actuelle
        const currentTimeEl = document.getElementById("current-time");
        if (currentTimeEl) currentTimeEl.innerHTML = "Heure actuelle (FR) : " + frTimeFormatter.format(nowObj);

        /* ========================================================
           1. CARTE 1 : COUNTDOWN
           ======================================================== */
        const timerEl = document.getElementById("timer");
        if (timerEl) {
            if (targetDate) {
                const diffFuture = targetDate.getTime() - now;
                
                if (diffFuture <= 0) {
                    timerEl.innerHTML = "Terminé !";
                    timerEl.className = "timer-box";

                    const progressBar = document.getElementById("progress-bar");
                    const progressText = document.getElementById("progress-text");
                    if(progressBar) progressBar.style.width = "0%";
                    if(progressText) progressText.innerHTML = "0.00000% restant";

                } else {
                    timerEl.innerHTML = formatTimeDiff(nowObj, targetDate);
                    
                    const hours24 = 24 * 60 * 60 * 1000;
                    const days7 = 7 * hours24;
                    
                    if (diffFuture < hours24) {
                        timerEl.className = "timer-box urgent-red";
                    } else if (diffFuture < days7) {
                        timerEl.className = "timer-box warning-orange";
                    } else {
                        timerEl.className = "timer-box";
                    }

                    if (progressStartDate) {
                        const totalDuration = targetDate.getTime() - progressStartDate.getTime();
                        const remainingDuration = targetDate.getTime() - now;
                        
                        if (totalDuration > 0) {
                            let percentRemaining = (remainingDuration / totalDuration) * 100;
                            percentRemaining = Math.max(0, Math.min(100, percentRemaining));
                            
                            const progressBar = document.getElementById("progress-bar");
                            const progressText = document.getElementById("progress-text");
                            if(progressBar) progressBar.style.width = percentRemaining + "%";
                            if(progressText) progressText.innerHTML = percentRemaining.toFixed(2) + "% restant";
                        }
                    }
                }
            } else {
                timerEl.innerHTML = "Veuillez définir une date.";
            }
        }

        /* ========================================================
           2. CARTE 2 : TEMPS ÉCOULÉ (PASSÉ)
           ======================================================== */
        const elapsedTimerEl = document.getElementById("elapsed-timer");
        if (elapsedTimerEl) {
            if (pastDate) {
                if (now - pastDate.getTime() < 0) {
                    elapsedTimerEl.innerHTML = "Cette date n'est pas encore passée !";
                } else {
                    elapsedTimerEl.innerHTML = formatTimeDiff(pastDate, nowObj);
                }
            } else {
                elapsedTimerEl.innerHTML = "Veuillez définir une date.";
            }
        }

        /* ========================================================
           3. CARTE 3 : PROGRESSION DE L'ANNÉE EN COURS
           ======================================================== */
        const currentYear = nowObj.getFullYear();
        const startOfYear = new Date(currentYear, 0, 1).getTime();
        const endOfYear = new Date(currentYear + 1, 0, 1).getTime();
        
        const yearTotal = endOfYear - startOfYear;
        const yearElapsed = now - startOfYear;
        let yearPercent = (yearElapsed / yearTotal) * 100;
        yearPercent = Math.max(0, Math.min(100, yearPercent));

        const yearTextEl = document.getElementById('current-year-text');
        const yearBarEl = document.getElementById('year-progress-bar');
        const yearPercentEl = document.getElementById('year-progress-text');
        
        if (yearTextEl) yearTextEl.innerHTML = `Du 1er Janvier au 31 Décembre ${currentYear}`;
        if (yearBarEl) yearBarEl.style.width = yearPercent + "%";
        if (yearPercentEl) yearPercentEl.innerHTML = yearPercent.toFixed(2) + "% écoulé";

        /* ========================================================
           4. CARTE 4 : PROCHAIN WEEK-END (Vendredi 18h)
           ======================================================== */
        let nextFriday = new Date(nowObj.getTime());
        const dayOfWeek = nextFriday.getDay(); 
        
        let isWeekend = false;
        if (dayOfWeek === 6 || dayOfWeek === 0 || (dayOfWeek === 5 && nextFriday.getHours() >= 18)) {
            isWeekend = true;
        } else {
            const daysToFriday = (5 - dayOfWeek + 7) % 7;
            nextFriday.setDate(nextFriday.getDate() + daysToFriday);
            nextFriday.setHours(18, 0, 0, 0); 
        }

        const weekendTimerEl = document.getElementById('weekend-timer');
        const weekendDateTextEl = document.getElementById('weekend-date-text');

        if (weekendTimerEl && weekendDateTextEl) {
            if (isWeekend) {
                weekendTimerEl.innerHTML = "C'est le week-end ! 🎉";
                weekendTimerEl.className = "timer-box warning-orange"; 
                weekendDateTextEl.innerHTML = "Profitez bien, retour au travail Lundi !";
            } else {
                weekendDateTextEl.innerHTML = "Vendredi à 18h00";
                weekendTimerEl.className = "timer-box";
                weekendTimerEl.innerHTML = formatTimeDiff(nowObj, nextFriday);
            }
        }

        // Boucle infinie fluide adaptée à l'écran
        requestAnimationFrame(updateTimers);
    }
    
    // Lancement de l'animation
    requestAnimationFrame(updateTimers);
});