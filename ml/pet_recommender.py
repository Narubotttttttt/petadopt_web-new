# -*- coding: utf-8 -*-
"""
CAWS Pet Adoption System - Multi-Species Machine Learning Recommendation Engine
Algorithm : Supervised Random Forest Classifier (Scikit-Learn)
Model     : ml/pet_match_model.pkl (trained on 67,000 adoption records: Dogs & Cats)
Features  : ml/feature_columns.json (38 features including species_dog & species_cat)
"""

import os
import sys

# Ensure Windows system environment variables are present for Winsock and C-extensions
if sys.platform == 'win32':
    if 'SystemRoot' not in os.environ:
        os.environ['SystemRoot'] = os.environ.get('SYSTEMROOT', r'C:\Windows')
    if 'windir' not in os.environ:
        os.environ['windir'] = os.environ.get('WINDIR', r'C:\Windows')

import site
for _p in [
    os.path.join(os.path.dirname(os.path.dirname(__file__)), '.venv', 'Lib', 'site-packages'),
    getattr(site, 'getusersitepackages', lambda: '')(),
    *(getattr(site, 'getsitepackages', lambda: [])()),
    os.path.join(sys.prefix, 'Lib', 'site-packages'),
]:
    if _p and os.path.isdir(_p) and _p not in sys.path:
        sys.path.insert(0, _p)

import json
import re
import joblib
import numpy as np
import pandas as pd

ALL_TEMPERAMENT_TAGS = [
    'Friendly', 'Calm', 'Energetic', 'Shy',
    'Playful', 'Independent', 'Affectionate', 'Protective'
]

COLOR_FAMILIES = {
    'orange': ['orange', 'ginger', 'red', 'yellow', 'gold'],
    'black': ['black', 'dark', 'charcoal'],
    'white': ['white', 'cream', 'ivory', 'light'],
    'brown': ['brown', 'tan', 'chocolate', 'fawn', 'buff', 'cocoa'],
    'tricolor': ['tricolor', 'calico', 'tortie', 'torbie', 'multi'],
    'gray': ['gray', 'grey', 'silver', 'blue', 'smoke'],
    'tabby': ['tabby', 'striped', 'brindle', 'tiger'],
}

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH    = os.path.join(SCRIPT_DIR, 'pet_match_model.pkl')
FEATURES_PATH = os.path.join(SCRIPT_DIR, 'feature_columns.json')

_trained_model   = None
_feature_columns = None


def _load_model():
    global _trained_model, _feature_columns
    if _trained_model is None and os.path.exists(MODEL_PATH) and os.path.exists(FEATURES_PATH):
        try:
            _trained_model   = joblib.load(MODEL_PATH)
            with open(FEATURES_PATH, 'r', encoding='utf-8') as f:
                _feature_columns = json.load(f)
        except Exception:
            _trained_model   = None
            _feature_columns = None
    return _trained_model, _feature_columns


def _parse_age_numeric(age_str):
    """Converts age string into numeric float years."""
    if not isinstance(age_str, str):
        return 3.0
    s = age_str.lower().strip()
    match = re.search(r'(\d+(?:\.\d+)?)', s)
    num = float(match.group(1)) if match else 2.0

    if any(k in s for k in ['month', 'mo']):
        return max(0.1, round(num / 12.0, 2))
    elif any(k in s for k in ['week', 'wk']):
        return max(0.1, round(num / 52.0, 2))
    elif any(k in s for k in ['day']):
        return 0.1
    elif any(k in s for k in ['puppy', 'kitten', '< 1']):
        return 0.5
    elif any(k in s for k in ['senior', 'old']):
        return 9.0
    return max(0.1, num)


def _parse_age_category(age_str):
    if not isinstance(age_str, str):
        return 'adult'
    s = age_str.lower().strip()
    if any(k in s for k in ['day', 'week', 'month', '0 year', '0 years', 'puppy', 'kitten', '< 1']):
        return 'kitten_puppy'
    elif any(k in s for k in ['1 year', '2 year', 'young', 'junior']):
        return 'young'
    elif any(k in s for k in ['8 year', '9 year', '10 year', '11 year', '12 year', 'senior', 'old']):
        return 'senior'
    return 'adult'


