export function calculateBodyMetrics(gender, age, height, weight, neck, waist, hip, wrist, calf, thigh, activityMultiplier, isAthlete = false) {
    if (!height || !weight || !waist || !age) return null;

    const imc = weight / Math.pow(height / 100, 2);

    // 1. CUN-BAE (Clínica Universidad de Navarra)
    const sexCun = gender === 'male' ? 0 : 1;
    const cunBae = -44.988 + (0.503 * age) + (3.172 * imc) - (0.026 * Math.pow(imc, 2)) 
        + (10.689 * sexCun) + (0.028 * age * sexCun) - (0.02 * imc * age) 
        + (0.00021 * Math.pow(imc, 2) * age) + (0.015 * Math.pow(imc, 2) * sexCun);

    // 2. RFM (Relative Fat Mass)
    const rfmConstant = gender === 'male' ? 64 : 76;
    const rfm = rfmConstant - (20 * (height / waist));

    // 3. Covert Bailey (Ajusté métrique)
    let covertBailey = 0;
    if (gender === 'male') {
        covertBailey = (waist + 0.5 * hip) * 0.35 - (wrist * 1.2) - (calf * 0.2) - (thigh * 0.2) - 3;
    } else {
        covertBailey = (hip + 0.8 * thigh) * 0.35 - (calf * 0.5) - (wrist * 0.5) - 10;
    }

    // Modèle Hybride DEXA (Moyenne pondérée : 40% CUN-BAE, 40% RFM, 20% Bailey)
    let bodyFat = 0;
    if (wrist > 0 && calf > 0 && thigh > 0 && hip > 0) {
        bodyFat = (cunBae * 0.4) + (rfm * 0.4) + (covertBailey * 0.2);
    } else {
        bodyFat = (cunBae + rfm) / 2; // Fallback sécurisé
    }

    // Ajustement Athlète (-15% de gras estimé car CUN-BAE surestime le gras via l'IMC)
    if (isAthlete) {
        bodyFat *= (gender === 'male' ? 0.85 : 0.90);
    }

    // Bornes physiologiques strictes
    const minFatPercent = gender === 'male' ? 4.0 : 12.0;
    bodyFat = Math.max(minFatPercent, Math.min(bodyFat, 60));

    // Balance des masses
    const fatMass = weight * (bodyFat / 100);
    const leanMass = weight - fatMass;

    // Katch-McArdle pour l'énergie
    const bmr = 370 + (21.6 * leanMass);
    const tdee = bmr * activityMultiplier;

    return { bf: bodyFat, fatMass, leanMass, bmr, tdee, imc };
}

export function calculateMacros(tdee, weight, trainingType) {
    let proteinMultiplier, fatMultiplier;

    switch(trainingType) {
        case 'force':
            proteinMultiplier = 2.2;
            fatMultiplier = 1.0;
            break;
        case 'endurance':
            proteinMultiplier = 1.6;
            fatMultiplier = 1.0;
            break;
        default: // repos
            proteinMultiplier = 1.8;
            fatMultiplier = 0.9;
    }

    const protein = weight * proteinMultiplier;
    const fat = weight * fatMultiplier;
    const carbs = (tdee - (protein * 4) - (fat * 9)) / 4;

    return {
        protein: Math.round(protein),
        fat: Math.round(fat),
        carbs: Math.max(0, Math.round(carbs))
    };
}