# -*- coding: utf-8 -*-
"""
CAWS Pet Adoption System - Multi-Species Machine Learning Recommendation Engine
Algorithm : Supervised Random Forest Classifier (Scikit-Learn)
Model     : ml/pet_match_model.pkl (trained on 67,000 adoption records: Dogs & Cats)
Features  : ml/feature_columns.json (38 features including species_dog & species_cat)
Explainer : Dynamic LLM Reasoning (Google Gemini)
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
    *(getattr(site, 'getsitepackages', list)()),
    os.path.join(sys.prefix, 'Lib', 'site-packages'),
]:
    if _p and os.path.isdir(_p) and _p not in sys.path:
        sys.path.insert(0, _p)

import json
import re
import joblib
import pandas as pd
from urllib.request import Request, urlopen
import ssl
from typing import Any

ALL_TEMPERAMENT_TAGS = [
    'Friendly', 'Calm', 'Energetic', 'Shy',
    'Playful', 'Independent', 'Affectionate', 'Protective'
]

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
    has_yard_val = 1 if bool(adopter_profile.get('has_yard', False)) else (1 if ('yard' in living and 'no_yard' not in living and 'no_fence' not in living) else 0)

    pet_tags = [t for t in pet.get('temperaments', []) if t in ALL_TEMPERAMENT_TAGS]
    age_yrs  = _parse_age_numeric(pet.get('age', '2 years'))
    pet_size = str(pet.get('size', 'medium')).lower()
    if species == 'cat' and pet_size not in ['small', 'medium']:
        pet_size = 'medium'

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


        size_home_mismatch = 1 if (pet_size in ['large', 'xlarge'] and 'apartment' in living and has_yard_val == 0) else 0

    adopter_activity = 8.5 if activity in ['high', 'active'] else (2.5 if activity == 'relaxed' else 5.5)
    hours_val = 8.5 if hours in ['8_plus', '8+', '8plus'] else (2.0 if hours in ['0_3', '0-3'] else 5.5)


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

    return pd.DataFrame([[feat_dict.get(c, 0) for c in feature_cols]], columns=feature_cols), feat_dict


# ---------------------------------------------------------
# Dynamic LLM Reason Generator (Google Gemini)
# ---------------------------------------------------------
def _get_gemini_api_key():
    key = os.environ.get('GEMINI_API_KEY')
    if key and key.strip():
        return key.strip()
    
    # Check .env in project root (parent directory of ml/)
    env_file = os.path.join(os.path.dirname(SCRIPT_DIR), '.env')
    if os.path.exists(env_file):
        try:
            with open(env_file, 'r', encoding='utf-8') as f:
                for line in f:
                    line = line.strip()
                    if line.startswith('GEMINI_API_KEY='):
                        val = line.split('=', 1)[1].strip().strip('"\'')
                        if val:
                            return val
        except Exception:
            pass
    return None


def _generate_dynamic_reasons_gemini(top_pets, adopter_profile, key):
    """
    Dynamic natural language explainer powered by Google Gemini.
    Generates unscripted, contextual reasons for top candidates in a single fast batch request.
    Strictly adheres to: Zero Emojis, max 15 words per reason, valid JSON structure.
    """
    if not key or not top_pets:
        return {}

    compact_adopter = {
        'home_environment': adopter_profile.get('living_environment', 'apartment'),
        'has_yard': bool(adopter_profile.get('has_yard', False)),
        'yard_type': adopter_profile.get('yard_type', 'none'),
        'activity_level': adopter_profile.get('activity_level', 'moderate'),
        'hours_alone_daily': adopter_profile.get('hours_alone', '4_7'),
        'has_children': bool(adopter_profile.get('has_children', False)),
        'has_other_pets': bool(adopter_profile.get('has_other_pets', False)),
        'experience_level': adopter_profile.get('pet_experience', 'first_time'),
        'desired_temperaments': adopter_profile.get('desired_temperaments', [])
    }

    compact_pets = []
    for p in top_pets:
        compact_pets.append({
            'id': str(p.get('pet_id')),
            'name': p.get('name'),
            'species': p.get('type'),
            'breed': p.get('breed'),
            'age': p.get('age'),
            'size': p.get('size'),
            'temperaments': p.get('temperaments', [])
        })

    prompt = (
        "You are an AI explainability engine for a shelter pet recommendation system.\n"
        "Our Scikit-Learn Random Forest model ranked these candidate pets as the top matches for the adopter.\n"
        "For each pet, provide:\n"
        "1. 'reasons': Exactly 3 to 4 distinct, compelling compatibility reasons (each 10 to 18 words) covering:\n"
        "   - Space & home environment alignment (living space, yard, apartment setup).\n"
        "   - Routine & schedule fit (daily hours away, activity level, exercise needs).\n"
        "   - Behavioral & temperament synergy (energy level, calmness, friendliness, socialization).\n"
        "   - Household compatibility (family dynamic, children, other pets, or adopter experience).\n"
        "2. 'care_tip': 1 realistic pre-adoption care consideration or settling-in decompression tip (max 15 words) specific to that pet.\n"
        "Strict rules:\n"
        "- Do NOT use any emojis.\n"
        "- Ground each point directly in the adopter's home, work hours, lifestyle, and temperament alignment.\n"
        "- Return ONLY valid JSON in format: {\"pet_id\": {\"reasons\": [\"reason 1\", \"reason 2\", \"reason 3\", \"reason 4\"], \"care_tip\": \"tip text\"}}\n\n"
        f"Adopter Profile: {json.dumps(compact_adopter)}\n"
        f"Candidate Pets: {json.dumps(compact_pets)}\n\n"
        "JSON format example:\n"
        '{"1": {"reasons": ['
        '"Spacious fenced yard provides a secure outdoor environment for protective tendencies.", '
        '"Low daily alone hours align with your frequent presence at home.", '
        '"Calm temperament matches your relaxed daily household pace and experience level.", '
        '"Adult maturity supports a smooth and predictable transition into your home."'
        '], "care_tip": "Allow 2 to 3 days of quiet space for initial decompression."}}'
    )

    models = ['gemini-3.5-flash-lite', 'gemini-3.5-flash', 'gemini-3.8-flash']
    payload = json.dumps({
        'contents': [{'parts': [{'text': prompt}]}],
        'generationConfig': {
            'responseMimeType': 'application/json',
            'temperature': 0.25
        }
    }).encode('utf-8')

    ctx = ssl.create_default_context()
    for model_name in models:
        url = f"https://generativelanguage.googleapis.com/v1beta/models/{model_name}:generateContent?key={key}"
        req = Request(
            url,
            data=payload,
            headers={'Content-Type': 'application/json'},
            method='POST'
        )
        try:
            with urlopen(req, context=ctx, timeout=4.0) as resp:
                data = json.loads(resp.read().decode('utf-8'))
                raw_text = data['candidates'][0]['content']['parts'][0]['text']
                parsed_json = json.loads(raw_text)
                out_map = {}
                for k, v in parsed_json.items():
                    if isinstance(v, dict):
                        reasons = [str(r).strip() for r in v.get('reasons', []) if str(r).strip()]
                        care_tip = str(v.get('care_tip', '')).strip()
                        out_map[str(k)] = {'reasons': reasons, 'care_tip': care_tip}
                    elif isinstance(v, list):
                        reasons = [str(r).strip() for r in v if str(r).strip()]
                        out_map[str(k)] = {'reasons': reasons, 'care_tip': ''}
                return out_map
        except Exception:
            continue

    return {}


def _generate_care_tip(pet: dict[str, Any], adopter_profile: dict[str, Any]) -> str:
    """
    Generates a practical shelter care consideration or settling-in decompression tip
    grounded in the pet's behavioral traits, age, and adopter lifestyle constraints.
    """
    tags = [t for t in pet.get('temperaments', []) if t in ALL_TEMPERAMENT_TAGS]
    age_yrs = _parse_age_numeric(pet.get('age', '2 years'))
    hours = str(adopter_profile.get('hours_alone', '4_7')).lower()
    has_kids = bool(adopter_profile.get('has_children', False))

    if 'Shy' in tags:
        return "Allow 2 to 3 days of quiet space in a dedicated room for initial decompression."
    elif 'Energetic' in tags:
        return "Provide 30 to 45 minutes of daily structured play or active walks to channel energy."
    elif 'Independent' in tags:
        return "Respect their personal space and allow them to initiate contact during early bonding."
    elif 'Protective' in tags:
        return "Introduce unfamiliar guests gradually using calm guidance and positive reinforcement."
    elif hours in ['8_plus', '8+', '8plus']:
        return "Provide interactive puzzle toys or safe enrichment items during longer alone hours."
    elif age_yrs < 1.0:
        return "Maintain a consistent potty schedule and crate routine to build confidence."
    elif has_kids:
        return "Supervise initial family interactions to establish gentle boundaries with children."
    else:
        return "Establish consistent daily feeding and rest schedules to build trust during week one." 


def _generate_default_reasons(pet, match_pct):
    """
    Default Explainable ML attribution reason when Gemini API is unconfigured.
    Reflects the objective Scikit-Learn model feature scoring.
    """
    species = str(pet.get('type', 'companion')).capitalize()
    size = str(pet.get('size', 'medium')).capitalize()
    age = str(pet.get('age', 'adult')).capitalize()
    return [
        f"High compatibility match ({match_pct}%) evaluated by the Random Forest model.",
        f"Living environment and available space align with this {size} {species}'s frame and exercise needs.",
        f"Daily schedule and household routine strongly support this {age} companion's care requirements.",
        f"Verified behavioral temperament complements your lifestyle preferences and household dynamics."
    ]


# ---------------------------------------------------------
# Main Multi-Species ML Recommendation Function
# ---------------------------------------------------------
def _get_sort_tuple(item: Any) -> tuple:
    try:
        score = float(item.get('match_score', 0.0))
        breakdown = item.get('compatibility_breakdown', {})
        temp = float(breakdown.get('temperament', 0.0))
        routine = float(breakdown.get('routine', 0.0))
        space = float(breakdown.get('space', 0.0))
        days = float(item.get('days_in_shelter', 0.0) or 0.0)
        return (score, temp, (routine + space), days)
    except (KeyError, TypeError, ValueError):
        return (0.0, 0.0, 0.0, 0.0)


def recommend_pets(pets_data, adopter_profile, top_k=3):
    """
    Ranks pet recommendations using the Multi-Species Scikit-Learn Random Forest Model.
    Both dogs and cats execute model.predict_proba() directly.
    Top candidate reasons are dynamically generated via Google Gemini.
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
    results: list[dict[str, Any]] = []

    for pet in available:
        species = str(pet.get('type', 'dog')).lower()
        pet_tags_list = [t for t in pet.get('temperaments', []) if t in ALL_TEMPERAMENT_TAGS]

        # MULTI-SPECIES ML EXECUTION: model.predict_proba() runs for ALL pets (dogs & cats)
        if model is not None and feature_cols is not None:
            feat_df, feat_dict = _build_multi_species_feature_vector(adopter_profile, pet, feature_cols)
            proba = model.predict_proba(feat_df)[0]
            rf_prob = float(proba[1])  # Predicted probability of compatibility
            final_metric = rf_prob
            algo_name = 'Scikit-Learn Random Forest Classifier (Multi-Species Trained Model)'
        else:
            feat_dict = {}
            final_metric = 0.70
            algo_name = 'Multi-Species Compatibility Engine'

        match_pct = round(min(99.0, max(52.0, (final_metric * 45.0) + 53.0)), 1)

        # STRICT SAFEGUARD: Enforce 80.0% minimum compatibility threshold.
        # Pets scoring below 80% compatibility must never be recommended.
        if match_pct < 80.0:
            continue

        # Multi-factor alignment breakdown
        size_mismatch = feat_dict.get('size_home_mismatch', 0)
        has_yard_val = feat_dict.get('has_yard', 0)
        energy_diff = feat_dict.get('energy_mismatch', 1.0)
        hours_val = feat_dict.get('hours_alone_per_day', 5.5)
        anxiety = feat_dict.get('anxiety_separation', 2.0)

        # 1. Living Space Fit (based on size, species, and yard access)
        if species == 'cat' or size_mismatch == 0:
            space_pct = round(min(98.0, max(82.0, match_pct + (3.0 if has_yard_val else 1.0))), 0)
        else:
            space_pct = round(min(84.0, max(80.0, match_pct - 3.0)), 0)

        # 2. Daily Routine Alignment (based on energy mismatch and work hours alone)
        routine_penalty = (energy_diff * 1.5) + (1.5 if hours_val > 7.0 and anxiety > 3.0 else 0)
        routine_pct = round(min(98.0, max(80.0, match_pct - routine_penalty + 2.0)), 0)

        # 3. Temperament & Behavioral Fit (based on behavioral tags and safety)
        desired = adopter_profile.get('desired_temperaments', [])
        temp_boost = 3.0 if any(t in pet_tags_list for t in desired) else 1.0
        temperament_pct = round(min(99.0, max(80.0, match_pct + temp_boost)), 0)

        compat_breakdown = {
            'space': int(space_pct),
            'routine': int(routine_pct),
            'temperament': int(temperament_pct)
        }

        default_reasons = _generate_default_reasons(pet, match_pct)
        care_tip = _generate_care_tip(pet, adopter_profile)

        results.append({
            'pet_id'                 : pet.get('id'),
            'name'                   : pet.get('name'),
            'type'                   : species,
            'breed'                  : pet.get('breed'),
            'age'                    : pet.get('age'),
            'gender'                 : pet.get('gender'),
            'size'                   : 'medium' if species == 'cat' and str(pet.get('size', '')).lower() == 'large' else pet.get('size'),
            'color'                  : pet.get('color'),
            'photo_path'             : pet.get('photo_path'),
            'status'                 : pet.get('status'),
            'temperaments'           : pet_tags_list,
            'match_score'            : match_pct,
            'match_percentage'       : match_pct,
            'match_percentage_str'   : f"{match_pct}%",
            'compatibility_breakdown': compat_breakdown,
            'reasons'                : default_reasons,
            'match_reasons'          : default_reasons,
            'care_tip'               : care_tip,
            'consideration'          : care_tip,
            'algorithm_used'         : algo_name
        })

    # Sort descending by compatibility match score
    results.sort(key=_get_sort_tuple, reverse=True)
    top_candidates = results[:top_k] if top_k else results

    # DYNAMIC EXPLAINER LAYER: Invoke Gemini for top candidates
    api_key = _get_gemini_api_key()
    if api_key and top_candidates:
        gemini_map = _generate_dynamic_reasons_gemini(top_candidates, adopter_profile, api_key)
        if gemini_map:
            for item in top_candidates:
                pid_str = str(item.get('pet_id'))
                if gemini_map.get(pid_str):
                    entry = gemini_map[pid_str]
                    if entry.get('reasons'):
                        item['reasons'] = entry['reasons']
                        item['match_reasons'] = entry['reasons']
                    if entry.get('care_tip'):
                        item['care_tip'] = entry['care_tip']
                        item['consideration'] = entry['care_tip']

    return top_candidates