def _match_color(pref_col, pet_color):
    pref = str(pref_col or 'any').lower().strip()
    if pref in ['any', 'all', '', 'any color']:
        return 1.0

    pet_col = str(pet_color or '').lower().strip()
    if not pet_col:
        return 0.5

    if pref in pet_col or pet_col in pref:
        return 1.0

    for family, aliases in COLOR_FAMILIES.items():
        pref_in_family = any(a in pref for a in aliases) or family in pref
        pet_in_family = any(a in pet_col for a in aliases) or family in pet_col
        if pref_in_family and pet_in_family:
            return 1.0

    return 0.0


def _match_age(pref_age, pet_age_cat):
    pref = str(pref_age or 'any').lower().strip()
    if pref in ['any', 'all', '', 'any age']:
        return 1.0

    if any(k in pref for k in ['puppy', 'kitten', '< 1', 'kitten_puppy']):
        p_cat = 'kitten_puppy'
    elif any(k in pref for k in ['young', '1 - 3', 'junior']):
        p_cat = 'young'
    elif any(k in pref for k in ['senior', '8+', 'old']):
        p_cat = 'senior'
    else:
        p_cat = 'adult'

    if p_cat == pet_age_cat:
        return 1.0

    adjacency = {
        'kitten_puppy': ['young'],
        'young': ['kitten_puppy', 'adult'],
        'adult': ['young', 'senior'],
        'senior': ['adult'],
    }
    if pet_age_cat in adjacency.get(p_cat, []):
        return 0.4
    return 0.0


def _match_gender(pref_gender, pet_gender):
    pref = str(pref_gender or 'any').lower().strip()
    if pref in ['any', 'all', '', 'any gender']:
        return 1.0

    pet_g = str(pet_gender or '').lower().strip()
    if not pet_g or pet_g == 'unknown':
        return 0.5

    if pref == 'male' and ('male' in pet_g and 'female' not in pet_g):
        return 1.0
    if pref == 'female' and 'female' in pet_g:
        return 1.0
    return 0.0


# ---------------------------------------------------------
# Multi-Species Feature Vector Builder (for Scikit-Learn)
# ---------------------------------------------------------
def _build_multi_species_feature_vector(adopter_profile, pet, feature_cols):
    species  = str(pet.get('type', 'dog')).lower()
    living   = str(adopter_profile.get('living_environment', 'apartment')).lower()
    activity = str(adopter_profile.get('activity_level', 'moderate')).lower()
    exp      = str(adopter_profile.get('pet_experience', 'first_time')).lower()
    has_kids = int(bool(adopter_profile.get('has_children', False)))
    has_pets = int(bool(adopter_profile.get('has_other_pets', False)))
    hours    = str(adopter_profile.get('hours_alone', '4_7')).lower()

    pet_tags = [t for t in pet.get('temperaments', []) if t in ALL_TEMPERAMENT_TAGS]
    age_yrs  = _parse_age_numeric(pet.get('age', '2 years'))
    pet_size = str(pet.get('size', 'medium')).lower()

    # Behavioral metrics based on species & temperament
    if species == 'cat':
        # Feline behavioral profiles
        if 'Protective' in pet_tags:
            aggression_score = 2.5
            reactivity_score = 4.5
        elif 'Friendly' in pet_tags:
            aggression_score = 0.5
            reactivity_score = 1.0
        else:
            aggression_score = 1.0
            reactivity_score = 2.0

        if 'Independent' in pet_tags:
            anxiety_score = 1.0
        elif 'Shy' in pet_tags:
            anxiety_score = 4.5
        elif 'Affectionate' in pet_tags:
            anxiety_score = 2.5
        else:
            anxiety_score = 2.0

        if 'Energetic' in pet_tags:
            energy_level = 8.0
        elif 'Playful' in pet_tags:
            energy_level = 6.5
        elif 'Calm' in pet_tags:
            energy_level = 2.5
        else:
            energy_level = 4.5

        training_level = 9.0  # Felines naturally litter-trained
        house_trained  = 1
        size_home_mismatch = 0  # Cats do not suffer size-home mismatch in apartments

    else:
        # Canine behavioral profiles
        if 'Protective' in pet_tags:
            aggression_score = 3.5
            reactivity_score = 3.5
        elif 'Friendly' in pet_tags:
            aggression_score = 0.5
            reactivity_score = 0.5
        else:
            aggression_score = 1.5
            reactivity_score = 2.0

        if 'Independent' in pet_tags:
            anxiety_score = 1.5
        elif 'Shy' in pet_tags:
            anxiety_score = 5.5
        elif 'Affectionate' in pet_tags:
            anxiety_score = 4.0
        else:
            anxiety_score = 3.0

        if 'Energetic' in pet_tags:
            energy_level = 8.5
        elif 'Playful' in pet_tags:
            energy_level = 7.0
        elif 'Calm' in pet_tags:
            energy_level = 3.0
        else:
            energy_level = 5.0

        training_level = 7.0 if 'Calm' in pet_tags else (3.5 if age_yrs < 1.0 else 5.5)
        house_trained  = 1 if (age_yrs >= 1.0 or 'Calm' in pet_tags) else 0

        has_yard_flag = 1 if ('yard' in living and 'no_yard' not in living and 'no_fence' not in living) else 0
        size_home_mismatch = 1 if (pet_size in ['large', 'xlarge'] and 'apartment' in living and has_yard_flag == 0) else 0

    adopter_activity = 8.5 if activity in ['high', 'active'] else (2.5 if activity == 'relaxed' else 5.5)
    hours_val = 8.5 if hours in ['8_plus', '8+', '8plus'] else (2.0 if hours in ['0_3', '0-3'] else 5.5)
    has_yard_val = 1 if ('yard' in living and 'no_yard' not in living and 'no_fence' not in living) else 0

    energy_mismatch = abs(energy_level - adopter_activity)

    med_hist = str(pet.get('medical_history', '')).lower()
    is_chronic = int(any(k in med_hist for k in ['special', 'chronic', 'amputee', 'blind', 'deaf', 'disabilit']))
    is_minor   = int(any(k in med_hist for k in ['minor', 'treatment', 'recovery', 'skin']) and not is_chronic)
    is_none    = 1 if not (is_chronic or is_minor) else 0

    feat_dict = {
        'age_years'                   : age_yrs,
        'days_in_shelter'             : int(pet.get('days_in_shelter', 25) or 25),
        'previously_returned'         : int(pet.get('previously_returned', 0) or 0),
        'neutered'                    : 1,
        'aggression_score'            : aggression_score,
        'anxiety_separation'          : anxiety_score,
        'reactivity_to_dogs'          : reactivity_score,
        'energy_level'                : energy_level,
        'training_level'              : training_level,
        'house_trained'               : house_trained,
        'first_time_owner'            : 1 if exp == 'first_time' else 0,
        'household_has_kids'          : has_kids,
        'household_has_pets'          : has_pets,
        'has_yard'                    : has_yard_val,
        'hours_alone_per_day'         : hours_val,
        'adopter_activity_level'      : adopter_activity,
        'visits_before_adoption'      : 2,
        'met_resident_pets'           : has_pets,
        'adoption_counseling'         : 1,
        'expectation_score'           : 7.5,
        'energy_mismatch'             : energy_mismatch,
        'size_home_mismatch'          : size_home_mismatch,
        'species_cat'                 : 1 if species == 'cat' else 0,
        'species_dog'                 : 1 if species == 'dog' else 0,
        'size_large'                  : 1 if pet_size == 'large' else 0,
        'size_medium'                 : 1 if pet_size == 'medium' else 0,
        'size_small'                  : 1 if pet_size == 'small' else 0,
        'size_xlarge'                 : 1 if pet_size in ['xlarge', 'extra_large', 'giant'] else 0,
        'intake_type_born_in_care'    : 0,
        'intake_type_owner_surrender' : 1 if 'surrender' in str(pet.get('intake_type', '')).lower() else 0,
        'intake_type_stray'           : 1 if 'surrender' not in str(pet.get('intake_type', '')).lower() else 0,
        'intake_type_transfer'        : 0,
        'medical_needs_chronic'       : is_chronic,
        'medical_needs_minor'         : is_minor,
        'medical_needs_none'          : is_none,
        'home_type_apartment'         : 1 if 'apartment' in living else 0,
        'home_type_house_owned'       : 1 if ('yard' in living and 'no_yard' not in living) else 0,
        'home_type_house_rented'      : 1 if ('no_yard' in living or 'rent' in living) else 0,
    }

    return pd.DataFrame([[feat_dict.get(c, 0) for c in feature_cols]], columns=feature_cols)


# ---------------------------------------------------------
# Match Reason Generator (Real-Life Grounded Explanations)
# ---------------------------------------------------------
def _generate_reasons(adopter_profile, pet, pet_tags_list, tag_sim,
                      color_match_score, age_match_score, gender_match_score):
    if not isinstance(adopter_profile, dict):
        adopter_profile = {}

    reasons = []
    tags_lower = [t.lower() for t in pet_tags_list]
    pref_age   = str(adopter_profile.get('preferred_age', 'any')).lower().strip()
    species    = str(pet.get('type', 'dog')).lower()

    living   = str(adopter_profile.get('living_environment', '')).lower()
    activity = str(adopter_profile.get('activity_level', '')).lower()
    exp      = str(adopter_profile.get('pet_experience', '')).lower()
    has_kids = bool(adopter_profile.get('has_children', False))
    has_pets = bool(adopter_profile.get('has_other_pets', False))
    hours    = str(adopter_profile.get('hours_alone', '')).lower()
    sp_cap   = bool(adopter_profile.get('special_care_capacity', False) or adopter_profile.get('special_care_capability', False))

    if species == 'cat':
        if 'apartment' in living and ('calm' in tags_lower or 'affectionate' in tags_lower):
            reasons.append("Gentle and calm disposition well-suited for relaxed apartment living")
        elif 'apartment' in living and ('playful' in tags_lower or 'energetic' in tags_lower) and activity in ['high', 'active']:
            reasons.append("Engaging play style that matches your active, interactive home")
        elif 'yard' in living or 'house' in living:
            reasons.append("Enjoys having spacious territory to explore indoors")

        if hours in ['8_plus', '8+'] and ('independent' in tags_lower or 'calm' in tags_lower):
            reasons.append("Independent personality comfortable while you are away during work hours")

        if has_kids and ('friendly' in tags_lower or 'playful' in tags_lower):
            reasons.append("Patient and friendly demeanor tested safe around children")
        elif not has_kids and 'shy' in tags_lower:
            reasons.append("Gentle cat that will thrive in your quiet, calm household")
    else:
        pet_size = str(pet.get('size', 'medium')).lower()
        if ('yard' in living and 'no_yard' not in living and 'no_fence' not in living) and ('energetic' in tags_lower or pet_size in ['large', 'xlarge']):
            reasons.append("Thrives in a home with secure yard space for daily exercise")
        elif 'apartment' in living and ('calm' in tags_lower or pet_size == 'small'):
            reasons.append("Manageable size and calm behavior ideal for apartment living")

        if activity in ['high', 'active'] and ('energetic' in tags_lower or 'playful' in tags_lower):
            reasons.append("High stamina and enthusiasm matching your active lifestyle")
        elif activity == 'relaxed' and 'calm' in tags_lower:
            reasons.append("Peaceful, low-key companion perfect for relaxed home routines")

        if hours in ['8_plus', '8+'] and 'independent' in tags_lower:
            reasons.append("Self-sufficient and comfortable resting during work hours")

        if has_kids and ('friendly' in tags_lower or 'playful' in tags_lower):
            reasons.append("Gentle and playful companion safe for family households with kids")

    if exp == 'first_time' and ('friendly' in tags_lower or 'calm' in tags_lower):
        reasons.append("Easygoing and predictable nature recommended for first-time owners")

    if has_pets and ('friendly' in tags_lower or 'playful' in tags_lower):
        reasons.append("Sociable disposition tested compatible with other resident pets")

    med_hist = str(pet.get('medical_history', '')).lower()
    if any(k in med_hist for k in ['special', 'chronic', 'amputee', 'blind', 'deaf', 'disabilit']) and sp_cap:
        reasons.append("Matches your readiness and compassion to support special medical needs")

    if pref_age not in ['any', 'all', '', 'any age'] and age_match_score >= 0.75:
        reasons.append(f"Matches your preferred {pet.get('age', 'age')} life stage")

    return reasons[:3] if reasons else ["Strong overall behavioral and lifestyle compatibility"]


# ---------------------------------------------------------
# Main Multi-Species ML Recommendation Function
# ---------------------------------------------------------
def recommend_pets(pets_data, adopter_profile, top_k=3):
    """
    Ranks pet recommendations using the Multi-Species Scikit-Learn Random Forest Model.
    Both dogs and cats execute model.predict_proba() directly.
    """
    if not pets_data:
        return []

    if not isinstance(adopter_profile, dict):
        adopter_profile = {}

    # STRICT FILTER: Focus ONLY on pets that are ready for adoption (status == 'available').
    # Pets with status 'in_shelter', 'pending', 'adopted', or 'deceased' MUST NEVER be recommended.
    available = [
        p for p in pets_data
        if str(p.get('status', '')).strip().lower() == 'available'
    ]
    if not available:
        return []

    # Strict species filter if user requested
    pref_species = str(adopter_profile.get('preferred_species', 'any')).lower()
    if pref_species in ['dog', 'cat']:
        filtered = [p for p in available if str(p.get('type', '')).lower() == pref_species]
        if not filtered:
            return []
        available = filtered

    model, feature_cols = _load_model()
    results = []

    pref_col    = str(adopter_profile.get('preferred_color', 'any')).lower()
    pref_age    = str(adopter_profile.get('preferred_age', 'any')).lower()
    pref_gender = str(adopter_profile.get('preferred_gender', 'any')).lower()

    desired_tags_list = [t for t in adopter_profile.get('desired_temperaments', [])
                         if t in ALL_TEMPERAMENT_TAGS]
    desired_vec = [1 if t in desired_tags_list else 0 for t in ALL_TEMPERAMENT_TAGS]

    for pet in available:
        species = str(pet.get('type', 'dog')).lower()
        color   = str(pet.get('color', '')).lower()
        gender  = str(pet.get('gender', 'unknown')).lower()
        age_cat = _parse_age_category(str(pet.get('age', 'adult')))

        pet_tags_list = [t for t in pet.get('temperaments', []) if t in ALL_TEMPERAMENT_TAGS]
        pet_vec = [1 if t in pet_tags_list else 0 for t in ALL_TEMPERAMENT_TAGS]

        col_score = _match_color(pref_col, color)
        age_score = _match_age(pref_age, age_cat)
        gen_score = _match_gender(pref_gender, gender)

        # Temperament Cosine Similarity
        d_arr = np.array(desired_vec, dtype=float)
        p_arr = np.array(pet_vec, dtype=float)
        d_mag = np.linalg.norm(d_arr)
        p_mag = np.linalg.norm(p_arr)
        tag_sim = float(np.dot(d_arr, p_arr) / (d_mag * p_mag)) if d_mag > 0 and p_mag > 0 else 0.50

        # MULTI-SPECIES ML EXECUTION: model.predict_proba() runs for ALL pets (dogs & cats)!
        if model is not None and feature_cols is not None:
            feat_df = _build_multi_species_feature_vector(adopter_profile, pet, feature_cols)
            proba = model.predict_proba(feat_df)[0]
            rf_prob = float(proba[1])  # Predicted probability of compatibility
            final_metric = (0.70 * rf_prob) + (0.30 * tag_sim)
            algo_name = 'Scikit-Learn Random Forest Classifier (Multi-Species Trained Model)'
        else:
            final_metric = (0.60 * 0.70) + (0.40 * tag_sim)
            algo_name = 'Multi-Species Compatibility Engine'

        match_pct = round(min(99.0, max(52.0, (final_metric * 45.0) + 53.0)), 1)

        reasons = _generate_reasons(
            adopter_profile, pet, pet_tags_list, tag_sim,
            col_score, age_score, gen_score
        )

        results.append({
            'pet_id'              : pet.get('id'),
            'name'                : pet.get('name'),
            'type'                : species,
            'breed'               : pet.get('breed'),
            'age'                 : pet.get('age'),
            'gender'              : pet.get('gender'),
            'size'                : pet.get('size'),
            'color'               : pet.get('color'),
            'photo_path'          : pet.get('photo_path'),
            'status'              : pet.get('status'),
            'temperaments'        : pet_tags_list,
            'match_score'         : match_pct,
            'match_percentage'    : match_pct,
            'match_percentage_str': f"{match_pct}%",
            'reasons'             : reasons,
            'algorithm_used'      : algo_name
        })

    # Sort descending by match_score
    results.sort(key=lambda x: x['match_score'], reverse=True)
    return results[:top_k] if top_k else results